-- ============================================================
-- Script de creación de Base de Datos - Tienda en Línea
-- Motor: MySQL / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS tienda_online;
CREATE DATABASE tienda_online CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE tienda_online;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    telefono        VARCHAR(20),
    fecha_registro  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Tabla: direcciones_envio
-- ------------------------------------------------------------
CREATE TABLE direcciones_envio (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL,
    calle               VARCHAR(150) NOT NULL,
    numero              VARCHAR(20),
    colonia             VARCHAR(100),
    ciudad              VARCHAR(100) NOT NULL,
    estado              VARCHAR(100) NOT NULL,
    codigo_postal       VARCHAR(10) NOT NULL,
    telefono_contacto   VARCHAR(20),
    CONSTRAINT fk_direccion_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255)
);

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150) NOT NULL,
    descripcion     TEXT,
    precio          DECIMAL(10,2) NOT NULL,
    stock           INT NOT NULL DEFAULT 0,
    categoria_id    INT,
    imagen_url      VARCHAR(255),
    fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Tabla: carrito  (1 renglón = 1 producto dentro del carrito de un usuario)
-- ------------------------------------------------------------
CREATE TABLE carrito (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL,
    producto_id     INT NOT NULL,
    cantidad        INT NOT NULL DEFAULT 1,
    fecha_agregado  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carrito_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_carrito_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT uk_usuario_producto UNIQUE (usuario_id, producto_id)
);

-- ------------------------------------------------------------
-- Tabla: pedidos
-- ------------------------------------------------------------
CREATE TABLE pedidos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL,
    direccion_envio_id  INT NOT NULL,
    fecha_pedido        DATETIME DEFAULT CURRENT_TIMESTAMP,
    total               DECIMAL(10,2) NOT NULL,
    estado              VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_pedido_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_pedido_direccion
        FOREIGN KEY (direccion_envio_id) REFERENCES direcciones_envio(id)
);

-- ------------------------------------------------------------
-- Tabla: detalle_pedido
-- ------------------------------------------------------------
CREATE TABLE detalle_pedido (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT NOT NULL,
    producto_id     INT NOT NULL,
    cantidad        INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- ============================================================
-- Datos de ejemplo
-- ============================================================
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónica', 'Dispositivos electrónicos y accesorios'),
('Ropa', 'Prendas de vestir para todas las edades'),
('Hogar', 'Artículos para el hogar y decoración');

INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, imagen_url) VALUES
('Audífonos Bluetooth', 'Audífonos inalámbricos con cancelación de ruido', 599.00, 25, 1, 'assets/images/audifonos.jpg'),
('Smartwatch', 'Reloj inteligente con monitor de ritmo cardiaco', 1299.00, 15, 1, 'assets/images/smartwatch.jpg'),
('Cargador Inalámbrico', 'Cargador rápido por inducción 15W', 349.00, 40, 1, 'assets/images/cargador.jpg'),
('Playera Casual', 'Playera de algodón 100%, varios colores', 199.00, 50, 2, 'assets/images/playera.jpg'),
('Sudadera con Capucha', 'Sudadera unisex, tela afelpada', 349.00, 30, 2, 'assets/images/sudadera.jpg'),
('Gorra Deportiva', 'Gorra ajustable, varios colores', 149.00, 60, 2, 'assets/images/gorra.jpg'),
('Lámpara LED de Escritorio', 'Lámpara con luz regulable y puerto USB', 249.00, 20, 3, 'assets/images/lampara.jpg'),
('Set de Cojines Decorativos', 'Set de 2 cojines, tela suave', 179.00, 35, 3, 'assets/images/cojines.jpg'),
('Organizador Multiusos', 'Organizador plegable para closet', 129.00, 45, 3, 'assets/images/organizador.jpg');
