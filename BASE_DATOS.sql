CREATE DATABASE IF NOT EXISTS mamalila_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mamalilabase_db;

-- Tabla usuarios (cliente, negocio, admin)
CREATE TABLE IF NOT EXISTS usuarios (
    Id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(120) NOT NULL,
    Email VARCHAR(160) NOT NULL UNIQUE,
    Contrasenia VARCHAR(255) NOT NULL,
    Direccion VARCHAR(255),
    Telefono VARCHAR(30),
    Rol ENUM('cliente','negocio','admin') NOT NULL DEFAULT 'cliente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla negocios (perfil del restaurante, vinculado al usuario dueño)
CREATE TABLE IF NOT EXISTS negocios (
    Id_negocio INT AUTO_INCREMENT PRIMARY KEY,
    Usuario_id INT NOT NULL,
    Nombre VARCHAR(120) NOT NULL,
    Direccion VARCHAR(255),
    Telefono VARCHAR(30),
    Email VARCHAR(160),
    Horario VARCHAR(120),
    FOREIGN KEY (Usuario_id) REFERENCES usuarios(Id_usuario) ON DELETE CASCADE
);

-- Tabla platillos
CREATE TABLE IF NOT EXISTS platillos (
    Id_platillo INT AUTO_INCREMENT PRIMARY KEY,
    Negocio_id INT NOT NULL,
    Nombre VARCHAR(120) NOT NULL,
    Descripcion VARCHAR(255),
    Precio DECIMAL(10,2) NOT NULL,
    Imagen VARCHAR(255),
    Disponible TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (Negocio_id) REFERENCES negocios(Id_negocio) ON DELETE CASCADE
);

-- Tabla pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    Id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    Cliente_id INT NOT NULL,
    Negocio_id INT NOT NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('Pendiente','En preparación','Listo') NOT NULL DEFAULT 'Pendiente',
    Direccion_Entrega VARCHAR(255),
    Metodo_Pago VARCHAR(50) DEFAULT 'Simulado',
    Total DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (Cliente_id) REFERENCES usuarios(Id_usuario),
    FOREIGN KEY (Negocio_id) REFERENCES negocios(Id_negocio)
);

-- Tabla detalle de pedido
CREATE TABLE IF NOT EXISTS pedido_detalles (
    Id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    Pedido_id INT NOT NULL,
    Platillo_id INT NOT NULL,
    Cantidad INT NOT NULL,
    Subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (Pedido_id) REFERENCES pedidos(Id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (Platillo_id) REFERENCES platillos(Id_platillo)
);

-- Tabla de cupones
CREATE TABLE IF NOT EXISTS cupones(
  Id INT AUTO_INCREMENT PRIMARY KEY,
  Codigo VARCHAR(40) UNIQUE,
  Tipo ENUM('monto','porc') NOT NULL,   
  Valor DECIMAL(10,2) NOT NULL,         
  Negocio_id INT NULL,              
  Vigente_desde DATE NULL,
  Vigente_hasta DATE NULL,
  Usos_max INT NULL,
  Usos_actuales INT NOT NULL DEFAULT 0,
  Activo TINYINT(1) NOT NULL DEFAULT 1
);

-- Guardar qué cupón y cuánto descuento se aplicó en cada pedido
ALTER TABLE pedidos
  ADD COLUMN Cupon_codigo VARCHAR(40) NULL,
  ADD COLUMN Descuento DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Índices útiles
CREATE INDEX IF NOT EXISTS idx_platillos_negocio ON platillos(Negocio_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_cliente ON pedidos(Cliente_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_negocio ON pedidos(Negocio_id);


INSERT IGNORE INTO usuarios (Id_usuario, Nombre, Email, Contrasenia, Rol, Telefono) VALUES
(1, 'Taquería La Casita', 'taqueria@mamalila.com', '$2y$10$0QH4tC8pVx8hQeKpV4xGQOSq7/0.h3NfH8kqQx6bV8v3QH1x0q9kS', 'negocio','8888-0001'),
(2, 'Sushi Tico', 'sushi@mamalila.com', '$2y$10$0QH4tC8pVx8hQeKpV4xGQOSq7/0.h3NfH8kqQx6bV8v3QH1x0q9kS', 'negocio','8888-0002'),
(3, 'Amanda Cliente', 'amanda@example.com', '$2y$10$0QH4tC8pVx8hQeKpV4xGQOSq7/0.h3NfH8kqQx6bV8v3QH1x0q9kS', 'cliente','8888-0003');

INSERT IGNORE INTO negocios (Id_negocio, Usuario_id, Nombre, Direccion, Telefono, Email, Horario) VALUES
(1, 1, 'Taquería La Casita','San José','8888-0001','taqueria@mamalila.com','L-D 11am-10pm'),
(2, 2, 'Sushi Tico','Cartago','8888-0002','sushi@mamalila.com','M-D 12md-9pm');

INSERT IGNORE INTO platillos (Id_platillo, Negocio_id, Nombre, Descripcion, Precio) VALUES
(1,1,'Taco de carne','Tortilla maíz, carne y pico de gallo',1500),
(2,1,'Quesadilla','Queso y pollo',1800),
(3,1,'Burrito','Arroz, frijol, carne',2500),
(4,2,'Sushi roll clásico','8 piezas',3500),
(5,2,'Yakisoba','Fideos salteados',3200);
