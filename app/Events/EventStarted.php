<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Event $event
    ) {}

    public function broadcastQueue(): string
    {
        return 'default';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('events');
    }

    public function broadcastAs(): string
    {
        return 'event.started';
    }

    public function broadcastWith(): array
    {
        return [
            'eventId' => $this->event->id,
            'eventName' => $this->event->event_name,
            'status' => $this->event->status,
        ];
    }
}
