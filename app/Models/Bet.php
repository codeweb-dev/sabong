<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fight_id',
        'side',
        'amount',
        'is_win',
        'is_lock',
        'payout_amount',
        'is_claimed',
        'status',
        'claimed_at',
        'claimed_by',
    ];

    public function fight()
    {
        return $this->belongsTo(Fight::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ticket_no = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        });
    }
}
