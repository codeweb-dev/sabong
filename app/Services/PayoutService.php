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
                $wrongBet->payout_amount = 0;
                $wrongBet->status        = 'short'; // <-- important
                $wrongBet->is_win        = false;
                $wrongBet->is_claimed    = true;  // <-- keep claimed
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
            $isWin = $bet->side === $winnerSide;

            $newPayout = $isWin
                ? ($bet->amount * ($winnerSide === 'meron'
                    ? $fight->meron_payout
                    : $fight->wala_payout))
                : 0;

            // keep existing short bets as "short"
            if ($bet->status === 'short') {
                $status = 'short';
            } else {
                $status = $isWin ? 'unpaid' : 'not win';
            }

            $bet->update([
                'is_win'        => $bet->status === 'short' ? false : $isWin,
                'payout_amount' => $bet->status === 'short' ? 0 : $newPayout,
                'is_claimed'    => $bet->status === 'short'
                    ? true        // short bets stay claimed
                    : ($isWin ? false : $bet->is_claimed),
                'claimed_at'    => $bet->status === 'short'
                    ? $bet->claimed_at // keep original claim time
                    : ($isWin ? null : $bet->claimed_at),
                'status'        => $status,
            ]);
        }

        return true;
    }
}
