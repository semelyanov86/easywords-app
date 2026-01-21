import { DeleteTokenDialog } from '@/features/api-token/ui/DeleteTokenDialog';
import profile from '@/routes/profile';
import { profileTranslations } from '@/shared/i18n/profile';
import { useNestedTranslation } from '@/shared/i18n/useNestedTranslation';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { ApiTokensCard } from '@/widgets/profile/ApiTokensCard';
import { UserInfoCard } from '@/widgets/profile/UserInfoCard';
import { Head, router } from '@inertiajs/react';
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
    const [deleteTokenId, setDeleteTokenId] = useState<string | null>(null);
    const [deleteTokenName, setDeleteTokenName] = useState<string>('');

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
        const token = tokens.find((t) => t.id === tokenId);
        if (token) {
            setDeleteTokenId(tokenId);
            setDeleteTokenName(token.name);
        }
    };

    const confirmDeleteToken = () => {
        if (deleteTokenId) {
            router.delete(`/profile/api-tokens/${deleteTokenId}`, {
                preserveScroll: true,
            });
            setDeleteTokenId(null);
            setDeleteTokenName('');
        }
    };

    return (
        <>
            <Head title={'Easywords Profile'} />
            <div className="min-h-screen bg-background">
                <AuthHeader userName={user.name} />
                <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-foreground sm:text-4xl">
                            {t.title || 'Profile'}
                        </h1>
                    </div>

                    <div className="mb-8">
                        <UserInfoCard
                            user={user}
                            onChangePassword={() =>
                                router.visit(profile.password.edit().url)
                            }
                            translations={{
                                userInfo: t.userInfo || 'User Information',
                                userId: t.userId || 'User ID',
                                userName: t.userName || 'Name',
                                email: t.email || 'Email',
                                createdAt: t.createdAt || 'Registration Date',
                                updatedAt: t.updatedAt || 'Last Updated',
                                changePassword:
                                    t.changePassword || 'Change Password',
                            }}
                        />
                    </div>

                    <ApiTokensCard
                        tokens={tokens}
                        onDelete={handleDeleteToken}
                        onCreateClick={() => setIsCreateTokenOpen(true)}
                        translations={{
                            apiKeys: t.apiKeys || 'API Keys',
                            apiKeysDescription:
                                t.apiKeysDescription ||
                                'Manage your API tokens for service access',
                            createToken: t.createToken || 'Add new token',
                            noTokens:
                                t.noTokens ||
                                "You don't have any API tokens yet",
                            created: t.created || 'Created',
                            lastUsed: t.lastUsed || 'Last used',
                            tokenWarning:
                                t.tokenWarning ||
                                'Save this token now. It will not be shown again.',
                        }}
                    />
                </main>

                <CreateToken
                    open={isCreateTokenOpen}
                    onOpenChange={setIsCreateTokenOpen}
                />

                <DeleteTokenDialog
                    open={deleteTokenId !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setDeleteTokenId(null);
                            setDeleteTokenName('');
                        }
                    }}
                    onConfirm={confirmDeleteToken}
                    tokenName={deleteTokenName}
                    translations={{
                        title: t.deleteTokenTitle || 'Delete API Token',
                        description:
                            t.deleteTokenDescription ||
                            'Are you sure you want to delete this token? This action cannot be undone.',
                        cancel: t.cancel || 'Cancel',
                        confirm: t.delete || 'Delete',
                    }}
                />
            </div>
        </>
    );
}
