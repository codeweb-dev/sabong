<?php

namespace App\Livewire\Admin;

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
        Flux::modal('create-event')->close();
    }

    public function startEvent(Event $event)
    {
        // Do here that if i start the event button then it will get the latest event and the status is ongoing it would see all the fights in the welcome page
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'events' => Event::latest()->get(),
        ]);
    }
}
