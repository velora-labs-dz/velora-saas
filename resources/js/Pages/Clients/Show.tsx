import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface ClientDetail {
    id: number;
    full_name: string;
    first_name: string;
    last_name: string;
    phone: string;
    alternate_phone: string | null;
    email: string | null;
    date_of_birth: string | null;
    notes: string | null;
    is_archived: boolean;
}

interface Props {
    client: ClientDetail;
    canManage: boolean;
    canArchive: boolean;
}

export default function Show({ client, canManage, canArchive }: Props) {
    const archive = () => {
        if (!confirm(`Archive ${client.full_name}?`)) {
            return;
        }

        router.delete(route('clients.destroy', client.id));
    };

    const restore = () => {
        router.post(route('clients.restore', client.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {client.full_name}
                    {client.is_archived && (
                        <span className="ml-2 text-sm font-normal text-gray-400">
                            (archived)
                        </span>
                    )}
                </h2>
            }
        >
            <Head title={client.full_name} />

            <div className="py-12">
                <div className="mx-auto max-w-xl space-y-6 sm:px-6 lg:px-8">
                    <div className="space-y-4 bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="grid grid-cols-3 gap-4 text-sm">
                            <dt className="text-gray-500">Phone</dt>
                            <dd className="col-span-2 text-gray-900">
                                {client.phone}
                            </dd>

                            {client.alternate_phone && (
                                <>
                                    <dt className="text-gray-500">
                                        Alternate phone
                                    </dt>
                                    <dd className="col-span-2 text-gray-900">
                                        {client.alternate_phone}
                                    </dd>
                                </>
                            )}

                            {client.email && (
                                <>
                                    <dt className="text-gray-500">Email</dt>
                                    <dd className="col-span-2 text-gray-900">
                                        {client.email}
                                    </dd>
                                </>
                            )}

                            {client.date_of_birth && (
                                <>
                                    <dt className="text-gray-500">
                                        Date of birth
                                    </dt>
                                    <dd className="col-span-2 text-gray-900">
                                        {client.date_of_birth}
                                    </dd>
                                </>
                            )}

                            {client.notes && (
                                <>
                                    <dt className="text-gray-500">Notes</dt>
                                    <dd className="col-span-2 whitespace-pre-wrap text-gray-900">
                                        {client.notes}
                                    </dd>
                                </>
                            )}
                        </dl>
                    </div>

                    <div className="flex items-center justify-between">
                        <Link href={route('clients.index')}>
                            <SecondaryButton>Back to clients</SecondaryButton>
                        </Link>

                        <div className="flex gap-3">
                            {canManage && !client.is_archived && (
                                <Link
                                    href={route('clients.edit', client.id)}
                                >
                                    <PrimaryButton>Edit</PrimaryButton>
                                </Link>
                            )}

                            {canArchive &&
                                (client.is_archived ? (
                                    <SecondaryButton onClick={restore}>
                                        Restore
                                    </SecondaryButton>
                                ) : (
                                    <SecondaryButton onClick={archive}>
                                        Archive
                                    </SecondaryButton>
                                ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
