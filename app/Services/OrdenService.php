<?php
namespace App\Services;

use App\Models\OrdenCabe;
use App\Core\Database;

class OrdenService
{
    private Database $db;
    private OrdenCabe $ordenModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ordenModel = new OrdenCabe();
    }

    public function getAll(array $filters = []): array
    {
        return $this->ordenModel->getWithRelations($filters);
    }

    public function getStats(): array
    {
        return $this->ordenModel->getStats();
    }

    public function findById(int $id): ?array
    {
        return $this->ordenModel->getByIdWithRelations($id);
    }

    public function create(array $data): int
    {
        $id = $this->ordenModel->create($data);
        $this->notifyNewOrder($id, $data);
        $this->logActivity('crear', 'orden', $id, "Orden #{$id} - Producto #{$data['id_producto']}");
        return $id;
    }

    public function start(int $id): void
    {
        $orden = $this->ordenModel->find($id);
        if (!$orden || $orden['cantidad_real_buenas']) {
            throw new \RuntimeException('No se puede iniciar esta orden');
        }

        $this->ordenModel->update($id, ['estatus' => 'en_progreso']);
        $this->db->insert('seguimiento_ordenes', [
            'id_orden_cabe' => $id,
            'fecha' => date('Y-m-d H:i:s'),
            'estatus' => 'en_progreso',
            'comentarios' => 'Orden iniciada por ' . (user_nombre_completo() ?: 'operador'),
        ]);

        $this->logActivity('iniciar', 'orden', $id, 'Orden iniciada');
    }

    public function complete(int $id, array $data): void
    {
        $this->ordenModel->update($id, [
            'cantidad_real_buenas' => $data['buenas'],
            'estatus' => 'completada',
        ]);

        $this->db->insert('seguimiento_ordenes', [
            'id_orden_cabe' => $id,
            'fecha' => date('Y-m-d H:i:s'),
            'estatus' => 'completada',
            'comentarios' => 'Cantidad real: ' . $data['buenas'] . (!empty($data['observaciones']) ? ' — ' . $data['observaciones'] : ''),
        ]);

        if (!empty($data['merma_kg']) && $data['merma_kg'] > 0) {
            $this->db->insert('ordenes_merma', [
                'id_orden_cabe' => $id,
                'tipo_merma' => $data['merma_tipo'] ?? 'general',
                'cantidad_kg' => $data['merma_kg'],
                'destino' => $data['merma_destino'] ?? 'reciclaje',
            ]);
        }

        $this->updateMoldeCiclos($id, (int) $data['buenas']);

        $this->logActivity('completar', 'orden', $id, "Cantidad real: {$data['buenas']}");
    }

    public function delete(int $id): void
    {
        $this->ordenModel->delete($id);
        $this->logActivity('eliminar', 'orden', $id, 'Orden eliminada');
    }

    public function getMermas(int $id): array
    {
        return $this->ordenModel->getMermasByOrden($id);
    }

    public function getSeguimiento(int $id): array
    {
        return $this->ordenModel->getSeguimientoByOrden($id);
    }

    private function notifyNewOrder(int $id, array $data): void
    {
        $operadores = $this->db->fetchAll(
            "SELECT id_usuario FROM usuarios WHERE id_rol = 2 AND activo = 1"
        );
        foreach ($operadores as $op) {
            notificar_operador((int) $op['id_usuario'], 'nueva_orden', "Nueva Orden #{$id}",
                "Se creó una orden para el turno {$data['turno']} — {$data['cantidad_planificada']} pzas", $id);
        }

        $supervisores = $this->db->fetchAll(
            "SELECT id_usuario FROM usuarios WHERE id_rol = 3 AND activo = 1"
        );
        foreach ($supervisores as $sup) {
            notificar_supervisor((int) $sup['id_usuario'], 'nueva_orden', "Nueva Orden #{$id}",
                "Se creó la orden #{$id} para {$data['turno']} — {$data['cantidad_planificada']} pzas", $id);
        }
    }

    private function updateMoldeCiclos(int $ordenId, int $buenas): void
    {
        $orden = $this->ordenModel->find($ordenId);
        if (!$orden || empty($orden['id_molde'])) return;

        $molde = $this->db->fetchOne(
            "SELECT numero_cavidades FROM moldes WHERE id_molde = :id",
            ['id' => $orden['id_molde']]
        );

        if ($molde) {
            $ciclos = (int) $buenas / max((int) ($molde['numero_cavidades'] ?? 1), 1);
            $this->db->query(
                "UPDATE moldes SET ciclos_acumulados = COALESCE(ciclos_acumulados, 0) + :ciclos WHERE id_molde = :id",
                ['ciclos' => max(1, (int) ceil($ciclos)), 'id' => $orden['id_molde']]
            );
        }
    }

    private function logActivity(string $accion, string $entidad, $idEntidad, ?string $detalle = null): void
    {
        registrar_log($accion, $entidad, $idEntidad, $detalle);
    }
}
