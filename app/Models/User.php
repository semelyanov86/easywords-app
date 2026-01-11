<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasSettingsField;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public string $settingsFieldName = 'settings';

    /** @var array<string, scalar|string[]> */
    public array $defaultSettings = [
        'paginate' => 20,
        'fresh_first' => true,
        'show_starred' => true,
        'latest_first' => false,
        'known_enabled' => false,
        'main_language' => 'RU',
        'show_imported' => true,
        'languages_list' => ['DE', 'EN'],
        'starred_enabled' => false,
        'default_language' => 'DE',
    ];

    /** @var string[] */
    public array $settingsRules = [
        'paginate' => 'integer',
        'fresh_first' => 'boolean',
        'show_starred' => 'boolean',
        'latest_first' => 'bool',
        'known_enabled' => 'bool',
        'main_language' => 'string',
        'show_imported' => 'bool',
        'languages_list' => 'array',
        'starred_enabled' => 'bool',
        'default_language' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    /**
     * Get attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_admin' => 'boolean',
            'has_premium' => 'boolean',
        ];
    }
}
