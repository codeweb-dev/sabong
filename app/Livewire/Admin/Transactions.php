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

    public $totalTransfer = 0;

    public function mount()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();
        $this->users = User::role('user')->orderBy('username')->get();
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
            'note' => 'nullable|string|max:255',
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
        Flux::modal('transfer')->close();
        Toaster::success('Transaction successfully created.');
    }

    public function calculateTotals()
    {
        $this->totalTransfer = $this->event ? $this->event->total_transfer : 0;
    }

    public function render()
    {
        $transactions = $this->event
            ? Transaction::with(['receiver', 'sender'])
            ->where('event_id', $this->event->id)
            ->latest()
            ->get()
            : collect();

        return view('livewire.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
