<?php

namespace App\Livewire;

use App\Models\Bet;
use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.welcome')]
class Welcome extends Component
{
    public $isSmallScreen = false;
    public $currentEvent = null;
    public $fights = [];
    public $activeFight = null;
    public $completedFights = [];
    public $totalMeronBet = 0;
    public $totalWalaBet = 0;

    public function mount($smallScreen = false)
    {
        $this->isSmallScreen = $smallScreen;
        $this->loadOngoingEvent();
        $this->loadBetTotals();
        $this->prepareFightsData();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
        $this->prepareFightsData();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->currentEvent = null;
            $this->fights = [];
            $this->activeFight = null;
            $this->completedFights = [];
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
        $this->prepareFightsData();
        $this->loadBetTotals();
    }

    private function getActiveFight()
    {
        if (! $this->currentEvent) return null;

        return $this->currentEvent->fights()
            ->whereIn('status', ['start', 'open', 'close'])
            ->orderBy('fight_number')
            ->first();
    }

    private function loadBetTotals()
    {
        if (!$this->activeFight) {
            $this->totalMeronBet = 0;
            $this->totalWalaBet = 0;
            return;
        }

        $this->totalMeronBet = Bet::where('fight_id', $this->activeFight->id)
            ->where('side', 'meron')
            ->sum('amount');

        $this->totalWalaBet = Bet::where('fight_id', $this->activeFight->id)
            ->where('side', 'wala')
            ->sum('amount');
    }

    private function prepareFightsData()
    {
        $activeStatuses = ['start', 'open', 'close'];
        $this->activeFight = collect($this->fights)
            ->first(fn($f) => in_array($f->status, $activeStatuses))
            ?: collect($this->fights)->firstWhere('status', 'pending');

        $this->completedFights = collect($this->fights)
            ->where('id', '!=', optional($this->activeFight)->id)
            ->whereNotIn('status', ['pending', 'start', 'open', 'close'])
            ->reverse()
            ->take(3)
            ->map(function($fight) {
                $fight->bgColor = match($fight->winner) {
                    'meron' => 'bg-red-400 text-white',
                    'wala' => 'bg-blue-400 text-white',
                    'draw' => 'bg-green-400 text-black',
                    'cancel' => 'bg-gray-400 text-black',
                    default => 'bg-white text-black',
                };

                $fight->badgeColor = match($fight->winner) {
                    'meron' => 'red',
                    'wala' => 'blue',
                    'draw' => 'green',
                    'cancel' => 'gray',
                    default => 'black',
                };

                return $fight;
            });
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
