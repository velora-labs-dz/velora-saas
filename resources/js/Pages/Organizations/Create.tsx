import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        contact_email: '',
        contact_phone: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('organizations.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    New organization
                </h2>
            }
        >
            <Head title="New organization" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit}>
                            <div>
                                <InputLabel htmlFor="name" value="Organization name" />

                                <TextInput
                                    id="name"
                                    name="name"
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

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="contact_email"
                                    value="Contact email (optional)"
                                />

                                <TextInput
                                    id="contact_email"
                                    type="email"
                                    name="contact_email"
                                    value={data.contact_email}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('contact_email', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.contact_email}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="contact_phone"
                                    value="Contact phone (optional)"
                                />

                                <TextInput
                                    id="contact_phone"
                                    name="contact_phone"
                                    value={data.contact_phone}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('contact_phone', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.contact_phone}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-6 flex items-center justify-end">
                                <PrimaryButton disabled={processing}>
                                    Create organization
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
