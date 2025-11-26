<?php

namespace App\Services;

class OddsService
{
    public static function compute($pool, $total)
    {
        if ($total <= 0 || $pool <= 0) {
            return [
                'payout' => 0,
                'display' => 0,
                'overflow' => 0,
            ];
        }

        $raw = $pool / $total;
        $scaled = $raw * 100;

        return [
            'payout'   => floor($raw * 100) / 100,
            'display'  => floor($scaled),
            'overflow' => $scaled - floor($scaled),
        ];
    }
}
