<?php

namespace App\Livewire\User;

use Livewire\Attributes\On;
use App\Events\TransactionsUpdated;
use App\Models\Event;
use App\Models\Transaction as ModelTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Flux\Flux;

class Transaction extends Component
{
    use WithPagination;

    public $amount;
    public $note;
    public $receiver_id;
    public $admin;

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
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    private function user()
    {
        return Auth::user();
    }

    /**
     * Get current ongoing event (if any)
     */
    private function currentEvent(): ?Event
    {
        return Event::where('status', 'ongoing')->latest()->first();
    }

    /**
     * Get this user's cash for a specific event (from pivot)
     */
    private function getUserEventCash(Event $event, int $userId): float
    {
        $eventUser = $event->users()
            ->where('user_id', $userId)
            ->first();

        return (float) ($eventUser?->pivot->cash ?? 0);
    }

    /**
     * Adjust this user's cash for a specific event (± delta)
     */
    private function adjustUserEventCash(Event $event, int $userId, float $delta): void
    {
        $currentCash = $this->getUserEventCash($event, $userId);
        $newCash     = $currentCash + $delta;

        $event->users()->syncWithoutDetaching([
            $userId => ['cash' => $newCash],
        ]);
    }

    private function findTransactionForReceiver($id)
    {
        return ModelTransaction::where('id', $id)
            ->where('receiver_id', $this->user()->id)
            ->first();
    }

    /**
     * USER → ADMIN: send cash up to admin (event-based)
     */
    public function createTransaction()
    {
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

        // ✅ Check event-based cash instead of users.cash
        $currentCash = $this->getUserEventCash($event, $user->id);

        if ($currentCash < $this->amount) {
            $this->failAndClose('transfer', 'Insufficient balance.');
            return;
        }

        // ✅ Deduct from user's event cash (pivot)
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
     * ADMIN → USER: user receives money that admin sent
     * (admin created it in admin panel; here user "claims" it)
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

    /**
     * Cancel a transaction where THIS user is the receiver
     * (usually admin → user pending transfer)
     */
    public function cancelTransaction($id)
    {
        $transaction = $this->findTransactionForReceiver($id);
        if (!$transaction || $transaction->status !== 'pending') {
            Toaster::error('Transaction cannot be cancelled.');
            return;
        }

        $event = Event::find($transaction->event_id);
        if ($event) {
            // Same logic as before: just fix the event side
            $event->increment('revolving', $transaction->amount);
            $event->decrement('total_transfer', $transaction->amount);
        }

        $transaction->update(['status' => 'cancelled']);
        broadcast(new TransactionsUpdated($transaction->event_id));

        Toaster::success(
            'Transaction of ' . number_format($transaction->amount, 2) . ' cancelled.'
        );
    }

    private function failAndClose($modal, $message)
    {
        Toaster::error($message);
        Flux::modal($modal)->close();
    }

    public function render()
    {
        $transactions = ModelTransaction::with(['sender', 'receiver'])
            ->where('receiver_id', $this->user()->id)
            ->latest()
            ->paginate(10);

        return view('livewire.user.transaction', compact('transactions'));
    }
}
