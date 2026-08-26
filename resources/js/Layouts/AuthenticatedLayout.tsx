import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

const NAV_ITEMS = [
    { name: 'Dashboard', route: 'dashboard', match: 'dashboard' },
    { name: 'Organizations', route: 'organizations.index', match: 'organizations.*' },
    { name: 'Clients', route: 'clients.index', match: 'clients.*' },
    { name: 'Services', route: 'services.index', match: 'services.*' },
    { name: 'Plans', route: 'membership-plans.index', match: 'membership-plans.*' },
    { name: 'Memberships', route: 'memberships.index', match: 'memberships.*' },
    { name: 'Appointments', route: 'appointments.index', match: 'appointments.*' },
    { name: 'Attendance', route: 'attendance.index', match: 'attendance.*' },
    { name: 'Payments', route: 'payments.index', match: 'payments.*' },
];

function Wordmark() {
    return (
        <span className="font-serif text-2xl font-light tracking-tight text-ink">
            Velora
            <span className="text-gold">.</span>
        </span>
    );
}

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;

    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-background">
            {/* Desktop sidebar */}
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-border bg-card lg:flex">
                <div className="flex h-16 items-center border-b border-border px-6">
                    <Link href="/">
                        <Wordmark />
                    </Link>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {NAV_ITEMS.map((item) => (
                        <NavLink
                            key={item.route}
                            href={route(item.route)}
                            active={route().current(item.match)}
                        >
                            {item.name}
                        </NavLink>
                    ))}
                </nav>

                <div className="border-t border-border p-3">
                    <Dropdown>
                        <Dropdown.Trigger>
                            <button
                                type="button"
                                className="flex w-full items-center justify-between rounded-xl px-3 py-2 text-start text-sm text-foreground transition duration-150 ease-in-out hover:bg-sand"
                            >
                                <span className="truncate">{user.name}</span>
                                <svg
                                    className="ms-2 h-4 w-4 flex-shrink-0 text-muted-foreground"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </button>
                        </Dropdown.Trigger>

                        <Dropdown.Content width="48" align="left">
                            <Dropdown.Link href={route('profile.edit')}>
                                Profile
                            </Dropdown.Link>
                            <Dropdown.Link
                                href={route('logout')}
                                method="post"
                                as="button"
                            >
                                Log Out
                            </Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </div>
            </aside>

            {/* Mobile top bar */}
            <div className="sticky top-0 z-30 border-b border-border bg-card lg:hidden">
                <div className="flex h-16 items-center justify-between px-4">
                    <Link href="/">
                        <Wordmark />
                    </Link>

                    <button
                        onClick={() => setMobileMenuOpen((v) => !v)}
                        className="inline-flex items-center justify-center rounded-xl p-2 text-muted-foreground transition duration-150 ease-in-out hover:bg-sand hover:text-ink focus:outline-none"
                    >
                        <svg
                            className="h-6 w-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                className={!mobileMenuOpen ? 'inline-flex' : 'hidden'}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                className={mobileMenuOpen ? 'inline-flex' : 'hidden'}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                {mobileMenuOpen && (
                    <div className="border-t border-border pb-3 pt-2">
                        <div className="space-y-1 px-2">
                            {NAV_ITEMS.map((item) => (
                                <ResponsiveNavLink
                                    key={item.route}
                                    href={route(item.route)}
                                    active={route().current(item.match)}
                                >
                                    {item.name}
                                </ResponsiveNavLink>
                            ))}
                        </div>

                        <div className="mt-3 border-t border-border pt-3">
                            <div className="px-4">
                                <div className="text-base font-medium text-ink">
                                    {user.name}
                                </div>
                                <div className="text-sm font-medium text-muted-foreground">
                                    {user.email}
                                </div>
                            </div>

                            <div className="mt-3 space-y-1">
                                <ResponsiveNavLink href={route('profile.edit')}>
                                    Profile
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    method="post"
                                    href={route('logout')}
                                    as="button"
                                >
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            <div className="lg:pl-64">
                {header && (
                    <header className="border-b border-border bg-card">
                        <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {header}
                        </div>
                    </header>
                )}

                <main>{children}</main>
            </div>
        </div>
    );
}
