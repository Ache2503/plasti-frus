# Guía de Desarrollo - Plasti Frus

## 1. Requisitos Previos

### 1.1 Requisitos Mínimos

```
Sistema Operativo:  Linux (Ubuntu 20.04+), macOS, o Windows 10+
PHP:               8.3+ (con extensiones: pdo, pdo_mysql, json, curl, gd, mbstring)
Base de Datos:     MySQL 8.0+ o MariaDB 11.4+
Composer:          2.0+
Node.js (opcional):18.0+ (para herramientas frontend)
Git:               2.30+
Editor:            VS Code, PhpStorm, o similar
```

### 1.2 Verificar Instalación

```bash
# Verificar PHP
php --version
# Output: PHP 8.3.x

# Verificar Composer
composer --version
# Output: Composer 2.x.x

# Verificar MySQL
mysql --version
# Output: mysql Ver 8.0.x

# Verificar Git
git --version
# Output: git version 2.x.x
```

---

## 2. Instalación Local

### 2.1 Clonar el Repositorio

```bash
git clone https://github.com/tu-repo/plasti_frus.git
cd plasti_frus
```

### 2.2 Instalar Dependencias PHP

```bash
composer install
# Instalará: PHPMailer, Dompdf, PhpSpreadsheet, phpdotenv, PHPUnit
```

### 2.3 Configurar Variables de Entorno

```bash
cp .env.example .env
```

Editar `.env` con tus valores:
```env
APP_NAME=Plasti Frus
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Mexico_City

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=fabrica_plasticos
DB_USER=plastifrus
DB_PASS=plastifrus

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=sistema@plastifrus.com
MAIL_FROM_NAME=Plasti Frus
```

### 2.4 Crear Base de Datos

```bash
# Opción 1: Con mysql CLI
mysql -u root -p
> CREATE DATABASE fabrica_plasticos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> CREATE USER 'plastifrus'@'localhost' IDENTIFIED BY 'plastifrus';
> GRANT ALL PRIVILEGES ON fabrica_plasticos.* TO 'plastifrus'@'localhost';
> FLUSH PRIVILEGES;
> EXIT;

# Opción 2: Con PhpMyAdmin
# Acceder a http://localhost/phpmyadmin
# Crear BD: fabrica_plasticos
# Crear usuario: plastifrus con contraseña plastifrus
```

### 2.5 Ejecutar Migraciones

```bash
# Método 1: SQL directo
mysql -u plastifrus -p fabrica_plasticos < database/schema.sql

# Método 2: CLI de Plasti Frus (si está disponible)
php bin/plasti migrate

# Aplicar correcciones (en orden cronológico)
mysql -u plastifrus -p fabrica_plasticos < database/corrections_2026_05_27_audit_phase2.sql
mysql -u plastifrus -p fabrica_plasticos < database/corrections_2026_05_28_commissions.sql
# ...continuar con otros corrections_*.sql
```

### 2.6 Cargar Datos de Demostración

```bash
# Carga inicial (no duplica si ya existen)
php seed.php

# Reiniciar datos (limpia primero)
php seed.php --fresh
```

### 2.7 Iniciar Servidor de Desarrollo

```bash
# Opción 1: Servidor built-in de PHP (recomendado para desarrollo)
php -S localhost:8000 -t public/ public/router.php

# Opción 2: Con Apache (si está instalado)
# Crear VirtualHost apuntando a /var/www/plasti_frus/public

# Opción 3: Con Nginx
# Configurar server block con root /var/www/plasti_frus/public
```

### 2.8 Acceder a la Aplicación

```
URL: http://localhost:8000
Usuario: admin
Contraseña: password
```

---

## 3. Instalación con Docker

### 3.1 Requisitos Docker

```bash
# Verificar instalación
docker --version
docker-compose --version
```

### 3.2 Lanzar Contenedores

```bash
# Desde el directorio raíz del proyecto
docker-compose up -d --build

# Esperar ~30 segundos a que se inicialice MySQL
# Ver logs
docker-compose logs -f

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes (CUIDADO: pierde datos)
docker-compose down -v
```

### 3.3 Acceso en Docker

```
Aplicación:     http://localhost:8000
PHPMyAdmin:     http://localhost:8081
User: admin
Pass: password
```

