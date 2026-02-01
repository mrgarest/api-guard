<?php

namespace Garest\ApiGuard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Helper;

/**
 * @property int $id
 * @property string|null $ip_address
 * @property array<array-key, mixed>|null $payload
 * @property \Illuminate\Support\Carbon $failed_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedAuth wherePayload($value)
 * @mixin \Eloquent
 */
class FailedAuth extends Model
{
    protected $table = 'ag_failed_auths';

    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'payload',
        'failed_at'
    ];

    protected $casts = [
        'ip_address' => 'string',
        'payload' => 'array',
        'failed_at' => 'datetime'
    ];

    /**
     * Create a failed authentication record from the AuthFailed event.
     *
     * @param AuthFailed $event
     * @return static
     */
    public static function createFromEvent(AuthFailed $event)
    {
        $ip = Helper::getIp($event->request);
        $now = Carbon::now();
        self::create([
            'ip_address' => $ip,
            'payload' => [
                'class' => get_class($event->exception),
                'datetime' => $now->toDateTimeString(),
                'message' => $event->exception->getMessage(),
                'code' => $event->exception->code(),
                'status' => $event->exception->status(),
                'method' => $event->request->method(),
                'path' => $event->request->path(),
                'ip' => $ip,
                'user_agent' => $event->request->userAgent(),
                'headers' => $event->request->headers->all(),
                'cookies' => $event->request->cookies->all(),
            ],
            'failed_at' => $now
        ]);
    }
}
