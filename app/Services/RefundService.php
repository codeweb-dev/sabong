<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public static function refundFight(Fight $fight, $winner = null, $previousWinner = null)
    {
        $fight->update([
            'is_refunded' => true,
        ]);

        if (
            $previousWinner &&
            $previousWinner !== $winner &&
            in_array($previousWinner, ['meron', 'wala'])
        ) {
            $paidWrongBets = Bet::where('fight_id', $fight->id)
                ->where('side', $previousWinner)
                ->where('is_claimed', true)
                ->where('status', 'paid')
                ->get();

            foreach ($paidWrongBets as $wrongBet) {
                $wrongBet->short_amount  = ($wrongBet->short_amount ?? 0) + ($wrongBet->payout_amount ?? 0);
                $wrongBet->payout_amount = $wrongBet->amount;
                $wrongBet->status        = 'unpaid';
                $wrongBet->is_win        = true;
                $wrongBet->is_claimed    = false;
                $wrongBet->save();
            }
        }

        Bet::where('fight_id', $fight->id)
            ->update([
                'is_win' => true,
                'payout_amount' => DB::raw('amount'),
                'status' => 'refund',
            ]);

        GrossIncome::where('fight_id', $fight->id)->delete();
        SystemOver::where('fight_id', $fight->id)->delete();

        return true;
    }
}
