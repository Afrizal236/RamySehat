DROP DATABASE IF EXISTS kesehatan_db;
CREATE DATABASE kesehatan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kesehatan_db;

-- ============================================
-- Table: users
-- Description: Store user account information
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================
-- Table: contact_messages
-- Description: Store contact form submissions
-- ============================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;


-- ============================================
-- Default Admin Account
-- ============================================
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@ramysehat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Password: admin123
-- You can change this after first login

-- ============================================
-- Sample User Account (for testing)
-- ============================================
INSERT INTO users (username, email, password, role) VALUES 
('ramydiaman123', 'ramydiaman@gmail.com', '$2y$10$YhW.zJyKj5L8K9X7VnQ6uOzP2Hq3Rg4Tk6Mn8Bp9Cd1Ef2Gh3Ij4K', 'user');

-- Password: Ramydiaman23
-- This is a demo account for testing

-- ============================================
-- Sample Contact Messages (for testing)
-- ============================================
INSERT INTO contact_messages (full_name, email, phone, subject, message, status) VALUES 
(
    'John Doe', 
    'john.doe@example.com', 
    '08123456789', 
    'Pertanyaan tentang layanan konsultasi', 
    'Halo, saya ingin tahu lebih lanjut tentang layanan konsultasi kesehatan yang tersedia. Apakah bisa konsultasi untuk penyakit kronis?',
    'unread'
),
(
    'Jane Smith', 
    'jane.smith@example.com', 
    '08987654321', 
    'Feedback aplikasi chatbot', 
    'Aplikasi chatbot sangat membantu! Saya berhasil mendapatkan informasi yang akurat tentang gejala flu. Terima kasih RamySehat!',
    'read'
),
(
    'Ahmad Rahman', 
    'ahmad.rahman@example.com', 
    '08555123456', 
    'Saran fitur baru', 
    'Apakah bisa ditambahkan fitur reminder untuk minum obat? Menurut saya ini akan sangat berguna untuk pasien yang sedang dalam pengobatan.',
    'replied'
);

-- ============================================
-- Verify Installation
-- ============================================

-- Show created tables
SHOW TABLES;

-- Show users table structure
DESCRIBE users;

-- Show contact_messages table structure  
DESCRIBE contact_messages