import AppLogoIcon from '@/components/app-logo-icon';
import { Link } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    return (
        <div className="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-white via-ice to-sky/30 p-6 md:p-10">
            <div className="pointer-events-none absolute -top-24 right-10 h-80 w-80 rounded-full bg-sky/25 blur-[110px]" />
            <div className="pointer-events-none absolute bottom-0 left-0 h-80 w-80 rounded-full bg-royal/20 blur-[110px]" />
            <div className="relative w-full max-w-md">
                <div className="flex flex-col gap-8 rounded-3xl border border-mist bg-white p-8 shadow-xl shadow-sky/10">
                    <div className="flex flex-col items-center gap-4">
                        <Link href={route('home')} className="flex flex-col items-center gap-2 font-medium">
                            <div className="mb-1 flex h-11 w-11 items-center justify-center rounded-xl bg-navy text-white">
                                <AppLogoIcon className="size-6 text-white" />
                            </div>
                            <span className="font-display text-lg font-bold text-navy">
                                LegalCloud <span className="text-royal">AI</span>
                            </span>
                        </Link>
                        <div className="space-y-2 text-center">
                            <h1 className="font-display text-xl font-bold text-navy">{title}</h1>
                            <p className="text-center text-sm text-muted-foreground">{description}</p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
