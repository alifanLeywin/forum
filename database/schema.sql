-- Create database
CREATE DATABASE IF NOT EXISTS 4chan_db;
USE 4chan_db;

-- Create boards table
CREATE TABLE IF NOT EXISTS boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_code VARCHAR(10) NOT NULL UNIQUE,
    board_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create threads table
CREATE TABLE IF NOT EXISTS threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_id INT NOT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_sticky BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    last_bump TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE
);

-- Create posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE
);

-- Create reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    thread_id INT,
    reason TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'resolved', 'ignored') DEFAULT 'pending',
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE
);

-- Create bans table
CREATE TABLE IF NOT EXISTS bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    reason TEXT NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some default boards
INSERT INTO boards (board_code, board_name, description) VALUES 
('b', 'Random', 'Random discussion'),
('a', 'Anime & Manga', 'Anime and Manga Discussion'),
('v', 'Video Games', 'Video Games Discussion'),
('g', 'Technology', 'Technology Discussion');
