import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface MembershipDetail {
    id: number;
    client: { id: number; full_name: string };
    plan: { id: number; name: string };
    starts_at: string;
    ends_at: string;
    price: string;
    currency: string;
    notes: string | null;
}

interface Props {
    membership: MembershipDetail;
}

export default function Edit({ membership }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        starts_at: membership.starts_at,
        ends_at: membership.ends_at,
        price: membership.price,
        currency: membership.currency,
        notes: membership.notes ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('memberships.update', membership.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit membership
                </h2>
            }
        >
            <Head title="Edit membership" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <p className="mb-4 text-sm text-gray-500">
                            {membership.client.full_name} &middot;{' '}
                            {membership.plan.name}
                        </p>

                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="starts_at"
                                        value="Start date"
                                    />
                                    <TextInput
                                        id="starts_at"
                                        type="date"
                                        value={data.starts_at}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'starts_at',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.starts_at}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="ends_at"
                                        value="End date"
                                    />
                                    <TextInput
                                        id="ends_at"
                                        type="date"
                                        value={data.ends_at}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData('ends_at', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.ends_at}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="price"
                                        value="Price"
                                    />
                                    <TextInput
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.price}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData('price', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.price}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="currency"
                                        value="Currency"
                                    />
                                    <TextInput
                                        id="currency"
                                        value={data.currency}
                                        maxLength={3}
                                        className="mt-1 block w-full uppercase"
                                        onChange={(e) =>
                                            setData(
                                                'currency',
                                                e.target.value.toUpperCase(),
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.currency}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="notes"
                                    value="Notes (optional)"
                                />
                                <textarea
                                    id="notes"
                                    value={data.notes}
                                    rows={3}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        setData('notes', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.notes}
                                    className="mt-2"
                                />
                            </div>

                            <div className="flex items-center justify-end">
                                <PrimaryButton disabled={processing}>
                                    Save changes
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
