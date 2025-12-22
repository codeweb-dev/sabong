<?php

namespace App\Livewire\User;

use Livewire\Attributes\On;
use App\Events\TransactionsUpdated;
use App\Models\Event;
use App\Models\Fight;
use App\Models\Transaction as ModelTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TellerReportExport;
use Flux\Flux;

class Transaction extends Component
{
    use WithPagination;

    public $amount;
    public $note;
    public $receiver_id;
    public $admin;

    /**
     * Use separate pagination names (important when you have 2 paginators)
     */
    public $receivedPage = 1;
    public $sentPage = 1;

    public function mount()
    {
        $this->admin = User::role('admin')->first();

        if ($this->admin) {
            $this->receiver_id = $this->admin->id;
        }
    }

    #[On('echo:transactions,.transactions.updated')]
    public function handleTransactionsUpdated($data)
    {
        // refresh both paginators
        $this->resetPage('receivedPage');
        $this->resetPage('sentPage');
        $this->dispatch('$refresh');
    }

    private function user()
    {
        return Auth::user();
    }

    public function downloadReport()
    {
        $event = $this->currentEvent();

        if (!$event) {
            Toaster::error('No ongoing event found.');
            return;
        }

        Toaster::info('Preparing your report...');

        return Excel::download(
            new TellerReportExport(Auth::id(), $event->id),
            'teller-report-' . $event->id . '-' . Auth::id() . '.xlsx'
        );
    }

    private function currentEvent(): ?Event
    {
        return Event::where('status', 'ongoing')->latest()->first();
    }

    private function getUserEventCash(Event $event, int $userId): float
    {
        $eventUser = $event->users()
            ->where('user_id', $userId)
            ->first();

        return (float) ($eventUser?->pivot->cash ?? 0);
    }

    private function adjustUserEventCash(Event $event, int $userId, float $delta): void
    {
        $currentCash = $this->getUserEventCash($event, $userId);
        $newCash     = $currentCash + $delta;

        $event->users()->syncWithoutDetaching([
            $userId => ['cash' => $newCash],
        ]);
    }

    /**
     * Find transaction where current user is receiver
     */
    private function findTransactionForReceiver($id)
    {
        return ModelTransaction::where('id', $id)
            ->where('receiver_id', $this->user()->id)
            ->first();
    }

    /**
     * USER → ADMIN (send money to admin)
     */
    public function createTransaction()
    {
        $cleanAmount = str_replace([',', ' '], '', $this->amount ?? '');
        $this->amount = $cleanAmount;

        $this->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'required|string|max:255',
        ]);

        $user  = $this->user();
        $event = $this->currentEvent();

        if (!$event) {
            $this->failAndClose('transfer', 'No ongoing event found.');
            return;
        }

        if (!$this->admin) {
            $this->failAndClose('transfer', 'Admin not found.');
            return;
        }

        $currentCash = $this->getUserEventCash($event, $user->id);

        if ($currentCash < $this->amount) {
            $this->failAndClose('transfer', 'Insufficient balance.');
            return;
        }

        // deduct user cash (event pivot)
        $this->adjustUserEventCash($event, $user->id, -$this->amount);

        ModelTransaction::create([
            'event_id'    => $event->id,
            'sender_id'   => $user->id,
            'receiver_id' => $this->receiver_id,
            'amount'      => $this->amount,
            'note'        => $this->note,
            'status'      => 'pending',
        ]);

        $this->reset(['amount', 'note']);
        broadcast(new TransactionsUpdated($event->id));
        Toaster::success('Transaction successfully sent.');
        Flux::modal('transfer')->close();
    }

    /**
     * ADMIN → USER (user claims it)
     */
    public function receiveTransaction($id)
    {
        $transaction = $this->findTransactionForReceiver($id);

        if (!$transaction || $transaction->status !== 'pending') {
            Toaster::error('Invalid or already processed transaction.');
            return;
        }

        $event = Event::find($transaction->event_id);

        if (!$event) {
            Toaster::error('Related event not found.');
            return;
        }

        $transaction->update(['status' => 'success']);
        $this->adjustUserEventCash($event, $this->user()->id, $transaction->amount);

        broadcast(new TransactionsUpdated($transaction->event_id));
        Toaster::success('You received ' . number_format($transaction->amount, 2));
    }

    public function cancelTransaction($id)
    {
        $transaction = $this->findTransactionForReceiver($id);

        if (!$transaction || $transaction->status !== 'pending') {
            Toaster::error('Transaction cannot be cancelled.');
            return;
        }

        $event = Event::find($transaction->event_id);
        if ($event) {
            $event->increment('revolving', $transaction->amount);
            $event->decrement('total_transfer', $transaction->amount);
        }

        $transaction->update(['status' => 'cancelled']);
        broadcast(new TransactionsUpdated($transaction->event_id));

        Toaster::success('Transaction of ' . number_format($transaction->amount, 2) . ' cancelled.');
    }

    private function failAndClose($modal, $message)
    {
        Toaster::error($message);
        Flux::modal($modal)->close();
    }

    private function getEventCash()
    {
        $event = Fight::whereHas('event', fn($q) => $q->where('status', 'ongoing'))
            ->first()
            ?->event;

        if (!$event) {
            return 0;
        }

        return $event->users()
            ->where('user_id', Auth::id())
            ->first()
            ?->pivot->cash ?? 0;
    }

    private function findTransactionForSender($id)
    {
        return ModelTransaction::where('id', $id)
            ->where('sender_id', $this->user()->id)
            ->first();
    }

    public function cancelSentTransaction($id)
    {
        $transaction = $this->findTransactionForSender($id);

        if (
            !$transaction ||
            $transaction->status !== 'pending' ||
            ($this->admin && $transaction->receiver_id !== $this->admin->id)
        ) {
            Toaster::error('Transaction cannot be cancelled.');
            return;
        }

        $event = Event::find($transaction->event_id);

        if (!$event) {
            Toaster::error('Related event not found.');
            return;
        }

        $this->adjustUserEventCash($event, $this->user()->id, +$transaction->amount);
        $transaction->update(['status' => 'cancelled']);
        broadcast(new TransactionsUpdated($transaction->event_id));
        Toaster::success('Cancelled and refunded ' . number_format($transaction->amount, 2));
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->resetPage('receivedPage');
        $this->resetPage('sentPage');

        Flux::modal('transfer')->close();
        Flux::modal('received')->close();

        $this->dispatch('$refresh');
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        $this->resetPage('receivedPage');
        $this->resetPage('sentPage');

        Flux::modal('transfer')->close();
        Flux::modal('received')->close();

        $this->dispatch('$refresh');
    }

    public function render()
    {
        $event   = $this->currentEvent();
        $eventId = $event?->id;

        if (!$eventId) {
            return view('livewire.user.transaction', [
                'receivedTransactions' => collect(),
                'sentTransactions'     => collect(),
                'coh'                  => 0,
                'hasOngoingEvent'      => false,
            ]);
        }

        $coh = $this->getEventCash();

        $sentTransactions = ModelTransaction::with(['sender', 'receiver'])
            ->where('event_id', $eventId)
            ->where('sender_id', $this->user()->id)
            ->latest()
            ->get();

        $receivedTransactions = ModelTransaction::with(['sender', 'receiver'])
            ->where('event_id', $eventId)
            ->where('receiver_id', $this->user()->id)
            ->latest()
            ->get();

        return view('livewire.user.transaction', [
            'receivedTransactions' => $receivedTransactions,
            'sentTransactions'     => $sentTransactions,
            'coh'                  => $coh,
            'hasOngoingEvent'      => true,
        ]);
    }
}
