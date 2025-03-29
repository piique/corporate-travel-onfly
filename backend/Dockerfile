FROM php:8.2-fpm

# Arguments
ARG user=appuser
ARG uid=1000

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

## Install Xdebug
#RUN pecl install xdebug \
#    && docker-php-ext-enable xdebug
#
## Configure Xdebug
#RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Create non-root user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Create startup script to run Laravel setup commands
RUN echo '#!/bin/bash \n\
set -e \n\
\n\
echo "🚀 Iniciando configuração do Laravel..." \n\
\n\
# Copiar .env.example para .env se necessário \n\
if [ ! -f .env ]; then \n\
    echo "📄 Copiando .env.example para .env..." \n\
    cp .env.example .env \n\
    echo "✅ Arquivo .env criado." \n\
fi \n\
\n\
# Instalar dependências \n\
if [ ! -d "vendor" ] || [ "$FORCE_COMPOSER_INSTALL" = "true" ]; then \n\
    echo "📦 Instalando dependências via Composer..." \n\
    composer install --no-interaction --optimize-autoloader \n\
    echo "✅ Dependências instaladas." \n\
fi \n\
\n\
# Gerar chave do aplicativo se necessário \n\
if grep -q "APP_KEY=base64:.*" .env; then \n\
    echo "🔑 Chave do aplicativo já existe." \n\
else \n\
    echo "🔑 Gerando chave do aplicativo..." \n\
    php artisan key:generate \n\
    echo "✅ Chave gerada." \n\
fi \n\
\n\
# Limpar configurações e rotas (sem acessar o banco de dados) \n\
echo "🧹 Limpando configurações e rotas..." \n\
php artisan config:clear \n\
php artisan route:clear \n\
php artisan view:clear \n\
\n\
# Modificando configuração de cache temporariamente \n\
echo "🔧 Configurando cache para usar file driver temporariamente..." \n\
sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=file/" .env \n\
\n\
# Esperar um pouco para o banco de dados iniciar \n\
echo "⏳ Aguardando 10 segundos para o banco de dados iniciar..." \n\
sleep 10 \n\
\n\
# Tentar executar migrações diretamente \n\
echo "🗃️ Tentando executar migrações..." \n\
if php artisan migrate --force --no-interaction; then \n\
    echo "✅ Migrações concluídas com sucesso." \n\
    \n\
    # Agora limpar o cache (após as migrações) \n\
    echo "🧹 Limpando cache..." \n\
    php artisan cache:clear \n\
    echo "✅ Cache limpo." \n\
    \n\
    echo "🌱 Executando seeders..." \n\
    php artisan db:seed --force \n\
    echo "✅ Seeders executados." \n\
else \n\
    echo "⚠️ Não foi possível executar as migrações. Continuando mesmo assim..." \n\
fi \n\
\n\
# Executar armazenamento de links simbólicos \n\
echo "🔗 Criando links simbólicos para storage..." \n\
php artisan storage:link --force \n\
echo "✅ Links criados." \n\
\n\
# Otimizar o aplicativo para produção se necessário \n\
if [ "$APP_ENV" = "production" ]; then \n\
    echo "⚙️ Otimizando aplicativo para produção..." \n\
    php artisan optimize \n\
    echo "✅ Aplicativo otimizado." \n\
fi \n\
\n\
echo "🎉 Configuração do Laravel concluída. Iniciando PHP-FPM..." \n\
\n\
# Iniciar PHP-FPM \n\
php-fpm \n\
' > /usr/local/bin/start-laravel.sh

# Make script executable
RUN chmod +x /usr/local/bin/start-laravel.sh

# Copy project files
COPY . /var/www

# Set proper permissions
RUN chown -R $user:$user /var/www

# Switch to non-root user
USER $user

# Expose port 9000
EXPOSE 9000

# Use our custom startup script
CMD ["/usr/local/bin/start-laravel.sh"]
