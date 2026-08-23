import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

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

interface AppointmentDetail {
    id: number;
    client: { id: number };
    service: { id: number };
    employee: { id: number };
    starts_at: string;
    ends_at: string;
    notes: string | null;
}

interface Props {
    clients: ClientOption[];
    services: ServiceOption[];
    staff: StaffOption[];
    appointment: AppointmentDetail;
}

function toLocalInput(iso: string): string {
    const date = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

export default function Edit({ clients, services, staff, appointment }: Props) {
    const [cancelReason, setCancelReason] = useState('');
    const [cancelling, setCancelling] = useState(false);

    const { data, setData, patch, processing, errors } = useForm({
        client_id: String(appointment.client.id),
        service_id: String(appointment.service.id),
        employee_id: String(appointment.employee.id),
        starts_at: toLocalInput(appointment.starts_at),
        ends_at: toLocalInput(appointment.ends_at),
        notes: appointment.notes ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('appointments.update', appointment.id));
    };

    const submitCancel: FormEventHandler = (e) => {
        e.preventDefault();
        setCancelling(true);

        router.patch(
            route('appointments.cancel', appointment.id),
            { cancellation_reason: cancelReason || undefined },
            { onFinish: () => setCancelling(false) },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit appointment
                </h2>
            }
        >
            <Head title="Edit appointment" />

            <div className="py-12">
                <div className="mx-auto max-w-xl space-y-6 sm:px-6 lg:px-8">
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
                                        setData('service_id', e.target.value)
                                    }
                                    required
                                >
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

                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="text-sm font-medium text-gray-900">
                            Cancel this appointment
                        </h3>
                        <form onSubmit={submitCancel} className="mt-3 space-y-3">
                            <textarea
                                value={cancelReason}
                                onChange={(e) =>
                                    setCancelReason(e.target.value)
                                }
                                rows={2}
                                placeholder="Reason (optional)"
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <div className="flex justify-end">
                                <DangerButton disabled={cancelling}>
                                    Cancel appointment
                                </DangerButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
