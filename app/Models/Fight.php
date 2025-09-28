<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fight extends Model
{
    protected $fillable = ['event_id', 'fight_number', 'fighter_a', 'fighter_b', 'status'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
