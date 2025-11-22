<?php

namespace App\Livewire\Declarator;

use App\Events\FightUpdated;
use App\Models\Bet;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;
use Masmerise\Toaster\Toaster;

class Dashboard extends Component
{
    public $currentEvent = null;
    public $fights = [];
    public $activeFight = null;

    public $fighterAName = '';
    public $fighterBName = '';

    public function mount()
    {
        $this->loadOngoingEvent();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
        $this->loadFighterNames();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->currentEvent = null;
            $this->fights = [];
            $this->activeFight = null;
            $this->fighterAName = '';
            $this->fighterBName = '';
        }
    }

    private function loadOngoingEvent()
    {
        $this->currentEvent = Event::where('status', 'ongoing')
            ->latest()
            ->with('fights')
            ->first();

        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
        $this->loadFighterNames();
    }

    public function getActiveFight()
    {
        if (! $this->currentEvent) return null;

        return $this->currentEvent->fights()
            ->whereIn('status', ['pending', 'start', 'open', 'close'])
            ->orderBy('fight_number')
            ->first();
    }

    public function openBet()
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight. Start one first.');
            return;
        }

        if ($this->activeFight->status !== 'start') {
            Toaster::error('Fight must be started before opening bets.');
            return;
        }

        $this->activeFight->update(['status' => 'open']);
        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($this->activeFight));
    }

    public function startFight()
    {
        $ongoingFight = $this->currentEvent?->fights()->where('status', 'start')->first();
        if ($ongoingFight) {
            Toaster::error('Another fight is already ongoing. End it first.');
            return;
        }

        $fight = $this->getActiveFight();
        if (! $fight) {
            Toaster::error('No pending fights to start.');
            return;
        }

        $fight->update(['status' => 'start']);
        $this->activeFight = $fight;
        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($fight));
    }

    public function addFighterName($side)
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight. Start one first.');
            return;
        }

        if ($side === 'a') {
            $this->activeFight->update(['fighter_a' => $this->fighterAName]);
        } else {
            $this->activeFight->update(['fighter_b' => $this->fighterBName]);
        }

        $this->fights = $this->currentEvent->fresh()->fights;
        $this->loadFighterNames();
        broadcast(new FightUpdated($this->activeFight));
    }

    private function loadFighterNames()
    {
        $this->fighterAName = $this->activeFight?->fighter_a ?? '';
        $this->fighterBName = $this->activeFight?->fighter_b ?? '';
    }

    public function lockSide($side)
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight.');
            return;
        }

        if ($this->activeFight->status === 'close') {
            Toaster::error('Cannot lock — betting is closed.');
            return;
        }

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side.');
            return;
        }

        $this->activeFight->update([$side => false]);
        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($this->activeFight));
    }

    public function unlockSide($side)
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight.');
            return;
        }

        if ($this->activeFight->status === 'close') {
            Toaster::error('Cannot unlock — betting is closed.');
            return;
        }

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side.');
            return;
        }

        $this->activeFight->update([$side => true]);
        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($this->activeFight));
    }

    public function closeBet()
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight to close.');
            return;
        }

        if ($this->activeFight->status !== 'open') {
            Toaster::error('You can only close a fight that is currently open.');
            return;
        }

        $this->activeFight->update([
            'meron' => false,
            'wala' => false,
            'status' => 'close',
        ]);

        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($this->activeFight));
    }

    public function setWinner($winner)
    {
        if (!$this->activeFight) {
            Toaster::error('No active fight.');
            return;
        }

        if (!in_array($winner, ['meron', 'wala', 'draw', 'cancel'])) {
            Toaster::error('Invalid winner selected.');
            return;
        }

        if ($this->activeFight->winner !== null) {
            Toaster::error('Winner already declared for this fight.');
            return;
        }

        if ($this->activeFight->status !== 'close') {
            Toaster::error('Fight must be closed before declaring a winner.');
            return;
        }

        $this->activeFight->update(['winner' => $winner]);

        $bets = Bet::where('fight_id', $this->activeFight->id)->get();

        if (in_array($winner, ['draw', 'cancel'])) {
            foreach ($bets as $bet) {
                $bet->user?->increment('cash', $bet->amount);
                $bet->update([
                    'is_win' => null,
                    'payout_amount' => $bet->amount,
                    'is_claimed' => true,
                    'claimed_at' => now(),
                ]);
            }

            Toaster::info('All bets refunded due to ' . strtoupper($winner) . '.');
        } else {
            $winnerSide = $winner;
            $loserSide = $winner === 'meron' ? 'wala' : 'meron';

            foreach ($bets as $bet) {
                if ($bet->side === $winnerSide) {
                    $payout = $bet->amount * ($winnerSide === 'meron'
                        ? $this->activeFight->meron_payout
                        : $this->activeFight->wala_payout);

                    $bet->update([
                        'is_win' => true,
                        'payout_amount' => $payout,
                        'is_claimed' => false,
                        'claimed_at' => null,
                    ]);
                } else {
                    $bet->update([
                        'is_win' => false,
                        'payout_amount' => 0,
                        'is_claimed' => false,
                        'claimed_at' => null,
                    ]);
                }
            }

            $this->activeFight->update([
                'payout' => $bets->where('side', $winnerSide)->sum('amount') *
                    ($winnerSide === 'meron' ? $this->activeFight->meron_payout : $this->activeFight->wala_payout)
            ]);

            Toaster::success(strtoupper($winner) . ' declared as winner! Users can now claim their winnings.');
        }

        $this->fights = $this->currentEvent->fresh()->fights;
        broadcast(new FightUpdated($this->activeFight));
    }

    public function endFight()
    {
        if (! $this->activeFight) {
            Toaster::error('No active fight to end.');
            return;
        }

        if (!$this->activeFight->winner) {
            Toaster::error('Please declare a winner before ending the fight.');
            return;
        }

        if ($this->activeFight->status !== 'close') {
            Toaster::error('Bet must be closed before ending the fight.');
            return;
        }

        $this->activeFight->update(['status' => 'done']);
        broadcast(new FightUpdated($this->activeFight));

        $nextFight = $this->currentEvent
            ->fights()
            ->where('status', 'pending')
            ->orderBy('fight_number')
            ->first();

        if ($nextFight) {
            $this->activeFight = $nextFight;
            $this->fights = $this->currentEvent->fresh()->fights;
            Toaster::success('Moved to next fight (#' . $nextFight->fight_number . ').');
        } else {
            $this->activeFight = null;
            $this->fights = $this->currentEvent->fresh()->fights;
            Toaster::info('All fights for this event have been completed.');
        }

        $this->loadFighterNames();
    }

    public function getFightResultCountsProperty()
    {
        if (!$this->currentEvent) {
            return [
                'meron' => 0,
                'wala' => 0,
                'draw' => 0,
                'cancel' => 0,
            ];
        }

        return [
            'meron' => $this->currentEvent->fights()->where('winner', 'meron')->count(),
            'wala' => $this->currentEvent->fights()->where('winner', 'wala')->count(),
            'draw' => $this->currentEvent->fights()->where('winner', 'draw')->count(),
            'cancel' => $this->currentEvent->fights()->where('winner', 'cancel')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.declarator.dashboard');
    }
}
