import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface ClientOption {
    id: number;
    full_name: string;
}

interface MembershipOption {
    id: number;
    client_id: number;
    label: string;
}

interface PaymentRow {
    id: number;
    status: string;
    status_label: string;
    client: { id: number; full_name: string };
    membership: { id: number; plan_name: string } | null;
    amount: string;
    currency: string;
    method_label: string;
    reference: string | null;
    paid_at: string;
    refunded_amount: string;
    refund_reason: string | null;
    void_reason: string | null;
    notes: string | null;
    can_void: boolean;
    can_refund: boolean;
    refundable_amount: string;
}

interface Props {
    payments: PaymentRow[];
    date: string;
    canManage: boolean;
    canCorrect: boolean;
    clients: ClientOption[];
    memberships: MembershipOption[];
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

function toLocalInput(date: Date): string {
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const STATUS_STYLES: Record<string, string> = {
    recorded: 'bg-green-100 text-green-700',
    voided: 'bg-red-100 text-red-700',
    refunded: 'bg-yellow-100 text-yellow-700',
};

export default function Index({
    payments,
    date,
    canManage,
    canCorrect,
    clients,
    memberships,
}: Props) {
    const [voidingId, setVoidingId] = useState<number | null>(null);
    const [refundingId, setRefundingId] = useState<number | null>(null);

    const recordForm = useForm({
        client_id: '',
        membership_id: '',
        amount: '',
        currency: 'DZD',
        method: 'cash',
        reference: '',
        paid_at: toLocalInput(new Date()),
        notes: '',
    });

    const voidForm = useForm({ void_reason: '' });
    const refundForm = useForm({ amount: '', refund_reason: '' });

    const goToDate = (value: string) => {
        router.get(
            route('payments.index'),
            { date: value },
            { preserveState: true, replace: true },
        );
    };

    const submitRecord: FormEventHandler = (e) => {
        e.preventDefault();

        recordForm.post(route('payments.store'), {
            onSuccess: () => recordForm.reset(),
        });
    };

    const openVoid = (id: number) => {
        voidForm.reset();
        voidForm.clearErrors();
        setVoidingId(id);
    };

    const submitVoid: FormEventHandler = (e) => {
        e.preventDefault();
        if (voidingId === null) return;

        voidForm.patch(route('payments.void', voidingId), {
            preserveScroll: true,
            onSuccess: () => setVoidingId(null),
        });
    };

    const openRefund = (payment: PaymentRow) => {
        refundForm.setData({
            amount: payment.refundable_amount,
            refund_reason: '',
        });
        refundForm.clearErrors();
        setRefundingId(payment.id);
    };

    const submitRefund: FormEventHandler = (e) => {
        e.preventDefault();
        if (refundingId === null) return;

        refundForm.patch(route('payments.refund', refundingId), {
            preserveScroll: true,
            onSuccess: () => setRefundingId(null),
        });
    };

    const availableMemberships = memberships.filter(
        (m) => String(m.client_id) === recordForm.data.client_id,
    );

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Payments
                </h2>
            }
        >
            <Head title="Payments" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                    {canManage && (
                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-sm font-medium text-gray-900">
                                Record a payment
                            </h3>
                            <form
                                onSubmit={submitRecord}
                                className="mt-3 space-y-4"
                            >
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="client_id"
                                            value="Client"
                                        />
                                        <select
                                            id="client_id"
                                            value={recordForm.data.client_id}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            onChange={(e) =>
                                                recordForm.setData({
                                                    ...recordForm.data,
                                                    client_id: e.target.value,
                                                    membership_id: '',
                                                })
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
                                            message={
                                                recordForm.errors.client_id
                                            }
                                            className="mt-2"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="membership_id"
                                            value="Membership (optional)"
                                        />
                                        <select
                                            id="membership_id"
                                            value={
                                                recordForm.data.membership_id
                                            }
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'membership_id',
                                                    e.target.value,
                                                )
                                            }
                                            disabled={!recordForm.data.client_id}
                                        >
                                            <option value="">
                                                No membership link
                                            </option>
                                            {availableMemberships.map((m) => (
                                                <option key={m.id} value={m.id}>
                                                    {m.label}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError
                                            message={
                                                recordForm.errors
                                                    .membership_id
                                            }
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="amount"
                                            value="Amount"
                                        />
                                        <TextInput
                                            id="amount"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            value={recordForm.data.amount}
                                            className="mt-1 block w-full"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'amount',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />
                                        <InputError
                                            message={recordForm.errors.amount}
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
                                            value={recordForm.data.currency}
                                            maxLength={3}
                                            className="mt-1 block w-full uppercase"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'currency',
                                                    e.target.value.toUpperCase(),
                                                )
                                            }
                                            required
                                        />
                                        <InputError
                                            message={
                                                recordForm.errors.currency
                                            }
                                            className="mt-2"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="method"
                                            value="Method"
                                        />
                                        <select
                                            id="method"
                                            value={recordForm.data.method}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'method',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            <option value="cash">Cash</option>
                                            <option value="transfer">
                                                Transfer
                                            </option>
                                        </select>
                                        <InputError
                                            message={recordForm.errors.method}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="paid_at"
                                            value="Paid at"
                                        />
                                        <TextInput
                                            id="paid_at"
                                            type="datetime-local"
                                            value={recordForm.data.paid_at}
                                            className="mt-1 block w-full"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'paid_at',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />
                                        <InputError
                                            message={recordForm.errors.paid_at}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="reference"
                                            value="Reference (optional)"
                                        />
                                        <TextInput
                                            id="reference"
                                            value={recordForm.data.reference}
                                            className="mt-1 block w-full"
                                            onChange={(e) =>
                                                recordForm.setData(
                                                    'reference',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={
                                                recordForm.errors.reference
                                            }
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
                                        value={recordForm.data.notes}
                                        rows={2}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onChange={(e) =>
                                            recordForm.setData(
                                                'notes',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={recordForm.errors.notes}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="flex justify-end">
                                    <PrimaryButton
                                        disabled={recordForm.processing}
                                    >
                                        Record payment
                                    </PrimaryButton>
                                </div>
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
                        {payments.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                No payments recorded on this day.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {payments.map((payment) => (
                                    <li key={payment.id} className="p-6">
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <div className="font-medium text-gray-900">
                                                    {formatTime(
                                                        payment.paid_at,
                                                    )}{' '}
                                                    &middot;{' '}
                                                    {payment.client.full_name}
                                                    <span
                                                        className={`ml-2 rounded-full px-2 py-0.5 text-xs ${STATUS_STYLES[payment.status] ?? 'bg-gray-100 text-gray-700'}`}
                                                    >
                                                        {
                                                            payment.status_label
                                                        }
                                                    </span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {payment.amount}{' '}
                                                    {payment.currency}{' '}
                                                    &middot;{' '}
                                                    {payment.method_label}
                                                    {payment.membership &&
                                                        ` · ${payment.membership.plan_name}`}
                                                    {payment.reference &&
                                                        ` · ref: ${payment.reference}`}
                                                </div>
                                                {payment.status ===
                                                    'refunded' && (
                                                    <div className="mt-1 text-sm text-yellow-700">
                                                        Refunded{' '}
                                                        {
                                                            payment.refunded_amount
                                                        }{' '}
                                                        {payment.currency}
                                                        {payment.refund_reason &&
                                                            ` — ${payment.refund_reason}`}
                                                    </div>
                                                )}
                                                {payment.status ===
                                                    'voided' && (
                                                    <div className="mt-1 text-sm text-red-700">
                                                        Voided
                                                        {payment.void_reason &&
                                                            ` — ${payment.void_reason}`}
                                                    </div>
                                                )}
                                            </div>

                                            {canCorrect && (
                                                <div className="flex flex-shrink-0 gap-2">
                                                    {payment.can_refund && (
                                                        <SecondaryButton
                                                            onClick={() =>
                                                                openRefund(
                                                                    payment,
                                                                )
                                                            }
                                                        >
                                                            Refund
                                                        </SecondaryButton>
                                                    )}
                                                    {payment.can_void && (
                                                        <DangerButton
                                                            onClick={() =>
                                                                openVoid(
                                                                    payment.id,
                                                                )
                                                            }
                                                        >
                                                            Void
                                                        </DangerButton>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>

            <Modal show={voidingId !== null} onClose={() => setVoidingId(null)}>
                <form onSubmit={submitVoid} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Void this payment?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        This marks the payment as if it never happened and
                        reverses it from any linked membership balance. Use
                        this for a data-entry mistake, not for money that
                        was genuinely paid and needs to go back — that's a
                        refund instead.
                    </p>

                    <div className="mt-4">
                        <InputLabel
                            htmlFor="void_reason"
                            value="Reason"
                            className="sr-only"
                        />
                        <textarea
                            id="void_reason"
                            value={voidForm.data.void_reason}
                            rows={3}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Why is this payment being voided?"
                            onChange={(e) =>
                                voidForm.setData(
                                    'void_reason',
                                    e.target.value,
                                )
                            }
                        />
                        <InputError
                            message={voidForm.errors.void_reason}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setVoidingId(null)}>
                            Never mind
                        </SecondaryButton>
                        <DangerButton
                            className="ms-3"
                            disabled={voidForm.processing}
                        >
                            Void payment
                        </DangerButton>
                    </div>
                </form>
            </Modal>

            <Modal
                show={refundingId !== null}
                onClose={() => setRefundingId(null)}
            >
                <form onSubmit={submitRefund} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Refund this payment
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Partial refunds are fine — refund what was actually
                        given back. This can be done more than once on the
                        same payment.
                    </p>

                    <div className="mt-4 space-y-4">
                        <div>
                            <InputLabel
                                htmlFor="refund_amount"
                                value="Refund amount"
                            />
                            <TextInput
                                id="refund_amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                value={refundForm.data.amount}
                                className="mt-1 block w-full"
                                onChange={(e) =>
                                    refundForm.setData(
                                        'amount',
                                        e.target.value,
                                    )
                                }
                                required
                            />
                            <InputError
                                message={refundForm.errors.amount}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="refund_reason"
                                value="Reason"
                            />
                            <textarea
                                id="refund_reason"
                                value={refundForm.data.refund_reason}
                                rows={3}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Why is this being refunded?"
                                onChange={(e) =>
                                    refundForm.setData(
                                        'refund_reason',
                                        e.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={refundForm.errors.refund_reason}
                                className="mt-2"
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setRefundingId(null)}>
                            Never mind
                        </SecondaryButton>
                        <DangerButton
                            className="ms-3"
                            disabled={refundForm.processing}
                        >
                            Refund
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
