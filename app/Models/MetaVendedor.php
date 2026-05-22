<?php
namespace App\Models;

use App\Core\Model;

class MetaVendedor extends Model
{
    protected $table = 'metas_vendedor';
    protected $primaryKey = 'id_meta';

    public function getMetaMes(int $vendedorId, int $anio, int $mes): ?array
    {
        $r = $this->fetchOne("
            SELECT * FROM {$this->table}
            WHERE id_vendedor = :vendedor AND anio = :anio AND mes = :mes
        ", ['vendedor' => $vendedorId, 'anio' => $anio, 'mes' => $mes]);
        return $r ?: null;
    }

    public function setMeta(int $vendedorId, int $anio, int $mes, float $monto): int
    {
        $existing = $this->getMetaMes($vendedorId, $anio, $mes);
        if ($existing) {
            $this->update($existing['id_meta'], ['monto_objetivo' => $monto]);
            return $existing['id_meta'];
        }
        return $this->create([
            'id_vendedor' => $vendedorId,
            'anio' => $anio,
            'mes' => $mes,
            'monto_objetivo' => $monto,
        ]);
    }
}
