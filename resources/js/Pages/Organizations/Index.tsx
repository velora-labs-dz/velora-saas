import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface OrganizationListItem {
    id: number;
    name: string;
    slug: string;
    status: string;
    role: string;
    is_active: boolean;
}

interface Props {
    organizations: OrganizationListItem[];
    currentOrganizationId: number | null;
}

export default function Index({ organizations, currentOrganizationId }: Props) {
    const switchTo = (organization: OrganizationListItem) => {
        router.post(route('organizations.switch', organization.slug));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Organizations
                </h2>
            }
        >
            <Head title="Organizations" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-4 flex justify-end">
                        <Link href={route('organizations.create')}>
                            <PrimaryButton>New organization</PrimaryButton>
                        </Link>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {organizations.length === 0 ? (
                            <div className="p-6 text-gray-600">
                                You don&apos;t belong to any organization yet.
                                Create one to get started.
                            </div>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {organizations.map((organization) => (
                                    <li
                                        key={organization.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {organization.name}
                                                {organization.id ===
                                                    currentOrganizationId && (
                                                    <span className="ml-2 rounded bg-green-100 px-2 py-0.5 text-xs font-semibold uppercase text-green-800">
                                                        Current
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                Role: {organization.role}
                                                {!organization.is_active && (
                                                    <span className="ml-2 text-red-600">
                                                        (inactive)
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex gap-2">
                                            <Link
                                                href={route(
                                                    'organizations.show',
                                                    organization.slug,
                                                )}
                                            >
                                                <SecondaryButton>
                                                    View
                                                </SecondaryButton>
                                            </Link>

                                            {organization.id !==
                                                currentOrganizationId && (
                                                <PrimaryButton
                                                    onClick={() =>
                                                        switchTo(organization)
                                                    }
                                                >
                                                    Switch
                                                </PrimaryButton>
                                            )}
                                        </div>
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
