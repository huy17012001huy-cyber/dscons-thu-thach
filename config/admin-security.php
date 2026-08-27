<?php

declare(strict_types=1);

return [
    'two_factor_enforced' => (bool) env('ADMIN_2FA_ENFORCED', false),
    'challenge_window_minutes' => 15,
    'recovery_code_count' => 8,
];
