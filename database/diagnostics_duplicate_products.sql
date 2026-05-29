-- Diagnostico de productos duplicados visibles en tienda.
-- No elimina datos. Sirve para revisar registros repetidos antes de depurar catalogo.

SELECT
    COALESCE(NULLIF(TRIM(codigo), ''), CONCAT('SIN_CODIGO:', LOWER(TRIM(nombre)))) as clave_producto,
    COUNT(*) as total_registros,
    GROUP_CONCAT(id_producto ORDER BY id_producto) as ids_productos,
    MIN(nombre) as nombre_muestra
FROM productos
WHERE publicar_web = 1 OR publicar_web IS NULL
GROUP BY clave_producto
HAVING COUNT(*) > 1
ORDER BY total_registros DESC, clave_producto;
