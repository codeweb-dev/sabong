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
}
