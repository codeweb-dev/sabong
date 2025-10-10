<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fight extends Model
{
    protected $fillable = ['event_id', 'fight_number', 'meron_bet', 'wala_bet',  'meron', 'wala', 'fighter_a', 'fighter_b', 'status', 'winner'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
