<?php

namespace App\Livewire\Declarator;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    public $currentEvent = null;
    public $fights = [];

    public function mount()
    {
        $this->loadOngoingEvent();
    }

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
        return view('livewire.declarator.dashboard');
    }
}
