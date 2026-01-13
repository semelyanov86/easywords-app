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
        <div className="group rounded-xl border border-neutral-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
            <div className="mb-3 flex items-center gap-3">
                <div
                    className={`rounded-lg ${iconBgColorMap[iconBgColor]} p-2`}
                >
                    <Icon className={`h-5 w-5 ${iconColorMap[iconBgColor]}`} />
                </div>
                <h3 className="font-semibold text-neutral-700">{title}</h3>
            </div>
            <p className="text-3xl font-bold text-neutral-900">{value}</p>
            <p className="mt-1 text-sm text-neutral-500">{description}</p>
        </div>
    );
}
