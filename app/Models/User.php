<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Deliberately NOT in #[Fillable]: only forceFill()/the make-admin
            // command/the admin panel's explicit action may ever change it.
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Without this, Filament lets *any* signed-in user into the panel whenever
     * APP_ENV=local — so access is tied to the admin flag explicitly.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // (bool): a user created earlier in the same request has no is_admin attribute loaded yet.
        return (bool) $this->is_admin;
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function ownsProduct(int $productId): bool
    {
        return $this->enrollments()->where('product_id', $productId)->exists();
    }

    /**
     * Every authenticated user has exactly one cart, created lazily on first use.
     * Explicitly re-caches the relation after creating — `$this->cart` alone
     * caches a null lookup, so a second call on the same instance (e.g. the
     * navbar and a controller both resolving it during one request) would
     * try to create a second cart and violate the unique constraint.
     */
    public function cartOrCreate(): Cart
    {
        $cart = $this->cart;

        if (! $cart) {
            $cart = $this->cart()->create();
            $this->setRelation('cart', $cart);
        }

        return $cart;
    }
}
