-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-08-2025 a las 01:39:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mamalilabase_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `Id` int(11) NOT NULL,
  `Codigo` varchar(40) DEFAULT NULL,
  `Tipo` enum('monto','porc') NOT NULL,
  `Valor` decimal(10,2) NOT NULL,
  `Negocio_id` int(11) DEFAULT NULL,
  `Vigente_desde` date DEFAULT NULL,
  `Vigente_hasta` date DEFAULT NULL,
  `Usos_max` int(11) DEFAULT NULL,
  `Usos_actuales` int(11) NOT NULL DEFAULT 0,
  `Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cupones`
--

INSERT INTO `cupones` (`Id`, `Codigo`, `Tipo`, `Valor`, `Negocio_id`, `Vigente_desde`, `Vigente_hasta`, `Usos_max`, `Usos_actuales`, `Activo`) VALUES
(1, 'GLOBAL10', 'porc', 10.00, NULL, '2025-08-18', '2025-11-16', 1000, 3, 1),
(2, 'BIENVENIDO500', 'monto', 500.00, NULL, '2025-08-18', '2025-11-16', 500, 0, 1),
(3, 'TAQUI15', 'porc', 15.00, 3, '2025-08-18', '2025-11-16', 300, 0, 1),
(4, 'ENVIO300', 'monto', 300.00, NULL, '2025-08-18', '2025-11-16', 500, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `Usuario_id` int(11) NOT NULL,
  `Negocio_id` int(11) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios`
--

CREATE TABLE `negocios` (
  `Id_negocio` int(11) NOT NULL,
  `Usuario_id` int(11) NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `Email` varchar(160) DEFAULT NULL,
  `Horario` varchar(120) DEFAULT NULL,
  `Costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Tiempo_minutos` int(11) NOT NULL DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios`
--

INSERT INTO `negocios` (`Id_negocio`, `Usuario_id`, `Nombre`, `Direccion`, `Telefono`, `Email`, `Horario`, `Costo_envio`, `Tiempo_minutos`) VALUES
(3, 5, 'TaqueriaMexicana', 'Cartago', '84423000', 'taqueriamexicana2025@gmail.com', 'L-D 10am a 10pm', 0.00, 20),
(4, 6, 'Pizzería La Nona', 'Av. Central 123', '2222-0001', 'nona@mamalila.com', 'L-D 11:00-22:00', 0.00, 20),
(5, 7, 'Sushi Raíces', 'Calle 4, Plaza Este', '2222-0002', 'raices@sushi.com', 'L-D 12:00-22:00', 0.00, 20),
(6, 8, 'Café Bosque', 'Boulevard Verde 55', '2222-0003', 'bosque@cafe.com', 'L-D 07:00-20:00', 0.00, 20),
(7, 9, 'Burger Criollo', 'Av. Oeste 9', '2222-0004', 'criollo@burger.com', 'L-D 11:30-23:00', 0.00, 20),
(8, 10, 'Vegan Vida', 'Plaza Norte Local 7', '2222-0005', 'vegan@vida.com', 'L-D 11:00-21:00', 0.00, 20),
(9, 11, 'Parrilla Andina', 'Av. Sur 101', '2233-0001', 'andina@parrilla.com', 'M-D 12:00-23:00', 0.00, 20),
(10, 12, 'El Puerto Marisquería', 'Costanera 8', '2233-0002', 'puerto@mariscos.com', 'L-D 11:00-22:00', 0.00, 20),
(11, 13, 'Wok Dragón Dorado', 'Barrio Oriental 12', '2233-0003', 'dragon@wok.com', 'L-D 11:30-21:30', 0.00, 20),
(12, 14, 'Arepería Caracas', 'Plaza Centro Local 3', '2233-0004', 'caracas@arepas.com', 'L-D 09:00-21:00', 0.00, 20),
(13, 15, 'La Casa del Postre', 'Paseo del Sol 44', '2233-0005', 'dulce@postres.com', 'M-D 10:00-21:30', 0.00, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `Id_pedido` int(11) NOT NULL,
  `Cliente_id` int(11) NOT NULL,
  `Negocio_id` int(11) NOT NULL,
  `Fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Pendiente','En preparación','Listo') NOT NULL DEFAULT 'Pendiente',
  `Direccion_Entrega` varchar(255) DEFAULT NULL,
  `Metodo_Pago` varchar(50) DEFAULT 'Simulado',
  `Total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Tipo_entrega` varchar(20) NOT NULL DEFAULT 'Domicilio',
  `Cupon_codigo` varchar(40) DEFAULT NULL,
  `Descuento` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`Id_pedido`, `Cliente_id`, `Negocio_id`, `Fecha`, `Estado`, `Direccion_Entrega`, `Metodo_Pago`, `Total`, `Costo_envio`, `Tipo_entrega`, `Cupon_codigo`, `Descuento`) VALUES
(1, 4, 12, '2025-08-19 13:18:30', 'Listo', '300 metros norte de la farmacia fischel', 'Simulado', 6800.00, 0.00, 'Domicilio', NULL, 0.00),
(2, 4, 3, '2025-08-19 13:29:38', 'En preparación', '300 norte de la farmacia fischel', 'Simulado', 12000.00, 0.00, 'Domicilio', NULL, 0.00),
(3, 4, 12, '2025-08-19 14:23:54', 'Pendiente', '150 metros sur de la escuela', 'Simulado', 9180.00, 0.00, 'Domicilio', 'GLOBAL10', 1020.00),
(4, 4, 7, '2025-08-19 14:24:18', 'Pendiente', '150 metros sur de la escuela de paso ancho', 'Simulado', 7740.00, 0.00, 'Domicilio', 'GLOBAL10', 860.00),
(5, 4, 6, '2025-08-19 14:26:12', 'Pendiente', 'cartago,cartago', 'Simulado', 6750.00, 0.00, 'Domicilio', 'GLOBAL10', 750.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `Id_detalle` int(11) NOT NULL,
  `Pedido_id` int(11) NOT NULL,
  `Platillo_id` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`Id_detalle`, `Pedido_id`, `Platillo_id`, `Cantidad`, `Subtotal`) VALUES
(1, 1, 146, 2, 6800.00),
(2, 2, 83, 4, 12000.00),
(3, 3, 146, 1, 3400.00),
(4, 3, 144, 1, 3200.00),
(5, 3, 143, 1, 3600.00),
(6, 4, 62, 1, 1900.00),
(7, 4, 57, 1, 5200.00),
(8, 4, 67, 1, 1500.00),
(9, 5, 40, 2, 3000.00),
(10, 5, 52, 3, 4500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `platillos`
--

CREATE TABLE `platillos` (
  `Id_platillo` int(11) NOT NULL,
  `Negocio_id` int(11) NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Imagen` varchar(255) DEFAULT NULL,
  `Disponible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `platillos`
--

INSERT INTO `platillos` (`Id_platillo`, `Negocio_id`, `Nombre`, `Descripcion`, `Precio`, `Imagen`, `Disponible`) VALUES
(7, 3, 'Tacos al pastor', 'Deliciosos tacos de pastor', 7500.00, 'pl_68a401d565f325.00928047.jpg', 1),
(8, 4, 'Pizza Margherita', 'Tomate, mozzarella y albahaca', 5200.00, 'pl_auto_68a405af919548.42854105.svg', 1),
(9, 4, 'Pepperoni', 'Pepperoni y queso', 5600.00, 'pl_auto_68a405af938128.33109467.svg', 1),
(10, 4, 'Cuatro Quesos', 'Mozzarella, gorgonzola, parmesano y provolone', 6200.00, 'pl_auto_68a405af94cf33.97265212.svg', 1),
(11, 4, 'Hawaiana', 'Jamón y piña', 5600.00, 'pl_auto_68a405af96d619.77495521.svg', 1),
(12, 4, 'Vegetariana', 'Verduras de temporada', 5400.00, 'pl_auto_68a405af980f87.62158313.svg', 1),
(13, 4, 'Napolitana', 'Anchoas y aceitunas', 5900.00, 'pl_auto_68a405af9950d0.71749332.svg', 1),
(14, 4, 'Prosciutto', 'Jamón curado y rúcula', 6500.00, 'pl_auto_68a405af9b6386.44520805.svg', 1),
(15, 4, 'BBQ Chicken', 'Pollo y salsa BBQ', 6400.00, 'pl_auto_68a405af9cac28.21940830.svg', 1),
(16, 4, 'Mexicana', 'Jalapeño y chorizo', 6300.00, 'pl_auto_68a405af9dc142.29000286.svg', 1),
(17, 4, 'Diavola', 'Salami picante', 6200.00, 'pl_auto_68a405af9f7fc3.46078173.svg', 1),
(18, 4, 'Marinara', 'Tomate, ajo, orégano', 4800.00, 'pl_auto_68a405afa10927.87697410.svg', 1),
(19, 4, 'Caprese', 'Tomate, mozzarella, pesto', 6000.00, 'pl_auto_68a405afa23b97.12549116.svg', 1),
(20, 4, 'Calzone', 'Relleno de jamón y queso', 6100.00, 'pl_auto_68a405afa3be66.33360046.svg', 1),
(21, 4, 'Lasaña', 'Clásica de carne', 6800.00, 'pl_auto_68a405afa4dd12.82628633.svg', 1),
(22, 4, 'Focaccia', 'Romero y aceite de oliva', 3500.00, 'pl_auto_68a405afa61416.04235368.svg', 1),
(23, 5, 'California Roll', 'Cangrejo, aguacate y pepino', 4500.00, 'pl_auto_68a405afa73f66.75157181.svg', 1),
(24, 5, 'Spicy Tuna Roll', 'Atún picante', 5200.00, 'pl_auto_68a405afa864e4.67539621.svg', 1),
(25, 5, 'Salmon Nigiri', 'Nigiri de salmón', 3800.00, 'pl_auto_68a405afa995b0.26302364.svg', 1),
(26, 5, 'Ebi Nigiri', 'Nigiri de camarón', 3600.00, 'pl_auto_68a405afaa8d24.98621035.svg', 1),
(27, 5, 'Dragon Roll', 'Anguila y aguacate', 6500.00, 'pl_auto_68a405afaba861.15351697.svg', 1),
(28, 5, 'Rainbow Roll', 'Variedad de pescados', 6700.00, 'pl_auto_68a405afad7929.87045566.svg', 1),
(29, 5, 'Philadelphia Roll', 'Salmón y queso crema', 5400.00, 'pl_auto_68a405afaebc66.22520710.svg', 1),
(30, 5, 'Gyozas', 'Empanaditas al vapor', 3200.00, 'pl_auto_68a405afaffec2.91645323.svg', 1),
(31, 5, 'Ramen Shoyu', 'Caldo soya y fideos', 6200.00, 'pl_auto_68a405afb0fed7.18584784.svg', 1),
(32, 5, 'Ramen Miso', 'Caldo miso', 6200.00, 'pl_auto_68a405afb21a40.93035089.svg', 1),
(33, 5, 'Yakimeshi', 'Arroz frito', 4500.00, 'pl_auto_68a405afb327b7.52562121.svg', 1),
(34, 5, 'Teriyaki Pollo', 'Pollo a la plancha teriyaki', 5800.00, 'pl_auto_68a405afb3f1a6.28879609.svg', 1),
(35, 5, 'Teriyaki Salmón', 'Salmón teriyaki', 6900.00, 'pl_auto_68a405afb51818.96238946.svg', 1),
(36, 5, 'Sashimi Mix', 'Cortes variados', 7200.00, 'pl_auto_68a405afb62629.16096481.svg', 1),
(37, 5, 'Edamame', 'Soja al vapor', 2500.00, 'pl_auto_68a405afb73841.38210912.svg', 1),
(38, 6, 'Latte', 'Doble espresso con leche', 1800.00, 'pl_auto_68a405afb84b88.64710477.svg', 1),
(39, 6, 'Cappuccino', 'Espresso, leche y espuma', 1800.00, 'pl_auto_68a405afb95b38.92334871.svg', 1),
(40, 6, 'Americano', 'Espresso extendido', 1500.00, 'pl_auto_68a405afba38f2.65578318.svg', 1),
(41, 6, 'Mocha', 'Chocolate y espresso', 2000.00, 'pl_auto_68a405afbb3446.12491648.svg', 1),
(42, 6, 'Cold Brew', 'Café frío macerado', 2200.00, 'pl_auto_68a405afbc4663.88057796.svg', 1),
(43, 6, 'Tostadas Francesas', 'Pan brioche con miel', 3500.00, 'pl_auto_68a405afbd55b9.06430564.svg', 1),
(44, 6, 'Pancakes', 'Con frutas de temporada', 3200.00, 'pl_auto_68a405afbe3567.79893950.svg', 1),
(45, 6, 'Croissant', 'Mantequilla', 1400.00, 'pl_auto_68a405afbf7c43.99558883.svg', 1),
(46, 6, 'Sandwich de Pavo', 'Pavo, queso y lechuga', 3500.00, 'pl_auto_68a405afc0aea6.89669205.svg', 1),
(47, 6, 'Bowl de Fruta', 'Fruta fresca y granola', 2800.00, 'pl_auto_68a405afc196e9.49339851.svg', 1),
(48, 6, 'Yogurt Parfait', 'Yogurt, granola y miel', 2600.00, 'pl_auto_68a405afc29860.85434339.svg', 1),
(49, 6, 'Avocado Toast', 'Aguacate y tomate', 3000.00, 'pl_auto_68a405afc3acb7.24218471.svg', 1),
(50, 6, 'Quiche', 'Verduras y queso', 3400.00, 'pl_auto_68a405afc4c4c7.30471455.svg', 1),
(51, 6, 'Ensalada César', 'Clásica con crutones', 3300.00, 'pl_auto_68a405afc5ee63.73276683.svg', 1),
(52, 6, 'Brownie', 'Chocolate', 1500.00, 'pl_auto_68a405afc6fe92.39261331.svg', 1),
(53, 7, 'Hamburguesa Clásica', 'Carne 120g, lechuga y tomate', 4200.00, 'pl_auto_68a405afc846c6.42731199.svg', 1),
(54, 7, 'Quesoburguesa', 'Clásica con queso', 4500.00, 'pl_auto_68a405afc93246.63330311.svg', 1),
(55, 7, 'Doble', 'Doble carne y queso', 5800.00, 'pl_auto_68a405afca42a0.66799405.svg', 1),
(56, 7, 'Criolla', 'Huevo, tocineta y queso', 5400.00, 'pl_auto_68a405afcb1967.00535155.svg', 1),
(57, 7, 'BBQ', 'Salsa BBQ y cebolla', 5200.00, 'pl_auto_68a405afcc2901.61933823.svg', 1),
(58, 7, 'Picante', 'Jalapeño y salsa picante', 5200.00, 'pl_auto_68a405afcf5381.47313244.svg', 1),
(59, 7, 'Pollo Crispy', 'Pechuga empanizada', 5100.00, 'pl_auto_68a405afd1a591.42902053.svg', 1),
(60, 7, 'Veggie Burger', 'Hamburguesa vegetal', 4900.00, 'pl_auto_68a405afd3cda5.91333364.svg', 1),
(61, 7, 'Papas Fritas', 'Porción mediana', 1800.00, 'pl_auto_68a405afd4f3e3.82326142.svg', 1),
(62, 7, 'Aros de Cebolla', 'Crujientes', 1900.00, 'pl_auto_68a405afd5e2a7.88462245.svg', 1),
(63, 7, 'Nuggets', '8 unidades', 2200.00, 'pl_auto_68a405afd71bf8.62852431.svg', 1),
(64, 7, 'Malteada Vainilla', 'Helado y leche', 2500.00, 'pl_auto_68a405afd83813.37641380.svg', 1),
(65, 7, 'Malteada Chocolate', 'Helado y leche', 2500.00, 'pl_auto_68a405afd91310.82685316.svg', 1),
(66, 7, 'Hot Dog', 'Clásico', 2600.00, 'pl_auto_68a405afda0444.10492479.svg', 1),
(67, 7, 'Ensalada de Col', 'Col y zanahoria', 1500.00, 'pl_auto_68a405afdb1420.30408338.svg', 1),
(68, 8, 'Bowl de Quinoa', 'Quinoa, verduras y aderezo', 5200.00, 'pl_auto_68a405afdc2de9.56769440.svg', 1),
(69, 8, 'Tofu Teriyaki', 'Tofu y vegetales', 5400.00, 'pl_auto_68a405afdd15b4.00360276.svg', 1),
(70, 8, 'Ensalada Mediterránea', 'Tomate, pepino y aceitunas', 4800.00, 'pl_auto_68a405afde2b68.25409853.svg', 1),
(71, 8, 'Falafel Wrap', 'Falafel y hummus', 4500.00, 'pl_auto_68a405afdf3b26.11696149.svg', 1),
(72, 8, 'Hummus & Pita', 'Clásico', 3000.00, 'pl_auto_68a405afe0f0e9.70980463.svg', 1),
(73, 8, 'Curry de Garbanzos', 'Con arroz', 5200.00, 'pl_auto_68a405afe24526.11972973.svg', 1),
(74, 8, 'Buddha Bowl', 'Granos y vegetales', 5300.00, 'pl_auto_68a405afe34405.65365089.svg', 1),
(75, 8, 'Pasta Pesto Vegano', 'Albahaca y nueces', 5600.00, 'pl_auto_68a405afe46cf3.28949463.svg', 1),
(76, 8, 'Sopa de Lentejas', 'Casera', 4200.00, 'pl_auto_68a405afe54090.81916360.svg', 1),
(77, 8, 'Arepas Veganas', 'Rellenas de vegetales', 4600.00, 'pl_auto_68a405afe65b26.76815746.svg', 1),
(78, 8, 'Burrito Vegano', 'Frijol negro y arroz', 4800.00, 'pl_auto_68a405afe79b86.53998155.svg', 1),
(79, 8, 'Nachos Veganos', 'Queso vegano', 4400.00, 'pl_auto_68a405afe8f766.77809204.svg', 1),
(80, 8, 'Smoothie Verde', 'Espinaca y piña', 2800.00, 'pl_auto_68a405afea4339.78515253.svg', 1),
(81, 8, 'Açaí Bowl', 'Açaí con frutas', 3200.00, 'pl_auto_68a405afeb5ac9.57057871.svg', 1),
(82, 8, 'Brownie Vegano', 'Chocolate', 1800.00, 'pl_auto_68a405afec2f23.55740345.svg', 1),
(83, 3, 'Tacos de Asada', 'Res a la parrilla', 3000.00, 'pl_68a40b99131ab9.27681966.jpg', 1),
(84, 3, 'Tacos de Pollo', 'Pollo marinado', 2700.00, 'pl_auto_68a405af7bc7c2.95021636.svg', 1),
(85, 3, 'Tacos de Carnitas', 'Cerdo confitado', 2900.00, 'pl_auto_68a405af7cf6f0.18225911.svg', 1),
(86, 3, 'Tacos de Barbacoa', 'Res al vapor', 3100.00, 'pl_auto_68a405af7e7e05.89757670.svg', 1),
(87, 3, 'Tacos de Camarón', 'Camarón y chipotle', 3400.00, 'pl_auto_68a405af817f13.48948877.svg', 1),
(88, 3, 'Quesadilla Sencilla', 'Queso fundido', 2600.00, 'pl_auto_68a405af827317.08265742.svg', 1),
(89, 3, 'Quesadilla Campechana', 'Mixta', 2900.00, 'pl_auto_68a405af83e607.80810047.svg', 1),
(90, 3, 'Gringas', 'Pastor con queso', 3200.00, 'pl_auto_68a405af84e038.89142491.svg', 1),
(91, 3, 'Burrito de Asada', 'Grande', 3600.00, 'pl_auto_68a405af8636f0.51091059.svg', 1),
(92, 3, 'Burrito al Pastor', 'Grande', 3400.00, 'pl_auto_68a405af87e5f9.70302069.svg', 1),
(93, 3, 'Nachos con Queso', 'Totopos y queso', 2600.00, 'pl_auto_68a405af8b12d5.68952241.svg', 1),
(94, 3, 'Elote Preparado', 'Mayonesa, queso y chile', 2200.00, 'pl_68a40b68d34801.17279245.jpg', 1),
(95, 3, 'Sopes', 'Fritos con frijol y carne', 2800.00, 'pl_auto_68a405af8d46a9.32413625.svg', 1),
(96, 3, 'Tostadas', 'Crujientes con guiso', 2500.00, 'pl_auto_68a405af8f2bf8.35705811.svg', 1),
(97, 9, 'Bife de chorizo', 'Corte 300g a la parrilla', 7200.00, NULL, 1),
(98, 9, 'Entraña', 'Corte angosto a punto', 7600.00, NULL, 1),
(99, 9, 'Asado de tira', 'Costilla a la brasa', 6900.00, NULL, 1),
(100, 9, 'Costillas BBQ', 'Salsa casera', 6500.00, NULL, 1),
(101, 9, 'Pollo a la parrilla', 'Muslo y encuentro', 5600.00, NULL, 1),
(102, 9, 'Chorizo parrillero', 'Con chimichurri', 2500.00, NULL, 1),
(103, 9, 'Morcilla', 'Parrillera', 2400.00, NULL, 1),
(104, 9, 'Provoleta', 'Queso a la plancha', 3900.00, NULL, 1),
(105, 9, 'Papas rústicas', 'Con romero', 2200.00, NULL, 1),
(106, 9, 'Ensalada mixta', 'Tomate, lechuga y cebolla', 2000.00, NULL, 1),
(107, 9, 'Choripán', 'Chorizo en pan', 3200.00, NULL, 1),
(108, 9, 'Empanadas criollas', 'Carne y pollo', 2600.00, NULL, 1),
(109, 9, 'Lomito al plato', 'Con guarnición', 6400.00, NULL, 1),
(110, 9, 'Milanesa napolitana', 'Salsa y queso', 6100.00, NULL, 1),
(111, 9, 'Chimichurri casero', 'Frasco de 120ml', 1200.00, NULL, 1),
(112, 10, 'Ceviche mixto', 'Pescado y camarón', 5800.00, NULL, 1),
(113, 10, 'Camarones al ajillo', 'Sartén con mantequilla', 7200.00, NULL, 1),
(114, 10, 'Pescado frito', 'Entero crujiente', 6900.00, NULL, 1),
(115, 10, 'Arroz con mariscos', 'Calamar, camarón y mejillón', 7000.00, NULL, 1),
(116, 10, 'Sopa de mariscos', 'Caldo concentrado', 6400.00, NULL, 1),
(117, 10, 'Filete de tilapia', 'Plancha con limón', 5500.00, NULL, 1),
(118, 10, 'Pulpo a la gallega', 'Pimentón y papa', 8200.00, NULL, 1),
(119, 10, 'Calamares fritos', 'Aros empanizados', 5200.00, NULL, 1),
(120, 10, 'Coctel de camarón', 'Salsa de tomate', 4900.00, NULL, 1),
(121, 10, 'Paella marinera', 'Para una persona', 7800.00, NULL, 1),
(122, 10, 'Tacos de pescado', 'Col y aderezo', 4300.00, NULL, 1),
(123, 10, 'Empanadas de camarón', 'Unidad', 2100.00, NULL, 1),
(124, 10, 'Chicharrón de calamar', 'Porción', 5400.00, NULL, 1),
(125, 10, 'Corvina en salsa', 'Mantequilla y alcaparras', 7200.00, NULL, 1),
(126, 10, 'Ensalada de mar', 'Fresca y cítrica', 5200.00, NULL, 1),
(127, 11, 'Arroz cantonés', 'Con cerdo, pollo y vegetales', 5200.00, NULL, 1),
(128, 11, 'Chow mein de pollo', 'Fideos salteados', 5200.00, NULL, 1),
(129, 11, 'Chow mein de res', 'Fideos salteados', 5400.00, NULL, 1),
(130, 11, 'Pollo agridulce', 'Clásico con piña', 5600.00, NULL, 1),
(131, 11, 'Cerdo a la naranja', 'Crocante', 5600.00, NULL, 1),
(132, 11, 'Res con brócoli', 'Salsa de ostión', 5900.00, NULL, 1),
(133, 11, 'Pollo kung pao', 'Cacahuate y chile', 5800.00, NULL, 1),
(134, 11, 'Rollitos primavera', '2 unidades', 2000.00, NULL, 1),
(135, 11, 'Sopa wonton', 'Ligera', 2600.00, NULL, 1),
(136, 11, 'Dumplings al vapor', '6 unidades', 3600.00, NULL, 1),
(137, 11, 'Camarones con vegetales', 'Salteado', 6400.00, NULL, 1),
(138, 11, 'Tofu con vegetales', 'Salteado', 5200.00, NULL, 1),
(139, 11, 'Chop suey mixto', 'Verduras y carnes', 5600.00, NULL, 1),
(140, 11, 'Arroz jazmín', 'Acompañamiento', 1500.00, NULL, 1),
(141, 11, 'Fideos salteados', 'Acompañamiento', 2000.00, NULL, 1),
(142, 12, 'Arepa reina pepiada', 'Pollo y aguacate', 3500.00, NULL, 1),
(143, 12, 'Arepa pelúa', 'Carne mechada y queso', 3600.00, NULL, 1),
(144, 12, 'Arepa dominó', 'Frijol negro y queso', 3200.00, NULL, 1),
(145, 12, 'Arepa perico', 'Huevo con vegetales', 3000.00, NULL, 1),
(146, 12, 'Arepa catira', 'Pollo y queso amarillo', 3400.00, NULL, 1),
(147, 12, 'Arepa llanera', 'Carne, aguacate y tomate', 3700.00, NULL, 1),
(148, 12, 'Arepa pabellón', 'Carne, frijol y plátano', 3900.00, NULL, 1),
(149, 12, 'Tequeños', '5 unidades', 3200.00, NULL, 1),
(150, 12, 'Cachapa con queso', 'Dulce y salada', 4100.00, NULL, 1),
(151, 12, 'Empanada de carne', 'Unidad', 2200.00, NULL, 1),
(152, 12, 'Empanada de queso', 'Unidad', 2100.00, NULL, 1),
(153, 12, 'Patacón', 'Doble', 3800.00, NULL, 1),
(154, 12, 'Mandocas', 'Dulces de maíz', 2400.00, NULL, 1),
(155, 12, 'Jugos naturales', 'Vaso 16oz', 1800.00, NULL, 1),
(156, 12, 'Papelón con limón', 'Bebida tradicional', 1500.00, NULL, 1),
(157, 13, 'Cheesecake de fresa', 'Porción', 3200.00, NULL, 1),
(158, 13, 'Tres leches', 'Porción clásica', 3000.00, NULL, 1),
(159, 13, 'Tiramisú', 'Porción', 3300.00, NULL, 1),
(160, 13, 'Brownie con helado', 'Caliente + bola', 3200.00, NULL, 1),
(161, 13, 'Flan napolitano', 'Porción', 2800.00, NULL, 1),
(162, 13, 'Pie de limón', 'Porción', 2900.00, NULL, 1),
(163, 13, 'Chocotorta', 'Porción', 3100.00, NULL, 1),
(164, 13, 'Helado artesanal', '2 bolas', 2500.00, NULL, 1),
(165, 13, 'Crepas de Nutella', 'Con frutas', 3200.00, NULL, 1),
(166, 13, 'Galletas artesanales', '3 unidades', 1800.00, NULL, 1),
(167, 13, 'Pan de banano', 'Rebanada', 1600.00, NULL, 1),
(168, 13, 'Alfajores', '2 unidades', 1700.00, NULL, 1),
(169, 13, 'Mousse de chocolate', 'Cremoso', 2900.00, NULL, 1),
(170, 13, 'Cupcakes surtidos', 'Unidad', 1500.00, NULL, 1),
(171, 13, 'Frappé de café', 'Bebida fría', 2500.00, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `Id_usuario` int(11) NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Email` varchar(160) NOT NULL,
  `Contrasenia` varchar(255) NOT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `Rol` enum('cliente','negocio','admin') NOT NULL DEFAULT 'cliente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`Id_usuario`, `Nombre`, `Email`, `Contrasenia`, `Direccion`, `Telefono`, `Rol`, `fecha_creacion`) VALUES
(4, 'Amanda', 'amandavm25@gmail.com', '$2y$10$6eFMefa2LWAVAO8WlKI2iOPQ9c2DXHgPriliHW4SJclTiJzYnFd8i', '300 Metros Norte De La Farmacia Fischel Cartago Los Ángeles', '86524305', 'cliente', '2025-08-19 03:41:50'),
(5, 'TaqueriaMexicana', 'taqueriamexicana2025@gmail.com', '$2y$10$CNrN0.jyurYsb5PHAe5s5ec94DTLtIt4ezyJzIksi8hK3q.FMb6pq', '', '84423000', 'negocio', '2025-08-19 03:42:38'),
(6, 'La Nona Admin', 'admin-nona@mamalila.com', '$2y$10$CV8W01UD8tHTMTCJQxg9p.hS/REP5Adqstbj8g1WQgIN.h.OnJMBu', 'Av. Central 123', '2222-0001', 'negocio', '2025-08-19 04:55:16'),
(7, 'Sushi Admin', 'admin-raices@sushi.com', '$2y$10$NQq8HW1rJ7Z4qt9cS6Ooiun36hVAfd4zf6UIYGPwx//Ci3UI2lzvW', 'Calle 4, Plaza Este', '2222-0002', 'negocio', '2025-08-19 04:55:17'),
(8, 'Bosque Admin', 'admin-bosque@cafe.com', '$2y$10$RHIdLZjla./qAkOvTCy/x./s.4Z3UlC87g4sqqFvTOmx3cbEbglX.', 'Boulevard Verde 55', '2222-0003', 'negocio', '2025-08-19 04:55:17'),
(9, 'Criollo Admin', 'admin-criollo@burger.com', '$2y$10$hp1OkC9r8.8Q38P2zTaj5OxaX12AX1ZEyeeCzO4Owz5pRukXC./vy', 'Av. Oeste 9', '2222-0004', 'negocio', '2025-08-19 04:55:17'),
(10, 'Vegan Admin', 'admin-vegan@vida.com', '$2y$10$MR4S8hi1NH.KebXCgXjLgujQhWFJ.HYvRLagFMGrF3BreDwLXTyhi', 'Plaza Norte Local 7', '2222-0005', 'negocio', '2025-08-19 04:55:17'),
(11, 'Andina Admin', 'admin-andina@parrilla.com', '$2y$10$.gkHHTVoOTSJrBomO8a99.jlC3qTNO4ONIx2KcChFbcAjgprLW4qa', 'Av. Sur 101', '2233-0001', 'negocio', '2025-08-19 05:14:01'),
(12, 'Puerto Admin', 'admin-puerto@mariscos.com', '$2y$10$uXfTz3.rj0MIzksPAmWHtO1h9cncKYPDq2Rlmo1Lh5l6MgODNWEAO', 'Costanera 8', '2233-0002', 'negocio', '2025-08-19 05:14:02'),
(13, 'Dragón Admin', 'admin-dragon@wok.com', '$2y$10$afj8ekjLxfZNk8SgBxhrM.TFutubUwjB1THJHrLVu37d4Lt4/aCCC', 'Barrio Oriental 12', '2233-0003', 'negocio', '2025-08-19 05:14:02'),
(14, 'Caracas Admin', 'admin-caracas@arepas.com', '$2y$10$GE78/KLxh6VOFl1IYVGg/.1HFDJNpNvQNo0XzIMDevzWgEs6cxxtC', 'Plaza Centro Local 3', '2233-0004', 'negocio', '2025-08-19 05:14:02'),
(15, 'Postres Admin', 'admin-postres@postres.com', '$2y$10$M4wrtb.6.pURRpdcMSkdj.U9I4mU8TMZ.dLAOugODk/Nu8k2JGJta', 'Paseo del Sol 44', '2233-0005', 'negocio', '2025-08-19 05:14:02');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Codigo` (`Codigo`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`Usuario_id`,`Negocio_id`),
  ADD KEY `Negocio_id` (`Negocio_id`);

--
-- Indices de la tabla `negocios`
--
ALTER TABLE `negocios`
  ADD PRIMARY KEY (`Id_negocio`),
  ADD KEY `Usuario_id` (`Usuario_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`Id_pedido`),
  ADD KEY `idx_pedidos_cliente` (`Cliente_id`),
  ADD KEY `idx_pedidos_negocio` (`Negocio_id`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`Id_detalle`),
  ADD KEY `Pedido_id` (`Pedido_id`),
  ADD KEY `Platillo_id` (`Platillo_id`);

--
-- Indices de la tabla `platillos`
--
ALTER TABLE `platillos`
  ADD PRIMARY KEY (`Id_platillo`),
  ADD KEY `idx_platillos_negocio` (`Negocio_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`Id_usuario`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `negocios`
--
ALTER TABLE `negocios`
  MODIFY `Id_negocio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `Id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `Id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `platillos`
--
ALTER TABLE `platillos`
  MODIFY `Id_platillo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `fk_fav_neg` FOREIGN KEY (`Negocio_id`) REFERENCES `negocios` (`Id_negocio`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`Usuario_id`) REFERENCES `usuarios` (`Id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `negocios`
--
ALTER TABLE `negocios`
  ADD CONSTRAINT `negocios_ibfk_1` FOREIGN KEY (`Usuario_id`) REFERENCES `usuarios` (`Id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`Cliente_id`) REFERENCES `usuarios` (`Id_usuario`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`Negocio_id`) REFERENCES `negocios` (`Id_negocio`);

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `pedido_detalles_ibfk_1` FOREIGN KEY (`Pedido_id`) REFERENCES `pedidos` (`Id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_detalles_ibfk_2` FOREIGN KEY (`Platillo_id`) REFERENCES `platillos` (`Id_platillo`);

--
-- Filtros para la tabla `platillos`
--
ALTER TABLE `platillos`
  ADD CONSTRAINT `platillos_ibfk_1` FOREIGN KEY (`Negocio_id`) REFERENCES `negocios` (`Id_negocio`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE negocios
  ADD COLUMN Lat DECIMAL(10,7) NULL,
  ADD COLUMN Lng DECIMAL(10,7) NULL;

  ALTER TABLE pedidos
  ADD COLUMN Pago_estado ENUM('Pendiente','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
  ADD COLUMN Referencia_pago VARCHAR(60) NULL,
  ADD COLUMN Pagado_en TIMESTAMP NULL DEFAULT NULL;


  CREATE TABLE IF NOT EXISTS envios (
  Id INT AUTO_INCREMENT PRIMARY KEY,
  Pedido_id INT NOT NULL UNIQUE,
  Driver_nombre VARCHAR(120) NOT NULL,
  Lat DECIMAL(10,7) NULL,
  Lng DECIMAL(10,7) NULL,
  Actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_envio_pedido FOREIGN KEY (Pedido_id) REFERENCES pedidos(Id_pedido) ON DELETE CASCADE
);



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
