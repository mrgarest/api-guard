<?php

namespace Garest\ApiGuard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $access_key
 * @property string $secret
 * @property bool $revoked
 * @property array<array-key, mixed>|null $scopes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property-read Model|\Eloquent $owner
 * @method static Builder<static>|HmacKey accessKey($value)
 * @method static Builder<static>|HmacKey newModelQuery()
 * @method static Builder<static>|HmacKey newQuery()
 * @method static Builder<static>|HmacKey notExpired()
 * @method static Builder<static>|HmacKey notRevoked()
 * @method static Builder<static>|HmacKey query()
 * @method static Builder<static>|HmacKey whereAccessKey($value)
 * @method static Builder<static>|HmacKey whereCreatedAt($value)
 * @method static Builder<static>|HmacKey whereExpiresAt($value)
 * @method static Builder<static>|HmacKey whereId($value)
 * @method static Builder<static>|HmacKey whereName($value)
 * @method static Builder<static>|HmacKey whereRevoked($value)
 * @method static Builder<static>|HmacKey whereScopes($value)
 * @method static Builder<static>|HmacKey whereSecret($value)
 * @method static Builder<static>|HmacKey whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HmacKey extends Model
{
    protected $table = 'ag_hmac_keys';

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'access_key',
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
        'access_key' => 'string',
        'secret' => 'string',
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
     * Public Access Key.
     *
     * @param Builder $query
     * @param string $value
     * @return Builder
     */
    public function scopeAccessKey(Builder $query, $value): Builder
    {
        return $query->where('access_key', $value);
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
     * Checks whether the key was revoked manually.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return (bool) $this->revoked;
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
     * Checks if the key has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the key has one or more scopes.
     *
     * @param string|array $scopes Single scope or array of scopes
     * @return bool
     */
    public function hasScope(string|array $scopes): bool
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        return empty(array_diff($scopes, $this->scopes ?? []));
    }
}
