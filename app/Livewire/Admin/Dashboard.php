<?php

namespace App\Livewire\Admin;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventReportExport;
use Masmerise\Toaster\Toaster;
use App\Events\EventStarted;
use Livewire\Attributes\On;
use App\Events\EventEnded;
use App\Events\TransactionsUpdated;
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

    public function downloadReport()
    {
        if (!$this->selectedEventId) {
            Toaster::error("Please select an event first.");
            return;
        }

        $event = Event::find($this->selectedEventId);

        if (!$event) {
            Toaster::error("Event not found.");
            return;
        }

        Toaster::info("Preparing download...");

        $fileName = $event->created_at->format('d-m-Y_H-i') . '.xlsx';

        return Excel::download(
            new EventReportExport($this->selectedEventId),
            $fileName
        );
    }

    // #[On('echo:events,.fight.started')]
    // public function handleFightUpdated($data)
    // {
    //     if ($this->selectedEventId && (int) $data['eventId'] !== (int) $this->selectedEventId) {
    //         return;
    //     }

    //     $this->loadEvents();
    //     $this->dispatch('$refresh');
    // }

    #[On('echo:bets,.bets.updated')]
    public function handleBetsUpdated($data)
    {
        if ($this->selectedEventId && isset($data['eventId'])) {
            if ((int) $data['eventId'] !== (int) $this->selectedEventId) {
                return;
            }
        }

        $this->loadEvents();
        $this->dispatch('$refresh');
    }

    #[On('echo:events,.fight.started')]
    public function handleFightUpdated($data)
    {
        $this->refreshDashboard($data['eventId'] ?? null);
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
        $cleanRevolving = str_replace([',', ' '], '', $this->revolving);

        $this->revolving = $cleanRevolving;

        $validated = $this->validate([
            'event_name'   => 'required|string|max:255',
            'description'  => 'nullable|string',
            'no_of_fights' => 'required|integer|min:1',
            'revolving'    => 'required|numeric|min:0',
        ]);

        $validated['revolving'] = $cleanRevolving;
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
        broadcast(new TransactionsUpdated($event->id));
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
        broadcast(new TransactionsUpdated($event->id));
        Toaster::success("Event '{$event->event_name}' ended.");
        $this->loadEvents();
    }

    // #[On('echo:events,.bet.placed')]
    // public function handleBetPlaced($data)
    // {
    //     // refresh only if it matches selected event
    //     if ($this->selectedEventId && isset($data['eventId'])) {
    //         if ((int) $data['eventId'] !== (int) $this->selectedEventId) {
    //             return;
    //         }
    //     }

    //     // if you also want fights table updated (meron_bet/wala_bet per fight)
    //     $this->loadEvents();

    //     // forces render() -> your withSum() runs again
    //     $this->dispatch('$refresh');
    // }

    #[On('echo:events,.bet.placed')]
    public function handleBetPlaced($data)
    {
        $this->refreshDashboard($data['eventId'] ?? null);
    }

    private function refreshDashboard(?int $eventIdFromBroadcast = null): void
    {
        // If broadcast tells an eventId and it doesn't match, ignore
        if ($this->selectedEventId && $eventIdFromBroadcast) {
            if ((int) $eventIdFromBroadcast !== (int) $this->selectedEventId) {
                return;
            }
        }

        $this->loadEvents();
        $this->dispatch('$refresh'); // re-runs render() => re-runs withSum query
    }

    public function render()
    {
        // Use already loaded list from loadEvents()
        $events = $this->events;

        $selectedEvent = null;

        if ($this->selectedEventId) {
            $selectedEvent = Event::where('id', $this->selectedEventId)
                ->withSum('systemOversApplied as sum_system_overflow_applied', 'overflow')
                ->withSum('systemOversApplied as sum_total_system_over_applied', 'total_system_over')
                ->withSum(['bets as total_bets' => fn($q) => $q->where('is_refunded', 0)], 'amount')
                ->withSum(['meronBets as total_bets_meron' => fn($q) => $q->where('is_refunded', 0)], 'amount')
                ->withSum(['walaBets as total_bets_wala' => fn($q) => $q->where('is_refunded', 0)], 'amount')
                ->withSum('grossIncomes as total_gross_income', 'income')
                ->first();
        }

        return view('livewire.admin.dashboard', [
            'events'        => $events,
            'selectedEvent' => $selectedEvent,
        ]);
    }
}
