CREATE DATABASE IF NOT EXISTS db_laptopmanagement;
USE db_laptopmanagement;

CREATE TABLE IF NOT EXISTS students (
    nis VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS laptop_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20) NOT NULL,
    action ENUM('ambil', 'kumpul') NOT NULL,
    status ENUM('normal', 'terlambat') DEFAULT 'normal',
    transaction_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nis) REFERENCES students(nis)
);

CREATE TABLE IF NOT EXISTS laptop_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20) NOT NULL,
    status ENUM('diambil', 'dikumpul', 'dikumpul_terlambat') NOT NULL,
    take_time DATETIME,
    return_time DATETIME,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nis) REFERENCES students(nis),
    UNIQUE KEY (nis)
);

INSERT INTO students (nis, name, class) VALUES
('0079787882', 'Rayhan Nulhafiz', 'XI D'),

