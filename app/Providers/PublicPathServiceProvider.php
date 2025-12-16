<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PublicPathServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * This provider has high priority to run before DomPDF
     */
    public function register(): void
    {
        // Fix for DomPDF public path in shared hosting
        // In shared hosting (Hostinger), public_html is separate from laravel directory
        // Laravel is in /laravel, public_html is at the same level
        $this->app->bind('path.public', function () {
            $basePath = base_path();
            
            // Check if we're in shared hosting structure
            // public_html should be at the same level as laravel directory
            $publicHtmlPath = dirname($basePath) . DIRECTORY_SEPARATOR . 'public_html';
            
            if (is_dir($publicHtmlPath)) {
                return realpath($publicHtmlPath);
            }
            
            // Fallback to standard Laravel structure
            $publicPath = $basePath . DIRECTORY_SEPARATOR . 'public';
            if (is_dir($publicPath)) {
                return realpath($publicPath);
            }
            
            // Last resort: return the expected path
            return $publicHtmlPath;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

