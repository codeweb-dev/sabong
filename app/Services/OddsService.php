<?php

namespace App\Services;

class OddsService
{
    public static function compute($pool, $total)
    {
        if ($total <= 0 || $pool <= 0) {
            return [
                'payout'   => 0,
                'display'  => 0,
                'overflow' => 0,
            ];
        }

        $raw = $pool / $total;          // e.g. 1.9580525

        // Display as whole-number percent (195 for 1.958...)
        $scaled  = $raw * 100;          // 195.80525
        $integer = floor($scaled);      // 195

        // Payout: floor to 2 decimals
        $payout = $integer / 100;       // 1.95

        // Overflow: leftover beyond 2 decimals
        $overflow = ($scaled - $integer) / 100;  // 0.80525 / 100 = 0.0080525

        return [
            'payout'   => $payout,
            'display'  => $integer,
            'overflow' => $overflow,
        ];
    }
}
