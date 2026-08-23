import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface PlanRow {
    id: number;
    name: string;
    duration_value: number;
    duration_unit: string;
    price: string;
    currency: string;
    freeze_allowed: boolean;
    active: boolean;
}

interface Props {
    plans: PlanRow[];
    showInactive: boolean;
    canManage: boolean;
}

export default function Index({ plans, showInactive, canManage }: Props) {
    const toggleInactive = () => {
        router.get(
            route('membership-plans.index'),
            { inactive: showInactive ? undefined : 1 },
            { preserveState: true, replace: true },
        );
    };

    const toggleStatus = (plan: PlanRow) => {
        router.patch(route('membership-plans.toggle-status', plan.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Membership plans
                </h2>
            }
        >
            <Head title="Membership plans" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between">
                        <button
                            type="button"
                            onClick={toggleInactive}
                            className="text-sm text-gray-500 underline"
                        >
                            {showInactive
                                ? 'Hide inactive plans'
                                : 'Show inactive plans'}
                        </button>

                        {canManage && (
                            <Link href={route('membership-plans.create')}>
                                <PrimaryButton>Add plan</PrimaryButton>
                            </Link>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {plans.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No membership plans yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {plans.map((plan) => (
                                    <li
                                        key={plan.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {plan.name}
                                                {!plan.active && (
                                                    <span className="ml-2 text-xs text-gray-400">
                                                        (inactive)
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {plan.duration_value}{' '}
                                                {plan.duration_unit} &middot;{' '}
                                                {plan.price} {plan.currency}
                                                {!plan.freeze_allowed &&
                                                    ' · freeze not allowed'}
                                            </div>
                                        </div>

                                        {canManage && (
                                            <div className="flex gap-3">
                                                <Link
                                                    href={route(
                                                        'membership-plans.edit',
                                                        plan.id,
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        Edit
                                                    </SecondaryButton>
                                                </Link>
                                                <SecondaryButton
                                                    onClick={() =>
                                                        toggleStatus(plan)
                                                    }
                                                >
                                                    {plan.active
                                                        ? 'Deactivate'
                                                        : 'Activate'}
                                                </SecondaryButton>
                                            </div>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
