@echo off
if "%1"=="" (
    echo Uso: laravel.bat [comando]
    echo Ejemplos:
    echo   laravel.bat migrate
    echo   laravel.bat "make:controller ProductController"
    echo   laravel.bat route:list
    goto :eof
)

php artisan %*
