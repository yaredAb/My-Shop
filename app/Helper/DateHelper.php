<?php

namespace App\Helper;

use Carbon\Carbon;

class DateHelper
{
    public static function expiryRemaining($expiryDate) {
        $expiry = Carbon::parse($expiryDate);
        $now = Carbon::now();

        if($expiry->isPast()) {
            return 'Expired';
        }

        $diff = $now->diff($expiry);

        $parts = [];

        if($diff->y > 0) {
            $parts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        }
        if($diff->m > 0) {
            $parts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        }
        if($diff->d > 0 && $diff->y == 0) {
            $parts[] = $diff->d . ' day' .($diff->d > 1 ? 's' : '');
        }

        return implode(' ', $parts) . ' left';
    }

    public static function expiryStatus($expiryDate) {
        $expiry = Carbon::parse($expiryDate);
        $now = Carbon::now();

        if($expiry->isPast()) {
            return 'expired';
        }
        $monthLeft = $now->diffInMonths($expiry);
        if($monthLeft > 6) {
            return 'far';
        }
        return 'near';
    }
}
