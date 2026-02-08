<?php

namespace Garest\ApiGuard\Contracts;

use Illuminate\Http\Request;

interface AuthenticationStrategy
{
    public function authenticate(Request $request): void;
}
