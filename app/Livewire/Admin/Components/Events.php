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

    public function loadEvents()
    {
        $this->events = Event::with('fights')
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->latest()
            ->get();

        // Get the first ongoing event (if any)
        $ongoingEvent = $this->events->firstWhere('status', 'ongoing');

        // If there’s an ongoing event, grab its fights
        $this->fights = $ongoingEvent
            ? $ongoingEvent->fights->sortBy('fight_number')->values()
            : collect(); // empty collection if no ongoing event
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
