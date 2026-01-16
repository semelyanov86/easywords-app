import { Card } from '@/components/ui/card';
import {
    BookOpen,
    Eye,
    LucideIcon,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-react';

interface StatCardProps {
    iconName: 'users' | 'book-open' | 'eye' | 'zap' | 'trending-up';
    colorScheme: 'primary' | 'secondary' | 'accent';
    title: string;
    value: number | string;
    description: string;
}

const iconMap: Record<string, LucideIcon> = {
    users: Users,
    'book-open': BookOpen,
    eye: Eye,
    zap: Zap,
    'trending-up': TrendingUp,
};

const colorClasses = {
    primary: {
        bg: 'bg-gradient-to-br from-[#1E5F8C] to-[#2B7DB8]',
        shadow: 'shadow-blue-500/20',
        accent: 'bg-blue-50',
    },
    secondary: {
        bg: 'bg-gradient-to-br from-[#7CB342] to-[#9CCC65]',
        shadow: 'shadow-green-500/20',
        accent: 'bg-green-50',
    },
    accent: {
        bg: 'bg-gradient-to-br from-[#33691E] to-[#558B2F]',
        shadow: 'shadow-green-700/20',
        accent: 'bg-green-100',
    },
};

export function StatCard({
    iconName,
    colorScheme,
    title,
    value,
    description,
}: StatCardProps) {
    const Icon = iconMap[iconName];
    const colors = colorClasses[colorScheme];

    return (
        <Card className="group relative overflow-hidden p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div
                className={`absolute top-0 right-0 h-32 w-32 rounded-full opacity-5 blur-3xl transition-opacity duration-300 group-hover:opacity-10 ${colors.bg}`}
            />

            <div className="relative flex items-start gap-4">
                <div
                    className={`flex h-14 w-14 shrink-0 items-center justify-center rounded-xl ${colors.bg} shadow-lg ${colors.shadow} transition-transform duration-300 group-hover:scale-110`}
                >
                    <Icon className="h-7 w-7 text-white" strokeWidth={2.5} />
                </div>

                <div className="min-w-0 flex-1">
                    <p className="text-sm font-medium text-neutral-500">
                        {title}
                    </p>
                    <p className="mt-1.5 truncate text-3xl font-bold text-neutral-900">
                        {value}
                    </p>
                    <p className="mt-1 text-sm text-neutral-500">
                        {description}
                    </p>
                </div>
            </div>
        </Card>
    );
}
