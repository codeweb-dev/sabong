<?php

namespace App\Livewire\Admin;

use Masmerise\Toaster\Toaster;
use App\Events\EventStarted;
use App\Events\EventEnded;
use Livewire\Component;
use App\Models\Event;
use App\Models\Fight;
use Flux\Flux;

class Dashboard extends Component
{
    public string $event_name = '';
    public string $description = '';
    public int    $no_of_fights = 0;
    public string $revolving = '';

    public ?int $selectedEventId = null;
    public $events;
    public $fights = [];

    public function mount()
    {
        $this->loadEvents();
    }

    public function loadEvents()
    {
        $this->events = Event::with('fights')
            ->latest()
            ->get();

        if (!$this->selectedEventId) {
            $ongoing = $this->events->firstWhere('status', 'ongoing');
            if ($ongoing) {
                $this->selectedEventId = $ongoing->id;
            }
        }

        $event = $this->events->firstWhere('id', $this->selectedEventId);

        $this->fights = $event
            ? $event->fights->sortBy('fight_number')->values()
            : collect();
    }

    public function selectEvent(int $eventId)
    {
        $this->selectedEventId = $eventId;
        $this->loadEvents();
    }

    private function hasOngoingEvent(): ?Event
    {
        return Event::where('status', 'ongoing')->first();
    }

    private function initializeFights(Event $event): void
    {
        $fights = [];

        for ($i = 1; $i <= $event->no_of_fights; $i++) {
            $fights[] = [
                'event_id'     => $event->id,
                'fight_number' => $i,
                'status'       => 'pending',
            ];
        }

        Fight::insert($fights);
    }

    public function save()
    {
        $validated = $this->validate([
            'event_name'   => 'required|string|max:255',
            'description'  => 'nullable|string',
            'no_of_fights' => 'required|integer|min:1',
            'revolving'    => 'required|numeric|min:0',
        ]);

        $event = Event::create($validated);

        $this->initializeFights($event);
        $this->reset(['event_name', 'description', 'no_of_fights', 'revolving']);
        Toaster::success('Event created successfully.');
        $this->loadEvents();
        Flux::modal('create-event')->close();
    }

    public function startEvent()
    {
        if (!$this->selectedEventId) {
            Toaster::error("Please select an event first.");
            return;
        }

        $event = Event::findOrFail($this->selectedEventId);

        if ($ongoing = $this->hasOngoingEvent()) {
            if ($ongoing->id !== $event->id) {
                Toaster::error("Cannot start. '{$ongoing->event_name}' is already ongoing.");
                return;
            }
        }

        $event->update(['status' => 'ongoing']);
        broadcast(new EventStarted($event));
        Toaster::success("Event '{$event->event_name}' started.");
        $this->loadEvents();
    }

    public function endEvent()
    {
        if (!$this->selectedEventId) {
            Toaster::error("Please select an event first.");
            return;
        }

        $event = Event::findOrFail($this->selectedEventId);

        if ($event->status !== 'ongoing') {
            Toaster::error("Only ongoing events can be ended.");
            return;
        }

        $event->update(['status' => 'finished']);
        broadcast(new EventEnded($event));
        Toaster::success("Event '{$event->event_name}' ended.");
        $this->loadEvents();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
