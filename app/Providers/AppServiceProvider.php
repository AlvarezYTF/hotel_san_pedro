<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.tailwind');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // Force secure cookies in production when APP_URL uses HTTPS
        if (config('app.env') === 'production' && config('session.secure') === null) {
            $appUrl = config('app.url');
            if ($appUrl && str_starts_with($appUrl, 'https://')) {
                config(['session.secure' => true]);
            }
        }

        // Audit Logs para Autenticación
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\AuditLog::create([
                    'user_id' => $event->user->id,
                    'event' => 'login',
                    'description' => 'Inicio de sesión exitoso',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                \App\Models\AuditLog::create([
                    'user_id' => $event->user?->id,
                    'event' => 'failed_login',
                    'description' => 'Intento de inicio de sesión fallido para: ' . ($event->credentials['email'] ?? 'unknown'),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );
    }
}
