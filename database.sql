CREATE DATABASE IF NOT EXISTS pc_store_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pc_store_ai;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS pc_build_components;
DROP TABLE IF EXISTS pc_builds;
DROP TABLE IF EXISTS pc_parts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS ai_chat_history;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  role ENUM('guest', 'customer', 'admin') NOT NULL DEFAULT 'customer',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pc_parts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  category VARCHAR(100) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  is_stock_empty BOOLEAN DEFAULT FALSE,
  image_url VARCHAR(255) NULL,
  description TEXT NULL,
  specifications JSON NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (category),
  INDEX idx_stock_status (is_stock_empty)
);

CREATE TABLE pc_builds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  description TEXT NULL,
  total_price DECIMAL(12,2) NOT NULL,
  image_url VARCHAR(255) NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pc_build_components (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pc_build_id INT NOT NULL,
  pc_part_id INT NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_build_component_build FOREIGN KEY (pc_build_id) REFERENCES pc_builds(id) ON DELETE CASCADE,
  CONSTRAINT fk_build_component_part FOREIGN KEY (pc_part_id) REFERENCES pc_parts(id) ON DELETE CASCADE,
  UNIQUE KEY unique_build_part (pc_build_id, pc_part_id)
);

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_type ENUM('part', 'build') NOT NULL,
  pc_part_id INT NULL,
  pc_build_id INT NULL,
  qty INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_part FOREIGN KEY (pc_part_id) REFERENCES pc_parts(id) ON DELETE SET NULL,
  CONSTRAINT fk_cart_build FOREIGN KEY (pc_build_id) REFERENCES pc_builds(id) ON DELETE SET NULL
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  total_price DECIMAL(12,2) NOT NULL,
  status ENUM('Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Sampai di Tujuan', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
  qris_code VARCHAR(500) NULL,
  payment_proof_url VARCHAR(255) NULL,
  tracking_number VARCHAR(100) NULL,
  delivery_note TEXT NULL,
  customer_notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_customer (customer_id)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_type ENUM('part', 'build') NOT NULL,
  pc_part_id INT NULL,
  pc_build_id INT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_order_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_item_part FOREIGN KEY (pc_part_id) REFERENCES pc_parts(id) ON DELETE SET NULL,
  CONSTRAINT fk_order_item_build FOREIGN KEY (pc_build_id) REFERENCES pc_builds(id) ON DELETE SET NULL
);

CREATE TABLE ai_chat_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_message TEXT NOT NULL,
  ai_response TEXT NOT NULL,
  session_id VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_session (session_id),
  INDEX idx_user (user_id)
);

-- Seed Data
INSERT INTO users (id, name, email, password_hash, role) VALUES
  (1, 'Admin Store', 'admin@pcstore.local', SHA2('admin123', 256), 'admin'),
  (2, 'Budi Santoso', 'budi@email.com', SHA2('pass123', 256), 'customer'),
  (3, 'Siti Nurasia', 'siti@email.com', SHA2('pass123', 256), 'customer');

-- PC Parts Sample Data
INSERT INTO pc_parts (name, category, brand, model, price, stock, description, specifications) VALUES
  ('Intel Core i9-14900K', 'Processor', 'Intel', 'Core i9-14900K', 9500000, 15, 'Prosesor desktop dengan 24 core terbaik untuk gaming dan workstation kelas atas', JSON_OBJECT('cores', 24, 'threads', 32, 'base_clock', '3.2 GHz', 'boost_clock', '6.0 GHz')),
  ('RTX 4090', 'Graphics Card', 'NVIDIA', 'GeForce RTX 4090', 28000000, 8, 'GPU desktop flagship untuk 4K gaming dan AI computation', JSON_OBJECT('memory', '24GB GDDR6X', 'memory_bus', '384-bit', 'cuda_cores', 16384)),
  ('DDR5 64GB Kit', 'Memory', 'G.Skill', 'Trident Z5', 4500000, 25, 'RAM DDR5 high performance untuk workstation dan gaming', JSON_OBJECT('capacity', '64GB', 'speed', '6000MHz', 'latency', 'CAS 28')),
  ('Samsung 990 Pro 2TB', 'Storage', 'Samsung', '990 Pro', 2800000, 30, 'SSD NVMe PCIe 4.0 dengan kecepatan luar biasa', JSON_OBJECT('capacity', '2TB', 'interface', 'PCIe 4.0 NVMe', 'read_speed', '7100 MB/s')),
  ('ASUS ROG Strix Z790-E', 'Motherboard', 'ASUS', 'ROG Strix Z790-E', 4200000, 20, 'Motherboard enthusiast untuk Intel generasi terbaru', JSON_OBJECT('socket', 'LGA1700', 'form_factor', 'ATX', 'pcie_gen', '5.0')),
  ('Corsair RM1200x Gold', 'Power Supply', 'Corsair', 'RM1200x', 2500000, 18, 'Power supply 1200W modular dengan efisiensi 80+ Gold', JSON_OBJECT('wattage', '1200W', 'efficiency', '80+ Gold', 'modularity', 'Full Modular')),
  ('Lian Li O11 Dynamic', 'Case', 'Lian Li', 'O11 Dynamic', 1800000, 25, 'Casing ATX dengan desain tempered glass modern', JSON_OBJECT('form_factor', 'ATX/E-ATX', 'max_gpu_length', '420mm', 'cooling', 'Supports 9x 120mm fans'));

-- PC Builds Sample Data
INSERT INTO pc_builds (name, description, total_price, is_active) VALUES
  ('Gaming Build Pro 4K', 'Rakitan PC untuk gaming 4K ultra settings dengan performa maksimal', 52800000, TRUE),
  ('Workstation Build Professional', 'Rakitan PC untuk rendering, video editing, dan 3D modeling profesional', 48500000, TRUE),
  ('Budget Gaming Build 1080p', 'Rakitan gaming entry-level untuk gaming 1080p high settings', 15000000, TRUE);

-- PC Build Components (linking parts to builds)
INSERT INTO pc_build_components (pc_build_id, pc_part_id, qty) VALUES
  (1, 1, 1), (1, 2, 1), (1, 3, 2), (1, 4, 2), (1, 5, 1), (1, 6, 1), (1, 7, 1),
  (2, 1, 1), (2, 3, 4), (2, 4, 2), (2, 5, 1), (2, 6, 1), (2, 7, 1),
  (3, 1, 1), (3, 3, 1), (3, 4, 1), (3, 5, 1), (3, 6, 1), (3, 7, 1);
