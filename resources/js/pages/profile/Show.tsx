import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { profileTranslations } from '@/shared/i18n/profile';
import { useNestedTranslation } from '@/shared/i18n/useNestedTranslation';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { router } from '@inertiajs/react';
import { Check, Copy, Key, Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import CreateToken from './CreateToken';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
}

interface PersonalAccessToken {
    id: string;
    name: string;
    token: string;
    created_at: string;
    last_used_at: string | null;
}

interface TokenWithPlainText extends PersonalAccessToken {
    plainTextToken?: string;
}

interface ProfilePageProps {
    user: User;
    tokens: PersonalAccessToken[];
    token?: {
        id: string;
        name: string;
        token: string;
        created_at: string;
    };
}

export default function Show({
    user,
    tokens: initialTokens,
    token: newToken,
}: ProfilePageProps) {
    const t = useNestedTranslation(profileTranslations);
    const [isCreateTokenOpen, setIsCreateTokenOpen] = useState(false);
    const [copiedTokenId, setCopiedTokenId] = useState<string | null>(null);
    console.log(newToken);
    // Объединяем новый токен с существующими
    const tokens = useMemo<TokenWithPlainText[]>(() => {
        if (newToken) {
            return [
                {
                    ...newToken,
                    last_used_at: null,
                    plainTextToken: newToken.token,
                },
                ...initialTokens,
            ];
        }
        return initialTokens;
    }, [newToken, initialTokens]);

    const handleDeleteToken = (tokenId: string) => {
        if (
            confirm(
                t.deleteTokenConfirm ||
                    'Are you sure you want to delete this token?',
            )
        ) {
            router.delete(`/profile/api-tokens/${tokenId}`, {
                preserveScroll: true,
            });
        }
    };

    const handleCopyToken = async (tokenId: string, plainTextToken: string) => {
        await navigator.clipboard.writeText(plainTextToken);
        setCopiedTokenId(tokenId);
        setTimeout(() => setCopiedTokenId(null), 2000);
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
            <AuthHeader userName={user.name} />
            <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                        {t.title || 'Profile'}
                    </h1>
                </div>

                <div className="mb-8">
                    <Card className="border-2 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-xl">
                                {t.userInfo || 'User Information'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label className="text-sm font-semibold text-neutral-600">
                                        {t.userId || 'User ID'}
                                    </Label>
                                    <p className="mt-1 text-lg font-medium text-neutral-900">
                                        {user.id}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-sm font-semibold text-neutral-600">
                                        {t.userName || 'Name'}
                                    </Label>
                                    <p className="mt-1 text-lg font-medium text-neutral-900">
                                        {user.name}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-sm font-semibold text-neutral-600">
                                        {t.email || 'Email'}
                                    </Label>
                                    <p className="mt-1 text-lg font-medium text-neutral-900">
                                        {user.email}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-sm font-semibold text-neutral-600">
                                        {t.createdAt || 'Registration Date'}
                                    </Label>
                                    <p className="mt-1 text-lg font-medium text-neutral-900">
                                        {new Date(
                                            user.created_at,
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                                <div className="sm:col-span-2">
                                    <Label className="text-sm font-semibold text-neutral-600">
                                        {t.updatedAt || 'Last Updated'}
                                    </Label>
                                    <p className="mt-1 text-lg font-medium text-neutral-900">
                                        {new Date(
                                            user.updated_at,
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="border-2 shadow-sm">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2 text-xl">
                                    <Key className="h-5 w-5 text-primary" />
                                    {t.apiKeys || 'API Keys'}
                                </CardTitle>
                                <CardDescription className="mt-2">
                                    {t.apiKeysDescription ||
                                        'Manage your API tokens for service access'}
                                </CardDescription>
                            </div>
                            <Button
                                onClick={() => setIsCreateTokenOpen(true)}
                                className="hover:bg-primary-600 inline-flex h-10 items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                            >
                                <Plus className="h-4 w-4" />
                                {t.createToken || 'Add new token'}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {tokens.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Key className="mb-4 h-12 w-12 text-neutral-300" />
                                <p className="text-lg font-medium text-neutral-600">
                                    {t.noTokens ||
                                        "You don't have any API tokens yet"}
                                </p>
                                <Button
                                    onClick={() => setIsCreateTokenOpen(true)}
                                    className="mt-4 inline-flex h-10 items-center justify-center gap-2 rounded-md border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-900 transition-colors hover:bg-neutral-100 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    variant="outline"
                                >
                                    <Plus className="h-4 w-4" />
                                    {t.createToken || 'Add new token'}
                                </Button>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {tokens.map((token) => (
                                    <div
                                        key={token.id}
                                        className="flex items-center gap-4 rounded-lg border bg-white p-4 shadow-sm transition-all hover:shadow-md"
                                    >
                                        <div className="flex-1">
                                            <h3 className="text-sm font-semibold text-neutral-900">
                                                {token.name}
                                            </h3>
                                            {token.plainTextToken && (
                                                <>
                                                    <div className="mt-2 flex items-center gap-2 rounded bg-yellow-50 p-3 ring-1 ring-yellow-200">
                                                        <code className="flex-1 font-mono text-xs break-all text-neutral-900">
                                                            {
                                                                token.plainTextToken
                                                            }
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
                                                            className="shrink-0"
                                                        >
                                                            {copiedTokenId ===
                                                            token.id ? (
                                                                <Check className="h-4 w-4 text-green-600" />
                                                            ) : (
                                                                <Copy className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </div>
                                                    <p className="mt-2 text-xs font-medium text-yellow-700">
                                                        {t.tokenWarning ||
                                                            'Save this token now. It will not be shown again.'}
                                                    </p>
                                                </>
                                            )}
                                            <div className="mt-2 flex items-center gap-4 text-xs text-neutral-500">
                                                <span>
                                                    {t.created || 'Created'}:{' '}
                                                    {new Date(
                                                        token.created_at,
                                                    ).toLocaleDateString()}
                                                </span>
                                                {token.last_used_at && (
                                                    <span>
                                                        {t.lastUsed ||
                                                            'Last used'}
                                                        :{' '}
                                                        {new Date(
                                                            token.last_used_at,
                                                        ).toLocaleDateString()}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                handleDeleteToken(token.id)
                                            }
                                        >
                                            <Trash2 className="h-4 w-4 text-red-500" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </main>

            <CreateToken
                open={isCreateTokenOpen}
                onOpenChange={setIsCreateTokenOpen}
            />
        </div>
    );
}
