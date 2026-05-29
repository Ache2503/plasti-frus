#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ]; then
    cat > .env <<EOF
APP_NAME="${APP_NAME:-Plasti Frus}"
APP_ENV=${APP_ENV:-local}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost:8000}
APP_TIMEZONE=${APP_TIMEZONE:-America/Mexico_City}

DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_NAME=${DB_NAME:-fabrica_plasticos}
DB_USER=${DB_USER:-plastifrus}
DB_PASS=${DB_PASS:-plastifrus}
EOF
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

mkdir -p storage/logs public/uploads
chown -R www-data:www-data storage public/uploads || true

echo "Esperando MariaDB en ${DB_HOST:-db}:${DB_PORT:-3306}..."
until mysqladmin ping \
    -h"${DB_HOST:-db}" \
    -P"${DB_PORT:-3306}" \
    -u"${DB_USER:-plastifrus}" \
    -p"${DB_PASS:-plastifrus}" \
    --silent; do
    sleep 2
done

TABLE_COUNT="$(mysql \
    -h"${DB_HOST:-db}" \
    -P"${DB_PORT:-3306}" \
    -u"${DB_USER:-plastifrus}" \
    -p"${DB_PASS:-plastifrus}" \
    -Nse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME:-fabrica_plasticos}'")"

if [ "${TABLE_COUNT}" = "0" ]; then
    echo "Inicializando base de datos ${DB_NAME:-fabrica_plasticos}..."
    sed '1,2d' database/schema.sql | mysql \
        -h"${DB_HOST:-db}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USER:-plastifrus}" \
        -p"${DB_PASS:-plastifrus}" \
        "${DB_NAME:-fabrica_plasticos}"

    mysql \
        -h"${DB_HOST:-db}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USER:-plastifrus}" \
        -p"${DB_PASS:-plastifrus}" \
        "${DB_NAME:-fabrica_plasticos}" < docker/init_roles.sql

    for sql in \
        database/migration_clientes.sql \
        database/migration_vendedores.sql \
        database/migration_mejoras_cliente.sql \
        database/migration_tickets.sql \
        database/migration_mensajes.sql \
        database/migration_contabilidad.sql \
        database/migration_cartera.sql \
        database/migration_interacciones.sql \
        database/migration_oportunidades.sql \
        database/migration_actividades.sql \
        database/migration_log_actividad.sql \
        database/migration_metas_vendedor.sql \
        database/migration_notificaciones_vendedor.sql \
        database/migration_notificaciones_supervisor.sql \
        database/migration_notificaciones_operador.sql \
        database/migration_horarios_operador.sql \
        database/migration_ordenes_estatus.sql \
        database/migration_contabilizar_facturas.sql \
        database/corrections_2026_05_27_audit_phase2.sql \
        database/corrections_2026_05_27_dynamic_selects.sql; do
        if [ -f "$sql" ]; then
            echo "Aplicando $sql"
            mysql \
                -h"${DB_HOST:-db}" \
                -P"${DB_PORT:-3306}" \
                -u"${DB_USER:-plastifrus}" \
                -p"${DB_PASS:-plastifrus}" \
                "${DB_NAME:-fabrica_plasticos}" < "$sql" || echo "Aviso: $sql no se pudo aplicar completo; revisar si ya estaba incluido."
        fi
    done

    php bin/plasti migrate || echo "Aviso: migraciones PHP no se aplicaron completo; revisar migraciones repetidas."

    mysql \
        -h"${DB_HOST:-db}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USER:-plastifrus}" \
        -p"${DB_PASS:-plastifrus}" \
        "${DB_NAME:-fabrica_plasticos}" < database/corrections_2026_05_27_support_messaging.sql || echo "Aviso: correccion de tickets no se aplico completo."

    mysql \
        -h"${DB_HOST:-db}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USER:-plastifrus}" \
        -p"${DB_PASS:-plastifrus}" \
        "${DB_NAME:-fabrica_plasticos}" < docker/init_compat.sql

    php seed.php || echo "Aviso: seed de demostracion termino con advertencias."
else
    echo "Base de datos existente detectada; no se reinicializa."
fi

exec "$@"
