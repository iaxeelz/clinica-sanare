FROM php:8.4-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache para usar /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código
COPY . .

# ============================================
# DIAGNÓSTICO: Verificar archivos críticos
# ============================================
RUN echo "=== VERIFICANDO ARCHIVOS CRÍTICOS ===" && \
    ls -la /var/www/html/ && \
    ls -la /var/www/html/public/ && \
    ls -la /var/www/html/vendor/ || echo "vendor no existe" && \
    ls -la /var/www/html/bootstrap/ || echo "bootstrap no existe"

# ============================================
# CREAR ARCHIVO DE PRUEBA test.php
# ============================================
RUN echo '<?php' > /var/www/html/public/test.php && \
    echo '// public/test.php - Archivo de prueba para diagnosticar el error 500' >> /var/www/html/public/test.php && \
    echo 'echo "=== DIAGNÓSTICO DE LARAVEL ===\n\n";' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 1. Verificar PHP' >> /var/www/html/public/test.php && \
    echo 'echo "1. PHP versión: " . phpversion() . "\n";' >> /var/www/html/public/test.php && \
    echo 'echo "   Memory limit: " . ini_get("memory_limit") . "\n\n";' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 2. Verificar autoload' >> /var/www/html/public/test.php && \
    echo 'echo "2. Verificando autoload...\n";' >> /var/www/html/public/test.php && \
    echo 'if (file_exists(__DIR__ . "/../vendor/autoload.php")) {' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ vendor/autoload.php existe\n";' >> /var/www/html/public/test.php && \
    echo '    require_once __DIR__ . "/../vendor/autoload.php";' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ Autoload cargado correctamente\n\n";' >> /var/www/html/public/test.php && \
    echo '} else {' >> /var/www/html/public/test.php && \
    echo '    die("   ❌ vendor/autoload.php NO existe\n");' >> /var/www/html/public/test.php && \
    echo '}' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 3. Verificar bootstrap' >> /var/www/html/public/test.php && \
    echo 'echo "3. Verificando bootstrap...\n";' >> /var/www/html/public/test.php && \
    echo 'if (file_exists(__DIR__ . "/../bootstrap/app.php")) {' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ bootstrap/app.php existe\n\n";' >> /var/www/html/public/test.php && \
    echo '} else {' >> /var/www/html/public/test.php && \
    echo '    die("   ❌ bootstrap/app.php NO existe\n");' >> /var/www/html/public/test.php && \
    echo '}' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 4. Verificar .env' >> /var/www/html/public/test.php && \
    echo 'echo "4. Verificando .env...\n";' >> /var/www/html/public/test.php && \
    echo 'if (file_exists(__DIR__ . "/../.env")) {' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ .env existe\n";' >> /var/www/html/public/test.php && \
    echo '    $env = file_get_contents(__DIR__ . "/../.env");' >> /var/www/html/public/test.php && \
    echo '    echo "   Tamaño: " . strlen($env) . " bytes\n\n";' >> /var/www/html/public/test.php && \
    echo '} else {' >> /var/www/html/public/test.php && \
    echo '    echo "   ⚠️ .env NO existe (usando variables de entorno)\n\n";' >> /var/www/html/public/test.php && \
    echo '}' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 5. Probar conexión a base de datos' >> /var/www/html/public/test.php && \
    echo 'echo "5. Probando conexión a base de datos...\n";' >> /var/www/html/public/test.php && \
    echo 'try {' >> /var/www/html/public/test.php && \
    echo '    $dsn = "pgsql:host=ep-rapid-mud-ayla9xkl.c-5.us-east-2.aws.neon.tech;port=5432;dbname=neondb";' >> /var/www/html/public/test.php && \
    echo '    $pdo = new PDO($dsn, "neondb_owner", "npg_GoDVC3YnHXB4");' >> /var/www/html/public/test.php && \
    echo '    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ Conexión exitosa a Neon\n";' >> /var/www/html/public/test.php && \
    echo '    $version = $pdo->query("SELECT version()")->fetchColumn();' >> /var/www/html/public/test.php && \
    echo '    echo "   Versión: " . substr($version, 0, 50) . "...\n\n";' >> /var/www/html/public/test.php && \
    echo '} catch (Exception $e) {' >> /var/www/html/public/test.php && \
    echo '    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n\n";' >> /var/www/html/public/test.php && \
    echo '}' >> /var/www/html/public/test.php && \
    echo '' >> /var/www/html/public/test.php && \
    echo '// 6. Intentar cargar Laravel' >> /var/www/html/public/test.php && \
    echo 'echo "6. Intentando cargar Laravel...\n";' >> /var/www/html/public/test.php && \
    echo 'try {' >> /var/www/html/public/test.php && \
    echo '    $app = require_once __DIR__ . "/../bootstrap/app.php";' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ App cargada correctamente\n";' >> /var/www/html/public/test.php && \
    echo '    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);' >> /var/www/html/public/test.php && \
    echo '    echo "   ✅ Kernel creado\n";' >> /var/www/html/public/test.php && \
    echo '    echo "   🎉 ¡Laravel está funcionando!\n";' >> /var/www/html/public/test.php && \
    echo '} catch (Exception $e) {' >> /var/www/html/public/test.php && \
    echo '    echo "   ❌ Error cargando Laravel: " . $e->getMessage() . "\n";' >> /var/www/html/public/test.php && \
    echo '    echo "   Archivo: " . $e->getFile() . "\n";' >> /var/www/html/public/test.php && \
    echo '    echo "   Línea: " . $e->getLine() . "\n";' >> /var/www/html/public/test.php && \
    echo '    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";' >> /var/www/html/public/test.php && \
    echo '}' >> /var/www/html/public/test.php

# ============================================
# CREAR HEALTHZ
# ============================================
RUN echo "OK" > /var/www/html/public/healthz

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=php --no-scripts

RUN composer dump-autoload --optimize --ignore-platform-req=php

# ============================================
# DIAGNÓSTICO: Verificar Composer y autoload
# ============================================
RUN echo "=== VERIFICANDO COMPOSER ===" && \
    ls -la /var/www/html/vendor/ && \
    ls -la /var/www/html/vendor/autoload.php || echo "autoload.php no existe" && \
    php -r "echo 'PHP está funcionando\n';" && \
    php -r "require_once 'vendor/autoload.php'; echo 'Autoload cargado correctamente\n';"

# ============================================
# CONFIGURAR LARAVEL
# ============================================
RUN php artisan optimize:clear || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true
RUN php artisan storage:link || true

# Crear archivo de log
RUN touch storage/logs/laravel.log && chmod 666 storage/logs/laravel.log

# ============================================
# DIAGNÓSTICO: Verificar rutas y configuración
# ============================================
RUN php artisan route:list || true
RUN php artisan config:show database || true
RUN cat storage/logs/laravel.log || echo "No logs"

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
