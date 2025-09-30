-- =========================
-- Users Tablosu (Hashlenmiş Parola, UTF8MB4 Turkish Collation)
-- =========================
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
lastname VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
email VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL UNIQUE,
username VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Suppliers Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS suppliers (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Products Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS products (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
type VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Projects Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS projects (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Price Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS price (
id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
supplier_id INT NOT NULL,
net_price DECIMAL(12,4) NOT NULL,
vat_amount DECIMAL(12,4) NOT NULL,
total_price DECIMAL(12,4) NOT NULL,
linked BOOLEAN DEFAULT FALSE,
price_date DATE NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (product_id) REFERENCES products(id),
FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Orders Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS orders (
id INT AUTO_INCREMENT PRIMARY KEY,
supplierOrder_no VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
project_id INT,
suppliers_id INT,
createdBy_id INT,
order_date DATE,
planned_date DATE,
actual_date DATE,
requested_date DATE NULL,
status VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
takenBy_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES projects(id),
FOREIGN KEY (suppliers_id) REFERENCES suppliers(id),
FOREIGN KEY (createdBy_id) REFERENCES users(id),
FOREIGN KEY (takenBy_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Order Items Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS order_items (
id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
product_id INT NOT NULL,
width DECIMAL(10,2) NOT NULL,
height DECIMAL(10,2) NOT NULL,
quantity INT NOT NULL,
total_area DECIMAL(12,4) NOT NULL,
unit_price DECIMAL(12,4) NOT NULL,
total_price DECIMAL(12,4) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (order_id) REFERENCES orders(id),
FOREIGN KEY (product_id) REFERENCES products(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Company Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS company (
id INT AUTO_INCREMENT PRIMARY KEY,
logo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
address VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
email VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
phone_number VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
fax_number VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Company IBANs Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS company_ibans (
id INT AUTO_INCREMENT PRIMARY KEY,
company_id INT NOT NULL,
bank_name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
iban VARCHAR(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
currency CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (company_id) REFERENCES company(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Company Descriptions Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS company_descriptions (
id INT AUTO_INCREMENT PRIMARY KEY,
company_id INT NOT NULL,
locale VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (company_id) REFERENCES company(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- =========================
-- Logs Tablosu
-- =========================
CREATE TABLE IF NOT EXISTS logs (
id INT AUTO_INCREMENT PRIMARY KEY,
reference_id INT NULL,
user_id INT NOT NULL,
table_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
column_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
old_value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
new_value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci,
action_type ENUM('INSERT','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
