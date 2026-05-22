<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;

class CarteraController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function obtenerCliente(): array
    {
        $idCliente = user_id_cliente();
        $cliente = $this->db->fetchOne("SELECT * FROM clientes WHERE id_cliente = :id", ['id' => $idCliente]);
        return [$idCliente, $cliente];
    }

    public function index(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Solo clientes pueden acceder a la cartera');
            $this->redirect('/');
        }

        [$idCliente, $cliente] = $this->obtenerCliente();

        if (!$idCliente) {
            set_flash('error', 'No tienes un perfil de cliente vinculado');
            $this->redirect('/');
        }

        $totalCargos = $this->db->fetchOne("
            SELECT COALESCE(SUM(monto), 0) as total FROM movimientos_cartera 
            WHERE id_cliente = :id AND tipo = 'cargo'
        ", ['id' => $idCliente])['total'];

        $totalAbonos = $this->db->fetchOne("
            SELECT COALESCE(SUM(monto), 0) as total FROM movimientos_cartera 
            WHERE id_cliente = :id AND tipo = 'abono'
        ", ['id' => $idCliente])['total'];

        $saldoActual = $totalCargos - $totalAbonos;

        $movimientos = $this->db->fetchAll("
            SELECT m.*, p.nombre as producto_nombre
            FROM movimientos_cartera m
            LEFT JOIN ventas v ON m.id_venta = v.id_venta
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE m.id_cliente = :id
            ORDER BY m.fecha_movimiento DESC
        ", ['id' => $idCliente]);

        $tarjetas = $this->db->fetchAll("
            SELECT * FROM tarjetas_cliente 
            WHERE id_cliente = :id AND activa = 1
            ORDER BY created_at DESC
        ", ['id' => $idCliente]);

        $referencias = $this->db->fetchAll("
            SELECT * FROM depositos_referencia 
            WHERE id_cliente = :id
            ORDER BY created_at DESC
        ", ['id' => $idCliente]);

        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $tiposTarjeta = $this->db->fetchAll("SELECT DISTINCT tipo FROM tarjetas_cliente WHERE activa = 1 ORDER BY tipo");
        $tiposTarjeta = array_column($tiposTarjeta, 'tipo');
        if (empty($tiposTarjeta)) {
            $tiposTarjeta = ['Visa', 'Mastercard', 'American Express', 'Tarjeta de Débito'];
        }

        $data = [
            'pageTitle' => 'Mi Cartera',
            'rol_nombre' => user_rol_nombre(),
            'cliente' => $cliente,
            'saldo_actual' => $saldoActual,
            'total_cargos' => $totalCargos,
            'total_abonos' => $totalAbonos,
            'movimientos' => $movimientos,
            'tarjetas' => $tarjetas,
            'referencias' => $referencias,
            'carrito_count' => $carritoCount,
            'tipos_tarjeta' => $tiposTarjeta,
        ];

        $this->view('home/cartera', $data);
    }

    public function agregarTarjeta(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/cartera');
        }

        $idCliente = user_id_cliente();

        $tipo = $this->postParam('tipo');
        $titular = $this->postParam('titular');
        $numero = $this->postParam('numero');
        $expiracion = $this->postParam('expiracion');

        $error = $this->validateRequired(['tipo', 'titular', 'numero'], [
            'tipo' => $tipo, 'titular' => $titular, 'numero' => $numero
        ]);

        if ($error) {
            set_flash('error', $error);
            $this->redirect('/cartera');
        }

        $numeroLimpio = preg_replace('/\D/', '', $numero);
        $enmascarado = str_repeat('*', max(0, strlen($numeroLimpio) - 4)) . substr($numeroLimpio, -4);
        $enmascarado = substr($enmascarado, 0, 16);
        $chunks = str_split($enmascarado, 4);
        $enmascarado = implode(' ', $chunks);

        $expiracionDate = null;
        if ($expiracion) {
            if (preg_match('/^\d{2}\/\d{4}$/', $expiracion)) {
                $expiracionDate = \DateTime::createFromFormat('d/m/Y', '01/' . $expiracion);
                if ($expiracionDate) {
                    $expiracionDate = $expiracionDate->format('Y-m-d');
                }
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiracion)) {
                $expiracionDate = $expiracion;
            }
        }

        $this->db->insert('tarjetas_cliente', [
            'id_cliente' => $idCliente,
            'tipo' => $tipo,
            'titular' => $titular,
            'numero_enmascarado' => $enmascarado,
            'fecha_expiracion' => $expiracionDate,
        ]);

        registrar_log('agregar_tarjeta', 'tarjetas_cliente', $idCliente, "Tarjeta {$tipo} agregada por cliente #{$idCliente}");
        set_flash('success', 'Tarjeta registrada correctamente');
        $this->redirect('/cartera');
    }

    public function eliminarTarjeta(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/cartera');
        }

        $idCliente = user_id_cliente();
        $idTarjeta = (int) ($params['id'] ?? 0);

        $tarjeta = $this->db->fetchOne(
            "SELECT * FROM tarjetas_cliente WHERE id_tarjeta = :id AND id_cliente = :cliente AND activa = 1",
            ['id' => $idTarjeta, 'cliente' => $idCliente]
        );

        if (!$tarjeta) {
            set_flash('error', 'Tarjeta no encontrada');
            $this->redirect('/cartera');
        }

        $this->db->update('tarjetas_cliente', ['activa' => 0], 'id_tarjeta = :id', ['id' => $idTarjeta]);
        registrar_log('eliminar_tarjeta', 'tarjetas_cliente', $idTarjeta, "Tarjeta #{$idTarjeta} desactivada");
        set_flash('success', 'Tarjeta eliminada correctamente');
        $this->redirect('/cartera');
    }

    public function generarReferencia(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/cartera');
        }

        $idCliente = user_id_cliente();

        $monto = $this->postParam('monto');
        if ($monto) {
            $monto = (float) str_replace(['$', ',', ' '], '', $monto);
            if ($monto <= 0) $monto = null;
        } else {
            $monto = null;
        }

        $referencia = 'DEP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $vencimiento = date('Y-m-d', strtotime('+7 days'));

        $this->db->insert('depositos_referencia', [
            'id_cliente' => $idCliente,
            'referencia' => $referencia,
            'monto_sugerido' => $monto,
            'fecha_vencimiento' => $vencimiento,
        ]);

        registrar_log('generar_referencia', 'depositos_referencia', $idCliente, "Referencia {$referencia} generada");
        set_flash('success', "Referencia generada: {$referencia}");
        $this->redirect('/cartera');
    }

    public function cancelarReferencia(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/cartera');
        }

        $idCliente = user_id_cliente();
        $idDeposito = (int) ($params['id'] ?? 0);

        $ref = $this->db->fetchOne(
            "SELECT * FROM depositos_referencia WHERE id_deposito = :id AND id_cliente = :cliente AND estatus = 'pendiente'",
            ['id' => $idDeposito, 'cliente' => $idCliente]
        );

        if (!$ref) {
            set_flash('error', 'Referencia no encontrada o ya fue procesada');
            $this->redirect('/cartera');
        }

        $this->db->update(
            'depositos_referencia',
            ['estatus' => 'cancelado'],
            'id_deposito = :id',
            ['id' => $idDeposito]
        );

        registrar_log('cancelar_referencia', 'depositos_referencia', $idDeposito, "Referencia #{$idDeposito} cancelada");
        set_flash('success', 'Referencia cancelada');
        $this->redirect('/cartera');
    }
}
