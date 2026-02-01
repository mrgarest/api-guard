<?php

namespace Garest\ApiGuard\Events;

use Illuminate\Http\Request;
use Garest\ApiGuard\Exceptions\ApiGuardException;

class AuthFailed
{
    public function __construct(
        public Request $request,
        public ApiGuardException $exception
    ) {}
}
