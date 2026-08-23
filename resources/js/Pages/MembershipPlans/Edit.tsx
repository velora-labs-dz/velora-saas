import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface PlanDetail {
    id: number;
    name: string;
    description: string | null;
    duration_value: number;
    duration_unit: string;
    price: string;
    currency: string;
    sessions_limit: number | null;
    visits_per_period: number | null;
    freeze_allowed: boolean;
    freeze_limit: number | null;
}

interface Props {
    plan: PlanDetail;
}

export default function Edit({ plan }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: plan.name,
        description: plan.description ?? '',
        duration_value: String(plan.duration_value),
        duration_unit: plan.duration_unit,
        price: plan.price,
        currency: plan.currency,
        sessions_limit: plan.sessions_limit ? String(plan.sessions_limit) : '',
        visits_per_period: plan.visits_per_period
            ? String(plan.visits_per_period)
            : '',
        freeze_allowed: plan.freeze_allowed,
        freeze_limit: plan.freeze_limit ? String(plan.freeze_limit) : '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('membership-plans.update', plan.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit membership plan
                </h2>
            }
        >
            <Head title="Edit membership plan" />

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="name" value="Name" />
                                <TextInput
                                    id="name"
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

                            <div>
                                <InputLabel
                                    htmlFor="description"
                                    value="Description (optional)"
                                />
                                <textarea
                                    id="description"
                                    value={data.description}
                                    rows={3}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.description}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="duration_value"
                                        value="Duration"
                                    />
                                    <TextInput
                                        id="duration_value"
                                        type="number"
                                        min="1"
                                        value={data.duration_value}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'duration_value',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.duration_value}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="duration_unit"
                                        value="Duration unit"
                                    />
                                    <select
                                        id="duration_unit"
                                        value={data.duration_unit}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onChange={(e) =>
                                            setData(
                                                'duration_unit',
                                                e.target.value,
                                            )
                                        }
                                    >
                                        <option value="days">Days</option>
                                        <option value="weeks">Weeks</option>
                                        <option value="months">Months</option>
                                    </select>
                                    <InputError
                                        message={errors.duration_unit}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

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

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="sessions_limit"
                                        value="Session limit (optional)"
                                    />
                                    <TextInput
                                        id="sessions_limit"
                                        type="number"
                                        min="1"
                                        value={data.sessions_limit}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'sessions_limit',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={errors.sessions_limit}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="visits_per_period"
                                        value="Visits per period (optional)"
                                    />
                                    <TextInput
                                        id="visits_per_period"
                                        type="number"
                                        min="1"
                                        value={data.visits_per_period}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'visits_per_period',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={errors.visits_per_period}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="flex items-center">
                                    <Checkbox
                                        checked={data.freeze_allowed}
                                        onChange={(e) =>
                                            setData(
                                                'freeze_allowed',
                                                e.target.checked,
                                            )
                                        }
                                    />
                                    <span className="ms-2 text-sm text-gray-600">
                                        Allow memberships on this plan to
                                        freeze
                                    </span>
                                </label>
                                <InputError
                                    message={errors.freeze_allowed}
                                    className="mt-2"
                                />
                            </div>

                            {data.freeze_allowed && (
                                <div>
                                    <InputLabel
                                        htmlFor="freeze_limit"
                                        value="Freeze limit (optional)"
                                    />
                                    <TextInput
                                        id="freeze_limit"
                                        type="number"
                                        min="1"
                                        value={data.freeze_limit}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData(
                                                'freeze_limit',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <p className="mt-1 text-xs text-gray-400">
                                        Stored for future use — not enforced
                                        yet.
                                    </p>
                                    <InputError
                                        message={errors.freeze_limit}
                                        className="mt-2"
                                    />
                                </div>
                            )}

                            <div className="flex items-center justify-end">
                                <PrimaryButton disabled={processing}>
                                    Save changes
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
