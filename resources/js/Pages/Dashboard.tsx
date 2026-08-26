import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

interface AppointmentSummary {
    id: number;
    client_name: string;
    service_name: string;
    starts_at: string;
}

interface Props {
    organization: { name: string } | null;
    todaysAppointments?: AppointmentSummary[];
    openAttendanceCount?: number;
    todaysPaymentsTotal?: string;
    activeMembershipsCount?: number;
}

function formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function StatCard({
    label,
    value,
    href,
}: {
    label: string;
    value: string | number;
    href: string;
}) {
    return (
        <Link
            href={href}
            className="block rounded-2xl border border-border bg-card p-6 shadow-soft transition hover:border-gold-soft"
        >
            <div className="eyebrow">{label}</div>
            <div className="mt-2 font-serif text-3xl font-light text-ink">
                {value}
            </div>
        </Link>
    );
}

export default function Dashboard({
    organization,
    todaysAppointments = [],
    openAttendanceCount = 0,
    todaysPaymentsTotal = '0',
    activeMembershipsCount = 0,
}: Props) {
    const today = new Date().toLocaleDateString(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    });

    // No current organization resolved for this session — either a
    // brand-new account with nothing set up yet, or a stale/forged
    // session value ResolveCurrentOrganization just cleared. Either way
    // this page must still render (see DashboardController), just with
    // nothing org-scoped to show.
    if (!organization) {
        return (
            <AuthenticatedLayout
                header={
                    <h2 className="font-serif text-2xl font-light text-ink">
                        Dashboard
                    </h2>
                }
            >
                <Head title="Dashboard" />

                <div className="py-12">
                    <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                        <div className="rounded-2xl border border-border bg-card p-10 text-center shadow-soft">
                            <h3 className="font-serif text-xl font-light text-ink">
                                No organization selected
                            </h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Create or select an organization to see
                                today's appointments, attendance, and
                                payments.
                            </p>
                            <div className="mt-6">
                                <Link href={route('organizations.index')}>
                                    <PrimaryButton>
                                        Go to organizations
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="font-serif text-2xl font-light text-ink">
                        {organization.name}
                    </h2>
                    <p className="text-sm text-muted-foreground">{today}</p>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl space-y-8 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <StatCard
                            label="Today's appointments"
                            value={todaysAppointments.length}
                            href={route('appointments.index')}
                        />
                        <StatCard
                            label="Checked in now"
                            value={openAttendanceCount}
                            href={route('attendance.index')}
                        />
                        <StatCard
                            label="Taken today"
                            value={`${todaysPaymentsTotal}`}
                            href={route('payments.index')}
                        />
                        <StatCard
                            label="Active memberships"
                            value={activeMembershipsCount}
                            href={route('memberships.index')}
                        />
                    </div>

                    <div className="rounded-2xl border border-border bg-card shadow-soft">
                        <div className="flex items-center justify-between border-b border-border px-6 py-4">
                            <h3 className="font-serif text-lg font-light text-ink">
                                Today's schedule
                            </h3>
                            <Link href={route('appointments.create')}>
                                <PrimaryButton>Book appointment</PrimaryButton>
                            </Link>
                        </div>

                        {todaysAppointments.length === 0 ? (
                            <p className="p-6 text-sm text-muted-foreground">
                                Nothing booked for today yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-border">
                                {todaysAppointments.map((appointment) => (
                                    <li
                                        key={appointment.id}
                                        className="flex items-center justify-between px-6 py-4"
                                    >
                                        <div>
                                            <div className="font-medium text-ink">
                                                {appointment.client_name}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {appointment.service_name}
                                            </div>
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {formatTime(appointment.starts_at)}
                                        </div>
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
