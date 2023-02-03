<?php

namespace App\Models\Admin;

use App\Models\Users\Onboard;
use App\Models\Users\OnboardExpiry;

class OnboardRegistrant extends Onboard
{
    /**
     * Get the onboard expiry that owns the onboard registration.
     */
    public function onboardExpiry()
    {
        return $this->belongsTo(OnboardExpiry::class, 'maxdays_rule_id');
    }
}
