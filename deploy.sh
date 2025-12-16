#!/bin/bash

# Navegar al directorio del repositorio
cd /home/u123456789/domains/tudominio.com/private_html/MovilTech

# Obtener los últimos cambios
git pull origin main

# Instalar dependencias de Composer
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Instalar dependencias de NPM
npm install
npm run build

# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Establecer permisos
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Si es necesario, establecer el propietario correcto
# chown -R usuario:grupo /ruta/a/tu/proyecto

echo "Despliegue completado correctamente"
exit 0
