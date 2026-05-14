ALTER TABLE clientes ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER sector;
UPDATE clientes SET activo = 1;
