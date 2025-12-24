<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;

class PayoutService
{
    public static function processWinner(Fight $fight, $winner, $previousWinner = null)
    {
        // -------------------------------------------------
        // 1) If winner changed, convert already-paid bets on
        //    old winner side into "short"
        // -------------------------------------------------
        // App\Services\PayoutService.php

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

                // move what was paid into short_amount
                $wrongBet->short_amount  = ($wrongBet->short_amount ?? 0) + ($wrongBet->payout_amount ?? 0);

                // remove payout because it was wrong
                $wrongBet->payout_amount = 0;

                // IMPORTANT: UI should show NOT WIN, not SHORT
                $wrongBet->status     = 'not win';
                $wrongBet->is_win     = false;

                // keep claimed (already paid before), keep claimed time
                $wrongBet->is_claimed = true;

                $wrongBet->save();
            }
        }

        // -------------------------------------------------
        // 2) reload all bets AFTER the short adjustments
        // -------------------------------------------------
        $bets = Bet::where('fight_id', $fight->id)->get();

        $winnerSide = $winner;

        $totalMeron = $bets->where('side', 'meron')->sum('amount');
        $totalWala  = $bets->where('side', 'wala')->sum('amount');

        $commission = 0.06;
        $pool = $totalMeron + $totalWala;
        $net  = $pool - ($pool * $commission);

        GrossIncome::updateOrCreate(
            ['fight_id' => $fight->id],
            ['income' => $pool * $commission]
        );

        $meronOdds = OddsService::compute($net, $totalMeron);
        $walaOdds  = OddsService::compute($net, $totalWala);

        $meronTotalSystemOver = $totalMeron > 0
            ? $totalMeron * $meronOdds['overflow']
            : 0;

        $walaTotalSystemOver = $totalWala > 0
            ? $totalWala * $walaOdds['overflow']
            : 0;

        $isMeronWinner = ($winnerSide === 'meron');
        $isWalaWinner  = ($winnerSide === 'wala');

        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'meron'],
            [
                'overflow'          => $meronOdds['overflow'],
                'total_system_over' => $isMeronWinner ? $meronTotalSystemOver : 0,
                'status'            => $isMeronWinner ? 'applied' : 'pending',
            ]
        );

        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'wala'],
            [
                'overflow'          => $walaOdds['overflow'],
                'total_system_over' => $isWalaWinner ? $walaTotalSystemOver : 0,
                'status'            => $isWalaWinner ? 'applied' : 'pending',
            ]
        );

        $fight->update([
            'meron_payout' => $meronOdds['payout'],
            'wala_payout'  => $walaOdds['payout'],
        ]);

        // -------------------------------------------------
        // 3) Apply new winner to all bets, but DO NOT
        //    overwrite "short" status
        // -------------------------------------------------
        foreach ($bets as $bet) {

            // If it already has a short amount and is already claimed,
            // leave it as NOT WIN with payout 0.
            $isLockedShort = ($bet->is_claimed && ($bet->short_amount ?? 0) > 0);

            if ($isLockedShort) {
                $bet->update([
                    'is_win'        => false,
                    'payout_amount' => 0,
                    'status'        => 'not win',
                ]);
                continue;
            }

            $isWin = $bet->side === $winnerSide;

            $newPayout = $isWin
                ? ($bet->amount * ($winnerSide === 'meron'
                    ? $fight->meron_payout
                    : $fight->wala_payout))
                : 0;

            $bet->update([
                'is_win'        => $isWin,
                'payout_amount' => $newPayout,
                'is_claimed'    => $isWin ? false : $bet->is_claimed,
                'claimed_at'    => $isWin ? null : $bet->claimed_at,
                'status'        => $isWin ? 'unpaid' : 'not win',
            ]);
        }

        return true;
    }
}
