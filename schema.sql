SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `biblioteca_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `biblioteca_db`;

-- --------------------------------------------------------
-- sedes_biblioteca
-- --------------------------------------------------------

CREATE TABLE `sedes_biblioteca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sedes_biblioteca` (`id`, `nombre`, `direccion`, `telefono`, `horario`, `estado`) VALUES
(1, 'Sede Central', 'Av. Amazonas N37-15 y Naciones Unidas, Quito', '022345678', 'Lun-Vie 8:00 - 18:00', 1),
(2, 'Sede Norte', 'Av. Eloy Alfaro y De los Granados, Quito', '022987654', 'Lun-Sab 9:00 - 17:00', 1);

-- --------------------------------------------------------
-- libros
-- --------------------------------------------------------

CREATE TABLE `libros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `autor` varchar(150) NOT NULL,
  `editorial` varchar(100) DEFAULT NULL,
  `anio_publicacion` int(4) DEFAULT NULL,
  `genero` varchar(50) DEFAULT NULL,
  `num_paginas` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `libros` (`id`, `isbn`, `titulo`, `autor`, `editorial`, `anio_publicacion`, `genero`, `num_paginas`) VALUES
(1, '978-84-376-0494-7', 'Cien Anos de Soledad', 'Gabriel Garcia Marquez', 'Editorial Sudamericana', 1967, 'Realismo Magico', 471),
(2, '978-0-06-112008-4', 'El Alquimista', 'Paulo Coelho', 'HarperCollins', 1988, 'Ficcion', 197),
(3, '978-84-204-6956-2', 'La Sombra del Viento', 'Carlos Ruiz Zafon', 'Planeta', 2001, 'Misterio', 487),
(4, '978-0-452-28423-4', '1984', 'George Orwell', 'Secker & Warburg', 1949, 'Ciencia Ficcion', 328),
(5, '978-0-7432-7356-5', 'Fahrenheit 451', 'Ray Bradbury', 'Ballantine Books', 1953, 'Ciencia Ficcion', 194),
(6, '978-84-206-5247-2', 'Rayuela', 'Julio Cortazar', 'Editorial Sudamericana', 1963, 'Ficcion', 736),
(7, '978-84-322-1189-8', 'Cronicas de una Muerte Anunciada', 'Gabriel Garcia Marquez', 'Bruguera', 1981, 'Realismo Magico', 122),
(8, '978-0-14-028333-4', 'El Senior de los Anillos', 'J.R.R. Tolkien', 'Allen & Unwin', 1954, 'Fantasia', 1178),
(9, '978-84-9793-641-7', 'Don Quijote de la Mancha', 'Miguel de Cervantes', 'Ediciones Catedra', 1605, 'Clasico', 863),
(10, '978-607-07-0365-2', 'Harry Potter y la Piedra Filosofal', 'J.K. Rowling', 'Salamandra', 1997, 'Fantasia', 309);

-- --------------------------------------------------------
-- ejemplares
-- --------------------------------------------------------

CREATE TABLE `ejemplares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libro_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `codigo_ejemplar` varchar(30) NOT NULL,
  `estado` enum('Disponible','Prestado','Danado','Sin disponibilidad') NOT NULL DEFAULT 'Disponible',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_ejemplar` (`codigo_ejemplar`),
  KEY `libro_id` (`libro_id`),
  KEY `sede_id` (`sede_id`),
  CONSTRAINT `ejemplares_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`),
  CONSTRAINT `ejemplares_ibfk_2` FOREIGN KEY (`sede_id`) REFERENCES `sedes_biblioteca` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ejemplares` (`id`, `libro_id`, `sede_id`, `codigo_ejemplar`, `estado`) VALUES
(1, 1, 1, 'EJ-001', 'Disponible'),
(2, 1, 1, 'EJ-002', 'Prestado'),
(3, 2, 1, 'EJ-003', 'Disponible'),
(4, 3, 1, 'EJ-004', 'Disponible'),
(5, 4, 1, 'EJ-005', 'Danado'),
(6, 5, 2, 'EJ-006', 'Disponible'),
(7, 6, 2, 'EJ-007', 'Disponible'),
(8, 7, 2, 'EJ-008', 'Prestado'),
(9, 8, 1, 'EJ-009', 'Disponible'),
(10, 8, 2, 'EJ-010', 'Disponible'),
(11, 9, 1, 'EJ-011', 'Disponible'),
(12, 9, 2, 'EJ-012', 'Disponible'),
(13, 10, 1, 'EJ-013', 'Disponible'),
(14, 10, 2, 'EJ-014', 'Prestado'),
(15, 3, 2, 'EJ-015', 'Disponible');

-- --------------------------------------------------------
-- socios
-- --------------------------------------------------------

CREATE TABLE `socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(10) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `socios` (`id`, `cedula`, `nombre_completo`, `correo`, `telefono`, `direccion`) VALUES
(1, '1723456789', 'Ana Maria Torres', 'ana.torres@email.com', '0991234567', 'Av. 6 de Diciembre y Colon, Quito'),
(2, '1712345678', 'Carlos Andres Lopez', 'carlos.lopez@email.com', '0987654321', 'Calle La Nina y Av. Americas, Quito'),
(3, '1756789012', 'Maria Jose Garcia', 'mj.garcia@email.com', '0976543210', 'Av. Eloy Alfaro N45-30, Quito'),
(4, '1789012345', 'Pedro Luis Martinez', 'pedro.martinez@email.com', '0965432109', 'Calle Bolivia y Av. Amazonas, Quito'),
(5, '1734567890', 'Laura Elena Rodriguez', 'laura.rodriguez@email.com', '0954321098', 'Av. De los Shyris N34-40, Quito');

-- --------------------------------------------------------
-- usuarios
-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `rol` enum('administrador','bibliotecario') DEFAULT 'bibliotecario',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id`, `usuario`, `password_hash`, `nombre`, `rol`, `estado`) VALUES
(1, 'admin', 'admin123', 'Administrador General', 'administrador', 1),
(2, 'biblioteca1', 'biblio123', 'Maria Paredes', 'bibliotecario', 1),
(3, 'biblioteca2', 'biblio123', 'Juan Espinoza', 'bibliotecario', 1);

-- --------------------------------------------------------
-- prestamos
-- --------------------------------------------------------

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `socio_id` int(11) NOT NULL,
  `ejemplar_id` int(11) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion_esperada` date NOT NULL,
  `fecha_devolucion_real` date DEFAULT NULL,
  `estado` enum('Activo','Devuelto','Vencido') NOT NULL DEFAULT 'Activo',
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `socio_id` (`socio_id`),
  KEY `ejemplar_id` (`ejemplar_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`),
  CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`ejemplar_id`) REFERENCES `ejemplares` (`id`),
  CONSTRAINT `prestamos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prestamos` (`id`, `socio_id`, `ejemplar_id`, `fecha_prestamo`, `fecha_devolucion_esperada`, `fecha_devolucion_real`, `estado`, `usuario_id`) VALUES
(1, 1, 2, '2026-07-01', '2026-07-15', NULL, 'Activo', 1),
(2, 2, 8, '2026-07-05', '2026-07-19', NULL, 'Activo', 2),
(3, 3, 14, '2026-07-10', '2026-07-24', NULL, 'Activo', 2),
(4, 1, 5, '2026-06-15', '2026-06-29', '2026-06-28', 'Devuelto', 1),
(5, 4, 3, '2026-06-20', '2026-07-04', '2026-07-02', 'Devuelto', 1);

COMMIT;
