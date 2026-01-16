import { StatCard } from './StatCard';

interface StatsGridProps {
    stats: Array<{
        iconName: 'users' | 'book-open' | 'eye' | 'zap' | 'trending-up';
        colorScheme: 'primary' | 'secondary' | 'accent';
        title: string;
        value: number | string;
        description: string;
    }>;
    columns?: 2 | 3 | 4;
}

export function StatsGrid({ stats, columns = 4 }: StatsGridProps) {
    const gridColsClass = {
        2: 'sm:grid-cols-2',
        3: 'sm:grid-cols-2 lg:grid-cols-3',
        4: 'sm:grid-cols-2 lg:grid-cols-4',
    };

    return (
        <div className={`grid gap-5 ${gridColsClass[columns]}`}>
            {stats.map((stat, index) => (
                <StatCard key={index} {...stat} />
            ))}
        </div>
    );
}
