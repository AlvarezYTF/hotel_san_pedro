<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Deployment routes - protected by deployment token instead of CSRF
        '__infra__/migrate',
        '__infra__/seed',
        '__infra__/sync-municipalities',
        '__infra__/sync-numbering-ranges',
        '__infra__/sync-measurement-units',
        '__infra__/status',
    ];
}
