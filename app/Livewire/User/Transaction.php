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
        $admin = User::role('admin')->first();

        if ($admin) {
            $this->admin = $admin;
            $this->receiver_id = $admin->id;
        }
    }

    public function createTransaction()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        if ($user->cash < $this->amount) {
            Toaster::error('Insufficient balance.');
            Flux::modal('transfer')->close();
            return;
        }

        $event = \App\Models\Event::where('status', 'ongoing')->latest()->first();

        if (! $event) {
            Toaster::error('No ongoing event found.');
            return;
        }

        $user->decrement('cash', $this->amount);

        ModelTransaction::create([
            'event_id' => $event->id,
            'sender_id' => $user->id,
            'receiver_id' => $this->receiver_id,
            'amount' => $this->amount,
            'note' => $this->note,
            'status' => 'pending',
        ]);

        $this->reset(['amount', 'note']);
        Toaster::success('Transaction successfully sent to admin.');
        Flux::modal('transfer')->close();
    }

    public function receiveTransaction($id)
    {
        $transaction = ModelTransaction::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$transaction) {
            Toaster::error('Invalid or already received transaction.');

            return;
        }

        $transaction->update(['status' => 'success']);
        $user = Auth::user();
        $user->increment('cash', $transaction->amount);

        Toaster::success('You have successfully received ₱' . number_format($transaction->amount, 2));
    }

    public function cancelTransaction($id)
    {
        $transaction = ModelTransaction::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$transaction) {
            Toaster::error('Invalid or already received transaction.');
            return;
        }

        $event = Event::find($transaction->event_id);

        if ($event) {
            $event->increment('revolving', $transaction->amount);
            $event->decrement('total_transfer', $transaction->amount);
        }

        $transaction->update(['status' => 'cancelled']);

        Toaster::success('You have successfully cancelled the transaction of ₱' . number_format($transaction->amount, 2));
    }

    public function render()
    {
        $transactions = ModelTransaction::with(['sender', 'receiver'])
            ->where('receiver_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.user.transaction', [
            'transactions' => $transactions,
        ]);
    }
}
