import { ButtonHTMLAttributes } from 'react';

export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            type={type}
            className={
                `inline-flex items-center rounded-xl border border-border bg-card px-4 py-2 text-xs font-semibold uppercase tracking-widest text-foreground shadow-sm transition duration-150 ease-in-out hover:bg-sand focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 disabled:opacity-40 ${
                    disabled && 'opacity-40'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
