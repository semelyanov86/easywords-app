import { Button } from '@/components/ui/button';
import { PaginationLink } from '@/shared/types/pagination';
import { router } from '@inertiajs/react';

interface PaginationProps {
    links: PaginationLink[];
    lastPage: number;
}

export function Pagination({ links, lastPage }: PaginationProps) {
    if (lastPage <= 1) {
        return null;
    }

    const handlePageChange = (url: string | null): void => {
        if (url) {
            router.visit(url, {
                preserveScroll: true,
                preserveState: true,
            });
        }
    };

    return (
        <nav
            className="flex items-center justify-center gap-2"
            aria-label="Pagination"
        >
            {links.map((link, index) => {
                const isDisabled = link.url === null;
                const isPrevNext = index === 0 || index === links.length - 1;

                return (
                    <Button
                        key={`${link.label}-${index}`}
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        disabled={isDisabled}
                        className={
                            link.active
                                ? 'hover:bg-primary-600 pointer-events-none bg-primary'
                                : isPrevNext
                                  ? 'border-neutral-300 dark:border-neutral-600'
                                  : ''
                        }
                        onClick={() => handlePageChange(link.url)}
                        aria-label={
                            isPrevNext
                                ? link.label
                                      .replace('&laquo;', 'Previous')
                                      .replace('&raquo;', 'Next')
                                : `Page ${link.label}`
                        }
                        aria-current={link.active ? 'page' : undefined}
                    >
                        <span
                            dangerouslySetInnerHTML={{
                                __html: link.label,
                            }}
                        />
                    </Button>
                );
            })}
        </nav>
    );
}
