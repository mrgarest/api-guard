<?php

namespace Garest\ApiGuard\Models;

use Carbon\Carbon;
use Garest\ApiGuard\Casts\Encrypted;
use Garest\ApiGuard\Traits\HasAuthCredential;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property string $name
 * @property string $client_id
 * @property $secret
 * @property bool $revoked
 * @property array<array-key, mixed>|null $scopes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property-read Model|\Eloquent|null $owner
 * @method static Builder<static>|JwtClient clientId(string $value)
 * @method static Builder<static>|JwtClient newModelQuery()
 * @method static Builder<static>|JwtClient newQuery()
 * @method static Builder<static>|JwtClient notExpired()
 * @method static Builder<static>|JwtClient notRevoked()
 * @method static Builder<static>|JwtClient query()
 * @method static Builder<static>|JwtClient whereClientId($value)
 * @method static Builder<static>|JwtClient whereCreatedAt($value)
 * @method static Builder<static>|JwtClient whereExpiresAt($value)
 * @method static Builder<static>|JwtClient whereId($value)
 * @method static Builder<static>|JwtClient whereName($value)
 * @method static Builder<static>|JwtClient whereOwnerId($value)
 * @method static Builder<static>|JwtClient whereOwnerType($value)
 * @method static Builder<static>|JwtClient whereRevoked($value)
 * @method static Builder<static>|JwtClient whereScopes($value)
 * @method static Builder<static>|JwtClient whereSecret($value)
 * @method static Builder<static>|JwtClient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class JwtClient extends Model
{
    use HasAuthCredential;

    protected $table = 'ag_jwt_clients';

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'client_id',
        'secret',
        'revoked',
        'scopes',
        'created_at',
        'updated_at',
        'expires_at'
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'name' => 'string',
        'client_id' => 'string',
        'secret'  => Encrypted::class,
        'revoked' => 'boolean',
        'scopes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Client identifier.
     *
     * @param Builder $query
     * @param string $value
     * @return Builder
     */
    public function scopeClientId(Builder $query, string $value): Builder
    {
        return $query->where('client_id', $value);
    }

    /**
     * Exclude revoked keys.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotRevoked(Builder $query): Builder
    {
        return $query->where('revoked', false);
    }

    /**
     * Filter keys that have not yet expired.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotExpired(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Checks whether the key has all scopes.
     *
     * @param string|array $scopes Single scope or array of scopes
     * @return bool
     */
    public function hasScope(string|array $scopes): bool
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];
        if (empty($scopes)) return true;

        return empty(array_diff($scopes, $this->scopes ?? []));
    }

    /**
     * Checks whether the key has at least one scope.
     *
     * @param string|array $scopes Single scope or array of scopes
     * @return bool
     */
    public function hasAnyScope(string|array $scopes): bool
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];
        if (empty($scopes)) return true;

        // intersect returns common elements. If the result is not empty, there is a match.
        return !empty(array_intersect($this->scopes ?? [], $scopes));
    }
}
