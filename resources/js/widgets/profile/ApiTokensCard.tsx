import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { TokenList } from '@/features/api-token/ui/TokenList';
import { Key, Plus } from 'lucide-react';

interface Token {
    id: string;
    name: string;
    plainTextToken?: string;
    created_at: string;
    last_used_at: string | null;
}

interface ApiTokensCardProps {
    tokens: Token[];
    onDelete: (tokenId: string) => void;
    onCreateClick: () => void;
    translations: {
        apiKeys: string;
        apiKeysDescription: string;
        createToken: string;
        noTokens: string;
        created: string;
        lastUsed: string;
        tokenWarning: string;
    };
}

export function ApiTokensCard({
    tokens,
    onDelete,
    onCreateClick,
    translations,
}: ApiTokensCardProps) {
    return (
        <Card className="border border-border bg-card shadow-sm transition-shadow hover:shadow-md">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2 text-xl">
                            <Key className="h-5 w-5 text-primary" />
                            {translations.apiKeys}
                        </CardTitle>
                        <CardDescription className="mt-2">
                            {translations.apiKeysDescription}
                        </CardDescription>
                    </div>
                    <Button onClick={onCreateClick}>
                        <Plus className="h-4 w-4" />
                        {translations.createToken}
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <TokenList
                    tokens={tokens}
                    onDelete={onDelete}
                    onCreateClick={onCreateClick}
                    translations={{
                        noTokens: translations.noTokens,
                        createToken: translations.createToken,
                        created: translations.created,
                        lastUsed: translations.lastUsed,
                        tokenWarning: translations.tokenWarning,
                    }}
                />
            </CardContent>
        </Card>
    );
}
