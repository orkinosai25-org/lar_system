-- ============================================================
-- LAR System — lar_webservices database schema
-- Web Services API (internal API layer)
-- Character set: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `user` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(255)  NOT NULL,
  `password`    VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
  `api_key`     VARCHAR(64)   NOT NULL DEFAULT '',
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_type` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `type_name` VARCHAR(100)  NOT NULL,
  `type_code` VARCHAR(50)   NOT NULL,
  PRIMARY KEY (`id`)
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

-- API request logging
CREATE TABLE IF NOT EXISTS `app_reference` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `reference_code` VARCHAR(100)  NOT NULL,
  `booking_type`   VARCHAR(50)   NOT NULL DEFAULT '',
  `client_id`      INT UNSIGNED,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ar_code` (`reference_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `booking_amount_logger` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)    NOT NULL,
  `booking_type`   VARCHAR(50)     NOT NULL DEFAULT '',
  `logged_amount`  DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)     NOT NULL DEFAULT 'ZAR',
  `log_source`     VARCHAR(100)    NOT NULL DEFAULT '',
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bal_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Flight
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

CREATE TABLE IF NOT EXISTS `flight_booking_itinerary_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `origin`        VARCHAR(10)   NOT NULL DEFAULT '',
  `destination`   VARCHAR(10)   NOT NULL DEFAULT '',
  `dep_datetime`  DATETIME,
  `arr_datetime`  DATETIME,
  `airline_code`  VARCHAR(10)   NOT NULL DEFAULT '',
  `flight_number` VARCHAR(20)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbid_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_transaction_details` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)  NOT NULL,
  `pnr`            VARCHAR(50)   NOT NULL DEFAULT '',
  `transaction_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbtd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_baggage_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `segment_id`    INT           NOT NULL DEFAULT 0,
  `pax_id`        INT           NOT NULL DEFAULT 0,
  `weight`        VARCHAR(20)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_meal_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `segment_id`    INT           NOT NULL DEFAULT 0,
  `pax_id`        INT           NOT NULL DEFAULT 0,
  `meal_code`     VARCHAR(20)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbmd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_seat_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `segment_id`    INT           NOT NULL DEFAULT 0,
  `pax_id`        INT           NOT NULL DEFAULT 0,
  `seat_no`       VARCHAR(10)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbsd_ref` (`app_reference`)
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

CREATE TABLE IF NOT EXISTS `flight_passenger_ticket_info` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `pax_id`        INT           NOT NULL DEFAULT 0,
  `ticket_no`     VARCHAR(50)   NOT NULL DEFAULT '',
  `issued_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fpti_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_airport_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `airport_name` VARCHAR(255)  NOT NULL DEFAULT '',
  `iata_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `city_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fal_iata` (`iata_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hotel
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

CREATE TABLE IF NOT EXISTS `hotel_booking_itinerary_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `room_type`     VARCHAR(255)  NOT NULL DEFAULT '',
  `board_type`    VARCHAR(100)  NOT NULL DEFAULT '',
  `rooms`         TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_hbid_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_booking_pax_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_hbpd_ref` (`app_reference`)
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

-- GRN hotel extended
CREATE TABLE IF NOT EXISTS `grn_room_boarding_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `room_id`       VARCHAR(50)   NOT NULL DEFAULT '',
  `board_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `rate`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_grbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Car
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

CREATE TABLE IF NOT EXISTS `car_booking_extra_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `extra_type`    VARCHAR(100)  NOT NULL DEFAULT '',
  `extra_value`   TEXT,
  PRIMARY KEY (`id`),
  KEY `idx_cbed_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_booking_itinerary_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `car_type`      VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_booking_pax_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search
CREATE TABLE IF NOT EXISTS `search_history` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`  VARCHAR(100)  NOT NULL DEFAULT '',
  `search_type` VARCHAR(50)   NOT NULL DEFAULT '',
  `params`      MEDIUMTEXT,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_hotel_history` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`  VARCHAR(100)  NOT NULL DEFAULT '',
  `destination` VARCHAR(255)  NOT NULL DEFAULT '',
  `check_in`    DATE,
  `check_out`   DATE,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `temp_booking` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_key` VARCHAR(100)  NOT NULL,
  `booking_type` VARCHAR(50)  NOT NULL DEFAULT '',
  `data`        MEDIUMTEXT,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`  DATETIME      NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reference lookups
CREATE TABLE IF NOT EXISTS `api_country_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(100)  NOT NULL,
  `country_code` VARCHAR(5)    NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_continent_list` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `continent_name` VARCHAR(100)  NOT NULL,
  `continent_code` VARCHAR(5)    NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_city_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `city_name`    VARCHAR(255)  NOT NULL,
  `city_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_city_master` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `city_name`    VARCHAR(255)  NOT NULL,
  `city_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  `source`       VARCHAR(50)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `all_api_city_master` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `city_name`    VARCHAR(255)  NOT NULL,
  `city_code`    VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  `api_source`   VARCHAR(50)   NOT NULL DEFAULT '',
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

CREATE TABLE IF NOT EXISTS `currency_converter` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `from_currency` VARCHAR(10)     NOT NULL,
  `to_currency`   VARCHAR(10)     NOT NULL,
  `rate`          DECIMAL(12,6)   NOT NULL DEFAULT 1.000000,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cc_pair` (`from_currency`, `to_currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Meta course list (training/educational content)
CREATE TABLE IF NOT EXISTS `meta_course_list` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `course_name` VARCHAR(255)  NOT NULL,
  `course_code` VARCHAR(50)   NOT NULL DEFAULT '',
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment
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
  `updated_datetime` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pgd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_payment_details` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `domain_id`      INT UNSIGNED  NOT NULL,
  `bank_name`      VARCHAR(100)  NOT NULL DEFAULT '',
  `account_no`     VARCHAR(50)   NOT NULL DEFAULT '',
  `status`         TINYINT(1)    NOT NULL DEFAULT 1,
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

CREATE TABLE IF NOT EXISTS `provab_xml_logger` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL DEFAULT '',
  `api_name`      VARCHAR(100)  NOT NULL DEFAULT '',
  `request_xml`   MEDIUMTEXT,
  `response_xml`  MEDIUMTEXT,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `track_log` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(100)  NOT NULL DEFAULT '',
  `action`       VARCHAR(100)  NOT NULL DEFAULT '',
  `details`      MEDIUMTEXT,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travelport extended caching
CREATE TABLE IF NOT EXISTS `travelport_price_xml_new` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `cache_key`      VARCHAR(255)  NOT NULL,
  `request_xml`    MEDIUMTEXT,
  `response_xml`   MEDIUMTEXT,
  `expires_at`     DATETIME      NOT NULL,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tpxn_key` (`cache_key`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bus_stations` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `station_name` VARCHAR(255)  NOT NULL DEFAULT '',
  `station_code` VARCHAR(20)   NOT NULL DEFAULT '',
  `city`         VARCHAR(100)  NOT NULL DEFAULT '',
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
