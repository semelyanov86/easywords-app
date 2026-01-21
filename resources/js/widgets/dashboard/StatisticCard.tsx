import type { LucideIcon } from 'lucide-react';

interface StatisticCardProps {
    icon: LucideIcon;
    iconBgColor: 'primary' | 'secondary' | 'accent';
    title: string;
    value: number | string;
    description: string;
}

const iconBgColorMap = {
    primary: 'bg-primary/10',
    secondary: 'bg-secondary/10',
    accent: 'bg-accent/10',
};

const iconColorMap = {
    primary: 'text-primary',
    secondary: 'text-secondary',
    accent: 'text-accent',
};

export function StatisticCard({
    icon: Icon,
    iconBgColor,
    title,
    value,
    description,
}: StatisticCardProps) {
    return (
        <div className="group rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div className="mb-3 flex items-center gap-3">
                <div
                    className={`rounded-lg ${iconBgColorMap[iconBgColor]} p-2`}
                >
                    <Icon className={`h-5 w-5 ${iconColorMap[iconBgColor]}`} />
                </div>
                <h3 className="font-semibold text-card-foreground">{title}</h3>
            </div>
            <p className="text-3xl font-bold text-foreground">{value}</p>
            <p className="mt-1 text-sm text-muted-foreground">{description}</p>
        </div>
    );
}
