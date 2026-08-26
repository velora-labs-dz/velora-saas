import { ButtonHTMLAttributes } from 'react';

export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-xl border border-transparent bg-ink px-4 py-2 text-xs font-semibold uppercase tracking-widest text-background transition duration-150 ease-in-out hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 active:opacity-100 ${
                    disabled && 'opacity-40'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
