import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-background pt-6 sm:justify-center sm:pt-0">
            <div>
                <Link href="/">
                    <span className="font-serif text-3xl font-light tracking-tight text-ink">
                        Velora
                        <span className="text-gold">.</span>
                    </span>
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden rounded-2xl border border-border bg-card px-6 py-4 shadow-soft sm:max-w-md">
                {children}
            </div>
        </div>
    );
}
