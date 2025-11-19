<?php

namespace App\Events;

use App\Models\Fight;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetPlaced implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $fightId;
    public $eventId;
    public $totalMeronBet;
    public $totalWalaBet;
    public $meronPayout;
    public $walaPayout;

    public function __construct(Fight $fight)
    {
        $this->fightId = $fight->id;
        $this->eventId = $fight->event_id;
        $this->totalMeronBet = $fight->meron_bet;
        $this->totalWalaBet = $fight->wala_bet;
        $this->meronPayout = $fight->meron_payout; // RAW
        $this->walaPayout  = $fight->wala_payout;  // RAW
    }

    public function broadcastOn()
    {
        return new Channel('events');
    }

    public function broadcastAs()
    {
        return 'bet.placed';
    }
}
