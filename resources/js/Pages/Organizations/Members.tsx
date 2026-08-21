import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const ROLES = ['owner', 'admin', 'staff', 'viewer'] as const;

interface Member {
    id: number;
    name: string;
    email: string;
    role: (typeof ROLES)[number];
    is_active: boolean;
    joined_at: string | null;
    is_you: boolean;
}

interface Props {
    organization: {
        id: number;
        name: string;
        slug: string;
    };
    members: Member[];
    canManage: boolean;
    viewerIsOwner: boolean;
}

export default function Members({
    organization,
    members,
    canManage,
    viewerIsOwner,
}: Props) {
    const assignableRoles = viewerIsOwner
        ? ROLES
        : ROLES.filter((role) => role !== 'owner');

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: 'staff' as (typeof ROLES)[number],
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('organizations.members.store', organization.slug), {
            onSuccess: () => reset(),
        });
    };

    const changeRole = (member: Member, role: string) => {
        router.patch(
            route('organizations.members.update', [
                organization.slug,
                member.id,
            ]),
            { role },
        );
    };

    const removeMember = (member: Member) => {
        if (!confirm(`Remove ${member.name} from ${organization.name}?`)) {
            return;
        }

        router.delete(
            route('organizations.members.destroy', [
                organization.slug,
                member.id,
            ]),
        );
    };

    const leave = () => {
        if (!confirm(`Leave ${organization.name}?`)) {
            return;
        }

        router.post(route('organizations.leave', organization.slug));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {organization.name} — Members
                </h2>
            }
        >
            <Head title={`${organization.name} — Members`} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                    {canManage && (
                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-sm font-medium text-gray-700">
                                Add a member
                            </h3>

                            <p className="mb-4 text-sm text-gray-500">
                                The person must already have a Velora account
                                — there is no email invite yet.
                            </p>

                            <form
                                onSubmit={submit}
                                className="flex flex-wrap items-end gap-4"
                            >
                                <div className="flex-1">
                                    <InputLabel htmlFor="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.email}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel htmlFor="role" value="Role" />
                                    <select
                                        id="role"
                                        value={data.role}
                                        className="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onChange={(e) =>
                                            setData(
                                                'role',
                                                e.target
                                                    .value as (typeof ROLES)[number],
                                            )
                                        }
                                    >
                                        {assignableRoles.map((role) => (
                                            <option key={role} value={role}>
                                                {role}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <PrimaryButton disabled={processing}>
                                    Add
                                </PrimaryButton>
                            </form>
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <ul className="divide-y divide-gray-100">
                            {members.map((member) => {
                                const targetIsOwner = member.role === 'owner';
                                const showControls =
                                    canManage &&
                                    !member.is_you &&
                                    (!targetIsOwner || viewerIsOwner);

                                return (
                                    <li
                                        key={member.id}
                                        className="flex items-center justify-between p-6"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {member.name}
                                                {member.is_you && (
                                                    <span className="ml-2 text-xs text-gray-400">
                                                        (you)
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {member.email}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-3">
                                            {showControls ? (
                                                <>
                                                    <select
                                                        value={member.role}
                                                        className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        onChange={(e) =>
                                                            changeRole(
                                                                member,
                                                                e.target
                                                                    .value,
                                                            )
                                                        }
                                                    >
                                                        {assignableRoles.map(
                                                            (role) => (
                                                                <option
                                                                    key={role}
                                                                    value={
                                                                        role
                                                                    }
                                                                >
                                                                    {role}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>

                                                    <SecondaryButton
                                                        onClick={() =>
                                                            removeMember(
                                                                member,
                                                            )
                                                        }
                                                    >
                                                        Remove
                                                    </SecondaryButton>
                                                </>
                                            ) : (
                                                <span className="text-sm text-gray-500">
                                                    {member.role}
                                                </span>
                                            )}
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>

                    <div className="flex justify-end">
                        <SecondaryButton onClick={leave}>
                            Leave organization
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
