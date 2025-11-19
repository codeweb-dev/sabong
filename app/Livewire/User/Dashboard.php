<?php

namespace App\Livewire\User;

use App\Events\BetPlaced;
use App\HandlesPayouts;
use App\Models\Bet;
use App\Models\Fight;
use App\Models\SystemOver;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Flux\Flux;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Carbon\Carbon;

class Dashboard extends Component
{
    use HandlesPayouts;

    public $cashOnHand;
    public $amount = 0;
    public $activeFight = null;
    public $bets = [];
    public $fights = [];
    public $fight_id;

    public $cancelBetInput;
    public $reprintTicketNo;

    public $previewTicketNo;
    public $previewBet;

    public $meronPayoutDisplay = 0;
    public $walaPayoutDisplay = 0;

    public function mount()
    {
        $this->cashOnHand = Auth::user()->cash ?? 0;
        $this->fights = Fight::latest()->get();
        $this->loadActiveFight();
        $this->loadUserBets();
    }

    public function loadPreview()
    {
        if (empty($this->previewTicketNo)) {
            $this->previewBet = null;
            Toaster::error('Please enter a ticket number.');
            Flux::modal('preview-modal')->close();
            return;
        }

        $this->previewBet = Bet::with(['fight.event', 'user'])
            ->where('ticket_no', $this->previewTicketNo)
            ->first();

        if (!$this->previewBet) {
            $this->previewBet = null;
            Toaster::error('No ticket found with that number.');
            Flux::modal('preview-modal')->close();
            return;
        }

        $this->previewTicketNo = null;
    }

    public function reprintTicket()
    {
        if (empty($this->reprintTicketNo)) {
            Toaster::error('Please enter a ticket number to reprint.');
            return;
        }

        $bet = Bet::with(['fight.event', 'user'])
            ->where('ticket_no', $this->reprintTicketNo)
            ->where('user_id', Auth::id())
            ->first();

        if (!$bet) {
            Toaster::error('No bet found with that ticket number.');
            return;
        }

        $user = Auth::user();

        try {
            $connector = new WindowsPrintConnector("POS-80");
            $printer = new Printer($connector);

            // === HEADER: SIDE (Meron/Wala) ===
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text(strtoupper($bet->side) . "\n\n");

            // === DIVIDER ===
            $printer->setTextSize(2, 1);
            $printer->text("-----------------------\n");

            // === DETAILS ===
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Event Name:   " . ($bet->fight->event->event_name ?? 'N/A') . "\n");
            $printer->text("Description:  " . ($bet->fight->event->description ?? 'N/A') . "\n");
            $printer->text("-----------------------\n");
            $printer->text("Inputed By:   " . $user->username . "\n");
            $printer->text("Ticket No:    " . $bet->ticket_no . "\n");
            $printer->text("Fight No:     " . $bet->fight->fight_number . "\n");
            $printer->text("Amount:       " . number_format($bet->amount, 2) . "\n");
            $printer->text("-----------------------\n");

            // === DATE ===
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(Carbon::now()->timezone('Asia/Manila')->format('M d, Y h:i A') . "\n\n");

            // === BARCODE ===
            $printer->barcode($bet->ticket_no, Printer::BARCODE_CODE39);
            $printer->text($bet->ticket_no . "\n\n");

            // === FOOTER ===
            $printer->text("** REPRINTED COPY **\n");
            $printer->text("Thank you for betting!\n");
            $printer->feed(3);

            $printer->cut();
            $printer->close();

            Toaster::success('Ticket reprinted successfully!');
            $this->reprintTicketNo = '';
        } catch (\Exception $e) {
            Toaster::error("Reprint failed: " . $e->getMessage());
        }
    }

    public function cancelBet()
    {
        if (empty($this->cancelBetInput)) {
            Toaster::error('Please enter a ticket ID to cancel.');
            return;
        }

        $bet = Bet::where('ticket_no', $this->cancelBetInput)
            ->where('user_id', Auth::id())
            ->first();

        if (!$bet) {
            Toaster::error('No bet found with that ticket ID.');
            return;
        }

        if ($bet->fight && $bet->fight->status !== 'open') {
            Toaster::error('Cannot cancel this bet. The fight is already closed.');
            return;
        }

        $user = Auth::user();

        $user->increment('cash', $bet->amount);

        if ($bet->side === 'meron') {
            $bet->fight?->decrement('meron_bet', $bet->amount);
        } elseif ($bet->side === 'wala') {
            $bet->fight?->decrement('wala_bet', $bet->amount);
        }

        $bet->delete();

        if ($bet->fight) {
            broadcast(new BetPlaced($bet->fight->fresh()));
        }

        $this->cashOnHand = $user->fresh()->cash;
        $this->loadUserBets();
        $this->cancelBetInput = null;

        Toaster::success('Bet canceled, refunded, and totals updated!');
        $this->previewBet = null;
        $this->previewTicketNo = null;
    }