### 3.4 Ejecutar Comandos en Docker

```bash
# Instalar dependencias
docker-compose exec app composer install

# Ejecutar migraciones
docker-compose exec app php bin/plasti migrate

# Cargar datos demo
docker-compose exec app php seed.php

# Ver logs
docker-compose logs -f app

# Acceder al bash del contenedor
docker-compose exec app bash
```

---

## 4. Estructura de Directorios para Desarrollo

```
plasti_frus/
├── app/
│   ├── Config/              # Archivos de configuración
│   │   ├── app.php
│   │   ├── database.php
│   │   └── permissions.php
│   ├── Core/                # Framework MVC
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Database.php
│   │   ├── View.php
│   │   ├── Pagination.php
│   │   └── Migration.php
│   ├── Http/
│   │   ├── Controllers/     # 54+ Controladores organizados por módulo
│   │   ├── Requests/        # Form Requests (validación)
│   │   └── Middleware/      # Middlewares (auth, CSRF, etc)
│   ├── Models/              # 31 Modelos (Active Record)
│   ├── Repositories/        # Repositorios (consultas especializadas)
│   ├── Services/            # 9 Servicios (lógica de negocio)
│   ├── Helpers/             # Funciones reutilizables
│   └── Exceptions/          # Manejo de excepciones
├── bootstrap/
│   └── app.php              # Punto de entrada (autoload, constantes)
├── config/                  # Archivos de configuración del app
├── database/
│   ├── migrations/          # Migraciones PHP
│   ├── seeds/               # Seeders de datos
│   ├── schema.sql           # Schema completo
│   ├── corrections_*.sql    # Correcciones/parches
│   └── diagnostics_*.sql    # Diagnósticos
├── docker/                  # Configuración Docker
│   ├── Dockerfile
│   ├── entrypoint.sh
│   ├── 000-default.conf     # Apache config
│   └── init_*.sql           # Scripts de inicialización
├── docs/                    # Documentación
│   ├── README.md
│   ├── ARQUITECTURA.md      # Arquitectura técnica
│   ├── BASE_DATOS.md        # Esquema de BD
│   ├── MODULOS.md           # Descripción de módulos
│   └── diagrams/            # Diagramas
├── public/                  # Carpeta pública (acceso HTTP)
│   ├── index.php            # Punto de entrada HTTP
│   ├── router.php           # Router para servidor built-in PHP
│   └── assets/
│       ├── css/
│       │   ├── main.css
│       │   ├── dashboard.css
│       │   └── enhanced.css
│       ├── js/
│       │   ├── main.js
│       │   ├── charts.js
│       │   └── validations.js
│       └── images/
├── resources/
│   └── views/               # ~70 vistas Blade-like PHP
│       ├── auth/
│       ├── layouts/
│       ├── partials/
│       └── home/
├── routes/                  # 17 archivos de rutas por módulo
│   ├── auth.php
│   ├── dashboard.php
│   ├── production.php
│   ├── sales.php
│   ├── ...
│   └── vendedor.php
├── storage/
│   ├── app/
│   ├── cache/
│   ├── logs/
│   └── exports/             # PDFs y Excels generados
├── tests/                   # PHPUnit tests
│   ├── bootstrap.php
│   ├── TestCase.php
│   ├── Feature/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── ...
│   └── Unit/
│       ├── Helpers/
│       ├── Core/
│       └── Services/
├── .env.example             # Template de variables de entorno
├── .gitignore
├── composer.json            # Dependencias PHP
├── phpunit.xml.dist         # Configuración de tests
├── seed.php                 # CLI para cargar datos demo
├── docker-compose.yml       # Orquestación Docker
└── Dockerfile               # Imagen Docker
```

---

## 5. Flujo de Desarrollo Típico

### 5.1 Crear un Nuevo Controlador

```bash
# Crear archivo
touch app/Http/Controllers/Nombre/MiController.php
```

Contenido base:
```php
<?php

namespace App\Http\Controllers\Nombre;

use App\Core\Controller;
use App\Models\MiModelo;

class MiController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
        $this->requireRol(1, 2, 3);  // Admin, Operador, Supervisor
    }
    
    public function index() {
        $items = MiModelo::all();
        return $this->render('mi-modulo/index', ['items' => $items]);
    }
    
    public function create() {
        return $this->render('mi-modulo/create');
    }
    
    public function store() {
        $data = $_POST;
        MiModelo::create($data);
        $this->logAudit('crear_mi_objeto', $data);
        $this->sessionFlash('success', 'Guardado correctamente');
        $this->redirect('/mi-ruta');
    }
    
    // ... más métodos (edit, update, delete)
}
```

