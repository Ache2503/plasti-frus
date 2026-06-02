# Arquitectura del Sistema Plasti Frus

## 1. Visión General

**Plasti Frus** es un sistema ERP/MRP modular basado en MVC nativo de PHP 8.3, sin frameworks externos. La arquitectura sigue principios SOLID y está organizada en capas horizontales (Core, Services, Models) y verticales (módulos por funcionalidad).

```
┌─────────────────────────────────────────────────────────┐
│                   CAPA DE PRESENTACIÓN                  │
│         (Views + Frontend: Bootstrap 5.3, DataTables)   │
└────────────┬────────────────────────────────────┬────────┘
             │                                    │
    ┌────────▼──────────┐              ┌─────────▼─────────┐
    │  Controllers      │              │   API Controllers │
    │  (54+ clases)     │              │   (REST endpoints)│
    └────────┬──────────┘              └────────┬──────────┘
             │                                  │
    ┌────────▼──────────────────────────────────▼──────────┐
    │              CAPA DE LÓGICA DE NEGOCIO               │
    │                     (Services)                        │
    │  ├─ AuthService     (autenticación, permisos)       │
    │  ├─ AuditService    (logging, cambios)              │
    │  ├─ MailService     (emails)                        │
    │  ├─ ExportService   (PDF, Excel)                    │
    │  ├─ OrdenService    (lógica órdenes)               │
    │  ├─ VentaService    (lógica ventas)                │
    │  ├─ KardexService   (inventarios)                  │
    │  ├─ ComisionService (comisiones vendedores)        │
    │  └─ NotificacionService (notificaciones)           │
    └────────┬────────────────────────────────────────────┘
             │
    ┌────────▼──────────────────────────────────────────┐
    │         CAPA DE ACCESO A DATOS                   │
    │              (Models + Repositories)              │
    │  ├─ 31 Modelos (Active Record)                  │
    │  ├─ 9 Repositorios (consultas especializadas)  │
    │  └─ Validadores integrados                      │
    └────────┬──────────────────────────────────────────┘
             │
    ┌────────▼──────────────────────────────────────────┐
    │         CAPA DE PERSISTENCIA                    │
    │    (MySQL 8.0+ / MariaDB 11.4, 130+ tablas)     │
    │  ├─ Datos transaccionales                       │
    │  ├─ Auditoría y cambios                         │
    │  ├─ Kardex e historial                          │
    │  └─ KPIs e indicadores                          │
    └────────────────────────────────────────────────────┘
```

---

## 2. Componentes Clave

### 2.1 Core Framework (`app/Core/`)

#### `Router.php` - Enrutador HTTP
```php
// Soporta verbos HTTP: GET, POST, PUT, DELETE
// Parámetros: {id}, {codigo}, {nombre}
// Middlewares: require_login, require_rol(1,2,3)

GET /materiales        → MaterialesController@index()
POST /materiales       → MaterialesController@store()
GET /materiales/{id}   → MaterialesController@edit()
PUT /materiales/{id}   → MaterialesController@update()
DELETE /materiales/{id}→ MaterialesController@delete()
```

#### `Controller.php` - Clase Base de Controladores
```php
abstract class Controller {
    protected $model;
    protected $view;
    protected $user;
    
    // Métodos disponibles
    public function requireLogin()
    public function requireRol(int $rol)
    public function puede_acceder(string $modulo, string $accion)
    public function render(string $view, array $data)
    public function redirect(string $url)
    public function json(array $data, int $status = 200)
    public function notFound()
}
```

#### `Model.php` - ORM Active Record
```php
class Material extends Model {
    protected $table = 'materiales';
    protected $fillable = ['nombre', 'descripcion', 'proveedor_id'];
    protected $casts = ['precio' => 'float', 'stock' => 'int'];
    
    // Métodos CRUD
    static::all()
    static::find($id)
    static::where($column, $operator, $value)
    static::create($data)
    static::update($id, $data)
    static::delete($id)
}
```

#### `Database.php` - Conexión PDO
- Maneja conexión a MySQL/MariaDB
- Ejecuta queries parametrizadas (prepared statements)
- Transacciones ACID
- Connection pooling

#### `View.php` - Motor de Vistas
- Renderiza archivos PHP como vistas
- Inyección de variables (`$data`)
- Layouts reutilizables (header, sidebar, footer)
- Escaping automático de salida HTML

