import { Button } from '@/components/ui/button';
import { Key, Plus } from 'lucide-react';
import { TokenCard } from './TokenCard';

interface Token {
    id: string;
    name: string;
    plainTextToken?: string;
    created_at: string;
    last_used_at: string | null;
}

interface TokenListProps {
    tokens: Token[];
    onDelete: (tokenId: string) => void;
    onCreateClick: () => void;
    translations: {
        noTokens: string;
        createToken: string;
        created: string;
        lastUsed: string;
        tokenWarning: string;
    };
}

export function TokenList({
    tokens,
    onDelete,
    onCreateClick,
    translations,
}: TokenListProps) {
    if (tokens.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-12 text-center">
                <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100">
                    <Key className="h-8 w-8 text-neutral-400" />
                </div>
                <p className="text-lg font-medium text-neutral-600">
                    {translations.noTokens}
                </p>
                <Button
                    onClick={onCreateClick}
                    className="mt-6 inline-flex h-10 items-center justify-center gap-2 rounded-md border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition-all hover:bg-neutral-50 hover:shadow focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
                    variant="outline"
                >
                    <Plus className="h-4 w-4" />
                    {translations.createToken}
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {tokens.map((token) => (
                <TokenCard
                    key={token.id}
                    token={token}
                    onDelete={onDelete}
                    translations={{
                        created: translations.created,
                        lastUsed: translations.lastUsed,
                        tokenWarning: translations.tokenWarning,
                    }}
                />
            ))}
        </div>
    );
}
