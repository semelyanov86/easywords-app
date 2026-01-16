import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Lock } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
}

interface UserInfoCardProps {
    user: User;
    translations: {
        userInfo: string;
        userId: string;
        userName: string;
        email: string;
        createdAt: string;
        updatedAt: string;
        changePassword: string;
    };
    onChangePassword?: () => void;
}

export function UserInfoCard({
    user,
    translations,
    onChangePassword,
}: UserInfoCardProps) {
    return (
        <Card className="border-2 shadow-sm transition-shadow hover:shadow-md">
            <CardHeader>
                <CardTitle className="text-xl">
                    {translations.userInfo}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <Label className="text-sm font-semibold text-neutral-600">
                            {translations.userId}
                        </Label>
                        <p className="mt-1 text-lg font-medium text-neutral-900">
                            {user.id}
                        </p>
                    </div>
                    <div>
                        <Label className="text-sm font-semibold text-neutral-600">
                            {translations.userName}
                        </Label>
                        <p className="mt-1 text-lg font-medium text-neutral-900">
                            {user.name}
                        </p>
                    </div>
                    <div>
                        <Label className="text-sm font-semibold text-neutral-600">
                            {translations.email}
                        </Label>
                        <p className="mt-1 text-lg font-medium text-neutral-900">
                            {user.email}
                        </p>
                    </div>
                    <div>
                        <Label className="text-sm font-semibold text-neutral-600">
                            {translations.createdAt}
                        </Label>
                        <p className="mt-1 text-lg font-medium text-neutral-900">
                            {new Date(user.created_at).toLocaleDateString()}
                        </p>
                    </div>
                    <div className="sm:col-span-2">
                        <Label className="text-sm font-semibold text-neutral-600">
                            {translations.updatedAt}
                        </Label>
                        <p className="mt-1 text-lg font-medium text-neutral-900">
                            {new Date(user.updated_at).toLocaleDateString()}
                        </p>
                    </div>
                </div>
                <div className="mt-4 border-t pt-4">
                    <Button
                        onClick={onChangePassword}
                        className="w-full"
                        variant="outline"
                    >
                        <Lock className="mr-2 h-4 w-4" />
                        {translations.changePassword}
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
