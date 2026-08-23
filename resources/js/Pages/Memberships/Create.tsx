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

interface PlanOption {
    id: number;
    name: string;
    duration_value: number;
    duration_unit: string;
    price: string;
    currency: string;
    freeze_allowed: boolean;
}

interface Props {
    clients: ClientOption[];
    plans: PlanOption[];
}

function addDuration(
    start: string,
    value: number,
    unit: string,
): string {
    const date = new Date(start);

    if (unit === 'days') date.setDate(date.getDate() + value);
    else if (unit === 'weeks') date.setDate(date.getDate() + value * 7);
    else date.setMonth(date.getMonth() + value);

    return date.toISOString().slice(0, 10);
}

export default function Create({ clients, plans }: Props) {
    const today = new Date().toISOString().slice(0, 10);

    const { data, setData, post, processing, errors } = useForm({
        client_id: '',
        membership_plan_id: '',
        starts_at: today,
        ends_at: '',
        price: '',
        currency: 'DZD',
        notes: '',
    });

    const applyPlanDefaults = (planId: string) => {
        const plan = plans.find((p) => String(p.id) === planId);

        setData((current) => ({
            ...current,
            membership_plan_id: planId,
            price: plan ? plan.price : current.price,
            currency: plan ? plan.currency : current.currency,
            ends_at: plan
                ? addDuration(
                      current.starts_at || today,
                      plan.duration_value,
                      plan.duration_unit,
                  )
                : current.ends_at,
        }));
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('memberships.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Assign membership
                </h2>
            }
        >
            <Head title="Assign membership" />

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
                                    htmlFor="membership_plan_id"
                                    value="Plan"
                                />
                                <select
                                    id="membership_plan_id"
                                    value={data.membership_plan_id}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        applyPlanDefaults(e.target.value)
                                    }
                                    required
                                >
                                    <option value="">Select a plan</option>
                                    {plans.map((plan) => (
                                        <option key={plan.id} value={plan.id}>
                                            {plan.name} — {plan.price}{' '}
                                            {plan.currency}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.membership_plan_id}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="starts_at"
                                        value="Start date"
                                    />
                                    <TextInput
                                        id="starts_at"
                                        type="date"
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
                                        value="End date"
                                    />
                                    <TextInput
                                        id="ends_at"
                                        type="date"
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
                                The end date is pre-filled from the plan's
                                duration but can be adjusted.
                            </p>

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
                                    Assign as draft
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
