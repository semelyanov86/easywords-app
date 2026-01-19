import { Link } from '@inertiajs/react';
import { ArrowLeft, type LucideIcon } from 'lucide-react';

interface PageHeaderProps {
    title: string;
    subtitle?: string;
    backLink?: {
        url: string;
        label: string;
    };
    icon?: LucideIcon;
}

export function PageHeader({
    title,
    subtitle,
    backLink,
    icon: Icon,
}: PageHeaderProps) {
    return (
        <div className="mb-8">
            {backLink && (
                <Link
                    href={backLink.url}
                    className="dark:hover:text-primary-400 mb-4 inline-flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary dark:text-neutral-400"
                >
                    <ArrowLeft className="h-4 w-4" />
                    {backLink.label}
                </Link>
            )}

            <div className="flex items-start gap-4">
                {Icon && (
                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-secondary to-accent text-white shadow-lg">
                        <Icon className="h-6 w-6" />
                    </div>
                )}
                <div className="flex-1">
                    <h1 className="text-primary-900 dark:text-primary-100 text-4xl font-bold tracking-tight sm:text-5xl">
                        {title}
                    </h1>
                    {subtitle && (
                        <p className="text-primary-700 dark:text-primary-300 mt-3 text-lg">
                            {subtitle}
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
