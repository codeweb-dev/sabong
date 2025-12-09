<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetsUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function broadcastOn(): Channel
    {
        // Public channel "bets"
        return new Channel('bets');
    }

    public function broadcastAs(): string
    {
        // Echo event name ".bets.updated"
        return 'bets.updated';
    }
}
