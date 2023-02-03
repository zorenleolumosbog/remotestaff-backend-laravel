<?php

namespace App\Models\Admin;

use App\Models\Users\Onboard;
use App\Models\Users\OnboardProfileBasicInfo;

class Registrant extends Onboard
{
    /**
     * Get the registrant basic info for the registrant.
     */
    public function basicInfo()
    {
        return $this->hasOne(OnboardProfileBasicInfo::class, 'reg_link_preregid');
    }

    /**
     * Get the registration expiry for the registrant.
     */
    public function hasExpiry()
    {
        return $this->hasOne(OnboardRegistrationExpiry::class, 'link_preregid');
    }
}
