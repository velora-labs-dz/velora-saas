import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ClientOption {
    id: number;
    full_name: string;
}

interface AttendanceRow {
    id: number;
    client: { id: number; full_name: string };
    check_in_at: string;
    check_out_at: string | null;
    is_open: boolean;
    notes: string | null;
}

interface Props {
    records: AttendanceRow[];
    date: string;
    canManage: boolean;
    clients: ClientOption[];
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

export default function Index({ records, date, canManage, clients }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        client_id: '',
        notes: '',
    });

    const goToDate = (value: string) => {
        router.get(
            route('attendance.index'),
            { date: value },
            { preserveState: true, replace: true },
        );
    };

    const submitCheckIn: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('attendance.check-in'), {
            onSuccess: () => reset(),
        });
    };

    const checkOut = (id: number) => {
        router.patch(route('attendance.check-out', id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Attendance
                </h2>
            }
        >
            <Head title="Attendance" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                    {canManage && (
                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-sm font-medium text-gray-900">
                                Check in a client
                            </h3>
                            <form
                                onSubmit={submitCheckIn}
                                className="mt-3 flex flex-wrap items-start gap-3"
                            >
                                <div className="min-w-[220px] flex-1">
                                    <select
                                        value={data.client_id}
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onChange={(e) =>
                                            setData(
                                                'client_id',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    >
                                        <option value="">
                                            Select a client
                                        </option>
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

                                <div className="min-w-[220px] flex-1">
                                    <InputLabel
                                        htmlFor="notes"
                                        value="Notes (optional)"
                                        className="sr-only"
                                    />
                                    <input
                                        id="notes"
                                        type="text"
                                        placeholder="Notes (optional)"
                                        value={data.notes}
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onChange={(e) =>
                                            setData('notes', e.target.value)
                                        }
                                    />
                                    <InputError
                                        message={errors.notes}
                                        className="mt-2"
                                    />
                                </div>

                                <PrimaryButton disabled={processing}>
                                    Check in
                                </PrimaryButton>
                            </form>
                        </div>
                    )}

                    <div className="flex flex-wrap items-center gap-2">
                        <SecondaryButton
                            onClick={() => goToDate(shiftDate(date, -1))}
                        >
                            &larr;
                        </SecondaryButton>
                        <input
                            type="date"
                            value={date}
                            onChange={(e) => goToDate(e.target.value)}
                            className="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <SecondaryButton
                            onClick={() => goToDate(shiftDate(date, 1))}
                        >
                            &rarr;
                        </SecondaryButton>
                        <button
                            type="button"
                            onClick={() =>
                                goToDate(new Date().toISOString().slice(0, 10))
                            }
                            className="text-sm text-gray-500 underline"
                        >
                            Today
                        </button>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {records.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No attendance recorded on this day.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {records.map((record) => (
                                    <li
                                        key={record.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {record.client.full_name}
                                                {record.is_open ? (
                                                    <span className="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                                        Checked in
                                                    </span>
                                                ) : (
                                                    <span className="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                                        Checked out
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {formatTime(
                                                    record.check_in_at,
                                                )}
                                                {record.check_out_at &&
                                                    ` – ${formatTime(record.check_out_at)}`}
                                                {record.notes &&
                                                    ` · ${record.notes}`}
                                            </div>
                                        </div>

                                        {canManage && record.is_open && (
                                            <SecondaryButton
                                                onClick={() =>
                                                    checkOut(record.id)
                                                }
                                            >
                                                Check out
                                            </SecondaryButton>
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
