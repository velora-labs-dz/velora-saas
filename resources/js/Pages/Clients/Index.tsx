import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface ClientRow {
    id: number;
    full_name: string;
    phone: string;
    email: string | null;
    is_archived: boolean;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    clients: Paginated<ClientRow>;
    search: string;
    showArchived: boolean;
    canManage: boolean;
}

export default function Index({
    clients,
    search: initialSearch,
    showArchived,
    canManage,
}: Props) {
    const [search, setSearch] = useState(initialSearch ?? '');

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();

        router.get(
            route('clients.index'),
            { search, archived: showArchived ? 1 : undefined },
            { preserveState: true, replace: true },
        );
    };

    const toggleArchived = () => {
        router.get(
            route('clients.index'),
            { search, archived: showArchived ? undefined : 1 },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Clients
                </h2>
            }
        >
            <Head title="Clients" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <form
                            onSubmit={submitSearch}
                            className="flex flex-1 gap-2"
                        >
                            <TextInput
                                value={search}
                                placeholder="Search name, phone, or email"
                                className="w-full max-w-sm"
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            <PrimaryButton>Search</PrimaryButton>
                        </form>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={toggleArchived}
                                className="text-sm text-gray-500 underline"
                            >
                                {showArchived
                                    ? 'Show active clients'
                                    : 'Show archived'}
                            </button>

                            {canManage && (
                                <Link href={route('clients.create')}>
                                    <PrimaryButton>Add client</PrimaryButton>
                                </Link>
                            )}
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {clients.data.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                {showArchived
                                    ? 'No archived clients.'
                                    : 'No clients yet.'}
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {clients.data.map((client) => (
                                    <li key={client.id}>
                                        <Link
                                            href={route(
                                                'clients.show',
                                                client.id,
                                            )}
                                            className="flex items-center justify-between p-6 hover:bg-gray-50"
                                        >
                                            <div>
                                                <div className="font-medium text-gray-900">
                                                    {client.full_name}
                                                    {client.is_archived && (
                                                        <span className="ml-2 text-xs text-gray-400">
                                                            (archived)
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {client.phone}
                                                    {client.email &&
                                                        ` · ${client.email}`}
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    {clients.last_page > 1 && (
                        <div className="flex flex-wrap gap-2">
                            {clients.links.map((link, i) => (
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
