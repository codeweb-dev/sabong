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

    public function mount($smallScreen = false)
    {
        $this->isSmallScreen = $smallScreen;
        $this->loadOngoingEvent();
    }

    // 👇 Listen to Reverb broadcast from the "events" channel
    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->fights = $this->currentEvent?->fights ?? [];
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->currentEvent = null;
            $this->fights = [];
        }
    }

    private function loadOngoingEvent()
    {
        $this->currentEvent = Event::where('status', 'ongoing')
            ->latest()
            ->with('fights')
            ->first();

        $this->fights = $this->currentEvent?->fights ?? [];
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
