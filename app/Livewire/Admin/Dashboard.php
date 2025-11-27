<?php

namespace App\Livewire\Admin;

use App\Events\EventEnded;
use App\Events\EventStarted;
use App\Models\Event;
use App\Models\Fight;
use Flux\Flux;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Dashboard extends Component
{
    public string $event_name = '';
    public string $description = '';
    public int    $no_of_fights = 0;
    public string $revolving = '';

    private function closeModal(string $name)
    {
        Flux::modal($name)->close();
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

        $this->reset();
        $this->dispatch('refresh-event');
        $this->closeModal('create-event');
        Toaster::success('Event created successfully.');
    }

    public function startEvent(Event $event)
    {
        if ($ongoing = $this->hasOngoingEvent()) {
            Toaster::error("Cannot start a new event. '{$ongoing->event_name}' is still ongoing.");
            return $this->closeModal("event-{$event->event_name}-start");
        }

        $event->update(['status' => 'ongoing']);
        broadcast(new EventStarted($event));

        $this->dispatch('$refresh');
        $this->closeModal("event-{$event->event_name}-start");

        Toaster::success("Event '{$event->event_name}' started.");
    }

    public function endEvent(Event $event)
    {
        $event->update(['status' => 'finished']);
        broadcast(new EventEnded($event));

        $this->dispatch('$refresh');
        $this->closeModal("event-{$event->event_name}-end");

        Toaster::success("Event '{$event->event_name}' ended.");
    }

    public function render()
    {
        $events = Event::whereIn('status', ['upcoming', 'ongoing'])
            ->withSum('systemOvers as total_system_overflow', 'overflow')
            ->withSum('bets as total_bets', 'amount')
            ->withSum('meronBets as total_bets_meron', 'amount')
            ->withSum('walaBets as total_bets_wala', 'amount')
            ->withSum('grossIncomes as total_gross_income', 'income')
            ->latest()
            ->get();

        return view('livewire.admin.dashboard', compact('events'));
    }
}
