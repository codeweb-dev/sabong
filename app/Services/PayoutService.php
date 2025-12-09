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
        $net = $pool - ($pool * $commission);

        GrossIncome::updateOrCreate(
            ['fight_id' => $fight->id],
            ['income' => $pool * $commission]
        );

        $meronOdds = OddsService::compute($net, $totalMeron);
        $walaOdds  = OddsService::compute($net, $totalWala);

        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'meron'],
            ['overflow' => $meronOdds['overflow']]
        );
        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'wala'],
            ['overflow' => $walaOdds['overflow']]
        );

        $fight->update([
            'meron_payout' => $meronOdds['payout'],
            'wala_payout'  => $walaOdds['payout'],
        ]);

        foreach ($bets as $bet) {
            $isWin = $bet->side === $winnerSide;

            $bet->update([
                'is_win' => $isWin,
                'payout_amount' => $isWin
                    ? ($bet->amount * ($winnerSide === 'meron'
                        ? $fight->meron_payout
                        : $fight->wala_payout))
                    : 0,
                'is_claimed' => false,
                'claimed_at' => null,
                'status' => $isWin ? 'unpaid' : 'not win',
            ]);
        }

        return true;
    }
}
