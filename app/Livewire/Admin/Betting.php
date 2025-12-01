<?php

namespace App\Livewire\Admin;

use Masmerise\Toaster\Toaster;
use Livewire\Component;
use App\Models\Event;

class Betting extends Component
{
    public $event;
    public $bets;

    public $total_bets = 0;
    public $total_payout = 0;

    public $teller_name = '';
    public $ticket_number = '';
    public $fight = 'all';
    public $side = 'all';
    public $status = 'all';

    public function search()
    {
        $this->render();
    }

    public function clearFilters()
    {
        $this->teller_name = '';
        $this->ticket_number = '';
        $this->fight = 'all';
        $this->side = 'all';
        $this->status = 'all';
        $this->search();
    }

    public function allPropertiesEmpty()
    {
        return empty($this->teller_name) &&
            empty($this->ticket_number) &&
            $this->fight === 'all' &&
            $this->side === 'all' &&
            $this->status === 'all';
    }

    public function lockBet($betId)
    {
        $bet = $this->event->bets()->find($betId);
        if (!$bet->is_win) {
            Toaster::error('Bet can only be locked if it is a winning bet.');
            return;
        }

        $bet->is_lock = true;
        $bet->save();
        Toaster::success('Bet locked successfully.');
    }

    public function unlockBet($betId)
    {
        $bet = $this->event->bets()->find($betId);
        $bet->is_lock = false;
        $bet->save();
        Toaster::success('Bet unlocked successfully.');
    }

    public function render()
    {
        $this->event = Event::where('status', 'ongoing')
            ->latest()
            ->first();

        if (!$this->event) {
            return view('livewire.admin.betting', [
                'bets' => collect(),
                'total_bets' => 0,
                'total_payout' => 0,
            ]);
        }

        $query = $this->event->bets()->with(['fight', 'user', 'claimedBy'])->orderBy('fight_id')->orderBy('id', 'asc');

        if ($this->teller_name) {
            $query->whereHas('user', function ($q) {
                $q->where('username', 'like', '%' . $this->teller_name . '%');
            });
        }

        if ($this->ticket_number) {
            $query->where('ticket_no', 'like', '%' . $this->ticket_number . '%');
        }

        if ($this->fight !== 'all') {
            $query->whereHas('fight', function ($q) {
                $q->where('fight_number', $this->fight);
            });
        }

        if ($this->side !== 'all') {
            $query->where('side', $this->side);
        }

        if ($this->status !== 'all') {
            $query->where('bets.status', $this->status);
        }

        $this->bets = $query->get();

        $this->total_bets = $this->bets->sum('amount');
        $this->total_payout = $this->bets->where('is_win', true)->sum('payout_amount');

        return view('livewire.admin.betting', [
            'bets' => $this->bets,
            'total_bets' => $this->total_bets,
            'total_payout' => $this->total_payout,
        ]);
    }
}
