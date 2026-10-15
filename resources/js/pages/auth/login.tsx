import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

const demos = [
    { role: 'Armas López Tatiana de Fátima Lucía', email: 'admin@legalcloud.com' },
    { role: 'Arteaga Benites Almendra Nicolle', email: 'documental@legalcloud.com' },
    { role: 'Domínguez Alba Carlos Daniel', email: 'analista@legalcloud.com' },
    { role: 'Lozano Díaz Omar Jack', email: 'consultor@legalcloud.com' },
    { role: 'Segura Venturo Williams Aaron', email: 'williams@legalcloud.com' },
];

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: 'consultor@legalcloud.com',
        password: 'password',
        remember: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Ingresar a LegalCloud AI" description="Gestión documental inteligente — mockup de demostración">
            <Head title="Iniciar sesión" />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Correo</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="consultor@legalcloud.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">Contraseña</Label>
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                    ¿Olvidó su contraseña?
                                </TextLink>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="password"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" tabIndex={3} defaultChecked />
                        <Label htmlFor="remember">Recordarme</Label>
                    </div>

                    <Button type="submit" className="mt-2 w-full bg-royal hover:bg-navy" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Entrar
                    </Button>
                </div>
            </form>

            <div className="rounded-xl border border-mist bg-ice p-4 text-xs text-navy">
                <p className="mb-2 font-semibold">Cuentas de demostración (contraseña: password)</p>
                <ul className="space-y-1">
                    {demos.map((demo) => (
                        <li key={demo.email}>
                            <button type="button" className="text-left hover:underline" onClick={() => setData('email', demo.email)}>
                                <span className="font-medium">{demo.role}:</span> {demo.email}
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            <p className="text-center text-sm text-muted-foreground">
                ¿No tiene cuenta?{' '}
                <TextLink href={route('register')} tabIndex={5}>
                    Registrarse como consultor
                </TextLink>
            </p>

            {status && <div className="text-center text-sm font-medium text-emerald-600">{status}</div>}
        </AuthLayout>
    );
}
