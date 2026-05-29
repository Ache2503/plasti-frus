# Ejecutar Plasti-Frus con Docker en Windows

Esta guia permite levantar el sistema en otra PC Windows sin instalar PHP, Composer, Apache ni MariaDB manualmente. Solo se necesita Docker Desktop.

## Requisitos

- Windows 10/11 con virtualizacion habilitada.
- Docker Desktop instalado y abierto.
- El proyecto Plasti-Frus descargado o clonado.

## Primer arranque

Abre PowerShell en la carpeta del proyecto y ejecuta:

```powershell
docker compose up -d --build
```

La primera vez puede tardar varios minutos porque descarga imagenes, instala extensiones PHP y crea la base de datos de demostracion.

Cuando termine, abre:

- Sistema: http://localhost:8000
- phpMyAdmin: http://localhost:8081

Datos de MariaDB para phpMyAdmin:

- Servidor: `db`
- Usuario: `plastifrus`
- Password: `plastifrus`
- Base de datos: `fabrica_plasticos`

## Usuarios de demostracion

Los seeders crean usuarios como:

- `admin` / `password`
- `supervisor` / `password`
- `operador1` / `password`
- `vendedor1` / `password`
- `cliente1` / `password`
- `contador` / `password`

## Comandos utiles

Ver logs del sistema:

```powershell
docker compose logs -f app
```

Detener contenedores sin borrar datos:

```powershell
docker compose down
```

Reiniciar desde cero borrando la base de datos:

```powershell
docker compose down -v
docker compose up -d --build
```

Entrar al contenedor PHP:

```powershell
docker compose exec app bash
```

Ejecutar migraciones PHP:

```powershell
docker compose exec app php bin/plasti migrate
```

Ejecutar pruebas:

```powershell
docker compose exec app vendor/bin/phpunit
```

## Puertos

Por defecto se usan estos puertos:

- `8000`: aplicacion web.
- `8081`: phpMyAdmin.

Si otra aplicacion ya usa alguno, cambia el lado izquierdo en `docker-compose.yml`. Por ejemplo:

```yaml
ports:
  - "8002:80"
```

Despues abre `http://localhost:8002`.

MariaDB no se expone directamente a Windows para evitar conflictos con instalaciones locales. Se accede desde la aplicacion y phpMyAdmin usando el host interno `db`.

## Variables de entorno

Docker configura estas variables automaticamente:

```env
APP_URL=http://localhost:8000
DB_HOST=db
DB_PORT=3306
DB_NAME=fabrica_plasticos
DB_USER=plastifrus
DB_PASS=plastifrus
```

Dentro de Docker, `DB_HOST` debe ser `db`, no `localhost`. Este es uno de los errores comunes cuando el proyecto se mueve desde una PC local a Windows.

## Notas importantes

- No copies una base de datos local manualmente dentro del contenedor si no es necesario.
- Si cambias SQL o seeders y quieres regenerar datos, usa `docker compose down -v`.
- El volumen `plasti_frus_db` conserva la base aunque apagues Docker.
- La carpeta del proyecto se monta dentro del contenedor, por eso los cambios de codigo se reflejan sin reconstruir la imagen en la mayoria de los casos.
