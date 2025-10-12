<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fight_id',
        'side',
        'amount',
    ];

    public function fight()
    {
        return $this->belongsTo(Fight::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
