import profile from '@/routes/profile';
import { passwordTranslations } from '@/shared/i18n/password';
import { useNestedTranslation } from '@/shared/i18n/useNestedTranslation';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { User } from '@/types';

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
        <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
            <AuthHeader userName={user.name} />
            <main className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                        {t.title || 'Change Password'}
                    </h1>
                    <p className="mt-2 text-neutral-600">
                        {t.subtitle ||
                            'Enter your current password and new password to update'}
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {recentlySuccessful && (
                        <div className="rounded-md bg-green-50 p-4">
                            <p className="text-sm font-medium text-green-800">
                                {t.successMessage ||
                                    'Password updated successfully'}
                            </p>
                        </div>
                    )}

                    {/* Current Password */}
                    <div>
                        <label
                            htmlFor="current_password"
                            className="block text-sm font-medium text-neutral-700"
                        >
                            {t.currentPassword || 'Current Password'}
                        </label>
                        <input
                            id="current_password"
                            type="password"
                            value={data.current_password}
                            onChange={(e) =>
                                setData('current_password', e.target.value)
                            }
                            className="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-neutral-900 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/50 focus:outline-none"
                            placeholder={
                                t.currentPasswordPlaceholder ||
                                'Enter current password'
                            }
                            required
                        />
                        {errors?.current_password && (
                            <p className="mt-2 text-sm text-red-600">
                                {errors.current_password[0]}
                            </p>
                        )}
                    </div>

                    {/* New Password */}
                    <div>
                        <label
                            htmlFor="password"
                            className="block text-sm font-medium text-neutral-700"
                        >
                            {t.newPassword || 'New Password'}
                        </label>
                        <input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            className="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-neutral-900 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/50 focus:outline-none"
                            placeholder={
                                t.newPasswordPlaceholder || 'Enter new password'
                            }
                            required
                        />
                        {errors?.password && (
                            <p className="mt-2 text-sm text-red-600">
                                {errors.password[0]}
                            </p>
                        )}
                    </div>

                    {/* Confirm Password */}
                    <div>
                        <label
                            htmlFor="password_confirmation"
                            className="block text-sm font-medium text-neutral-700"
                        >
                            {t.confirmPassword || 'Confirm Password'}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            className="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-neutral-900 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/50 focus:outline-none"
                            placeholder={
                                t.confirmPasswordPlaceholder ||
                                'Repeat new password'
                            }
                            required
                        />
                        {errors?.password && !errors.current_password && (
                            <p className="mt-2 text-sm text-red-600">
                                {errors.password[0]}
                            </p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="hover:bg-primary-600 flex w-full justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing
                                ? t.saving || 'Saving...'
                                : t.saveButton || 'Save Password'}
                        </button>
                    </div>
                </form>
            </main>
        </div>
    );
}
