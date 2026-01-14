import { Button } from '@/components/ui/button';
import { copyToClipboard } from '@/shared/lib/clipboard';
import { Check, Copy, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface TokenCardProps {
    token: {
        id: string;
        name: string;
        plainTextToken?: string;
        created_at: string;
        last_used_at: string | null;
    };
    onDelete: (tokenId: string) => void;
    translations: {
        created: string;
        lastUsed: string;
        tokenWarning: string;
    };
}

export function TokenCard({ token, onDelete, translations }: TokenCardProps) {
    const [copiedTokenId, setCopiedTokenId] = useState<string | null>(null);

    const handleCopyToken = async (tokenId: string, plainTextToken: string) => {
        const success = await copyToClipboard(plainTextToken);
        if (success) {
            setCopiedTokenId(tokenId);
            setTimeout(() => setCopiedTokenId(null), 2000);
        }
    };

    return (
        <div className="group flex items-center gap-4 rounded-lg border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:border-primary/50 hover:shadow-md">
            <div className="flex-1">
                <h3 className="text-sm font-semibold text-neutral-900">
                    {token.name}
                </h3>
                {token.plainTextToken && (
                    <>
                        <div className="mt-2 flex items-center gap-2 rounded bg-yellow-50 p-3 ring-1 ring-yellow-200">
                            <code className="flex-1 font-mono text-xs break-all text-neutral-900">
                                {token.plainTextToken}
                            </code>
                            <Button
                                size="sm"
                                variant="ghost"
                                onClick={() =>
                                    handleCopyToken(
                                        token.id,
                                        token.plainTextToken!,
                                    )
                                }
                                className="shrink-0 hover:bg-yellow-100"
                            >
                                {copiedTokenId === token.id ? (
                                    <Check className="h-4 w-4 text-green-600" />
                                ) : (
                                    <Copy className="h-4 w-4" />
                                )}
                            </Button>
                        </div>
                        <p className="mt-2 text-xs font-medium text-yellow-700">
                            {translations.tokenWarning}
                        </p>
                    </>
                )}
                <div className="mt-2 flex items-center gap-4 text-xs text-neutral-500">
                    <span>
                        {translations.created}:{' '}
                        {new Date(token.created_at).toLocaleDateString()}
                    </span>
                    {token.last_used_at && (
                        <span>
                            {translations.lastUsed}:{' '}
                            {new Date(token.last_used_at).toLocaleDateString()}
                        </span>
                    )}
                </div>
            </div>
            <Button
                size="sm"
                variant="outline"
                onClick={() => onDelete(token.id)}
                className="border-red-200 hover:border-red-300 hover:bg-red-50"
            >
                <Trash2 className="h-4 w-4 text-red-500" />
            </Button>
        </div>
    );
}
