<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionsUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('transactions');
    }

    public function broadcastAs(): string
    {
        return 'transactions.updated';
    }
}
