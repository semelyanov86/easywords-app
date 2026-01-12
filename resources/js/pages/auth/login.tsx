import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { Form, Head } from '@inertiajs/react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: LoginProps) {
    const t = useTranslation();

    return (
        <AuthLayout
            title={t.auth.login.title}
            description={t.auth.login.description}
        >
            <Head title={t.auth.login.title} />

            <Form
                action={login().url}
                method="post"
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t.auth.login.email}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder={t.auth.login.email_placeholder}
                                    className="focus-visible:ring-primary"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password">
                                        {t.auth.login.password}
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href="/forgot-password"
                                            className="ml-auto text-sm text-primary hover:text-primary/80"
                                            tabIndex={5}
                                        >
                                            {t.auth.login.forgot_password}
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={
                                        t.auth.login.password_placeholder
                                    }
                                    className="focus-visible:ring-primary"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                    className="data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                                />
                                <Label
                                    htmlFor="remember"
                                    className="cursor-pointer"
                                >
                                    {t.auth.login.remember_me}
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full bg-primary text-primary-foreground hover:bg-primary/90"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                {t.auth.login.submit}
                            </Button>
                        </div>

                        {canRegister && (
                            <div className="text-center text-sm text-muted-foreground">
                                {t.auth.login.no_account}{' '}
                                <TextLink href="/register" tabIndex={5}>
                                    {t.auth.login.sign_up}
                                </TextLink>
                            </div>
                        )}
                    </>
                )}
            </Form>

            {status && (
                <div className="mb-4 rounded-md bg-secondary/10 p-3 text-center text-sm font-medium text-secondary">
                    {status}
                </div>
            )}
        </AuthLayout>
    );
}
