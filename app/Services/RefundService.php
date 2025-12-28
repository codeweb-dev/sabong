<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;
use Illuminate\Support\Facades\DB;

class RefundService
{
    // App\Services\RefundService.php

    public static function refundFight(Fight $fight, $winner = null, $previousWinner = null)
    {
        $fight->update(['is_refunded' => true]);

        if ($previousWinner && $previousWinner !== $winner && in_array($previousWinner, ['meron', 'wala'])) {

            $paidOldWinnerBets = Bet::where('fight_id', $fight->id)
                ->where('side', $previousWinner)
                ->where('is_claimed', true)
                ->where('status', 'paid')
                ->get();

            foreach ($paidOldWinnerBets as $bet) {
                $bet->short_amount  = ($bet->short_amount ?? 0) + ($bet->payout_amount ?? 0);
                $bet->save();
            }
        }

        // Everyone is refundable at base amount, claimable again
        Bet::where('fight_id', $fight->id)->update([
            'is_win'        => true,
            'payout_amount' => DB::raw('amount'),
            'status'        => 'unpaid',
            'is_claimed'    => false,
            'claimed_at'    => null,
            'claimed_by'    => null,
        ]);

        GrossIncome::where('fight_id', $fight->id)->delete();
        SystemOver::where('fight_id', $fight->id)->delete();

        return true;
    }
}
