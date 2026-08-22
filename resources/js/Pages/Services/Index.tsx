import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface ServiceRow {
    id: number;
    name: string;
    duration_minutes: number;
    price: string;
    currency: string;
    capacity: number | null;
    is_active: boolean;
}

interface Props {
    services: ServiceRow[];
    showInactive: boolean;
    canManage: boolean;
}

export default function Index({ services, showInactive, canManage }: Props) {
    const toggleInactive = () => {
        router.get(
            route('services.index'),
            { inactive: showInactive ? undefined : 1 },
            { preserveState: true, replace: true },
        );
    };

    const toggleStatus = (service: ServiceRow) => {
        router.patch(route('services.toggle-status', service.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Services
                </h2>
            }
        >
            <Head title="Services" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between">
                        <button
                            type="button"
                            onClick={toggleInactive}
                            className="text-sm text-gray-500 underline"
                        >
                            {showInactive
                                ? 'Hide inactive services'
                                : 'Show inactive services'}
                        </button>

                        {canManage && (
                            <Link href={route('services.create')}>
                                <PrimaryButton>Add service</PrimaryButton>
                            </Link>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {services.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No services yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {services.map((service) => (
                                    <li
                                        key={service.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {service.name}
                                                {!service.is_active && (
                                                    <span className="ml-2 text-xs text-gray-400">
                                                        (inactive)
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {service.duration_minutes} min
                                                &middot; {service.price}{' '}
                                                {service.currency}
                                                {service.capacity &&
                                                    service.capacity > 1 &&
                                                    ` · up to ${service.capacity}`}
                                            </div>
                                        </div>

                                        {canManage && (
                                            <div className="flex gap-3">
                                                <Link
                                                    href={route(
                                                        'services.edit',
                                                        service.id,
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        Edit
                                                    </SecondaryButton>
                                                </Link>
                                                <SecondaryButton
                                                    onClick={() =>
                                                        toggleStatus(service)
                                                    }
                                                >
                                                    {service.is_active
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
