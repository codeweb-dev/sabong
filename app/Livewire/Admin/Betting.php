<?php

namespace App\Livewire\Admin;

use Masmerise\Toaster\Toaster;
use Livewire\Attributes\On;
use App\Events\BetsUpdated;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Event;

class Betting extends Component
{
    use WithPagination;

    public $event;

    public $total_bets = 0;
    public $total_payout = 0;
    public $total_refund = 0;
    public $total_unpaid = 0;
    public $total_short = 0;

    public $teller_name = '';
    public $ticket_number = '';
    public $fight = 'all';
    public $side = 'all';
    public $status = 'all';

    #[On('echo:bets,.bets.updated')]
    public function handleBetsUpdated($data)
    {
        if ($this->event && ($data['eventId'] ?? null) !== $this->event->id) {
            return;
        }

        $this->resetPage(); // optional but nice
        $this->dispatch('$refresh');
    }

    public function allPropertiesEmpty()
    {
        return empty($this->teller_name) &&
            empty($this->ticket_number) &&
            $this->fight === 'all' &&
            $this->side === 'all' &&
            $this->status === 'all';
    }

    public function updated($property)
    {
        // whenever any filter changes, go back to page 1
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->teller_name = '';
        $this->ticket_number = '';
        $this->fight = 'all';
        $this->side = 'all';
        $this->status = 'all';

        $this->resetPage();
    }

    public function lockBet($betId)
    {
        $bet = $this->event->bets()->find($betId);

        if (!$bet->is_win) {
            Toaster::error('Bet can only be locked if it is a winning bet.');
            return;
        }

        if ($bet->status == 'paid') {
            Toaster::error('Only unclaimed bets can be locked.');
            return;
        }

        $bet->is_lock = true;
        $bet->save();

        broadcast(new BetsUpdated($this->event->id));
        Toaster::success('Bet locked successfully.');
    }

    public function unlockBet($betId)
    {
        $bet = $this->event->bets()->find($betId);

        $bet->is_lock = false;
        $bet->save();

        broadcast(new BetsUpdated($this->event->id));
        Toaster::success('Bet unlocked successfully.');
    }

    public function render()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();

        if (!$this->event) {
            return view('livewire.admin.betting', [
                'bets' => collect(),
                'total_bets' => 0,
                'total_payout' => 0,
                'total_unpaid' => 0,
                'total_short' => 0,
            ]);
        }

        $query = $this->event->bets()
            ->with(['fight', 'user', 'claimedBy'])
            ->orderBy('created_at', 'desc');

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

        // totals must be computed from the FULL filtered query (not just page 1)
        $allBets = (clone $query)->get();

        $this->total_bets   = $allBets->sum('amount');
        $this->total_payout = $allBets->where('is_win', true)->where('status', 'paid')->sum('payout_amount');
        $this->total_refund = $allBets->where('is_win', false)->where('status', 'refund')->sum('short_amount');
        $this->total_unpaid = $allBets->where('is_win', true)->where('status', 'unpaid')->sum('payout_amount');
        $this->total_short  = $allBets->where('is_win', false)->where('status', 'short')->sum('short_amount');

        // paginate (DO NOT store in $this->bets)
        $bets = $query->simplePaginate(5);

        return view('livewire.admin.betting', [
            'bets' => $bets,
            'total_bets' => $this->total_bets,
            'total_payout' => $this->total_payout,
            'total_refund' => $this->total_refund,
            'total_unpaid' => $this->total_unpaid,
            'total_short' => $this->total_short,
        ]);
    }
}
