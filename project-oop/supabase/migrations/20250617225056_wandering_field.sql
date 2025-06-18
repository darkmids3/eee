-- Truck Management System Database
-- Run this in phpMyAdmin or MySQL console after creating 'truck_management' database

CREATE DATABASE IF NOT EXISTS truck_management;
USE truck_management;

-- Trucks table
CREATE TABLE trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    truck_id VARCHAR(20) UNIQUE NOT NULL,
    model VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status ENUM('in_warehouse', 'in_use', 'maintenance') DEFAULT 'in_warehouse',
    assigned_user_id INT NULL,
    last_used DATETIME NULL,
    condition_status ENUM('good', 'fair', 'needs_repair') DEFAULT 'good',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    truck_id INT,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (truck_id) REFERENCES trucks(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO trucks (truck_id, model, type, status) VALUES
('TRK-001', 'Ford F-150', 'Pickup', 'in_warehouse'),
('TRK-002', 'Chevrolet Silverado', 'Pickup', 'in_warehouse'),
('TRK-003', 'Isuzu NPR', 'Box Truck', 'in_use'),
('TRK-004', 'Mercedes Sprinter', 'Van', 'in_warehouse'),
('TRK-005', 'Ram 2500', 'Heavy Duty', 'maintenance');

INSERT INTO users (name, role, phone, email) VALUES
('John Smith', 'Driver', '555-0101', 'john@company.com'),
('Maria Garcia', 'Supervisor', '555-0102', 'maria@company.com'),
('David Johnson', 'Driver', '555-0103', 'david@company.com'),
('Sarah Wilson', 'Manager', '555-0104', 'sarah@company.com');

INSERT INTO activity_logs (truck_id, user_id, action, details) VALUES
(1, 1, 'assigned', 'Truck assigned for delivery route'),
(2, 2, 'returned', 'Truck returned in good condition'),
(3, 3, 'assigned', 'Truck assigned for warehouse pickup'),
(4, 1, 'returned', 'Minor scratches reported on rear bumper'),
(5, NULL, 'maintenance', 'Scheduled maintenance - oil change and inspection');