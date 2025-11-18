<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Transactions extends Component
{
    public $event;
    public $users = [];
    public $amount;
    public $receiver_id;
    public $note;

    public $userToAdminTransactions;
    public $adminToUserTransactions;

    public $totalTransfer = 0;

    public function mount()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();
        $this->users = User::role('user')->orderBy('username')->get();

        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        if ($this->event) {
            $this->userToAdminTransactions = Transaction::with('sender', 'receiver')
                ->where('event_id', $this->event->id)
                ->where('receiver_id', Auth::id())
                ->latest()
                ->get();

            $this->adminToUserTransactions = Transaction::with('sender', 'receiver')
                ->where('event_id', $this->event->id)
                ->where('sender_id', Auth::id())
                ->latest()
                ->get();
        } else {
            $this->userToAdminTransactions = collect();
            $this->adminToUserTransactions = collect();
        }
    }

    public function createTransaction()
    {
        if (! $this->event) {
            Toaster::error('You cannot transfer while there is no ongoing event.');
            Flux::modal('transfer')->close();
            return;
        }

        $this->validate([
            'amount' => 'required|numeric|min:1',
            'receiver_id' => 'required|exists:users,id',
            'note' => 'required|string|max:255',
        ]);

        if ($this->event->revolving < $this->amount) {
            Toaster::error('Insufficient revolving funds.');
            return;
        }

        $this->event->decrement('revolving', $this->amount);

        Transaction::create([
            'event_id' => $this->event->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->receiver_id,
            'amount' => $this->amount,
            'note' => $this->note,
        ]);

        $this->event->increment('total_transfer', $this->amount);
        $this->totalTransfer = $this->event->fresh()->total_transfer;

        $this->reset(['amount', 'receiver_id', 'note']);
        $this->loadTransactions();
        Flux::modal('transfer')->close();
        Toaster::success('Transaction successfully created.');
    }

    public function calculateTotals()
    {
        $this->totalTransfer = $this->event ? $this->event->total_transfer : 0;
    }

    public function receiveTransaction($id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            Toaster::error('Invalid or already received transaction.');
            return;
        }

        $transaction->update(['status' => 'success']);

        if ($this->event) {
            $this->event->increment('revolving', $transaction->amount);
            $this->event = $this->event->fresh();
        }

        $this->loadTransactions();
        Toaster::success('Transaction successfully received.');
    }

    public function render()
    {
        $transactions = $this->event
            ? Transaction::with(['sender', 'receiver'])
            ->where('event_id', $this->event->id)
            ->where('receiver_id', Auth::id()) // only admin transactions
            ->latest()
            ->get()
            : collect();

        return view('livewire.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
