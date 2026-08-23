<?php

namespace App\Actions\MembershipPlans;

use App\Models\MembershipPlan;

class ToggleMembershipPlanStatusAction
{
    public function handle(MembershipPlan $plan): MembershipPlan
    {
        $plan->active = ! $plan->active;
        $plan->save();

        return $plan;
    }
}
