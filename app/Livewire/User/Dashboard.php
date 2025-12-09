<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use App\Services\PrinterService;
use Masmerise\Toaster\Toaster;
use App\Events\BetsUpdated;
use Livewire\Attributes\On;
use App\Events\BetPlaced;
use Livewire\Component;
use App\HandlesPayouts;
use App\Models\Fight;
use App\Models\Bet;
use Flux\Flux;

class Dashboard extends Component
{
    use HandlesPayouts;

    public $cashOnHand;
    public $amount = null;
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

    public function mount()
    {
        $user = Auth::user();
        $this->cashOnHand = $user->cash;
        $this->fights = Fight::whereHas('event', function ($q) {
            $q->where('status', 'ongoing');
        })
            ->orderBy('fight_number')
            ->get();
        $this->loadActiveFight();
        $this->loadUserBets();
    }

    #[On('echo:bets,.bets.updated')]
    public function handleBetsUpdated($data)
    {
        $this->cashOnHand = $this->user()->cash;
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
        Toaster::info($this->scanMode ? 'Scan mode activated.' : 'Scan mode deactivated.');
    }

    public function updatedScannedBarcode()
    {
        if (!$this->scanMode || empty($this->scannedBarcode)) return;

        $ticketNo = trim(str_replace('*', '', $this->scannedBarcode));

        if (strlen($ticketNo) >= 3) {
            $this->previewTicketNo = $ticketNo;
            $this->scanMode = false;
            $this->scannedBarcode = '';

            $this->loadPreview();
            if ($this->previewBet) {
                $this->payout();
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

        $user = $this->user();
        $user->decrement('cash', $bet->amount);
        $bet->fight?->decrement($bet->side . '_bet', $bet->amount);
        $bet->delete();
        broadcast(new BetPlaced($bet->fight->fresh()));
        broadcast(new BetsUpdated($bet->fight->event_id));

        $this->cashOnHand = $user->cash;
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
        $this->amount += (int) str_replace(',', '', $value);
    }

    public function clearAmount()
    {
        $this->amount = 0;
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
        if ($this->amount <= 0) {
            Toaster::error('Invalid amount.');
            Flux::modal(
                $side === 'meron'
                    ? 'meron-confirmation-modal'
                    : 'wala-confirmation-modal'
            )->close();
            return;
        }

        $user->increment('cash', $this->amount);
        $this->activeFight->increment($side . '_bet', $this->amount);
        $payouts = $this->calculateAndSavePayout($this->activeFight->fresh());

        $bet = Bet::create([
            'user_id'  => $user->id,
            'fight_id' => $this->activeFight->id,
            'side'     => $side,
            'amount'   => $this->amount,
        ]);

        broadcast(new BetPlaced($this->activeFight->fresh()));
        broadcast(new BetsUpdated($this->activeFight->event_id));

        $this->amount = null;
        $this->cashOnHand = $user->fresh()->cash;
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

    public function payout()
    {
        if (!$this->previewBet) {
            Toaster::error('No bet loaded.');
            return;
        }

        $bet = $this->previewBet;
        $user = $this->user();

        if ($bet->is_claimed) {
            Toaster::error('Already claimed.');
            return;
        }
        if ($bet->is_lock) {
            Toaster::error('Bet is locked. Please contact the admin for assistance.');
            return;
        }
        if (!$bet->is_win) {
            Toaster::error('Not a winning ticket.');
            return;
        }
        if ($user->cash < $bet->payout_amount) {
            Toaster::error('Insufficient cash.');
            return;
        }

        $user->decrement('cash', $bet->payout_amount);

        $bet->update([
            'is_claimed' => true,
            'claimed_at' => now(),
            'claimed_by' => $user->id,
            'status'     => 'paid',
        ]);

        broadcast(new BetsUpdated($bet->fight->event_id));

        $this->cashOnHand = $user->cash;
        $this->previewBet = null;

        Toaster::success('Payout successful!');
        Flux::modal('preview-modal')->close();
    }

    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
