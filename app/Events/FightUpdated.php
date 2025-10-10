<?php

namespace App\Events;

use App\Models\Fight as FightModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FightUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FightModel $fight;

    public function __construct(FightModel $fight)
    {
        $this->fight = $fight;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('events');
    }

    public function broadcastAs(): string
    {
        return 'fight.started';
    }

    public function broadcastWith(): array
    {
        return [
            'fightId' => $this->fight->id,
            'fightNumber' => $this->fight->fight_number,
            'fighterA' => $this->fight->fighter_a,
            'fighterB' => $this->fight->fighter_b,
            'status' => $this->fight->status,
            'eventId' => $this->fight->event_id,
        ];
    }
}
