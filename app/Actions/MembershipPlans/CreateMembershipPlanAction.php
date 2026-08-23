<?php

namespace App\Actions\MembershipPlans;

use App\Models\MembershipPlan;
use App\Models\Organization;

class CreateMembershipPlanAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from StoreMembershipPlanRequest.
     */
    public function handle(Organization $organization, array $attributes): MembershipPlan
    {
        $plan = new MembershipPlan($attributes);
        $plan->organization_id = $organization->id;
        $plan->active = true;
        $plan->save();

        return $plan;
    }
}
