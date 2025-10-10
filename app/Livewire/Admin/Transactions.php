<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    public $event;
    public $users = [];
    public $amount;
    public $receiver_id;
    public $note;

    public $totalTransfer = 0; // note dito ba is once na status is success lahat ng total transfer makikita o pending lang din?

    public function mount()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();
        $this->users = User::role('user')->orderBy('username')->get();
    }

    public function updating()
    {
        $this->resetPage();
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
        if ($this->event) {
        $transactions = Transaction::with(['receiver', 'sender'])
                ->where('event_id', $this->event->id)
                ->latest()
                ->paginate(2);
        } else {
            $transactions = new LengthAwarePaginator([], 0, 2);
        }

        return view('livewire.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
