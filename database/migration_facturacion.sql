ALTER TABLE clientes
  ADD COLUMN codigo_postal VARCHAR(10) DEFAULT NULL AFTER estado,
  ADD COLUMN regimen_fiscal VARCHAR(10) DEFAULT NULL AFTER codigo_postal,
  ADD COLUMN uso_cfdi VARCHAR(10) DEFAULT NULL AFTER regimen_fiscal,
  ADD COLUMN correo_fiscal VARCHAR(100) DEFAULT NULL AFTER correo;

ALTER TABLE solicitudes_factura
  ADD COLUMN id_usuario_procesa INT DEFAULT NULL AFTER estatus,
  ADD COLUMN fecha_procesada DATETIME DEFAULT NULL AFTER id_usuario_procesa,
  ADD COLUMN id_factura INT DEFAULT NULL AFTER fecha_procesada,
  ADD FOREIGN KEY (id_factura) REFERENCES facturas(id_factura);
