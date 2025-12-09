<?php

namespace App\Livewire\User;

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

    private function user()
    {
        return Auth::user();
    }

    private function findTransactionForReceiver($id)
    {
        return ModelTransaction::where('id', $id)
            ->where('receiver_id', $this->user()->id)
            ->first();
    }

    public function createTransaction()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'required|string|max:255',
        ]);

        $user = $this->user();
        if ($user->cash < $this->amount) {
            $this->failAndClose('transfer', 'Insufficient balance.');
            return;
        }

        $event = Event::where('status', 'ongoing')->latest()->first();
        if (!$event) {
            $this->failAndClose('transfer', 'No ongoing event found.');
            return;
        }

        $user->decrement('cash', $this->amount);

        ModelTransaction::create([
            'event_id'    => $event->id,
            'sender_id'   => $user->id,
            'receiver_id' => $this->receiver_id,
            'amount'      => $this->amount,
            'note'        => $this->note,
            'status'      => 'pending',
        ]);

        $this->reset(['amount', 'note']);
        Toaster::success('Transaction successfully sent.');
        Flux::modal('transfer')->close();
    }

    public function receiveTransaction($id)
    {
        $transaction = $this->findTransactionForReceiver($id);

        if (!$transaction || $transaction->status !== 'pending') {
            Toaster::error('Invalid or already processed transaction.');
            return;
        }

        $transaction->update(['status' => 'success']);
        $this->user()->increment('cash', $transaction->amount);

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

        Toaster::success(
            'Transaction of ₱' . number_format($transaction->amount, 2) . ' cancelled.'
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
