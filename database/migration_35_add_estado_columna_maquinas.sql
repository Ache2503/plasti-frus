ALTER TABLE maquinas ADD COLUMN IF NOT EXISTS estado ENUM('operando','setup','detenida','apagada','mantenimiento') DEFAULT 'apagada' AFTER estatus;