#### `Pagination.php` - Paginación Reutilizable
```php
$pagination = new Pagination($total_items, $items_per_page);
$offset = $pagination->offset();
$items = Material::limit($pagination->limit)
                 ->offset($offset)
                 ->get();
```

---

### 2.2 Controllers (54+ archivos organizados por módulo)

#### Estructura de Carpetas
```
app/Http/Controllers/
├── Accounting/
│   ├── ContabilidadController.php
│   ├── FacturasController.php
│   ├── PolizasController.php
│   └── CierreContableController.php
├── Api/
│   ├── DashboardApiController.php
│   ├── ClientesApiController.php
│   └── ...
├── Auth/
│   └── AuthController.php
├── Crm/
│   ├── ClientesController.php
│   ├── OportunidadController.php
│   ├── InteraccionController.php
│   └── ActividadController.php
├── Dashboard/
│   └── HomeController.php
├── Inventory/
│   └── KardexController.php
├── Maintenance/
│   ├── MaquinasController.php
│   ├── MantenimientoController.php
│   └── MoldesController.php
├── Portal/
│   ├── CatalogoController.php
│   ├── CarritoController.php
│   ├── TicketController.php
│   └── FacturaPublicaController.php
├── Production/
│   ├── MaterialesController.php
│   ├── ProductosController.php
│   ├── RecetasController.php
│   └── OrdenesController.php
├── Purchasing/
│   └── ProveedoresController.php
├── Quality/
│   └── CalidadController.php
├── Sales/
│   ├── VentasController.php
│   ├── PresupuestoController.php
│   └── ReportesVendedorController.php
└── System/
    ├── UsuariosController.php
    ├── AdminController.php
    └── ReportesController.php
```

#### Patrón de Controllers
```php
class MaterialesController extends Controller {
    public function __construct() {
        $this->requireLogin();
        $this->requireRol(1, 2, 3); // Admin, Operador, Supervisor
    }
    
    public function index() {
        // GET /materiales - Listar
        $materiales = Material::all();
        return $this->render('materiales/index', ['materiales' => $materiales]);
    }
    
    public function create() {
        // GET /materiales/create - Formulario
        return $this->render('materiales/create');
    }
    
    public function store() {
        // POST /materiales - Guardar
        $data = $_POST;
        Material::create($data);
        $this->logAudit('crear_material', $data);
        $this->redirect('/materiales');
    }
    
    public function edit() {
        // GET /materiales/{id}/edit - Formulario edición
        $material = Material::find($_GET['id']);
        return $this->render('materiales/edit', ['material' => $material]);
    }
    
    public function update() {
        // PUT /materiales/{id} - Actualizar
        Material::update($_POST['id'], $_POST);
        $this->logAudit('editar_material', $_POST);
        $this->redirect('/materiales');
    }
    
    public function delete() {
        // DELETE /materiales/{id} - Eliminar
        Material::delete($_GET['id']);
        $this->logAudit('eliminar_material', ['id' => $_GET['id']]);
        $this->redirect('/materiales');
    }
}
```

---

### 2.3 Models (31 clases, Active Record)

#### Modelo Estándar
```php
class Material extends Model {
    protected $table = 'materiales';
    protected $fillable = ['nombre', 'descripcion', 'tipo', 'proveedor_id', 'precio_compra', 'stock', 'punto_reorden'];
    protected $casts = [
        'precio_compra' => 'float',
        'stock' => 'int',
        'punto_reorden' => 'int',
    ];
    
    // Relaciones
    public function proveedor() {
        return Proveedor::find($this->proveedor_id);
    }
    
    // Scopes / Métodos especializados
    public static function getLowStock() {
        return DB::select("SELECT * FROM materiales WHERE stock <= punto_reorden");
    }
    
    // Validación
    public function validar() {
        if (empty($this->nombre)) throw new ValidationException("Nombre requerido");
        if ($this->stock < 0) throw new ValidationException("Stock negativo");
    }
}
```

#### Modelos Principales por Categoría

**Maestros:**
- `User` (usuarios del sistema)
- `Cliente`
- `Proveedor`
- `Empleado`

**Producción:**
- `Material`
- `Producto`
- `RecetaCabe` (cabecera de receta)
- `Maquina`
- `Molde`
- `OrdenCabe` (orden de producción)

