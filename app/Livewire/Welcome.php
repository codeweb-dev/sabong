<?php

namespace App\Livewire;

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

    public function mount($smallScreen = false)
    {
        $this->isSmallScreen = $smallScreen;
        $this->loadOngoingEvent();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->currentEvent = null;
            $this->fights = [];
            $this->activeFight = null;
        }
    }

    #[On('echo:events,.fight.started')]
    public function handleFightUpdated($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->fights = Event::with('fights')->find($data['eventId'])->fights;
            $this->activeFight = collect($this->fights)
                ->first(function ($fight) {
                    return in_array($fight->status, ['start', 'open', 'close']);
                });

            if ($this->activeFight) {
                $this->activeFight->fighter_a = $data['fighterA'];
                $this->activeFight->fighter_b = $data['fighterB'];
            }
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
    }

    private function getActiveFight()
    {
        if (! $this->currentEvent) return null;

        return $this->currentEvent->fights()
            ->whereIn('status', ['pending', 'start', 'open', 'close'])
            ->orderBy('fight_number')
            ->first();
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
