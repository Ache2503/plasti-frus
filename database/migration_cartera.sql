-- Migration: Cartera / Wallet
-- Ejecutar: mysql -u root -p fabrica_plasticos < database/migration_cartera.sql

-- 1. Movimientos de cartera (cargos = compras, abonos = pagos/depósitos)
CREATE TABLE IF NOT EXISTS movimientos_cartera (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    tipo ENUM('cargo', 'abono') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo_despues DECIMAL(10,2) NOT NULL DEFAULT 0,
    concepto VARCHAR(255) NOT NULL,
    referencia VARCHAR(100),
    id_venta INT,
    estatus VARCHAR(50) DEFAULT 'completado',
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE SET NULL,
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_venta (id_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tarjetas registradas del cliente
CREATE TABLE IF NOT EXISTS tarjetas_cliente (
    id_tarjeta INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    tipo VARCHAR(30) NOT NULL COMMENT 'visa, mastercard, oxxo, etc',
    titular VARCHAR(150) NOT NULL,
    numero_enmascarado VARCHAR(20) NOT NULL COMMENT 'últimos 4 dígitos',
    fecha_expiracion DATE,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    INDEX idx_id_cliente (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Referencias de depósito generadas
CREATE TABLE IF NOT EXISTS depositos_referencia (
    id_deposito INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    referencia VARCHAR(50) UNIQUE NOT NULL,
    monto_sugerido DECIMAL(10,2),
    estatus ENUM('pendiente', 'pagado', 'cancelado') DEFAULT 'pendiente',
    fecha_vencimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_referencia (referencia),
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