**Ventas & CRM:**
- `Venta`
- `Presupuesto`
- `Oportunidad`
- `Actividad`
- `Interaccion`
- `Ticket` / `TicketSoporte`

**Contabilidad:**
- `PlanCuenta`
- `Poliza`
- `CierreContable`
- `Factura`

**Procesos:**
- `MantenimientoMaquina`
- `InspeccionCalidad`
- `RechazoCalidad`
- `IncidenciaProduccion`
- `KardexMaterial`

---

### 2.4 Services (9 clases, Lógica de Negocio)

#### `AuthService.php`
```php
class AuthService {
    // Autenticación
    public static function login($email, $password): ?User
    public static function logout()
    public static function user(): ?User
    
    // Permisos
    public static function puede($modulo, $accion): bool
    public static function requireRol(...$roles)
    public static function hasRol($rol): bool
}
```

#### `AuditService.php`
```php
class AuditService {
    // Logging de cambios
    public static function log($tabla, $operacion, $data, $user_id)
    public static function getHistorial($tabla, $id)
    public static function getDiferencias($registro_viejo, $registro_nuevo): array
}
```

#### `ExportService.php`
```php
class ExportService {
    public static function toPdf($view, $data, $filename)
    public static function toExcel($data, $filename)
    public static function toCSV($data, $filename)
}
```

#### `MailService.php`
```php
class MailService {
    public static function send($to, $subject, $body, $isHtml = true)
    public static function sendTemplate($to, $template, $data)
    public static function sendNotificacion($usuario_id, $titulo, $mensaje)
}
```

#### `OrdenService.php`
```php
class OrdenService {
    public static function crearOrden($receta_id, $cantidad): OrdenCabe
    public static function iniciarOrden($orden_id, $maquina_id, $turno)
    public static function completarOrden($orden_id, $cantidad_real, $mermas)
    public static function calcularMerma($cantidad_esperada, $cantidad_real): float
    public static function actualizarKardex($orden_id)
}
```

#### `ComisionService.php`
```php
class ComisionService {
    public static function calcularComisiones($vendedor_id, $mes, $año)
    public static function getMetasVendedor($vendedor_id): MetaVendedor
    public static function getTopVendedores($mes, $año): array
}
```

---

### 2.5 Helpers (Funciones Reutilizables)

#### `funciones.php` - 23 funciones

**URLs:**
```php
url('/materiales')                           // Genera URL absoluta
asset('/css/main.css')                       // URL de assets
route('materiales.edit', ['id' => 5])        // URL con nombre de ruta
```

**Sesión:**
```php
session_start()                              // Iniciar sesión
session('user')                              // Obtener variable de sesión
session('user', $user)                       // Guardar variable
sessionFlash('success', 'Guardado!')         // Guardar mensaje temporal
flash('success')                             // Obtener mensaje flash
```

**Formato:**
```php
format_money(1500.50)                        // $ 1,500.50
format_date('2026-06-02')                    // 2 de junio de 2026
safe_string('<script>alert("XSS")</script>')// Escape HTML
truncate($text, 50)                          // Limitar caracteres
time_ago('2026-06-02 15:30:00')             // Hace 2 horas
```

**Permisos:**
```php
puede('dashboard', 'ver')                    // Verificar permiso
requireRol(1, 2, 3)                          // Requerir rol
isAuthenticated()                            // ¿Usuario autenticado?
getAuthUser()                                // Obtener usuario actual
```

**Utilidades:**
```php
csrf_token()                                 // Generar token CSRF
is_post()                                    // ¿Es POST?
is_get()                                     // ¿Es GET?
is_ajax()                                    // ¿Es AJAX?
get_numeric($var)                            // Obtener número limpio
```

#### `validators.php` - 15 validadores

```php
validate_email('test@example.com')           // Email válido
validate_rfc('ABC123456AB0')                 // RFC México válido
validate_phone('5551234567')                 // Teléfono válido
validate_range($number, 0, 100)              // En rango
validate_date('2026-06-02')                  // Fecha válida
validate_time('15:30:00')                    // Hora válida
validate_url('https://example.com')          // URL válida
validate_password_strength($password)        // Contraseña segura
validate_unique($table, $column, $value)     // Valor único
validate_exists($table, $column, $value)     // Valor existe
validate_currency($amount)                   // Cantidad válida
validate_iban($iban)                         // IBAN válido
validate_credit_card($number)                // Tarjeta válida
validate_file_extension($file, $allowed)     // Extensión permitida
validate_file_size($file, $max_kb)           // Tamaño permitido
```

