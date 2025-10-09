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

        Transaction::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->receiver_id,
            'amount' => $this->amount,
            'note' => $this->note,
        ]);

        $this->reset(['amount', 'receiver_id', 'note']);
        Flux::modal('transfer')->close();
        Toaster::success('Transaction successfully created.');
    }

    public function render()
    {
        $transactions = Transaction::with(['receiver', 'sender'])
            ->latest()
            ->get();

        return view('livewire.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
