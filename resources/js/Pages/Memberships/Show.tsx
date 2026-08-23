import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface MembershipDetail {
    id: number;
    status: string;
    status_label: string;
    client: { id: number; full_name: string };
    plan: { id: number; name: string; freeze_allowed: boolean };
    starts_at: string;
    ends_at: string;
    price: string;
    currency: string;
    paid_amount: string;
    remaining_amount: string;
    notes: string | null;
    activated_at: string | null;
    frozen_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    created_at: string | null;
}

interface Props {
    membership: MembershipDetail;
    canManage: boolean;
    canCancel: boolean;
}

const STATUS_STYLES: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700',
    active: 'bg-green-100 text-green-700',
    frozen: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    expired: 'bg-yellow-100 text-yellow-700',
};

export default function Show({ membership, canManage, canCancel }: Props) {
    const [confirmingCancel, setConfirmingCancel] = useState(false);

    const {
        data,
        setData,
        patch,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        cancellation_reason: '',
    });

    const activate = () => {
        router.patch(route('memberships.activate', membership.id));
    };

    const freeze = () => {
        router.patch(route('memberships.freeze', membership.id));
    };

    const unfreeze = () => {
        router.patch(route('memberships.unfreeze', membership.id));
    };

    const expire = () => {
        router.patch(route('memberships.expire', membership.id));
    };

    const closeCancelModal = () => {
        setConfirmingCancel(false);
        clearErrors();
        reset();
    };

    const submitCancel: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('memberships.cancel', membership.id), {
            preserveScroll: true,
            onSuccess: () => closeCancelModal(),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {membership.client.full_name}'s membership
                </h2>
            }
        >
            <Head title={`${membership.client.full_name} — Membership`} />

            <div className="py-12">
                <div className="mx-auto max-w-xl space-y-6 sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="flex items-start justify-between">
                            <div>
                                <div className="text-lg font-medium text-gray-900">
                                    {membership.plan.name}
                                </div>
                                <div className="text-sm text-gray-500">
                                    {membership.starts_at} –{' '}
                                    {membership.ends_at}
                                </div>
                            </div>
                            <span
                                className={`rounded-full px-3 py-1 text-sm font-medium ${STATUS_STYLES[membership.status] ?? 'bg-gray-100 text-gray-700'}`}
                            >
                                {membership.status_label}
                            </span>
                        </div>

                        <dl className="mt-6 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt className="text-gray-500">Price</dt>
                                <dd className="text-gray-900">
                                    {membership.price} {membership.currency}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Paid</dt>
                                <dd className="text-gray-900">
                                    {membership.paid_amount}{' '}
                                    {membership.currency}
                                </dd>
                            </div>
                            {membership.notes && (
                                <div className="col-span-2">
                                    <dt className="text-gray-500">Notes</dt>
                                    <dd className="text-gray-900">
                                        {membership.notes}
                                    </dd>
                                </div>
                            )}
                            {membership.status === 'cancelled' && (
                                <div className="col-span-2">
                                    <dt className="text-gray-500">
                                        Cancellation reason
                                    </dt>
                                    <dd className="text-gray-900">
                                        {membership.cancellation_reason}
                                    </dd>
                                </div>
                            )}
                        </dl>

                        {canManage && (
                            <div className="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-6">
                                {membership.status === 'draft' && (
                                    <>
                                        <Link
                                            href={route(
                                                'memberships.edit',
                                                membership.id,
                                            )}
                                        >
                                            <SecondaryButton>
                                                Edit
                                            </SecondaryButton>
                                        </Link>
                                        <PrimaryButton onClick={activate}>
                                            Activate
                                        </PrimaryButton>
                                    </>
                                )}

                                {membership.status === 'active' && (
                                    <>
                                        {membership.plan.freeze_allowed && (
                                            <SecondaryButton onClick={freeze}>
                                                Freeze
                                            </SecondaryButton>
                                        )}
                                        <SecondaryButton onClick={expire}>
                                            Mark expired
                                        </SecondaryButton>
                                    </>
                                )}

                                {membership.status === 'frozen' && (
                                    <PrimaryButton onClick={unfreeze}>
                                        Unfreeze
                                    </PrimaryButton>
                                )}

                                {canCancel &&
                                    (membership.status === 'draft' ||
                                        membership.status === 'active' ||
                                        membership.status === 'frozen') && (
                                        <DangerButton
                                            onClick={() =>
                                                setConfirmingCancel(true)
                                            }
                                        >
                                            Cancel
                                        </DangerButton>
                                    )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Modal show={confirmingCancel} onClose={closeCancelModal}>
                <form onSubmit={submitCancel} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Cancel this membership?
                    </h2>

                    <p className="mt-1 text-sm text-gray-600">
                        This ends {membership.client.full_name}'s access
                        immediately and can't be undone — a cancelled
                        membership can't be reactivated. Please give a
                        reason for the record.
                    </p>

                    <div className="mt-4">
                        <InputLabel
                            htmlFor="cancellation_reason"
                            value="Reason"
                            className="sr-only"
                        />
                        <textarea
                            id="cancellation_reason"
                            value={data.cancellation_reason}
                            rows={3}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Why is this membership being cancelled?"
                            onChange={(e) =>
                                setData(
                                    'cancellation_reason',
                                    e.target.value,
                                )
                            }
                        />
                        <InputError
                            message={errors.cancellation_reason}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeCancelModal}>
                            Never mind
                        </SecondaryButton>

                        <DangerButton className="ms-3" disabled={processing}>
                            Cancel membership
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