---

### 2.6 Configuración

#### `config/app.php`
```php
return [
    'name' => 'Plasti Frus',
    'env' => env('APP_ENV', 'local'),      // production|local|testing
    'debug' => env('APP_DEBUG', false),
    'timezone' => 'America/Mexico_City',
    'locale' => 'es_MX',
    'currency' => 'MXN',
    
    'roles' => [
        1 => 'Administrador',
        2 => 'Operador',
        3 => 'Supervisor',
        4 => 'Vendedor',
        5 => 'Cliente',
        6 => 'Contador',
    ],
    
    'modules' => [
        'auth', 'dashboard', 'production', 'sales', 'purchasing',
        'accounting', 'crm', 'quality', 'maintenance', 'inventory',
        'incidents', 'notifications', 'reports', 'portal', 'system'
    ],
];
```

#### `config/database.php`
```php
return [
    'driver' => env('DB_DRIVER', 'mysql'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_NAME', 'fabrica_plasticos'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

---

## 3. Flujos de Datos

### 3.1 Flujo Típico de Lectura (GET)

```
1. Usuario abre http://localhost:8000/materiales
   ↓
2. Router.php identifica ruta y controlador
   ↓
3. MaterialesController::index() se ejecuta
   ↓
4. Model Material::all() consulta base de datos
   ↓
5. BD devuelve registros
   ↓
6. Controller inyecta datos en vista
   ↓
7. View renderiza HTML con Bootstrap
   ↓
8. Frontend: DataTables inicializa tabla, AJAX búsqueda
   ↓
9. Navegador muestra página
```

### 3.2 Flujo Típico de Creación (POST)

```
1. Usuario rellenó formulario y clickeó "Guardar"
   ↓
2. POST /materiales + CSRF token
   ↓
3. Router enruta a MaterialesController::store()
   ↓
4. Controller valida datos $_POST
   ↓
5. Material::create($data) inserta en BD
   ↓
6. AuditService::log() registra cambio
   ↓
7. SessionFlash con mensaje de éxito
   ↓
8. Redirect a /materiales
   ↓
9. Usuario ve lista actualizada + notificación toast
```

### 3.3 Flujo de Orden de Producción

```
CREAR ORDEN
├─ Seleccionar Receta (material A, B, C)
├─ Cantidad a producir
├─ Asignar Máquina
├─ OrdenService::crearOrden() → BD
└─ Auditoría registra creación

INICIAR ORDEN (Operador)
├─ Asignar turno
├─ Validar máquina disponible
├─ OrdenService::iniciarOrden() → BD
├─ ShiftLog crea registro
└─ Auditoría

DURANTE PRODUCCIÓN
├─ Registrar mermas/scrap
├─ Reportar paros (máquina, duración, causa)
├─ Registrar calibraciones
├─ BitacoraProduccion actualiza

