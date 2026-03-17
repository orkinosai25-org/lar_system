-- ============================================================
-- LAR System — lar_ultralux database schema
-- UltraLux Premium Module
-- Character set: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `user` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(255)  NOT NULL,
  `password`    VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
  `firstname`   VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`    VARCHAR(100)  NOT NULL DEFAULT '',
  `phone`       VARCHAR(30)   NOT NULL DEFAULT '',
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_manager` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED  NOT NULL,
  `session_token` VARCHAR(255)  NOT NULL,
  `ip_address`    VARCHAR(45)   NOT NULL DEFAULT '',
  `login_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`    DATETIME      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lm_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `domain_list` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `domain_name` VARCHAR(255)  NOT NULL,
  `domain_key`  VARCHAR(100)  NOT NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_domain_key` (`domain_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `domain_module_map` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `domain_id`   INT UNSIGNED  NOT NULL,
  `module_name` VARCHAR(100)  NOT NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `hotel_name`     VARCHAR(255)   NOT NULL DEFAULT '',
  `check_in`       DATE,
  `check_out`      DATE,
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `pickup_loc`     VARCHAR(255)   NOT NULL DEFAULT '',
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_gateway_details` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `txnid`           VARCHAR(100)    NOT NULL,
  `app_reference`   VARCHAR(100)    NOT NULL,
  `amount`          DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `currency`        VARCHAR(10)     NOT NULL DEFAULT 'ZAR',
  `status`          VARCHAR(50)     NOT NULL DEFAULT 'pending',
  `request_params`  MEDIUMTEXT,
  `response_params` MEDIUMTEXT,
  `created_datetime` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pgd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_log` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL DEFAULT '',
  `log_type`      VARCHAR(50)   NOT NULL DEFAULT '',
  `log_message`   MEDIUMTEXT,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exception_logger` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `vertical`   VARCHAR(50)   NOT NULL DEFAULT '',
  `message`    MEDIUMTEXT,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_subscribtion` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255)  NOT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_es_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
