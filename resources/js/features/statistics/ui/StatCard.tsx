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
        bg: 'bg-gradient-to-br from-primary to-primary/80',
        text: 'text-primary-foreground',
    },
    secondary: {
        bg: 'bg-gradient-to-br from-secondary to-secondary/80',
        text: 'text-secondary-foreground',
    },
    accent: {
        bg: 'bg-gradient-to-br from-accent to-accent/80',
        text: 'text-accent-foreground',
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
        <Card className="group relative overflow-hidden border border-border bg-card p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div
                className={`absolute top-0 right-0 h-32 w-32 rounded-full opacity-5 blur-3xl transition-opacity duration-300 group-hover:opacity-10 ${colors.bg}`}
            />

            <div className="relative flex items-start gap-4">
                <div
                    className={`flex h-14 w-14 shrink-0 items-center justify-center rounded-xl ${colors.bg} shadow-lg transition-transform duration-300 group-hover:scale-110`}
                >
                    <Icon
                        className={`h-7 w-7 ${colors.text}`}
                        strokeWidth={2.5}
                    />
                </div>

                <div className="min-w-0 flex-1">
                    <p className="text-sm font-medium text-muted-foreground">
                        {title}
                    </p>
                    <p className="mt-1.5 truncate text-3xl font-bold text-foreground">
                        {value}
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </div>
        </Card>
    );
}