COMPLETAR ORDEN
├─ Cantidad real producida
├─ Mermas finales
├─ OrdenService::completarOrden() → BD
├─ KardexService::actualizar() → Actualiza stock de materiales
├─ Calcula eficiencia y OEE
├─ Auditoría registra cierre
└─ Disponible para facturación
```

---

## 4. Seguridad en Profundidad

### 4.1 Autenticación
- Contraseñas: `password_hash($password, PASSWORD_BCRYPT)` con cost 12
- Sesión: `session_regenerate_id()` después de login
- Timeout: 3600 segundos (1 hora) configurable

### 4.2 Autorización
- Verificación de rol en constructor de Controller
- Permisos granulares por modulo + acción
- Tabla `permisos` vincula roles a modulos + acciones

### 4.3 Inyección SQL
- 100% de queries con prepared statements (PDO prepared)
- Nunca concatenar SQL directamente
- Model::where() usa vinculación de parámetros

### 4.4 XSS (Cross-Site Scripting)
- `safe_string()` escapa HTML: `htmlspecialchars($text, ENT_QUOTES, 'UTF-8')`
- Toda salida en vistas está escapada
- Content-Security-Policy headers en respuesta

### 4.5 CSRF (Cross-Site Request Forgery)
- Token CSRF generado por `csrf_token()`
- Validado en middleware `verify_csrf_token`
- Token regenerado por sesión

### 4.6 Auditoría Integral
- Tabla `auditorias` registra:
  - Usuario, fecha, hora, tabla, operación (INSERT/UPDATE/DELETE)
  - Datos anteriores vs nuevos (comparación)
  - IP origen, user agent
  - Resultado (éxito/error)

---

## 5. Patrones de Diseño Implementados

| Patrón | Ubicación | Propósito |
|--------|-----------|----------|
| **MVC** | Core/Router → Controllers → Models/Views | Separación de responsabilidades |
| **Active Record** | Models | Mapeo objeto-relacional |
| **Service Layer** | Services/ | Lógica de negocio centralizada |
| **Repository** | Repositories/ | Consultas especializadas |
| **Factory** | Models::create() | Crear instancias |
| **Singleton** | Database, AuthService | Una única instancia global |
| **Strategy** | ExportService (PDF, Excel, CSV) | Intercambiar algoritmos |
| **Observer** | AuditService | Registrar cambios |
| **Middleware** | Router::middleware() | Interceptar requests |

---

## 6. Performance y Optimización

### Query Optimization
- Índices en claves foráneas (FK)
- Índices en columnas de búsqueda frecuente
- Paginación de resultados grandes (25-100 items por página)
- Caché de configuración en `storage/cache/`

### Frontend Optimization
- Assets minificados (CSS, JS)
- Carga lazy de imágenes
- DataTables: búsqueda servidor-side
- AJAX para operaciones rápidas (sin recargar página)

### Caching
- Variables de sesión para usuario autenticado
- Cache de configuración en Bootstrap
- Caché de permisos por sesión

---

## 7. Testing

### Estructura de Tests
```
tests/
├── bootstrap.php                # Configuración inicial
├── TestCase.php                 # Clase base
├── Feature/
│   ├── LoginTest.php           # 6 tests autenticación
│   ├── RegisterTest.php        # 4 tests registro
│   ├── InventoryTest.php       # 4 tests inventario
│   └── ProductionOrderTest.php # 5 tests órdenes
└── Unit/
    ├── Helpers/
    │   ├── ValidatorsTest.php  # 16 tests validadores
    │   └── FuncionesTest.php   # 12 tests funciones
    ├── Core/
    │   └── PaginationTest.php  # 7 tests paginación
    └── Services/
        └── ServiceTest.php     # 9 tests servicios
```

### Ejecución
```bash
vendor/bin/phpunit                    # Todos los tests
vendor/bin/phpunit --testsuite=Unit   # Solo unitarios
vendor/bin/phpunit --testsuite=Feature# Solo funcionales
vendor/bin/phpunit tests/Feature/LoginTest.php  # Test específico
```

### Coverage
- Autenticación: 100%
- Helpers: 95%
- Core: 90%
- Models: 70%

---

## 8. Deployments Soportados

### Docker (Recomendado)
```bash
docker-compose up -d --build
# PHP 8.3-Apache, MariaDB 11.4, PhpMyAdmin
```

### Servidor Linux/Ubuntu
```bash
apt install php8.3 mysql-server-8.0
composer install
php -S localhost:8000 -t public/ public/router.php
```

### Servidor Windows IIS
- Requisitos: PHP 8.3+ con FastCGI, MySQL 8.0+
- Configurar rewrite rules para enrutamiento

### Servidor Apache Compartido
- Configurar `.htaccess` para rewrite
- AllowOverride All en VirtualHost
- Directorios permitidos

---

## 9. Conclusión

La arquitectura de **Plasti Frus** sigue principios modernos de desarrollo web:
- ✅ Separación clara de responsabilidades (MVC)
- ✅ Reutilización de código (Services, Helpers)
- ✅ Seguridad en profundidad (Auth, Audit, Escape)
- ✅ Testing integral (PHPUnit 63 tests)
- ✅ Modularidad (17 módulos independientes)
- ✅ Escalabilidad (130+ tablas, índices, caché)
- ✅ Documentación completa

**Próximas mejoras:**
- Implementar métodos de pago en ventas
- Generar CFDI automático en facturas
- Control de stock en venta
- Módulo de compras completo
- API REST más extensa
