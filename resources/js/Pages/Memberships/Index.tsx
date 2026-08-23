import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface MembershipRow {
    id: number;
    status: string;
    status_label: string;
    client: { id: number; full_name: string };
    plan: { id: number; name: string };
    starts_at: string;
    ends_at: string;
    price: string;
    currency: string;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    memberships: Paginated<MembershipRow>;
    status: string;
    canManage: boolean;
}

const STATUSES = [
    { value: '', label: 'All' },
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Active' },
    { value: 'frozen', label: 'Frozen' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'expired', label: 'Expired' },
];

const STATUS_STYLES: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700',
    active: 'bg-green-100 text-green-700',
    frozen: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    expired: 'bg-yellow-100 text-yellow-700',
};

export default function Index({ memberships, status, canManage }: Props) {
    const filterByStatus = (value: string) => {
        router.get(
            route('memberships.index'),
            { status: value || undefined },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Memberships
                </h2>
            }
        >
            <Head title="Memberships" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap gap-2">
                            {STATUSES.map((s) => (
                                <button
                                    key={s.value}
                                    type="button"
                                    onClick={() => filterByStatus(s.value)}
                                    className={`rounded-md px-3 py-1 text-sm ${
                                        status === s.value
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 shadow-sm'
                                    }`}
                                >
                                    {s.label}
                                </button>
                            ))}
                        </div>

                        {canManage && (
                            <Link href={route('memberships.create')}>
                                <PrimaryButton>Assign membership</PrimaryButton>
                            </Link>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {memberships.data.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No memberships yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {memberships.data.map((membership) => (
                                    <li key={membership.id}>
                                        <Link
                                            href={route(
                                                'memberships.show',
                                                membership.id,
                                            )}
                                            className="flex items-center justify-between p-6 hover:bg-gray-50"
                                        >
                                            <div>
                                                <div className="font-medium text-gray-900">
                                                    {
                                                        membership.client
                                                            .full_name
                                                    }
                                                    <span
                                                        className={`ml-2 rounded-full px-2 py-0.5 text-xs ${STATUS_STYLES[membership.status] ?? 'bg-gray-100 text-gray-700'}`}
                                                    >
                                                        {
                                                            membership.status_label
                                                        }
                                                    </span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {membership.plan.name}{' '}
                                                    &middot;{' '}
                                                    {membership.starts_at} –{' '}
                                                    {membership.ends_at}
                                                    {' · '}
                                                    {membership.price}{' '}
                                                    {membership.currency}
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    {memberships.last_page > 1 && (
                        <div className="flex flex-wrap gap-2">
                            {memberships.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    preserveState
                                    className={`rounded-md px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 shadow-sm'
                                    } ${!link.url && 'pointer-events-none opacity-50'}`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
