-- ============================================================
-- LAR System — lar_supervision database schema
-- Back-office / Supervision Panel
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
  `user_type`   VARCHAR(50)   NOT NULL DEFAULT 'admin',
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

-- Currency converter
CREATE TABLE IF NOT EXISTS `currency_converter` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `from_currency` VARCHAR(10)     NOT NULL,
  `to_currency`   VARCHAR(10)     NOT NULL,
  `rate`          DECIMAL(12,6)   NOT NULL DEFAULT 1.000000,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cc_pair` (`from_currency`, `to_currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Convenience fees configuration
CREATE TABLE IF NOT EXISTS `convenience_fees` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `domain_id`      INT UNSIGNED    NOT NULL,
  `booking_type`   VARCHAR(50)     NOT NULL DEFAULT '',
  `fee_type`       VARCHAR(20)     NOT NULL DEFAULT 'flat',
  `fee_value`      DECIMAL(10,4)   NOT NULL DEFAULT 0.0000,
  `status`         TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank payment details
CREATE TABLE IF NOT EXISTS `bank_payment_details` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `domain_id`      INT UNSIGNED  NOT NULL,
  `bank_name`      VARCHAR(100)  NOT NULL DEFAULT '',
  `account_no`     VARCHAR(50)   NOT NULL DEFAULT '',
  `branch_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `account_holder` VARCHAR(100)  NOT NULL DEFAULT '',
  `status`         TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Flight management
CREATE TABLE IF NOT EXISTS `flight_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `domain_origin`  VARCHAR(100)   NOT NULL DEFAULT '',
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_passenger_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  `pax_type`      VARCHAR(20)   NOT NULL DEFAULT 'ADT',
  PRIMARY KEY (`id`),
  KEY `idx_fbpd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_transaction_details` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`   VARCHAR(100)  NOT NULL,
  `pnr`             VARCHAR(50)   NOT NULL DEFAULT '',
  `transaction_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbtd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `cancel_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fcd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CRS (Central Reservation System) master data
CREATE TABLE IF NOT EXISTS `crs_update_flight_details` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `flight_no`   VARCHAR(20)   NOT NULL DEFAULT '',
  `origin`      VARCHAR(10)   NOT NULL DEFAULT '',
  `destination` VARCHAR(10)   NOT NULL DEFAULT '',
  `update_type` VARCHAR(50)   NOT NULL DEFAULT '',
  `details`     MEDIUMTEXT,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_crs_master_seat_price_range` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `flight_no`    VARCHAR(20)     NOT NULL DEFAULT '',
  `cabin_class`  VARCHAR(20)     NOT NULL DEFAULT '',
  `min_price`    DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `max_price`    DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `currency`     VARCHAR(10)     NOT NULL DEFAULT 'ZAR',
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hotel management
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

CREATE TABLE IF NOT EXISTS `hotel_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `cancel_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hcd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Car management
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

CREATE TABLE IF NOT EXISTS `car_booking_itinerary_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `car_type`      VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reference lookups
CREATE TABLE IF NOT EXISTS `api_country_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(100)  NOT NULL,
  `country_code` VARCHAR(5)    NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_city_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `city_name`    VARCHAR(255)  NOT NULL,
  `city_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_state_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `state_name`   VARCHAR(100)  NOT NULL,
  `state_code`   VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `city_code_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `city_name`    VARCHAR(255)  NOT NULL DEFAULT '',
  `city_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `airline_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `airline_name` VARCHAR(255)  NOT NULL DEFAULT '',
  `iata_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `status`       TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `booking_source` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `source_name` VARCHAR(100)  NOT NULL,
  `source_code` VARCHAR(50)   NOT NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logging
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

CREATE TABLE IF NOT EXISTS `app_reference` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `reference_code` VARCHAR(100)  NOT NULL,
  `booking_type`   VARCHAR(50)   NOT NULL DEFAULT '',
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ar_code` (`reference_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_subscribtion` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255)  NOT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_es_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
