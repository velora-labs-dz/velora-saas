import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        phone: '',
        alternate_phone: '',
        email: '',
        date_of_birth: '',
        notes: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('clients.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    New client
                </h2>
            }
        >
            <Head title="New client" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="first_name"
                                        value="First name"
                                    />
                                    <TextInput
                                        id="first_name"
                                        value={data.first_name}
                                        className="mt-1 block w-full"
                                        isFocused
                                        onChange={(e) =>
                                            setData(
                                                'first_name',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.first_name}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="last_name"
                                        value="Last name"
                                    />
                                    <TextInput
                                        id="last_name"
                                        value={data.last_name}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'last_name',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.last_name}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel htmlFor="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    value={data.phone}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('phone', e.target.value)
                                    }
                                    required
                                />
                                <InputError
                                    message={errors.phone}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="alternate_phone"
                                    value="Alternate phone (optional)"
                                />
                                <TextInput
                                    id="alternate_phone"
                                    value={data.alternate_phone}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData(
                                            'alternate_phone',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={errors.alternate_phone}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="email"
                                    value="Email (optional)"
                                />
                                <TextInput
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.email}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="date_of_birth"
                                    value="Date of birth (optional)"
                                />
                                <TextInput
                                    id="date_of_birth"
                                    type="date"
                                    value={data.date_of_birth}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData(
                                            'date_of_birth',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={errors.date_of_birth}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="notes"
                                    value="Notes (optional)"
                                />
                                <textarea
                                    id="notes"
                                    value={data.notes}
                                    rows={4}
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
                                    Add client
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
