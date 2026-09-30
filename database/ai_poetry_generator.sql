-- AI Poetry Generator Database Schema
-- Compatible with MySQL 5.7+ and MySQL 8.0+

CREATE DATABASE IF NOT EXISTS `ai_poetry_generator` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ai_poetry_generator`;

-- Table 1: users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: poems
CREATE TABLE IF NOT EXISTS `poems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) DEFAULT 'Untitled Poem',
    `prompt` VARCHAR(255) NOT NULL,
    `style` VARCHAR(100) DEFAULT 'romantic',
    `tone` VARCHAR(100) DEFAULT 'calm',
    `length_type` VARCHAR(50) DEFAULT 'medium',
    `poem_text` TEXT NOT NULL,
    `is_shared` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: user_preferences
CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `favorite_theme` VARCHAR(100) DEFAULT NULL,
    `preferred_style` VARCHAR(100) DEFAULT 'romantic',
    `preferred_tone` VARCHAR(100) DEFAULT 'calm',
    `preferred_length` VARCHAR(50) DEFAULT 'medium',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: prompt_logs
CREATE TABLE IF NOT EXISTS `prompt_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `prompt` VARCHAR(255) NOT NULL,
    `style` VARCHAR(100) DEFAULT NULL,
    `tone` VARCHAR(100) DEFAULT NULL,
    `length_type` VARCHAR(50) DEFAULT NULL,
    `generated_poem` TEXT,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Seed Data (default passwords are 'password')
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrator', 'admin@poetry.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Creative Poet', 'user@poetry.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Insert System Settings Seeds
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('generation_mode', 'rule_based'), -- options: rule_based, api_based
('gemini_api_key', ''),
('featured_themes', 'love,rain,nature,sadness,hope,dreams,motivation')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
