<?php

namespace App\Livewire\Declarator;

use App\Services\PayoutService;
use App\Services\RefundService;
use Masmerise\Toaster\Toaster;
use App\Events\FightUpdated;
use App\Events\BetsUpdated;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Event;
use Flux\Flux;

class Dashboard extends Component
{
    public $currentEvent;
    public $fights = [];
    public $activeFight;

    public $fighterAName = '';
    public $fighterBName = '';

    public function mount()
    {
        $this->loadOngoingEvent();
    }

    #[On('echo:bets,.bets.updated')]
    public function handleBetsUpdated($data)
    {
        $this->loadOngoingEvent();
        $this->dispatch('$refresh');
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
        $fight = $this->activeFight?->fresh();

        if ($fight) {
            broadcast(new FightUpdated($fight));
            broadcast(new BetsUpdated($fight->event_id));
        }

        $this->refreshFights();
        $this->loadFighterNames();
        $this->dispatch('$refresh');
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

    public function addFight()
    {
        if (!$this->currentEvent) {
            Toaster::error('No ongoing event to add a fight to.');
            return;
        }

        $lastFightNumber = $this->currentEvent->fights()
            ->max('fight_number');

        $nextFightNumber = $lastFightNumber ? $lastFightNumber + 1 : 1;

        $fight = $this->currentEvent->fights()->create([
            'fight_number' => $nextFightNumber,
            'status'       => 'pending',
            'meron'        => false,
            'wala'         => false,
        ]);

        $this->currentEvent->increment('no_of_fights');
        $this->currentEvent->load('fights');
        $this->refreshFights();
        $this->loadFighterNames();

        broadcast(new FightUpdated($fight));
        broadcast(new BetsUpdated($this->currentEvent->id));

        Flux::modal('add-fight')->close();
        Toaster::success("Fight #{$nextFightNumber} added to {$this->currentEvent->event_name}.");
    }

    public function openBet()
    {
        if (!$this->ensureActiveFight()) return;

        $this->activeFight->update([
            'meron' => true,
            'wala' => true,
            'status' => 'open',
        ]);
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
        broadcast(new BetsUpdated($this->activeFight->event_id));

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

        // 👇 remember old winner before changing
        $previousWinner = $this->activeFight->winner;

        $this->activeFight->update(['winner' => $winner]);

        if (in_array($winner, ['draw', 'cancel'])) {
            RefundService::refundFight($this->activeFight);
            Toaster::info('All bets refunded.');
        } else {
            // 👇 pass previous winner
            PayoutService::processWinner($this->activeFight->fresh(), $winner, $previousWinner);
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
