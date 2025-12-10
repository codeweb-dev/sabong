<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemOver extends Model
{
    protected $fillable = [
        'fight_id',
        'side',
        'overflow',
        'total_system_over',
        'status',
    ];

    public function fight()
    {
        return $this->belongsTo(Fight::class);
    }
}
