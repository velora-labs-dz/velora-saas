import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface Props {
    organization: {
        id: number;
        name: string;
        slug: string;
        status: string;
    };
    role: string;
}

export default function Show({ organization, role }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {organization.name}
                </h2>
            }
        >
            <Head title={organization.name} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="grid grid-cols-2 gap-4 text-sm">
                            <dt className="text-gray-500">Status</dt>
                            <dd className="text-gray-900">{organization.status}</dd>

                            <dt className="text-gray-500">Your role</dt>
                            <dd className="text-gray-900">{role}</dd>

                            <dt className="text-gray-500">Slug</dt>
                            <dd className="text-gray-900">{organization.slug}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
