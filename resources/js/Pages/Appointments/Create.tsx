import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ClientOption {
    id: number;
    full_name: string;
}

interface ServiceOption {
    id: number;
    name: string;
    duration_minutes: number;
}

interface StaffOption {
    id: number;
    name: string;
}

interface Props {
    clients: ClientOption[];
    services: ServiceOption[];
    staff: StaffOption[];
}

function toLocalInput(date: Date): string {
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function addMinutes(value: string, minutes: number): string {
    const date = new Date(value);
    date.setMinutes(date.getMinutes() + minutes);
    return toLocalInput(date);
}

export default function Create({ clients, services, staff }: Props) {
    const defaultStart = (() => {
        const d = new Date();
        d.setMinutes(0, 0, 0);
        d.setHours(d.getHours() + 1);
        return toLocalInput(d);
    })();

    const { data, setData, post, processing, errors } = useForm({
        client_id: '',
        service_id: '',
        employee_id: '',
        starts_at: defaultStart,
        ends_at: addMinutes(defaultStart, 60),
        notes: '',
    });

    const applyServiceDefaults = (serviceId: string) => {
        const service = services.find((s) => String(s.id) === serviceId);

        setData((current) => ({
            ...current,
            service_id: serviceId,
            ends_at: service
                ? addMinutes(current.starts_at, service.duration_minutes)
                : current.ends_at,
        }));
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('appointments.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Book appointment
                </h2>
            }
        >
            <Head title="Book appointment" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel
                                    htmlFor="client_id"
                                    value="Client"
                                />
                                <select
                                    id="client_id"
                                    value={data.client_id}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        setData('client_id', e.target.value)
                                    }
                                    required
                                >
                                    <option value="">Select a client</option>
                                    {clients.map((client) => (
                                        <option
                                            key={client.id}
                                            value={client.id}
                                        >
                                            {client.full_name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.client_id}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="service_id"
                                    value="Service"
                                />
                                <select
                                    id="service_id"
                                    value={data.service_id}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        applyServiceDefaults(e.target.value)
                                    }
                                    required
                                >
                                    <option value="">Select a service</option>
                                    {services.map((service) => (
                                        <option
                                            key={service.id}
                                            value={service.id}
                                        >
                                            {service.name} (
                                            {service.duration_minutes} min)
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.service_id}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="employee_id"
                                    value="Staff"
                                />
                                <select
                                    id="employee_id"
                                    value={data.employee_id}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        setData('employee_id', e.target.value)
                                    }
                                    required
                                >
                                    <option value="">
                                        Select a staff member
                                    </option>
                                    {staff.map((member) => (
                                        <option
                                            key={member.id}
                                            value={member.id}
                                        >
                                            {member.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.employee_id}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="starts_at"
                                        value="Start"
                                    />
                                    <TextInput
                                        id="starts_at"
                                        type="datetime-local"
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
                                        value="End"
                                    />
                                    <TextInput
                                        id="ends_at"
                                        type="datetime-local"
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

                            <p className="text-xs text-gray-400">
                                The end time is pre-filled from the
                                service's duration but can be adjusted.
                            </p>

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
                                    Book appointment
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
