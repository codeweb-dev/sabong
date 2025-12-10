<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;

class PayoutService
{
    public static function processWinner(Fight $fight, $winner)
    {
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

        // Odds from your updated OddsService
        $meronOdds = OddsService::compute($net, $totalMeron);
        $walaOdds  = OddsService::compute($net, $totalWala);

        // Compute total_system_over per side
        $meronTotalSystemOver = $totalMeron > 0
            ? $totalMeron * $meronOdds['overflow']
            : 0;

        $walaTotalSystemOver = $totalWala > 0
            ? $totalWala * $walaOdds['overflow']
            : 0;

        // Winner-only application
        $isMeronWinner = ($winnerSide === 'meron');
        $isWalaWinner  = ($winnerSide === 'wala');

        // MERON row
        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'meron'],
            [
                'overflow'          => $meronOdds['overflow'],
                'total_system_over' => $isMeronWinner ? $meronTotalSystemOver : 0,
                'status'            => $isMeronWinner ? 'applied' : 'pending',
            ]
        );

        // WALA row
        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'wala'],
            [
                'overflow'          => $walaOdds['overflow'],
                'total_system_over' => $isWalaWinner ? $walaTotalSystemOver : 0,
                'status'            => $isWalaWinner ? 'applied' : 'pending',
            ]
        );

        // Save payouts to fight
        $fight->update([
            'meron_payout' => $meronOdds['payout'],
            'wala_payout'  => $walaOdds['payout'],
        ]);

        // Bets updates
        foreach ($bets as $bet) {
            $isWin = $bet->side === $winnerSide;

            $bet->update([
                'is_win'        => $isWin,
                'payout_amount' => $isWin
                    ? ($bet->amount * ($winnerSide === 'meron'
                        ? $fight->meron_payout
                        : $fight->wala_payout))
                    : 0,
                'is_claimed'    => false,
                'claimed_at'    => null,
                'status'        => $isWin ? 'unpaid' : 'not win',
            ]);
        }

        return true;
    }
}
