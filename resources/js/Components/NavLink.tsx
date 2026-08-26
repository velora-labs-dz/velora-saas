import { InertiaLinkProps, Link } from '@inertiajs/react';

/**
 * Styled as a vertical sidebar row, not a horizontal tab underline — the
 * dashboard nav outgrew a top bar around the 7th/8th entity (Attendance,
 * Payments), so the shell moved to a sidebar. Kept as NavLink rather than
 * renamed since it's only ever consumed by AuthenticatedLayout.
 *
 * Hover deliberately changes text/border color only, not background —
 * this project's color tokens are raw oklch() values (see app.css), which
 * Tailwind 3's opacity modifiers (bg-x/40) can't apply an alpha channel
 * to. Reserving the solid bg-sand fill for the true active state, rather
 * than reaching for a faded variant on hover, keeps "which page am I on"
 * unambiguous.
 */
export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active: boolean }) {
    return (
        <Link
            {...props}
            className={
                'flex items-center rounded-xl border-l-2 px-3 py-2 text-sm font-medium transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'border-gold bg-sand text-ink'
                    : 'border-transparent text-muted-foreground hover:border-gold-soft hover:text-ink') +
                ' ' +
                className
            }
        >
            {children}
        </Link>
    );
}