### 5.2 Crear un Nuevo Modelo

```bash
touch app/Models/MiModelo.php
```

Contenido base:
```php
<?php

namespace App\Models;

use App\Core\Model;

class MiModelo extends Model {
    protected $table = 'mi_tabla';
    protected $fillable = ['campo1', 'campo2', 'campo3'];
    protected $casts = [
        'precio' => 'float',
        'cantidad' => 'int',
    ];
    
    // Relaciones
    public function otraTabla() {
        return OtraTabla::find($this->otra_tabla_id);
    }
    
    // Scopes especializados
    public static function activos() {
        return self::where('estado', '=', 'activo');
    }
    
    // Validaciones
    public function validar() {
        if (empty($this->campo1)) throw new ValidationException("Campo1 requerido");
    }
}
```

### 5.3 Crear una Nueva Vista

```bash
mkdir -p resources/views/mi-modulo
touch resources/views/mi-modulo/index.php
```

Contenido base:
```php
<?php $this->layout('layouts/main'); ?>

<div class="container mt-4">
    <h1>Mi Módulo</h1>
    
    <a href="/mi-ruta/create" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Crear
    </a>
    
    <table class="table table-hover" id="miTabla">
        <thead class="table-light">
            <tr>
                <th>Campo1</th>
                <th>Campo2</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo safe_string($item->campo1); ?></td>
                <td><?php echo safe_string($item->campo2); ?></td>
                <td>
                    <a href="/mi-ruta/<?php echo $item->id; ?>/edit" class="btn btn-sm btn-info">
                        Editar
                    </a>
                    <a href="/mi-ruta/<?php echo $item->id; ?>" method="DELETE" class="btn btn-sm btn-danger">
                        Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // DataTables inicialización
    new DataTable('#miTabla', {
        language: {
            url: '/assets/js/dataTables.es-ES.json'
        }
    });
</script>
```

### 5.4 Registrar Nueva Ruta

En `routes/mi-modulo.php`:
```php
<?php

// Mi Módulo - Rutas
Router::get('/mi-ruta', 'Nombre\MiController@index');
Router::get('/mi-ruta/create', 'Nombre\MiController@create');
Router::post('/mi-ruta', 'Nombre\MiController@store');
Router::get('/mi-ruta/{id}/edit', 'Nombre\MiController@edit');
Router::put('/mi-ruta/{id}', 'Nombre\MiController@update');
Router::delete('/mi-ruta/{id}', 'Nombre\MiController@delete');
```

Incluir en `bootstrap/app.php`:
```php
// En la sección de rutas
require_once APP_PATH . '/routes/mi-modulo.php';
```

---

## 6. Comando Útiles

### 6.1 Composer

```bash
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Ejecutar scripts definidos
composer test                # Ejecutar tests
composer test:unit          # Tests unitarios
composer test:feature       # Tests funcionales
composer lint               # Validar sintaxis PHP
```

### 6.2 PHPUnit (Tests)

```bash
# Todos los tests
vendor/bin/phpunit

# Tests específicos
vendor/bin/phpunit tests/Feature/LoginTest.php

# Solo suite Unit
vendor/bin/phpunit --testsuite=Unit

# Solo suite Feature
vendor/bin/phpunit --testsuite=Feature

# Con coverage
vendor/bin/phpunit --coverage-html=coverage

# Tests específicos
vendor/bin/phpunit tests/Feature/LoginTest.php::LoginTest::testLoginExitoso
```

### 6.3 Base de Datos

```bash
# Backup completo
mysqldump -u plastifrus -p fabrica_plasticos > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar
mysql -u plastifrus -p fabrica_plasticos < backup_20260602_150000.sql

# Ejecutar SQL directo
mysql -u plastifrus -p -e "SHOW TABLES;" fabrica_plasticos
```

### 6.4 Git

