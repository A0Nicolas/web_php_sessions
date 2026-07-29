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

-- 11. Sedes activas
SELECT id, nombre, direccion, telefono, horario FROM sedes_biblioteca WHERE estado = 1;

-- 12. Ejemplares disponibles por sede
SELECT s.nombre AS sede, COUNT(e.id) AS disponibles
FROM sedes_biblioteca s
LEFT JOIN ejemplares e ON s.id = e.sede_id AND e.estado = 'Disponible'
WHERE s.estado = 1
GROUP BY s.id, s.nombre
ORDER BY disponibles DESC;

-- 13. Libros con al menos un ejemplar disponible
SELECT DISTINCT l.id, l.titulo, l.autor, l.isbn
FROM libros l
INNER JOIN ejemplares e ON l.id = e.libro_id
WHERE e.estado = 'Disponible'
ORDER BY l.titulo;

-- 14. Socios activos (con préstamos activos o sin deudas)
SELECT id, nombre_completo, cedula, telefono, correo
FROM socios
WHERE id NOT IN (
    SELECT socio_id FROM prestamos WHERE estado = 'Activo'
)
ORDER BY nombre_completo;

-- 15. Socios con préstamos activos actualmente
SELECT DISTINCT soc.id, soc.nombre_completo, soc.cedula, soc.telefono
FROM socios soc
INNER JOIN prestamos p ON soc.id = p.socio_id
WHERE p.estado = 'Activo'
ORDER BY soc.nombre_completo;

-- 16. Usuarios activos del sistema
SELECT id, usuario, nombre, rol FROM usuarios WHERE estado = 1 ORDER BY nombre;

-- 17. Usuarios inactivos (dados de baja)
SELECT id, usuario, nombre, rol FROM usuarios WHERE estado = 0 ORDER BY nombre;

-- 18. Ejemplares por sede (todos los estados)
SELECT s.nombre AS sede,
       SUM(CASE WHEN e.estado = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
       SUM(CASE WHEN e.estado = 'Prestado' THEN 1 ELSE 0 END) AS prestados,
       SUM(CASE WHEN e.estado = 'Danado' THEN 1 ELSE 0 END) AS danados,
       SUM(CASE WHEN e.estado = 'Sin disponibilidad' THEN 1 ELSE 0 END) AS sin_disponibilidad,
       COUNT(*) AS total
FROM sedes_biblioteca s
LEFT JOIN ejemplares e ON s.id = e.sede_id
GROUP BY s.id, s.nombre
ORDER BY s.nombre;

-- 19. Préstamos activos próximos a vencer (próximos 3 días)
SELECT p.id, soc.nombre_completo AS socio, l.titulo AS libro,
       p.fecha_devolucion_esperada,
       DATEDIFF(p.fecha_devolucion_esperada, CURDATE()) AS dias_restantes
FROM prestamos p
INNER JOIN socios soc ON p.socio_id = soc.id
INNER JOIN ejemplares e ON p.ejemplar_id = e.id
INNER JOIN libros l ON e.libro_id = l.id
WHERE p.estado = 'Activo'
  AND p.fecha_devolucion_esperada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
ORDER BY p.fecha_devolucion_esperada;

-- 20. Material más prestado (top 5)
SELECT l.titulo, l.autor, COUNT(p.id) AS veces_prestado
FROM libros l
INNER JOIN ejemplares e ON l.id = e.libro_id
INNER JOIN prestamos p ON e.id = p.ejemplar_id
GROUP BY l.id, l.titulo, l.autor
ORDER BY veces_prestado DESC
LIMIT 5;

-- 21. Préstamos por bibliotecario
SELECT u.nombre AS bibliotecario, COUNT(p.id) AS prestamos_gestionados
FROM usuarios u
LEFT JOIN prestamos p ON u.id = p.usuario_id
WHERE u.rol = 'bibliotecario'
GROUP BY u.id, u.nombre
ORDER BY prestamos_gestionados DESC;

-- 22. Sede con más ejemplares actualmente prestados
SELECT s.nombre AS sede, COUNT(e.id) AS prestados
FROM sedes_biblioteca s
INNER JOIN ejemplares e ON s.id = e.sede_id
WHERE e.estado = 'Prestado'
GROUP BY s.id, s.nombre
ORDER BY prestados DESC
LIMIT 1;

-- 23. Libros que nunca han sido prestados
SELECT l.titulo, l.autor, l.isbn
FROM libros l
WHERE l.id NOT IN (
    SELECT DISTINCT e.libro_id
    FROM prestamos p
    INNER JOIN ejemplares e ON p.ejemplar_id = e.id
)
ORDER BY l.titulo;

-- 24. Estado actual del sistema (resumen)
SELECT
    (SELECT COUNT(*) FROM sedes_biblioteca WHERE estado = 1) AS sedes_activas,
    (SELECT COUNT(*) FROM ejemplares WHERE estado = 'Disponible') AS ejemplares_disponibles,
    (SELECT COUNT(*) FROM prestamos WHERE estado = 'Activo') AS prestamos_activos,
    (SELECT COUNT(*) FROM socios) AS total_socios,
    (SELECT COUNT(*) FROM libros) AS total_libros;

## Ejecutar en Clever Cloud desde consola

```bash
docker exec -i cotizaciones_mysql mysql -h HOST -P 3306 -u USER --password=PASSWORD \
  --ssl-mode=REQUIRED DB_NAME -e "SELECT 1"
```

## Ejecutar en local

```bash
docker exec -i cotizaciones_mysql mysql -uroot -proot123 biblioteca_db -e "SELECT 1"
```
