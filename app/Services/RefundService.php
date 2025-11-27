<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public static function refundFight(Fight $fight)
    {
        $fight->update([
            'is_refunded' => true,
        ]);

        Bet::where('fight_id', $fight->id)
            ->update([
                'is_win' => true,
                'payout_amount' => DB::raw('amount'),
                'status' => 'unpaid',
            ]);

        GrossIncome::where('fight_id', $fight->id)->delete();
        SystemOver::where('fight_id', $fight->id)->delete();

        return true;
    }
}
