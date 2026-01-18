import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { search } from '@/routes/words';
import { router, useForm } from '@inertiajs/react';
import { Search as SearchIcon } from 'lucide-react';

interface SearchFormProps {
    initialQuery: string;
    placeholder: string;
    buttonText: string;
}

export function SearchForm({
    initialQuery,
    placeholder,
    buttonText,
}: SearchFormProps) {
    const searchForm = useForm({
        query: initialQuery,
    });

    const handleSearch = (e: React.FormEvent): void => {
        e.preventDefault();
        if (!searchForm.data.query.trim()) return;

        router.get(
            search().url,
            { q: searchForm.data.query },
            {
                preserveState: true,
                preserveScroll: false,
            },
        );
    };

    return (
        <form onSubmit={handleSearch} className="w-full">
            <div className="group relative">
                <div className="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary/20 to-secondary/20 opacity-0 blur-xl transition-opacity duration-500 group-hover:opacity-100" />

                <div className="relative">
                    <SearchIcon className="absolute top-1/2 left-5 h-5 w-5 -translate-y-1/2 text-neutral-400 transition-colors group-hover:text-primary" />

                    <Input
                        type="text"
                        placeholder={placeholder}
                        value={searchForm.data.query}
                        onChange={(e) =>
                            searchForm.setData('query', e.target.value)
                        }
                        className="h-16 rounded-2xl border-2 border-neutral-200 pr-32 pl-14 text-base shadow-sm transition-all duration-300 hover:border-neutral-300 focus:border-primary focus:ring-4 focus:ring-primary/10"
                    />

                    <Button
                        type="submit"
                        disabled={
                            searchForm.processing ||
                            !searchForm.data.query.trim()
                        }
                        className="absolute top-1/2 right-2 h-12 -translate-y-1/2 rounded-xl bg-gradient-to-r from-primary to-primary/90 px-6 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl disabled:scale-100 disabled:opacity-50"
                    >
                        {searchForm.processing ? (
                            <div className="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
                        ) : (
                            buttonText
                        )}
                    </Button>
                </div>
            </div>
        </form>
    );
}
