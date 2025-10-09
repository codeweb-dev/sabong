<?php

namespace App\Livewire\Admin\Components;

use App\Models\Event;
use Livewire\Attributes\On;
use Livewire\Component;

class Events extends Component
{
    public $events;

    public $fights = [];

    public function mount()
    {
        $this->loadEvents();
    }

    #[On('refresh-event')]
    public function loadEvents()
    {
        $this->events = Event::with('fights')
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->latest()
            ->get();

        $ongoingEvent = $this->events->firstWhere('status', 'ongoing');
        $this->fights = $ongoingEvent
            ? $ongoingEvent->fights->sortBy('fight_number')->values()
            : collect();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->loadEvents();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        $this->loadEvents();
    }

    public function render()
    {
        return view('livewire.admin.components.events');
    }
}
