<?php

return [
    'cookie_days' => env('AFFILIATE_COOKIE_DAYS', 30),
    'default_commission' => env('AFFILIATE_DEFAULT_COMMISSION', 10),
    'commission_trigger_status' => env('COMMISSION_TRIGGER_STATUS', 'delivered'),
    'min_payout' => env('AFFILIATE_MIN_PAYOUT', 100),
];
