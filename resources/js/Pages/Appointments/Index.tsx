import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface AppointmentRow {
    id: number;
    status: string;
    status_label: string;
    client: { id: number; full_name: string };
    service: { id: number; name: string };
    employee: { id: number; name: string };
    starts_at: string;
    ends_at: string;
}

interface Props {
    appointments: AppointmentRow[];
    date: string;
    canManage: boolean;
}

function formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function shiftDate(date: string, days: number): string {
    const d = new Date(date + 'T00:00:00');
    d.setDate(d.getDate() + days);
    return d.toISOString().slice(0, 10);
}

export default function Index({ appointments, date, canManage }: Props) {
    const goToDate = (value: string) => {
        router.get(
            route('appointments.index'),
            { date: value },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Appointments
                </h2>
            }
        >
            <Head title="Appointments" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-2">
                            <SecondaryButton
                                onClick={() =>
                                    goToDate(shiftDate(date, -1))
                                }
                            >
                                &larr;
                            </SecondaryButton>
                            <input
                                type="date"
                                value={date}
                                onChange={(e) => goToDate(e.target.value)}
                                className="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <SecondaryButton onClick={() => goToDate(shiftDate(date, 1))}>
                                &rarr;
                            </SecondaryButton>
                            <button
                                type="button"
                                onClick={() =>
                                    goToDate(
                                        new Date().toISOString().slice(0, 10),
                                    )
                                }
                                className="text-sm text-gray-500 underline"
                            >
                                Today
                            </button>
                        </div>

                        {canManage && (
                            <Link
                                href={route('appointments.create')}
                            >
                                <PrimaryButton>Book appointment</PrimaryButton>
                            </Link>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {appointments.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No appointments on this day.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {appointments.map((appointment) => (
                                    <li
                                        key={appointment.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {formatTime(
                                                    appointment.starts_at,
                                                )}{' '}
                                                –{' '}
                                                {formatTime(
                                                    appointment.ends_at,
                                                )}{' '}
                                                &middot;{' '}
                                                {
                                                    appointment.client
                                                        .full_name
                                                }
                                                {appointment.status ===
                                                    'cancelled' && (
                                                    <span className="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">
                                                        Cancelled
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {appointment.service.name}{' '}
                                                &middot; with{' '}
                                                {appointment.employee.name}
                                            </div>
                                        </div>

                                        {canManage &&
                                            appointment.status ===
                                                'scheduled' && (
                                                <Link
                                                    href={route(
                                                        'appointments.edit',
                                                        appointment.id,
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        Edit
                                                    </SecondaryButton>
                                                </Link>
                                            )}
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
