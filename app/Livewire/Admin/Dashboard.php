<?php

namespace App\Livewire\Admin;

use App\Events\EventEnded;
use App\Events\EventStarted;
use App\Models\Event;
use App\Models\Fight;
use Flux\Flux;
use Livewire\Component;

class Dashboard extends Component
{
    public string $event_name = '';

    public string $description = '';

    public int $no_of_fights = 0;

    public string $revolving = '';

    public function save()
    {
        $this->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'no_of_fights' => 'required|integer|min:1',
            'revolving' => 'required',
        ]);

        $event = Event::create([
            'event_name' => $this->event_name,
            'description' => $this->description,
            'no_of_fights' => $this->no_of_fights,
            'revolving' => (float) $this->revolving,
        ]);

        for ($i = 1; $i <= $event->no_of_fights; $i++) {
            Fight::create([
                'event_id' => $event->id,
                'fight_number' => $i,
                'status' => 'pending',
            ]);
        }

        $this->reset();
        $this->dispatch('refresh-event');
        Flux::modal('create-event')->close();
    }

    public function startEvent(Event $event)
    {
        $event->update(['status' => 'ongoing']);
        broadcast(new EventStarted($event));
        $this->dispatch('$refresh');
        Flux::modal("event-{$event->event_name}-start")->close();
    }

    public function endEvent(Event $event)
    {
        $event->update(['status' => 'finished']);
        broadcast(new EventEnded($event));
        $this->dispatch('$refresh');
        Flux::modal("event-{$event->event_name}-end")->close();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'events' => Event::whereIn('status', ['upcoming', 'ongoing'])
                ->latest()
                ->get(),
        ]);
    }
}
