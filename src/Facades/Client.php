<?php

namespace Garest\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string ip(\Illuminate\Http\Request $request)
 *
 * @see \Garest\ApiGuard\Support\Client
 */
class Client extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ag.client';
    }
}

