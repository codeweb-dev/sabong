<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrossIncome extends Model
{
    protected $fillable = [
        'fight_id',
        'income',
    ];

    public function fight()
    {
        return $this->belongsTo(Fight::class);
    }
}
