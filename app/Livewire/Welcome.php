<?php

namespace App\Livewire;

use App\HandlesPayouts;
use App\Models\Bet;
use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.welcome')]
class Welcome extends Component
{
    use HandlesPayouts;

    public $isSmallScreen = false;
    public $currentEvent = null;
    public $fights = [];
    public $activeFight = null;
    public $totalMeronBet = 0;
    public $totalWalaBet = 0;

    public $meronPayoutDisplay = null;
    public $walaPayoutDisplay = null;
    public $showPayout = false;

    public $showWinnerOverlay = false;
    public $winnerSide = null;

    public function mount($smallScreen = false)
    {
        $this->isSmallScreen = $smallScreen;
        $this->loadOngoingEvent();
        $this->loadBetTotals();
    }

    #[On('echo:events,.event.started')]
    public function handleEventStarted($data)
    {
        $this->currentEvent = Event::with('fights')->find($data['eventId']);
        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
    }

    #[On('echo:events,.event.ended')]
    public function handleEventEnded($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->currentEvent = null;
            $this->fights = [];
            $this->activeFight = null;
            $this->totalMeronBet = 0;
            $this->totalWalaBet = 0;

            $this->showWinnerOverlay = false;
            $this->winnerSide = null;
        }
    }

    #[On('echo:events,.fight.started')]
    public function handleFightUpdated($data)
    {
        if ($this->currentEvent && $this->currentEvent->id === $data['eventId']) {
            $this->fights = Event::with('fights')->find($data['eventId'])->fights;

            $this->activeFight = collect($this->fights)
                ->first(fn($fight) => in_array($fight->status, ['start', 'open', 'close']));

            if ($this->activeFight) {
                $this->activeFight->fighter_a = $data['fighterA'];
                $this->activeFight->fighter_b = $data['fighterB'];
            }

            $this->loadBetTotals();
            $this->updateWinnerOverlay();
        }
    }

    #[On('echo:events,.bet.placed')]
    public function handleBetPlaced($data)
    {
        if ($this->activeFight && $this->activeFight->id === $data['fightId']) {
            $this->totalMeronBet = Bet::where('fight_id', $this->activeFight->id)
                ->where('side', 'meron')
                ->sum('amount');

            $this->totalWalaBet = Bet::where('fight_id', $this->activeFight->id)
                ->where('side', 'wala')
                ->sum('amount');

            $payouts = $this->calculateAndSavePayout($this->activeFight->fresh());

            $this->meronPayoutDisplay = $payouts['meronDisplay'];
            $this->walaPayoutDisplay = $payouts['walaDisplay'];

            $this->showPayout =
                $this->meronPayoutDisplay > 0 &&
                $this->walaPayoutDisplay > 0;
        }
    }

    private function updateWinnerOverlay()
    {
        if ($this->activeFight && $this->activeFight->winner) {
            $this->winnerSide = $this->activeFight->winner;
            $this->showWinnerOverlay = true;
        } else {
            $this->winnerSide = null;
            $this->showWinnerOverlay = false;
        }
    }

    private function loadOngoingEvent()
    {
        $this->currentEvent = Event::where('status', 'ongoing')
            ->latest()
            ->with('fights')
            ->first();

        $this->fights = $this->currentEvent?->fights ?? [];
        $this->activeFight = $this->getActiveFight();
        $this->loadBetTotals();
        $this->updateWinnerOverlay();
    }

    private function getActiveFight()
    {
        if (! $this->currentEvent) {
            return null;
        }

        return $this->currentEvent->fights()
            ->whereIn('status', ['start', 'open', 'close'])
            ->orderBy('fight_number')
            ->first();
    }

    private function loadBetTotals()
    {
        if (!$this->activeFight) {
            $this->totalMeronBet = 0;
            $this->totalWalaBet = 0;
            $this->meronPayoutDisplay = null;
            $this->walaPayoutDisplay = null;
            return;
        }

        $this->totalMeronBet = Bet::where('fight_id', $this->activeFight->id)
            ->where('side', 'meron')
            ->sum('amount');

        $this->totalWalaBet = Bet::where('fight_id', $this->activeFight->id)
            ->where('side', 'wala')
            ->sum('amount');

        $payouts = $this->calculateAndSavePayout($this->activeFight);

        $this->meronPayoutDisplay = $payouts['meronDisplay'];
        $this->walaPayoutDisplay = $payouts['walaDisplay'];

        $this->showPayout =
            $this->meronPayoutDisplay > 0 &&
            $this->walaPayoutDisplay > 0;
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
