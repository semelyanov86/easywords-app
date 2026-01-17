import { ReactNode } from 'react';

interface WordStudyCardProps {
    children: ReactNode;
}

export function WordStudyCard({ children }: WordStudyCardProps) {
    return <div className="mx-auto max-w-2xl">{children}</div>;
}