```bash
# Ver estado
git status

# Crear rama
git checkout -b feature/mi-caracteristica

# Commit
git add .
git commit -m "feat: descripción del cambio"

# Push a rama
git push origin feature/mi-caracteristica

# Merge a main/master
git checkout main
git merge feature/mi-caracteristica
git push origin main
```

---

## 7. Buenas Prácticas

### 7.1 Convención de Nombres

```
Tablas:         snake_case (usuario, cliente_orden)
Columnas:       snake_case (fecha_creacion, monto_total)
Clases:         PascalCase (ClienteController, Usuario)
Métodos:        camelCase (getClientes, crearOrden)
Variables:      camelCase (totalMonto, esActivo)
Constantes:     UPPER_SNAKE_CASE (MAX_ITEMS, DB_HOST)
```

### 7.2 Estructura de Controladores

```php
class MiController extends Controller {
    // 1. Constructor con requisitos
    public function __construct()
    
    // 2. Métodos GET (lectura)
    public function index()
    public function show()
    public function edit()
    
    // 3. Métodos POST/PUT/DELETE (escritura)
    public function store()
    public function update()
    public function delete()
    
    // 4. Métodos auxiliares privados
    private function validar()
    private function calcular()
}
```

### 7.3 Validación

```php
// Siempre validar en store() y update()
if (empty($_POST['nombre'])) {
    throw new ValidationException("Nombre requerido");
}

if (!validate_email($_POST['email'])) {
    throw new ValidationException("Email inválido");
}

if (!validate_unique('usuarios', 'email', $_POST['email'], $_POST['id'] ?? null)) {
    throw new ValidationException("Email ya existe");
}
```

### 7.4 Auditoría

```php
// Registrar cambios importantes
$this->logAudit('crear_cliente', [
    'nombre' => $_POST['nombre'],
    'email' => $_POST['email'],
]);

// O en el Service
AuditService::log('clientes', 'INSERT', $data, auth_user()->id);
```

### 7.5 Seguridad

```php
// ✅ Siempre escapar salida
echo safe_string($usuario->nombre);

// ✅ Usar prepared statements
$usuarios = DB::prepare("SELECT * FROM usuarios WHERE email = ?")
    ->execute([$email])
    ->fetchAll();

// ✅ Validar CSRF
if ($_POST['_token'] !== csrf_token()) {
    throw new Exception("CSRF invalid");
}

// ✅ Requerir autenticación
$this->requireLogin();

// ✅ Requerir rol específico
$this->requireRol(1, 2);  // Admin o Operador
```

---

## 8. Testing

### 8.1 Estructura de Tests

```
tests/
├── bootstrap.php              # Configuración inicial
├── TestCase.php              # Clase base con helpers
├── Feature/
│   ├── LoginTest.php         # Tests funcionales
│   ├── InventoryTest.php
│   └── ProductionOrderTest.php
└── Unit/
    ├── Helpers/
    │   ├── FuncionesTest.php
    │   └── ValidatorsTest.php
    ├── Core/
    │   └── PaginationTest.php
    └── Services/
        └── ServiceTest.php
```

