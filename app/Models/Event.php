<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['event_name', 'description', 'no_of_fights', 'revolving', 'total_transfer', 'status'];

    protected $casts = [
        'revolving' => 'float',
    ];

    public function fights()
    {
        return $this->hasMany(Fight::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function systemOvers()
    {
        return $this->hasManyThrough(
            SystemOver::class,
            Fight::class,
            'event_id',
            'fight_id',
            'id',
            'id'
        );
    }

    public function bets()
    {
        return $this->hasManyThrough(
            Bet::class,
            Fight::class,
            'event_id',
            'fight_id',
            'id',
            'id'
        );
    }

    public function grossIncomes()
    {
        return $this->hasManyThrough(
            GrossIncome::class,
            Fight::class,
            'event_id',
            'fight_id',
            'id',
            'id'
        );
    }

    public function meronBets()
    {
        return $this->hasManyThrough(Bet::class, Fight::class)
            ->where('side', 'meron');
    }

    public function walaBets()
    {
        return $this->hasManyThrough(Bet::class, Fight::class)
            ->where('side', 'wala');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('cash')->withTimestamps();
    }
}
