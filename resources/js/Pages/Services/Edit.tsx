import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ServiceDetail {
    id: number;
    name: string;
    description: string | null;
    duration_minutes: number;
    price: string;
    currency: string;
    capacity: number | null;
}

interface Props {
    service: ServiceDetail;
}

export default function Edit({ service }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: service.name,
        description: service.description ?? '',
        duration_minutes: String(service.duration_minutes),
        price: service.price,
        currency: service.currency,
        capacity: service.capacity ? String(service.capacity) : '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('services.update', service.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit service
                </h2>
            }
        >
            <Head title="Edit service" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="name" value="Name" />
                                <TextInput
                                    id="name"
                                    value={data.name}
                                    className="mt-1 block w-full"
                                    isFocused
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    required
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="description"
                                    value="Description (optional)"
                                />
                                <textarea
                                    id="description"
                                    value={data.description}
                                    rows={3}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.description}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="duration_minutes"
                                        value="Duration (minutes)"
                                    />
                                    <TextInput
                                        id="duration_minutes"
                                        type="number"
                                        min="1"
                                        value={data.duration_minutes}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'duration_minutes',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.duration_minutes}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="capacity"
                                        value="Capacity (optional)"
                                    />
                                    <TextInput
                                        id="capacity"
                                        type="number"
                                        min="1"
                                        value={data.capacity}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'capacity',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={errors.capacity}
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