### 8.2 Ejemplo de Test Funcional

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase {
    
    public function testLoginExitoso() {
        $response = $this->post('/login', [
            'email' => 'admin@plastifrus.com',
            'password' => 'password'
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSessionHas('user');
    }
    
    public function testLoginFallido() {
        $response = $this->post('/login', [
            'email' => 'admin@plastifrus.com',
            'password' => 'wrongpassword'
        ]);
        
        $this->assertSessionHasErrors();
    }
}
```

### 8.3 Ejemplo de Test Unitario

```php
<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class FuncionesTest extends TestCase {
    
    public function testFormatMoney() {
        $result = format_money(1500.50);
        $this->assertEquals('$ 1,500.50', $result);
    }
    
    public function testFormatDate() {
        $result = format_date('2026-06-02');
        $this->assertEquals('2 de junio de 2026', $result);
    }
    
    public function testTruncate() {
        $text = 'Este es un texto muy largo que debe truncarse';
        $result = truncate($text, 20);
        $this->assertEquals('Este es un texto muy...', $result);
    }
}
```

### 8.4 Ejecutar Tests

```bash
# Todos los tests
composer test

# Solo Feature tests
vendor/bin/phpunit --testsuite=Feature

# Solo Unit tests
vendor/bin/phpunit --testsuite=Unit

# Test específico
vendor/bin/phpunit tests/Feature/LoginTest.php::LoginTest::testLoginExitoso

# Con reporte HTML de cobertura
vendor/bin/phpunit --coverage-html=coverage
```

---

## 9. Debugging

### 9.1 Debugging con Logs

```php
// Escribir a log
error_log("Variable: " . print_r($variable, true));
error_log("Debug: Antes de actualizar DB");

// Ver logs
tail -f storage/logs/application.log
```

### 9.2 Debugging con var_dump

```php
// SOLO en desarrollo
if (APP_DEBUG) {
    var_dump($variable);
    die();
}

// O usar dd() helper
dd($variable);  // dump and die
```

### 9.3 Debugging con Xdebug (PhpStorm/VS Code)

```php
// Setear breakpoint en PhpStorm
// F9 para ejecutar hasta breakpoint
// F10 para step over
// F11 para step into
```

---

## 10. Performance & Optimization

### 10.1 Consultas Optimizadas

```php
// ❌ MAL: N+1 queries
foreach ($clientes as $cliente) {
    echo $cliente->vendedor->nombre;  // Query por cada cliente!
}

// ✅ BIEN: Eager loading (si fuera Laravel)
$clientes = Cliente::all();
// O cargar relación manualmente
foreach ($clientes as $cliente) {
    $cliente->vendedor = Vendedor::find($cliente->vendedor_id);
}
```

### 10.2 Índices en BD

```sql
-- Crear índices para búsqueda rápida
CREATE INDEX idx_email ON usuarios(email);
CREATE INDEX idx_cliente_id ON ordenes_cabecera(cliente_id);
CREATE INDEX idx_estado ON ordenes_cabecera(estado);

-- Índices compuestos para queries frecuentes
CREATE INDEX idx_vendedor_estado ON ventas(vendedor_id, estado);
```

### 10.3 Caché

```php
// Caché en sesión
session('config_roles', $roles);
$roles = session('config_roles');

// Caché en archivo
file_put_contents('storage/cache/config.json', json_encode($config));
$config = json_decode(file_get_contents('storage/cache/config.json'), true);
```

---

## 11. Deployment en Producción

### 11.1 Checklist Pre-Deployment

- [ ] APP_ENV=production en .env
- [ ] APP_DEBUG=false en .env
- [ ] Base de datos backup actualizado
- [ ] Ejecutar: `composer install --no-dev`
- [ ] Ejecutar tests: `composer test`
- [ ] Verificar logs de errores
- [ ] HTTPS habilitado
- [ ] Contraseña segura en BD
- [ ] SMTP configurado para emails
- [ ] Carpeta `storage/` con permisos 775

### 11.2 Deployment con Docker

```bash
# Construir imagen
docker build -t plastifrus:latest .

# Push a registry
docker push tu-registry/plastifrus:latest

# En servidor de producción
docker pull tu-registry/plastifrus:latest
docker run -d \
  --name plastifrus \
  -p 80:80 \
  -e APP_ENV=production \
  -e DB_HOST=bd.ejemplo.com \
  -v /data/plastifrus:/var/www/html/storage \
  tu-registry/plastifrus:latest
```

### 11.3 Monitoreo

```bash
# Ver logs de la aplicación
tail -f storage/logs/application.log

# Ver logs de Apache/PHP
tail -f /var/log/apache2/error.log
tail -f /var/log/php-fpm.log

# Monitorear BD
SHOW PROCESSLIST;
SHOW STATUS;
```

---

## 12. Conclusión

**Plasti Frus** está diseñado para ser fácil de desarrollar y mantener:

✅ Estructura clara MVC sin frameworks pesados
✅ Convenciones consistentes
✅ Validación y seguridad integradas
✅ Tests automatizados
✅ Documentación exhaustiva
✅ Fácil deployment con Docker

**Próximos pasos para nuevos desarrolladores:**

1. Clonar repositorio
2. Ejecutar `composer install`
3. Configurar `.env`
4. Ejecutar `php seed.php --fresh`
5. Iniciar servidor: `php -S localhost:8000 -t public/ public/router.php`
6. Acceder a `http://localhost:8000`
7. Leer documentación de módulos específicos

**¡Feliz desarrollo!** 🚀
