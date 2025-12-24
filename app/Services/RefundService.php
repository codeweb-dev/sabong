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

        // If previously there was a real winner (meron/wala) and it was already PAID,
        // convert those PAID tickets into refundable UNPAID tickets:
        if (
            $previousWinner &&
            $previousWinner !== $winner &&
            in_array($previousWinner, ['meron', 'wala'])
        ) {
            $paidOldWinnerBets = Bet::where('fight_id', $fight->id)
                ->where('side', $previousWinner)
                ->where('is_claimed', true)
                ->where('status', 'paid')
                ->get();

            foreach ($paidOldWinnerBets as $bet) {

                // Move wrong payout into short
                $bet->short_amount  = ($bet->short_amount ?? 0) + ($bet->payout_amount ?? 0);

                // Refund base becomes the bet amount
                $bet->payout_amount = $bet->amount;

                // Make it claimable again
                $bet->is_win     = true;
                $bet->is_claimed = false;
                $bet->claimed_at = null;
                $bet->claimed_by = null;

                // show as UNPAID until teller claims (then we set REFUND on payout())
                $bet->status = 'unpaid';
                $bet->save();
            }
        }

        // Now for DRAW/CANCEL: everyone is refundable at base amount
        Bet::where('fight_id', $fight->id)
            ->update([
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
