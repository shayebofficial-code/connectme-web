-- ============================================================
-- ConnectMe Online Dating Platform Database Schema
-- Database: MySQL 8.0+
-- Charset: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `defaultdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `connectme_db`;

-- Set SQL mode for strict data integrity
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `plans`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `blocks`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `matches`;
DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NULL COMMENT 'Null for Google OAuth users without local password',
  `google_id` VARCHAR(255) NULL UNIQUE,
  `email_verified` TINYINT(1) DEFAULT 0,
  `is_admin` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: profiles
-- ------------------------------------------------------------
CREATE TABLE `profiles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `dob` DATE NOT NULL,
  `age` TINYINT UNSIGNED NOT NULL,
  `gender` ENUM('male', 'female', 'non-binary', 'other') NOT NULL,
  `looking_for` ENUM('male', 'female', 'everyone') DEFAULT 'everyone',
  `city` VARCHAR(100) NOT NULL,
  `bio` TEXT NULL,
  `interests` TEXT NULL COMMENT 'JSON array or comma-separated list',
  `photo` VARCHAR(255) DEFAULT 'default.jpg',
  `is_verified` TINYINT(1) DEFAULT 0,
  `is_premium` TINYINT(1) DEFAULT 0,
  `premium_until` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_profiles_city` (`city`),
  INDEX `idx_profiles_gender` (`gender`),
  INDEX `idx_profiles_age` (`age`),
  INDEX `idx_profiles_premium` (`is_premium`, `premium_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: likes
-- ------------------------------------------------------------
CREATE TABLE `likes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `from_user_id` INT UNSIGNED NOT NULL,
  `to_user_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_from_to` (`from_user_id`, `to_user_id`),
  INDEX `idx_likes_to_user` (`to_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: matches
-- ------------------------------------------------------------
CREATE TABLE `matches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user1_id` INT UNSIGNED NOT NULL COMMENT 'Lower User ID',
  `user2_id` INT UNSIGNED NOT NULL COMMENT 'Higher User ID',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_matches_pair` (`user1_id`, `user2_id`),
  INDEX `idx_matches_user1` (`user1_id`),
  INDEX `idx_matches_user2` (`user2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: messages
-- ------------------------------------------------------------
CREATE TABLE `messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_chat_conversation` (`sender_id`, `receiver_id`, `created_at`),
  INDEX `idx_messages_receiver_read` (`receiver_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: blocks
-- ------------------------------------------------------------
CREATE TABLE `blocks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `blocker_id` INT UNSIGNED NOT NULL,
  `blocked_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`blocker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`blocked_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_block_pair` (`blocker_id`, `blocked_id`),
  INDEX `idx_blocks_blocker` (`blocker_id`),
  INDEX `idx_blocks_blocked` (`blocked_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: reports
-- ------------------------------------------------------------
CREATE TABLE `reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` INT UNSIGNED NOT NULL,
  `reported_id` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed', 'actioned') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reported_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_reports_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: plans
-- ------------------------------------------------------------
CREATE TABLE `plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `duration_months` INT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'INR',
  `features` TEXT NULL COMMENT 'JSON array of feature bullet points',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans (as requested)
INSERT INTO `plans` (`id`, `name`, `duration_months`, `price`, `currency`, `features`, `is_active`) VALUES
(1, 'Monthly', 1, 199.00, 'INR', '["Unlimited Likes", "Direct Messaging", "Advanced Search Filters", "Premium Badge", "See Who Liked You"]', 1),
(2, 'Quarterly', 3, 499.00, 'INR', '["Unlimited Likes", "Direct Messaging", "Advanced Search Filters", "Premium Badge", "See Who Liked You", "Profile Boost 1x/month"]', 1),
(3, 'Half Year', 6, 799.00, 'INR', '["Unlimited Likes", "Direct Messaging", "Advanced Search Filters", "Premium Badge", "See Who Liked You", "Profile Boost 2x/month", "Priority Support"]', 1),
(4, 'Yearly', 12, 1299.00, 'INR', '["Unlimited Likes", "Direct Messaging", "Advanced Search Filters", "Premium Badge", "See Who Liked You", "Profile Boost 4x/month", "Priority Matching", "VIP Status"]', 1);

-- ------------------------------------------------------------
-- Table: subscriptions
-- ------------------------------------------------------------
CREATE TABLE `subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
  `provider_subscription_id` VARCHAR(255) NULL,
  `provider_payment_id` VARCHAR(255) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
  `starts_at` DATETIME NOT NULL,
  `ends_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`),
  INDEX `idx_sub_user_status` (`user_id`, `status`),
  INDEX `idx_sub_ends_at` (`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: payments
-- ------------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
  `payment_id` VARCHAR(255) NULL UNIQUE,
  `order_id` VARCHAR(255) NOT NULL UNIQUE,
  `signature` VARCHAR(255) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'INR',
  `status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `raw_response` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`),
  INDEX `idx_payments_order` (`order_id`),
  INDEX `idx_payments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
