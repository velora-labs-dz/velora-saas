<?php

namespace App\Actions\MembershipPlans;

use App\Models\MembershipPlan;

class UpdateMembershipPlanAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from UpdateMembershipPlanRequest.
     */
    public function handle(MembershipPlan $plan, array $attributes): MembershipPlan
    {
        $plan->fill($attributes);
        $plan->save();

        return $plan;
    }
}
