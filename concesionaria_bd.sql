-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-07-2021 a las 02:20:52
-- Versión del servidor: 10.4.18-MariaDB
-- Versión de PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `concesionaria_bd`
--
CREATE DATABASE IF NOT EXISTS `concesionaria_bd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `concesionaria_bd`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autos`
--

CREATE TABLE `autos` (
  `id` int(10) UNSIGNED NOT NULL,
  `color` varchar(100) CHARACTER SET utf8 NOT NULL,
  `marca` varchar(100) CHARACTER SET utf8 NOT NULL,
  `precio` int(30) NOT NULL,
  `modelo` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `autos`
--

INSERT INTO `autos` (`id`, `color`, `marca`, `precio`, `modelo`) VALUES
(4, 'verde', 'fiat', 55000, 2000),
(5, 'gris', 'ford', 50000, 2015),
(6, 'gris', 'ford', 50000, 2015),
(7, 'rojo', 'ford', 55000, 2015),
(8, 'rojo', 'ford', 55000, 2015),
(9, 'azul', 'fiat', 1234, 1995),
(10, 'azul', 'fiat', 1234, 1995),
(11, 'rojo', 'ford', 55000, 2015);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `correo` varchar(100) CHARACTER SET utf8 NOT NULL,
  `clave` varchar(100) CHARACTER SET utf8 NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,
  `apellido` varchar(50) CHARACTER SET utf8 NOT NULL,
  `perfil` varchar(50) CHARACTER SET utf8 NOT NULL,
  `foto` varchar(100) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `clave`, `nombre`, `apellido`, `perfil`, `foto`) VALUES
(2, 'correo@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.60601.png'),
(3, 'correo2@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.210914.png'),
(4, 'correo23@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.212734.png'),
(5, 'correo4@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.212936.png'),
(6, 'correo5@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.212956.png'),
(7, 'correo55@correo.com', '12345', 'test', 'test', 'propietario', '../fotos/nahuel.cisa.213720.png'),
(8, 'correo555@correo.com', '12345', 'test', 'test', 'encargado', '../fotos/nahuel.cisa.221953.png'),
(9, 'correo5555@correo.com', '12345', 'test', 'test', 'empleado', '../fotos/nahuel.cisa.225423.png'),
(10, 'test@gmail.com', '12345', 'test1', 'test2', 'empleado', '../fotos/test1.test2.12326.jpg'),
(11, 'test@gmail.com', '12345', 'test21', 'test22', 'encargado', '../fotos/test21.test22.13318.jpg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autos`
--
ALTER TABLE `autos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autos`
--
ALTER TABLE `autos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
