<?php

namespace App\Livewire\Declarator;

use App\Services\PayoutService;
use App\Services\RefundService;
use Masmerise\Toaster\Toaster;
use App\Events\FightUpdated;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Event;

class Dashboard extends Component
{
    public $currentEvent;
    public $fights = [];
    public $activeFight;

    public $fighterAName = '';
    public $fighterBName = '';

    public $winnerChangeModal = false;
    public $newWinner = null;

    public function mount()
    {
        $this->loadOngoingEvent();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->refreshFights();
        $this->loadFighterNames();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent?->id === $data['eventId']) {
            $this->resetEventState();
        }
    }

    private function loadOngoingEvent()
    {
        $this->currentEvent = Event::where('status', 'ongoing')
            ->latest()
            ->with('fights')
            ->first();

        $this->refreshFights();
        $this->loadFighterNames();
    }

    private function refreshFights()
    {
        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
    }

    private function resetEventState()
    {
        $this->currentEvent = null;
        $this->fights = [];
        $this->activeFight = null;
        $this->fighterAName = '';
        $this->fighterBName = '';
    }

    public function getActiveFight()
    {
        if (!$this->currentEvent) return null;

        return $this->currentEvent->fights()
            ->whereIn('status', ['pending', 'start', 'open', 'close'])
            ->orderBy('fight_number')
            ->first();
    }

    private function ensureActiveFight()
    {
        if (!$this->activeFight) {
            Toaster::error('No active fight.');
            return false;
        }
        return true;
    }

    private function loadFighterNames()
    {
        $this->fighterAName = $this->activeFight->fighter_a ?? '';
        $this->fighterBName = $this->activeFight->fighter_b ?? '';
    }

    private function broadcastRefresh()
    {
        broadcast(new FightUpdated($this->activeFight->fresh()));
        $this->refreshFights();
    }

    public function startFight()
    {
        if (!$this->ensureActiveFight()) return;

        if ($this->currentEvent->fights()->where('status', 'start')->exists()) {
            Toaster::error('Another fight is already ongoing.');
            return;
        }

        $this->activeFight->update(['status' => 'start']);
        $this->broadcastRefresh();
    }

    public function openBet()
    {
        if (!$this->ensureActiveFight()) return;

        if ($this->activeFight->status !== 'start') {
            Toaster::error('Fight must be started first.');
            return;
        }

        $this->activeFight->update(['status' => 'open']);
        $this->broadcastRefresh();
    }

    public function closeBet()
    {
        if (!$this->ensureActiveFight()) return;

        if ($this->activeFight->status !== 'open') {
            Toaster::error('Betting is not open.');
            return;
        }

        $this->activeFight->update([
            'meron' => false,
            'wala' => false,
            'status' => 'close',
        ]);
        $this->broadcastRefresh();
    }

    public function endFight()
    {
        if (!$this->ensureActiveFight()) return;

        if (!$this->activeFight->winner) {
            Toaster::error('Declare a winner first.');
            return;
        }

        $this->activeFight->update(['status' => 'done']);
        broadcast(new FightUpdated($this->activeFight));

        $nextFight = $this->currentEvent->fights()
            ->where('status', 'pending')
            ->orderBy('fight_number')
            ->first();

        $this->activeFight = $nextFight;
        $this->refreshFights();

        $nextFight
            ? Toaster::success("Moved to fight #{$nextFight->fight_number}.")
            : Toaster::info('All fights completed.');

        $this->loadFighterNames();
    }

    public function addFighterName($side)
    {
        if (!$this->ensureActiveFight()) return;

        $field = $side === 'a' ? 'fighter_a' : 'fighter_b';
        $value = $side === 'a' ? $this->fighterAName : $this->fighterBName;

        $this->activeFight->update([$field => $value]);
        $this->broadcastRefresh();
        $this->loadFighterNames();
    }

    public function toggleSide($side)
    {
        if (!$this->ensureActiveFight()) return;

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side.');
            return;
        }

        if ($this->activeFight->status === 'close') {
            Toaster::error('Betting is closed.');
            return;
        }

        $newValue = !$this->activeFight->$side;
        $this->activeFight->update([$side => $newValue]);
        $this->broadcastRefresh();
        Toaster::success(strtoupper($side) . ($newValue ? ' opened!' : ' locked!'));
    }

    public function setWinner($winner)
    {
        if (!$this->ensureActiveFight()) return;

        if (!in_array($winner, ['meron', 'wala', 'draw', 'cancel'])) {
            Toaster::error('Invalid winner.');
            return;
        }

        if ($this->activeFight->winner && $this->activeFight->winner !== $winner) {
            $this->newWinner = $winner;
            $this->winnerChangeModal = true;
            return;
        }

        $this->applyWinner($winner);
    }

    public function confirmPenaltyChange()
    {
        if (!$this->newWinner) {
            return;
        }

        $this->activeFight->update(['is_penalty' => true]);

        $this->applyWinner($this->newWinner);

        Toaster::warning("Winner changed to " . strtoupper($this->newWinner) . " with penalty applied.");

        $this->winnerChangeModal = false;
        $this->newWinner = null;
    }

    private function applyWinner($winner)
    {
        $this->activeFight->update(['winner' => $winner]);

        if (in_array($winner, ['draw', 'cancel'])) {
            RefundService::refundFight($this->activeFight);
            Toaster::info('All bets refunded.');
        } else {
            PayoutService::processWinner($this->activeFight, $winner);
            Toaster::success(strtoupper($winner) . ' wins! Payouts ready.');
        }

        $this->broadcastRefresh();
    }

    public function getFightResultCountsProperty()
    {
        if (!$this->currentEvent) {
            return ['meron' => 0, 'wala' => 0, 'draw' => 0, 'cancel' => 0];
        }

        return $this->currentEvent->fights()
            ->selectRaw("
                SUM(winner = 'meron') AS meron,
                SUM(winner = 'wala') AS wala,
                SUM(winner = 'draw') AS draw,
                SUM(winner = 'cancel') AS cancel
            ")
            ->first()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.declarator.dashboard');
    }
}
