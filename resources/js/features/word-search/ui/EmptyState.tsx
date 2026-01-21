import { LucideIcon } from 'lucide-react';

interface EmptyStateProps {
    icon: LucideIcon;
    title: string;
    description: string;
}

export function EmptyState({
    icon: Icon,
    title,
    description,
}: EmptyStateProps) {
    return (
        <div className="group rounded-3xl border-2 border-dashed border-border bg-gradient-to-br from-card to-muted/50 p-16 text-center transition-all duration-500 hover:border-primary/30 hover:bg-gradient-to-br hover:from-primary/5 hover:to-secondary/5">
            <div className="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-muted to-muted/50 shadow-inner transition-all duration-500 group-hover:scale-110 group-hover:from-primary/10 group-hover:to-secondary/10">
                <Icon className="h-10 w-10 text-muted-foreground transition-colors duration-500 group-hover:text-primary" />
            </div>

            <h3 className="mb-3 text-xl font-bold text-foreground">{title}</h3>

            <p className="mx-auto max-w-md text-base text-muted-foreground">
                {description}
            </p>
        </div>
    );
}