    private function loadActiveFight()
    {
        $this->activeFight = Fight::whereHas('event', function ($q) {
            $q->where('status', 'ongoing');
        })
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    public function updatedFightId()
    {
        $this->loadUserBets();
    }

    private function loadUserBets()
    {
        $query = Bet::with('fight')
            ->where('user_id', Auth::id())
            ->latest();

        if ($this->fight_id) {
            $query->where('fight_id', $this->fight_id);
        }

        $this->bets = $query->take(5)->get();
    }

    public function refreshFights()
    {
        $this->fights = Fight::latest()->get();
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
        $user = Auth::user();

        if (!$this->activeFight) {
            Toaster::error('No active fight open for betting.');
            return;
        }

        if (!in_array($side, ['meron', 'wala'])) {
            Toaster::error('Invalid side selected.');
            return;
        }

        if (!$this->activeFight->$side) {
            Toaster::error(ucfirst($side) . ' side is locked. You cannot bet here.');
            return;
        }

        if ($this->activeFight->status !== 'open') {
            Toaster::error('Betting is not open at the moment.');
            return;
        }

        if ($this->amount <= 0) {
            Toaster::error('Enter a valid amount to bet.');
            return;
        }

        // Increment cash
        $user->increment('cash', $this->amount);

        // Increment fight bets
        if ($side === 'meron') {
            $this->activeFight->increment('meron_bet', $this->amount);
        } else {
            $this->activeFight->increment('wala_bet', $this->amount);
        }

        // Recalculate payouts
        $payouts = $this->calculateAndSavePayout($this->activeFight->fresh());

        // Create bet
        $bet = Bet::create([
            'user_id' => $user->id,
            'fight_id' => $this->activeFight->id,
            'side' => $side,
            'amount' => $this->amount,
        ]);

        broadcast(new BetPlaced($this->activeFight->fresh()));

        $this->amount = 0;
        $this->cashOnHand = $user->fresh()->cash;
        $this->loadUserBets();

        $this->meronPayoutDisplay = $payouts['meronDisplay'];
        $this->walaPayoutDisplay = $payouts['walaDisplay'];

        try {
            $connector = new WindowsPrintConnector("POS-80");
            $printer = new Printer($connector);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text(strtoupper($bet->side) . "\n\n");
            $printer->setTextSize(2, 1);
            $printer->text("-----------------------\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Inputed By:   " . $user->username . "\n");
            $printer->text("Ticket No:    " . $bet->ticket_no . "\n");
            $printer->text("Fight No:     " . $bet->fight->fight_number . "\n");
            $printer->text("Amount:       " . number_format($bet->amount, 2) . "\n");
            $printer->text("-----------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(Carbon::now()->timezone('Asia/Manila')->format('M d, Y h:i A') . "\n\n");
            $printer->barcode($bet->ticket_no, Printer::BARCODE_CODE39);
            $printer->text($bet->ticket_no . "\n\n");
            $printer->text("Thank you for betting!\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            Toaster::error("Print failed: " . $e->getMessage());
        }

        Toaster::success('Bet placed successfully!');
        $side === 'meron'
            ? Flux::modal('meron-confirmation-modal')->close()
            : Flux::modal('wala-confirmation-modal')->close();
    }

    public function payout()
    {
        if (!$this->previewBet) {
            Toaster::error('No bet loaded for payout.');
            return;
        }

        $bet = $this->previewBet;

        // Check if bet has already been claimed
        if ($bet->is_claimed) {
            Toaster::error('This bet has already been claimed.');
            Flux::modal('preview-modal')->close();
            return;
        }

        // Check if bet is a winning bet
        if (!$bet->is_win) {
            Toaster::error('This bet did not win. Cannot claim payout.');
            Flux::modal('preview-modal')->close();
            return;
        }

        $user = Auth::user();

        // Check if user has enough cash to pay out
        if ($user->cash < $bet->payout_amount) {
            Toaster::error('Insufficient cash to process payout. Please find another teller.');
            Flux::modal('preview-modal')->close();
            return;
        }

        // Deduct payout from user's cash
        $user->decrement('cash', $bet->payout_amount);

        // Mark bet as claimed
        $bet->update([
            'is_claimed' => true,
            'claimed_at' => now(),
            'claimed_by' => $user->id,
        ]);

        $this->cashOnHand = $user->fresh()->cash;

        Toaster::success('Payout successful!');
        $this->previewBet = null;
    }

    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
