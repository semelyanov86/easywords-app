import { AuthNavigation } from '@/widgets/auth/AuthNavigation';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

/**
 * Simple authentication layout with navigation header.
 * Used for login, registration, and other auth pages.
 */
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col">
            {/* Navigation header */}
            <AuthNavigation />

            {/* Main content */}
            <div className="flex flex-1 items-center justify-center bg-background p-6 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="space-y-2 text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-center text-sm text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    <div className="mt-8">{children}</div>
                </div>
            </div>
        </div>
    );
}
