<?php
namespace App\Models;

use App\Core\Model;

class DireccionCliente extends Model
{
    protected $table = 'direcciones_cliente';
    protected $primaryKey = 'id_direccion';

    public function getByCliente(int $idCliente): array
    {
        return $this->fetchAll("
            SELECT * FROM {$this->table}
            WHERE id_cliente = :id AND activa = 1
            ORDER BY predeterminada DESC, created_at DESC
        ", ['id' => $idCliente]);
    }

    public function getDefault(int $idCliente)
    {
        return $this->fetchOne("
            SELECT * FROM {$this->table}
            WHERE id_cliente = :id AND activa = 1 AND predeterminada = 1
            LIMIT 1
        ", ['id' => $idCliente]);
    }

    public function createForCliente(int $idCliente, array $data): int
    {
        $isFirst = !$this->fetchOne(
            "SELECT id_direccion FROM {$this->table} WHERE id_cliente = :id AND activa = 1",
            ['id' => $idCliente]
        );

        $predeterminada = $data['predeterminada'] ?? $isFirst ? 1 : 0;

        if ($predeterminada) {
            $this->db->query(
                "UPDATE {$this->table} SET predeterminada = 0 WHERE id_cliente = :id",
                ['id' => $idCliente]
            );
        }

        return $this->db->insert($this->table, [
            'id_cliente' => $idCliente,
            'alias' => $data['alias'],
            'destinatario' => $data['destinatario'] ?? null,
            'telefono_contacto' => $data['telefono_contacto'] ?? null,
            'calle' => $data['calle'],
            'numero_exterior' => $data['numero_exterior'] ?? null,
            'numero_interior' => $data['numero_interior'] ?? null,
            'colonia' => $data['colonia'] ?? null,
            'ciudad' => $data['ciudad'],
            'estado' => $data['estado'],
            'codigo_postal' => $data['codigo_postal'],
            'referencia' => $data['referencia'] ?? null,
            'predeterminada' => $predeterminada,
        ]);
    }

    public function updateForCliente(int $idDireccion, int $idCliente, array $data): bool
    {
        $direccion = $this->find($idDireccion);
        if (!$direccion || $direccion['id_cliente'] !== $idCliente) return false;

        $predeterminada = $data['predeterminada'] ?? $direccion['predeterminada'] ? 1 : 0;
        if ($predeterminada && !$direccion['predeterminada']) {
            $this->db->query(
                "UPDATE {$this->table} SET predeterminada = 0 WHERE id_cliente = :id",
                ['id' => $idCliente]
            );
        }

        $this->db->update($this->table, [
            'alias' => $data['alias'] ?? $direccion['alias'],
            'destinatario' => $data['destinatario'] ?? $direccion['destinatario'],
            'telefono_contacto' => $data['telefono_contacto'] ?? $direccion['telefono_contacto'],
            'calle' => $data['calle'] ?? $direccion['calle'],
            'numero_exterior' => $data['numero_exterior'] ?? $direccion['numero_exterior'],
            'numero_interior' => $data['numero_interior'] ?? $direccion['numero_interior'],
            'colonia' => $data['colonia'] ?? $direccion['colonia'],
            'ciudad' => $data['ciudad'] ?? $direccion['ciudad'],
            'estado' => $data['estado'] ?? $direccion['estado'],
            'codigo_postal' => $data['codigo_postal'] ?? $direccion['codigo_postal'],
            'referencia' => $data['referencia'] ?? $direccion['referencia'],
            'predeterminada' => $predeterminada,
        ], 'id_direccion = :id', ['id' => $idDireccion]);

        return true;
    }

    public function deleteForCliente(int $idDireccion, int $idCliente): bool
    {
        $direccion = $this->find($idDireccion);
        if (!$direccion || $direccion['id_cliente'] !== $idCliente) return false;

        $this->db->update($this->table, [
            'activa' => 0,
        ], 'id_direccion = :id', ['id' => $idDireccion]);

        if ($direccion['predeterminada']) {
            $newDefault = $this->fetchOne(
                "SELECT id_direccion FROM {$this->table} WHERE id_cliente = :id AND activa = 1 LIMIT 1",
                ['id' => $idCliente]
            );
            if ($newDefault) {
                $this->db->update($this->table, [
                    'predeterminada' => 1,
                ], 'id_direccion = :id', ['id' => $newDefault['id_direccion']]);
            }
        }
        return true;
    }

    public function setDefault(int $idDireccion, int $idCliente): bool
    {
        $direccion = $this->find($idDireccion);
        if (!$direccion || $direccion['id_cliente'] !== $idCliente) return false;

        $this->db->query(
            "UPDATE {$this->table} SET predeterminada = 0 WHERE id_cliente = :id",
            ['id' => $idCliente]
        );
        $this->db->update($this->table, [
            'predeterminada' => 1,
        ], 'id_direccion = :id', ['id' => $idDireccion]);
        return true;
    }
}
