-- Corrección manual equivalente a la migración PHP 2026_05_28_000002.
-- Ejecutar solo si no se usa `php bin/plasti migrate`.

CREATE TABLE IF NOT EXISTS comisiones_vendedor (
    id_comision INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    id_venta INT NOT NULL,
    monto_comision DECIMAL(10,2) NOT NULL DEFAULT 0,
    porcentaje_comision DECIMAL(5,2) NOT NULL DEFAULT 0,
    estatus VARCHAR(20) DEFAULT 'pendiente',
    fecha_calculo DATE,
    fecha_pago DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_id_vendedor (id_vendedor),
    INDEX idx_id_venta (id_venta),
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si la columna ventas.id_vendedor no existe, agregarla antes del backfill:
-- ALTER TABLE ventas ADD COLUMN id_vendedor INT NULL AFTER id_cliente;

INSERT INTO comisiones_vendedor (id_vendedor, id_venta, monto_comision, porcentaje_comision, estatus, fecha_calculo)
SELECT COALESCE(v.id_vendedor, c.id_vendedor),
       v.id_venta,
       ROUND(COALESCE(v.cantidad_vendida, 0) * COALESCE(v.precio_unitario, 0) * 5 / 100, 2),
       5,
       CASE WHEN COALESCE(v.estatus, v.estado, '') = 'cancelado' THEN 'cancelada' ELSE 'pendiente' END,
       COALESCE(v.fecha_venta, CURRENT_DATE)
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
LEFT JOIN comisiones_vendedor cv ON cv.id_venta = v.id_venta
WHERE cv.id_comision IS NULL
  AND COALESCE(v.id_vendedor, c.id_vendedor) IS NOT NULL
  AND COALESCE(v.cantidad_vendida, 0) > 0
  AND COALESCE(v.precio_unitario, 0) > 0;
