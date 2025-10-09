<?php

namespace App\Livewire\Admin\Components;

use App\Models\Event;
use Livewire\Attributes\On;
use Livewire\Component;

class Events extends Component
{
    public $events;

    public function mount()
    {
        $this->loadEvents();
    }

    public function loadEvents()
    {
        $this->events = Event::whereIn('status', ['upcoming', 'ongoing'])
            ->latest()
            ->get();
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
