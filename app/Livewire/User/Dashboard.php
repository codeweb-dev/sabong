<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use App\Services\PrinterService;
use Masmerise\Toaster\Toaster;
use App\Events\BetsUpdated;
use Livewire\Attributes\On;
use App\Events\BetPlaced;
use App\Events\TransactionsUpdated;
use Livewire\Component;
use App\HandlesPayouts;
use App\Models\Fight;
use App\Models\Bet;
use App\Models\SystemOver;
use Flux\Flux;

class Dashboard extends Component
{
    use HandlesPayouts;

    public $cashOnHand;
    public $amount = '';
    public $activeFight;
    public $bets = [];
    public $fights = [];
    public $fight_id;

    public $cancelBetInput;
    public $reprintTicketNo;

    public $previewTicketNo;
    public $previewBet;

    public $meronPayoutDisplay = 0;
    public $walaPayoutDisplay = 0;

    public $scanMode = false;
    public $scannedBarcode = '';

    private function cleanAmount(): float
    {
        $value = (string) ($this->amount ?? '0');

        $clean = preg_replace('/[^0-9.]/', '', $value) ?? '0';

        if (substr_count($clean, '.') > 1) {
            $parts = explode('.', $clean);
            $clean = array_shift($parts) . '.' . implode('', $parts);
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    public function mount()
    {
        $this->cashOnHand = $this->getEventCash();
        $this->fights = Fight::whereHas('event', fn($q) => $q->where('status', 'ongoing'))
            ->orderBy('fight_number')
            ->get();
        $this->loadActiveFight();
        $this->loadUserBets();
    }

    private function getEventCash()
    {
        $event = Fight::whereHas('event', fn($q) => $q->where('status', 'ongoing'))
            ->first()
            ?->event;

        if (!$event) {
            return 0;
        }

        return $event->users()
            ->where('user_id', Auth::id())
            ->first()
            ?->pivot->cash ?? 0;
    }

    private function updateEventCash($amount)
    {
        $event = Fight::whereHas('event', fn($q) => $q->where('status', 'ongoing'))
            ->first()
            ?->event;

        if (!$event) {
            return;
        }

        $currentCash = $this->getEventCash();
        $newCash     = $currentCash + $amount;

        $event->users()->syncWithoutDetaching([
            Auth::id() => ['cash' => $newCash],
        ]);
    }

    #[On('echo:bets,.bets.updated')]
    public function handleBetsUpdated($data)
    {
        $this->cashOnHand = $this->getEventCash();
        $this->loadActiveFight();
        $this->loadUserBets();

        $this->dispatch('$refresh');
    }

    private function user()
    {
        return Auth::user();
    }

    private function findBetByTicket($ticket, $mustBelongToUser = false)
    {
        $query = Bet::with(['fight.event', 'user'])
            ->where('ticket_no', $ticket);

        if ($mustBelongToUser) {
            $query->where('user_id', $this->user()->id);
        }

        return $query->first();
    }

    public function toggleScanMode()
    {
        $this->scanMode = !$this->scanMode;
        $this->scannedBarcode = '';

        if ($this->scanMode) {
            $this->dispatch('focus-barcode');
        }

        Toaster::info($this->scanMode ? 'Scan mode activated.' : 'Scan mode deactivated.');
    }

    public function updatedScannedBarcode()
    {
        if (!$this->scanMode || empty($this->scannedBarcode)) return;

        $ticketNo = trim(str_replace('*', '', $this->scannedBarcode));

        if (strlen($ticketNo) >= 3) {
            $this->previewTicketNo = $ticketNo;
            $this->scannedBarcode = '';
            $this->loadPreview();

            if ($this->previewBet) {
                $this->payout();
            }

            if ($this->scanMode) {
                $this->dispatch('focus-barcode');
            }
        }
    }

    public function loadPreview()
    {
        if (empty($this->previewTicketNo)) {
            $this->previewError('Please enter a ticket number.');
            return;
        }

        $this->previewBet = $this->findBetByTicket($this->previewTicketNo);

        if (!$this->previewBet) {
            $this->previewError('No ticket found with that number.');
            return;
        }

        $this->previewTicketNo = null;
    }

    private function previewError($message)
    {
        $this->previewBet = null;
        Toaster::error($message);
        Flux::modal('preview-modal')->close();
        return;
    }

    public function reprintTicket()
    {
        if (!$this->reprintTicketNo) {
            Toaster::error('Please enter a ticket number.');
            return;
        }

        $bet = $this->findBetByTicket($this->reprintTicketNo, true);

        if (!$bet) {
            Toaster::error('No bet found with that ticket number.');
            return;
        }

        if (app(PrinterService::class)->printTicket($bet, true)) {
            Toaster::success('Ticket reprinted!');
        } else {
            Toaster::error('Reprint failed.');
            $this->reprintTicketNo = '';
        }
    }

    public function cancelBet()
    {
        if (!$this->cancelBetInput) {
            Toaster::error('Enter a ticket ID to cancel.');
            return;
        }

        $bet = $this->findBetByTicket($this->cancelBetInput, true);

        if (!$bet) {
            Toaster::error('No bet found with that ticket number.');
            return;
        }

        if ($bet->fight?->status !== 'open') {
            Toaster::error('This bet cannot be cancelled.');
            return;
        }

        $this->updateEventCash(-$bet->amount);
        $bet->fight?->decrement($bet->side . '_bet', $bet->amount);
        $bet->delete();
        broadcast(new BetPlaced($bet->fight->fresh()));
        broadcast(new BetsUpdated($bet->fight->event_id));
        $this->cashOnHand = $this->getEventCash();
        $this->loadUserBets();

        Toaster::success('Bet canceled & refunded!');
        $this->cancelBetInput = '';
    }

    private function loadActiveFight()
    {
        $this->activeFight = Fight::whereHas(
            'event',
            fn($q) =>
            $q->where('status', 'ongoing')
        )
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    private function loadUserBets()
    {
        $query = Bet::with('fight.event')
            ->where('user_id', $this->user()->id)
            ->whereHas('fight.event', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->latest();

        if ($this->fight_id) {
            $query->where('fight_id', $this->fight_id);
        }

        $this->bets = $query->take(5)->get();
    }

    public function updatedFightId()
    {
        $this->loadUserBets();
    }

    public function addAmount($value)
    {
        $current = $this->cleanAmount();

        $raw = (string) $value;
        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '0';
        $add = is_numeric($raw) ? (float) $raw : 0.0;

        $this->amount = number_format($current + $add, 0, '.', ',');
    }

    public function clearAmount()
    {
        $this->reset('amount');
    }

    public function placeBet($side)
    {
        $user = $this->user();

        if (!$this->activeFight) {
            Toaster::error('No active fight for betting.');
            Flux::modal(
                $side === 'meron'
                    ? 'meron-confirmation-modal'
                    : 'wala-confirmation-modal'
            )->close();
            return;
        }

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side.');
            Flux::modal(
                $side === 'meron'
                    ? 'meron-confirmation-modal'
                    : 'wala-confirmation-modal'
            )->close();
            return;
        }

        if (!$this->activeFight->$side) {
            Toaster::error(ucfirst($side) . ' is locked.');
            Flux::modal(
                $side === 'meron'
                    ? 'meron-confirmation-modal'
                    : 'wala-confirmation-modal'
            )->close();
            return;
        }

        if ($this->activeFight->status !== 'open') {
            Toaster::error('Betting closed.');
            Flux::modal(
                $side === 'meron'
                    ? 'meron-confirmation-modal'
                    : 'wala-confirmation-modal'
            )->close();
            return;
        }

        $betAmount = $this->cleanAmount();

        if ($betAmount <= 0) {
            Toaster::error('Invalid amount.');
            Flux::modal($side === 'meron' ? 'meron-confirmation-modal' : 'wala-confirmation-modal')->close();
            return;
        }

        $this->updateEventCash($betAmount);
        $this->activeFight->increment($side . '_bet', $betAmount);
        $payouts = $this->calculateAndSavePayout($this->activeFight->fresh());

        $bet = Bet::create([
            'user_id'  => $user->id,
            'fight_id' => $this->activeFight->id,
            'side'     => $side,
            'amount'   => $betAmount,
        ]);

        $this->reset('amount');
        sleep(1);

        broadcast(new BetPlaced($this->activeFight->fresh()));
        broadcast(new BetsUpdated($this->activeFight->event_id));

        $this->cashOnHand = $this->getEventCash();
        $this->loadUserBets();

        $this->meronPayoutDisplay = $payouts['meronDisplay'];
        $this->walaPayoutDisplay  = $payouts['walaDisplay'];

        app(PrinterService::class)->printTicket($bet);

        Toaster::success('Bet placed!');

        Flux::modal(
            $side === 'meron'
                ? 'meron-confirmation-modal'
                : 'wala-confirmation-modal'
        )->close();
    }

    private function currentEventId(): ?int
    {
        return Fight::whereHas('event', fn($q) => $q->where('status', 'ongoing'))
            ->first()
            ?->event_id;
    }

    public function payout()
    {
        if (!$this->previewBet) {
            Toaster::error('No bet loaded.');
            Flux::modal('preview-modal')->close();
            return;
        }

        $currentEventId = $this->currentEventId();

        if (!$currentEventId) {
            Toaster::error('No ongoing event right now. Please ask the admin to start an event.');
            Flux::modal('preview-modal')->close();
            return;
        }

        $bet  = $this->previewBet;
        $user = $this->user();

        $betEventId = $bet->fight?->event_id;

        if (!$betEventId) {
            Toaster::error('This ticket has no event info. Please contact admin.');
            Flux::modal('preview-modal')->close();
            return;
        }

        if ($betEventId !== $currentEventId) {
            Toaster::error(
                "Cannot process payout. This ticket is from a previous event. Please ask the admin for assistance."
            );
            Flux::modal('preview-modal')->close();
            return;
        }

        if ($bet->is_claimed) {
            Toaster::error('Already claimed.');
            Flux::modal('preview-modal')->close();
            return;
        }

        if ($bet->is_lock) {
            Toaster::error('Bet is locked. Please contact the admin for assistance.');
            Flux::modal('preview-modal')->close();
            return;
        }

        if (!$bet->is_win) {
            Toaster::error('Not a winning ticket.');
            Flux::modal('preview-modal')->close();
            return;
        }

        // ----- REAL PAYOUT vs CASH PAYOUT -----
        $rawPayout = (float) $bet->payout_amount;      // e.g. 20599.20
        $cashPayout = floor($rawPayout);               // e.g. 20599
        $systemRemainder = round($rawPayout - $cashPayout, 2); // e.g. 0.20

        // Make sure we have enough event cash for the *cash* part
        $currentCash = $this->getEventCash();

        if ($currentCash < $cashPayout) {
            Toaster::error('Insufficient cash.');
            Flux::modal('preview-modal')->close();
            return;
        }

        // ----- PUSH REMAINDER INTO SYSTEM_OVER -----
        // We store the remainder on the row of this fight + side (winner side)
        $systemOver = SystemOver::firstOrCreate(
            [
                'fight_id' => $bet->fight_id,
                'side'     => $bet->side, // winner side of this ticket
            ],
            [
                'overflow'          => 0,
                'total_system_over' => 0,
                'status'            => 'applied', // or 'pending', up to you
            ]
        );

        if ($systemRemainder > 0) {
            $systemOver->increment('total_system_over', $systemRemainder);
        }

        $this->updateEventCash(-$cashPayout);

        $bet->update([
            'is_claimed' => true,
            'claimed_at' => now(),
            'claimed_by' => $user->id,
            'status'     => 'paid',
        ]);

        broadcast(new BetsUpdated($bet->fight->event_id));

        $this->cashOnHand = $this->getEventCash();
        $this->previewBet = null;

        Toaster::success('Payout successful!');
        Flux::modal('preview-modal')->close();
        broadcast(new TransactionsUpdated($this->currentEventId()));
    }

    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
