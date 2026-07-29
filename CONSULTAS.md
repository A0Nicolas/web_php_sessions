# Consultas SQL de prueba

```sql
-- 1. Login de usuario (password_hash en texto plano)
SELECT id, usuario, nombre, rol, estado FROM usuarios WHERE usuario = 'admin' AND password_hash = 'admin123' AND estado = 1;

SELECT id, usuario, nombre, rol, estado FROM usuarios WHERE usuario = 'biblioteca1' AND password_hash = 'biblio123' AND estado = 1;

-- 2. Libros con ejemplares
SELECT l.titulo, l.isbn, e.codigo_ejemplar, e.estado, s.nombre AS sede
FROM libros l
INNER JOIN ejemplares e ON l.id = e.libro_id
INNER JOIN sedes_biblioteca s ON e.sede_id = s.id
ORDER BY l.titulo;

-- 3. Prestamos activos
SELECT p.id, soc.nombre_completo AS socio, l.titulo AS libro,
       e.codigo_ejemplar, p.fecha_prestamo, p.fecha_devolucion_esperada
FROM prestamos p
INNER JOIN socios soc ON p.socio_id = soc.id
INNER JOIN ejemplares e ON p.ejemplar_id = e.id
INNER JOIN libros l ON e.libro_id = l.id
WHERE p.estado = 'Activo';

-- 4. Ejemplares disponibles
SELECT e.codigo_ejemplar, l.titulo, s.nombre AS sede
FROM ejemplares e
INNER JOIN libros l ON e.libro_id = l.id
INNER JOIN sedes_biblioteca s ON e.sede_id = s.id
WHERE e.estado = 'Disponible';

-- 5. Ejemplares prestados (quién los tiene)
SELECT e.codigo_ejemplar, l.titulo, soc.nombre_completo AS prestado_a
FROM ejemplares e
INNER JOIN libros l ON e.libro_id = l.id
INNER JOIN prestamos p ON e.id = p.ejemplar_id AND p.estado = 'Activo'
INNER JOIN socios soc ON p.socio_id = soc.id;

-- 6. Historial de prestamos de un socio
SELECT p.id, l.titulo, p.fecha_prestamo, p.fecha_devolucion_esperada,
       p.fecha_devolucion_real, p.estado
FROM prestamos p
INNER JOIN ejemplares e ON p.ejemplar_id = e.id
INNER JOIN libros l ON e.libro_id = l.id
WHERE p.socio_id = 1
ORDER BY p.fecha_prestamo DESC;

-- 7. Prestamos vencidos (no devueltos después de la fecha esperada)
SELECT p.id, soc.nombre_completo, l.titulo, p.fecha_devolucion_esperada
FROM prestamos p
INNER JOIN socios soc ON p.socio_id = soc.id
INNER JOIN ejemplares e ON p.ejemplar_id = e.id
INNER JOIN libros l ON e.libro_id = l.id
WHERE p.estado = 'Activo' AND p.fecha_devolucion_esperada < CURDATE();

-- 8. Contar por estado de ejemplares
SELECT estado, COUNT(*) AS cantidad FROM ejemplares GROUP BY estado;

-- 9. Contar prestamos por estado
SELECT estado, COUNT(*) AS cantidad FROM prestamos GROUP BY estado;

-- 10. Socios que más prestamos han hecho
SELECT soc.nombre_completo, COUNT(p.id) AS total_prestamos
FROM socios soc
LEFT JOIN prestamos p ON soc.id = p.socio_id
GROUP BY soc.id, soc.nombre_completo
ORDER BY total_prestamos DESC;
```

## Ejecutar en Clever Cloud desde consola

```bash
docker exec -i cotizaciones_mysql mysql -h HOST -P 3306 -u USER --password=PASSWORD \
  --ssl-mode=REQUIRED DB_NAME -e "SELECT 1"
```

## Ejecutar en local

```bash
docker exec -i cotizaciones_mysql mysql -uroot -proot123 biblioteca_db -e "SELECT 1"
```
