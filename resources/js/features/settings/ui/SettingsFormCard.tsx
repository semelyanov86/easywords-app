import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ReactNode } from 'react';

interface SettingsFormCardProps {
    title: string;
    description?: string;
    icon: ReactNode;
    children: ReactNode;
}

export function SettingsFormCard({
    title,
    description,
    icon,
    children,
}: SettingsFormCardProps) {
    return (
        <Card className="border border-border bg-card shadow-sm transition-shadow hover:shadow-md">
            <CardHeader>
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        {icon}
                    </div>
                    <div>
                        <CardTitle className="text-lg">{title}</CardTitle>
                        {description && (
                            <CardDescription className="mt-1">
                                {description}
                            </CardDescription>
                        )}
                    </div>
                </div>
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}
