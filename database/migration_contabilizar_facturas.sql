ALTER TABLE facturas
    ADD COLUMN subtotal DECIMAL(10,2) AFTER monto_total,
    ADD COLUMN iva DECIMAL(10,2) AFTER subtotal,
    ADD COLUMN contabilizada TINYINT(1) NOT NULL DEFAULT 0 AFTER estatus,
    ADD COLUMN id_poliza INT(11) NULL AFTER contabilizada;
