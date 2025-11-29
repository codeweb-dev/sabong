<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use App\Models\Transaction;
use Livewire\Component;
use App\Models\Event;
use App\Models\User;
use Flux\Flux;

class Transactions extends Component
{
    public ?Event $event = null;
    public $users = [];
    public $amount;
    public $receiver_id;
    public $note;

    public $userToAdminTransactions;
    public $adminToUserTransactions;

    public $totalTransfer = 0;
    public $totalReceived = 0;

    public function mount()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();
        $this->users = User::role('user')->orderBy('username')->get();
        $this->loadTransactions();
        $this->updateTotals();
    }

    private function loadTransactions()
    {
        if (!$this->event) {
            $this->userToAdminTransactions = collect();
            $this->adminToUserTransactions = collect();
            return;
        }

        $eventId = $this->event->id;
        $adminId = Auth::id();

        $this->userToAdminTransactions = Transaction::with(['sender', 'receiver'])
            ->where('event_id', $eventId)
            ->where('receiver_id', $adminId)
            ->latest()
            ->get();

        $this->adminToUserTransactions = Transaction::with(['sender', 'receiver'])
            ->where('event_id', $eventId)
            ->where('sender_id', $adminId)
            ->latest()
            ->get();
    }

    private function updateTotals()
    {
        $this->totalTransfer = $this->event?->total_transfer ?? 0;

        $this->totalReceived = $this->userToAdminTransactions
            ->where('status', 'success')
            ->sum('amount');
    }


    public function createTransaction()
    {
        if (!$this->event) {
            $this->errorAndClose('You cannot transfer while there is no ongoing event.');
            return;
        }

        $this->validate([
            'amount'       => 'required|numeric|min:1',
            'receiver_id'  => 'required|exists:users,id',
            'note'         => 'required|string|max:255',
        ]);

        if ($this->event->revolving < $this->amount) {
            Toaster::error('Insufficient revolving funds.');
            return;
        }

        $this->event->decrement('revolving', $this->amount);

        Transaction::create([
            'event_id'    => $this->event->id,
            'sender_id'   => Auth::id(),
            'receiver_id' => $this->receiver_id,
            'amount'      => $this->amount,
            'note'        => $this->note,
        ]);

        $this->event->increment('total_transfer', $this->amount);
        $this->event->refresh();

        $this->reset(['amount', 'receiver_id', 'note']);
        $this->loadTransactions();
        $this->updateTotals();

        Flux::modal('transfer')->close();
        Toaster::success('Transaction successfully created.');
    }

    public function receiveTransaction($id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$transaction) {
            return Toaster::error('Invalid or already received transaction.');
        }

        $transaction->update(['status' => 'success']);

        if ($this->event) {
            $this->event->increment('revolving', $transaction->amount);
            $this->event->refresh();
        }

        $this->loadTransactions();
        $this->updateTotals();
        Toaster::success('Transaction successfully received.');
    }

    private function errorAndClose($message)
    {
        Toaster::error($message);
        Flux::modal('transfer')->close();
        return;
    }

    public function render()
    {
        $transactions = $this->event
            ? Transaction::with(['sender', 'receiver'])
            ->where('event_id', $this->event->id)
            ->latest()
            ->get()
            : collect();

        $eventId = $this->event?->id;

        $users = User::role('user')->get();

        $userSummaries = $users->map(function ($user) use ($eventId) {
            $cashIn = $user->receivedTransactions->sum('amount');
            $cashOut = $user->sentTransactions->sum('amount');

            $totalPayout = $user->bets()
                ->whereHas('fight', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->where('is_win', true)
                ->sum('payout_amount');

            $totalBets = $user->bets()
                ->whereHas('fight', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->sum('amount');

            return [
                'user' => $user,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'total_bets' => $totalBets,
                'total_payout' => $totalPayout,
            ];
        });

        return view('livewire.admin.transactions', compact('transactions', 'userSummaries'));
    }
}
