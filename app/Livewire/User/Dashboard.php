<?php

namespace App\Livewire\User;

use App\Events\BetPlaced;
use App\Models\Bet;
use App\Models\Fight;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;

class Dashboard extends Component
{
    public $cashOnHand;
    public $amount = 0;
    public $activeFight = null;
    public $bets = [];
    public $fights = [];
    public $fight_id;

    public function mount()
    {
        $this->cashOnHand = Auth::user()->cash ?? 0;
        $this->fights = Fight::latest()->get();
        $this->loadActiveFight();
        $this->loadUserBets();
    }

    private function loadActiveFight()
    {
        $this->activeFight = Fight::whereHas('event', function ($q) {
            $q->where('status', 'ongoing');
        })
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    public function updatedFightId()
    {
        $this->loadUserBets();
    }

    private function loadUserBets()
    {
        $query = Bet::with('fight')
            ->where('user_id', Auth::id())
            ->latest();

        if ($this->fight_id) {
            $query->where('fight_id', $this->fight_id);
        }

        $this->bets = $query->take(10)->get();
    }

    public function refreshFights()
    {
        $this->fights = Fight::latest()->get();
    }

    public function addAmount($value)
    {
        $this->amount += (int) str_replace(',', '', $value);
    }

    public function clearAmount()
    {
        $this->amount = 0;
    }

    public function placeBet($side)
    {
        $user = Auth::user();

        if (!$this->activeFight) {
            Toaster::error('No active fight open for betting.');
            return;
        }

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side selected.');
            return;
        }

        // 🛑 Check if the side is locked
        if (!$this->activeFight->$side) {
            Toaster::error(ucfirst($side) . ' side is locked. You cannot bet here.');
            return;
        }

        if ($this->activeFight->status !== 'open') {
            Toaster::error('Betting is not open at the moment.');
            return;
        }

        if ($this->amount <= 0) {
            Toaster::error('Enter a valid amount to bet.');
            return;
        }

        if ($user->cash < $this->amount) {
            Toaster::error('Insufficient cash to place bet.');
            return;
        }

        // Deduct from user cash
        $user->decrement('cash', $this->amount);

        // Add bet to fight totals
        if ($side === 'meron') {
            $this->activeFight->increment('meron_bet', $this->amount);
        } else {
            $this->activeFight->increment('wala_bet', $this->amount);
        }

        // Save bet record
        Bet::create([
            'user_id' => $user->id,
            'fight_id' => $this->activeFight->id,
            'side' => $side,
            'amount' => $this->amount,
        ]);

        // 🔊 Broadcast new totals
        broadcast(new BetPlaced($this->activeFight->fresh()));

        // Reset amount and update balance
        $this->amount = 0;
        $this->cashOnHand = $user->fresh()->cash;
        $this->loadUserBets();

        Toaster::success('Bet placed successfully!');
    }

    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
