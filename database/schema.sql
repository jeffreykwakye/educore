-- Create schools table
CREATE TABLE IF NOT EXISTS `schools` (
    `school_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `address` TEXT,
    `city` VARCHAR(100),
    `country` VARCHAR(100),
    `contact_email` VARCHAR(255) NOT NULL UNIQUE,
    `phone_number` VARCHAR(50),
    `status` ENUM('active', 'inactive', 'trial') NOT NULL DEFAULT 'trial',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_schools_slug` (`slug`),
    INDEX `idx_schools_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create school_branding table
CREATE TABLE IF NOT EXISTS `school_branding` (
    `branding_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL UNIQUE,
    `logo_url` VARCHAR(255),
    `mission_statement` TEXT,
    `vision_statement` TEXT,
    `tagline` VARCHAR(255),
    `theme_color_primary` VARCHAR(7),
    `theme_color_secondary` VARCHAR(7),
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`school_id`) ON DELETE CASCADE,
    INDEX `idx_branding_school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;