import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import profile from '@/routes/profile';
import { passwordTranslations } from '@/shared/i18n/password';
import { useNestedTranslation } from '@/shared/i18n/useNestedTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Lock } from 'lucide-react';
import { FormEvent } from 'react';

interface PasswordEditProps {
    user: User;
    errors?: {
        current_password?: string[];
        password?: string[];
    };
}

export default function Edit({ user, errors }: PasswordEditProps) {
    const t = useNestedTranslation(passwordTranslations);
    const { data, setData, post, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post(profile.password.update().url);
    };

    return (
        <>
            <Head title={'Easywords Change Password'} />
            <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
                <AuthHeader userName={user.name} />
                <main className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
                    <Card className="border-neutral-200/60 bg-white/80 shadow-lg backdrop-blur-sm">
                        <CardHeader className="space-y-1 pb-6">
                            <div className="flex items-center gap-3">
                                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#1E5F8C] to-[#7CB342]">
                                    <Lock className="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <CardTitle className="text-2xl font-bold text-neutral-900">
                                        {t.title}
                                    </CardTitle>
                                    <CardDescription className="text-neutral-600">
                                        {t.subtitle}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-5">
                                {recentlySuccessful && (
                                    <Alert className="border-[#7CB342]/20 bg-green-50/50">
                                        <CheckCircle2 className="h-4 w-4 text-[#7CB342]" />
                                        <AlertDescription className="text-sm font-medium text-[#33691E]">
                                            {t.successMessage}
                                        </AlertDescription>
                                    </Alert>
                                )}

                                <div className="space-y-2">
                                    <Label
                                        htmlFor="current_password"
                                        className="text-sm font-medium text-neutral-700"
                                    >
                                        {t.currentPassword}
                                    </Label>
                                    <Input
                                        id="current_password"
                                        type="password"
                                        value={data.current_password}
                                        onChange={(e) =>
                                            setData(
                                                'current_password',
                                                e.target.value,
                                            )
                                        }
                                        placeholder={
                                            t.currentPasswordPlaceholder
                                        }
                                        className="focus-visible:ring-[#1E5F8C]"
                                        required
                                        aria-invalid={
                                            !!errors?.current_password
                                        }
                                        aria-describedby={
                                            errors?.current_password
                                                ? 'current-password-error'
                                                : undefined
                                        }
                                    />
                                    {errors?.current_password && (
                                        <div
                                            id="current-password-error"
                                            className="flex items-center gap-1.5 text-sm text-red-600"
                                        >
                                            <AlertCircle className="h-4 w-4" />
                                            <span>
                                                {errors.current_password[0]}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label
                                        htmlFor="password"
                                        className="text-sm font-medium text-neutral-700"
                                    >
                                        {t.newPassword}
                                    </Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) =>
                                            setData('password', e.target.value)
                                        }
                                        placeholder={t.newPasswordPlaceholder}
                                        className="focus-visible:ring-[#1E5F8C]"
                                        required
                                        aria-invalid={!!errors?.password}
                                        aria-describedby={
                                            errors?.password
                                                ? 'password-error'
                                                : undefined
                                        }
                                    />
                                    {errors?.password && (
                                        <div
                                            id="password-error"
                                            className="flex items-center gap-1.5 text-sm text-red-600"
                                        >
                                            <AlertCircle className="h-4 w-4" />
                                            <span>{errors.password[0]}</span>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label
                                        htmlFor="password_confirmation"
                                        className="text-sm font-medium text-neutral-700"
                                    >
                                        {t.confirmPassword}
                                    </Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) =>
                                            setData(
                                                'password_confirmation',
                                                e.target.value,
                                            )
                                        }
                                        placeholder={
                                            t.confirmPasswordPlaceholder
                                        }
                                        className="focus-visible:ring-[#1E5F8C]"
                                        required
                                    />
                                </div>

                                <div className="pt-2">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full bg-gradient-to-r from-[#1E5F8C] to-[#7CB342] text-white shadow-md transition-all hover:from-[#1E5F8C]/90 hover:to-[#7CB342]/90 hover:shadow-lg disabled:opacity-50"
                                    >
                                        {processing ? t.saving : t.saveButton}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </main>
            </div>
        </>
    );
}
