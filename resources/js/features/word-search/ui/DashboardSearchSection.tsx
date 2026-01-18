import { SearchForm } from './SearchForm';

interface DashboardSearchSectionProps {
    title: string;
    placeholder: string;
    buttonText: string;
}

export function DashboardSearchSection({
    title,
    placeholder,
    buttonText,
}: DashboardSearchSectionProps) {
    return (
        <section className="mb-12">
            <div className="mb-6 flex items-center gap-4">
                <h2 className="text-2xl font-bold text-neutral-900 sm:text-3xl">
                    {title}
                </h2>
                <div className="h-px flex-1 bg-gradient-to-r from-neutral-300 to-transparent" />
            </div>

            <div className="max-w-3xl">
                <SearchForm
                    initialQuery=""
                    placeholder={placeholder}
                    buttonText={buttonText}
                />
            </div>
        </section>
    );
}
