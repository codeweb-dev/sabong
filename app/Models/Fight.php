<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fight extends Model
{
    protected $fillable = [
        'event_id',
        'fight_number',
        'meron_bet',
        'wala_bet',
        'meron_payout',
        'wala_payout',
        'meron',
        'wala',
        'fighter_a',
        'fighter_b',
        'status',
        'winner',
        'redeclared_at',
        'is_refunded',
    ];

    protected $casts = [
        'meron_payout' => 'float',
        'wala_payout'  => 'float',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function grossIncomes()
    {
        return $this->hasMany(GrossIncome::class);
    }

    public function systemOvers()
    {
        return $this->hasMany(SystemOver::class);
    }
}
