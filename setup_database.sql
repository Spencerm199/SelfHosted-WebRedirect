-- Create analytics database if it doesn't exist
CREATE DATABASE IF NOT EXISTS analytics_db;
USE analytics_db;

-- Create visits table to store analytics data
CREATE TABLE IF NOT EXISTS visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(36) NOT NULL,
    ip_address VARCHAR(45),
    isp VARCHAR(255),
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(100),
    coordinates VARCHAR(50),
    timezone VARCHAR(50),
    user_agent TEXT,
    language VARCHAR(20),
    screen_resolution VARCHAR(20),
    device_data TEXT,
    url VARCHAR(2048),
    referrer VARCHAR(2048),
    load_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 