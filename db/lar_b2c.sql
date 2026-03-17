-- ============================================================
-- LAR System — lar_b2c database schema
-- B2C Consumer Website
-- Character set: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- Core identity / authentication
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `email`          VARCHAR(255)     NOT NULL,
  `password`       VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash',
  `firstname`      VARCHAR(100)     NOT NULL DEFAULT '',
  `lastname`       VARCHAR(100)     NOT NULL DEFAULT '',
  `phone`          VARCHAR(30)      NOT NULL DEFAULT '',
  `status`         TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_manager` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED     NOT NULL,
  `session_token`  VARCHAR(255)     NOT NULL,
  `ip_address`     VARCHAR(45)      NOT NULL DEFAULT '',
  `user_agent`     VARCHAR(512)     NOT NULL DEFAULT '',
  `login_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`     DATETIME         NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lm_user` (`user_id`),
  KEY `idx_lm_token` (`session_token`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Domain / module configuration
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `domain_list` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `domain_name`    VARCHAR(255)     NOT NULL,
  `domain_key`     VARCHAR(100)     NOT NULL,
  `status`         TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_domain_key` (`domain_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `domain_module_map` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `domain_id`      INT UNSIGNED     NOT NULL,
  `module_name`    VARCHAR(100)     NOT NULL,
  `status`         TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_dmm_domain` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Booking source / reference
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `booking_source` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `source_name`    VARCHAR(100)     NOT NULL,
  `source_code`    VARCHAR(50)      NOT NULL,
  `status`         TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Payment
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payment_gateway_details` (
  `id`                       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `txnid`                    VARCHAR(100)    NOT NULL,
  `app_reference`            VARCHAR(100)    NOT NULL,
  `amount`                   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `convenience_amount`       DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `promocode_discount`       DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `currency`                 VARCHAR(10)     NOT NULL DEFAULT 'ZAR',
  `currency_conversion_rate` DECIMAL(12,6)   NOT NULL DEFAULT 1.000000,
  `domain_origin`            VARCHAR(100)    NOT NULL DEFAULT '',
  `status`                   VARCHAR(50)     NOT NULL DEFAULT 'pending',
  `request_params`           MEDIUMTEXT,
  `response_params`          MEDIUMTEXT,
  `created_datetime`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pgd_ref` (`app_reference`),
  KEY `idx_pgd_txn` (`txnid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offline_payment` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)    NOT NULL,
  `amount`         DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `payment_mode`   VARCHAR(50)     NOT NULL DEFAULT '',
  `status`         VARCHAR(50)     NOT NULL DEFAULT 'pending',
  `notes`          TEXT,
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_op_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Flight bookings
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `flight_booking_details` (
  `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`    VARCHAR(100)   NOT NULL,
  `booking_status`   VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `domain_origin`    VARCHAR(100)   NOT NULL DEFAULT '',
  `booking_source`   VARCHAR(100)   NOT NULL DEFAULT '',
  `total_fare`       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`         VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_passenger_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  `pax_type`      VARCHAR(20)   NOT NULL DEFAULT 'ADT',
  `dob`           DATE,
  `passport_no`   VARCHAR(50)   NOT NULL DEFAULT '',
  `nationality`   VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbpd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_itinerary_details` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`   VARCHAR(100)  NOT NULL,
  `origin`          VARCHAR(10)   NOT NULL DEFAULT '',
  `destination`     VARCHAR(10)   NOT NULL DEFAULT '',
  `dep_datetime`    DATETIME,
  `arr_datetime`    DATETIME,
  `airline_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `flight_number`   VARCHAR(20)   NOT NULL DEFAULT '',
  `cabin_class`     VARCHAR(20)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_fbid_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_transaction_details` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`   VARCHAR(100)  NOT NULL,
  `pnr`             VARCHAR(50)   NOT NULL DEFAULT '',
  `gds_ref`         VARCHAR(100)  NOT NULL DEFAULT '',
  `ticket_nos`      TEXT,
  `transaction_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbtd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flight_booking_baggage_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `segment_id`    INT           NOT NULL DEFAULT 0,
  `pax_id`        INT           NOT NULL DEFAULT 0,
  `weight`        VARCHAR(20)   NOT NULL DEFAULT '',
  `pieces`        TINYINT       NOT NULL DEFAULT 0,
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
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`   VARCHAR(100)  NOT NULL,
  `cancel_ref`      VARCHAR(100)  NOT NULL DEFAULT '',
  `cancel_status`   VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `cancel_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reason`          TEXT,
  `requested_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

-- ----------------------------------------------------------
-- Hotel bookings
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `hotel_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `domain_origin`  VARCHAR(100)   NOT NULL DEFAULT '',
  `hotel_name`     VARCHAR(255)   NOT NULL DEFAULT '',
  `hotel_code`     VARCHAR(100)   NOT NULL DEFAULT '',
  `check_in`       DATE,
  `check_out`      DATE,
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hbd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_booking_itinerary_details` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference`   VARCHAR(100)  NOT NULL,
  `room_type`       VARCHAR(255)  NOT NULL DEFAULT '',
  `board_type`      VARCHAR(100)  NOT NULL DEFAULT '',
  `rooms`           TINYINT       NOT NULL DEFAULT 1,
  `nights`          TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_hbid_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_booking_pax_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  `is_lead`       TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_hbpd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_ref`    VARCHAR(100)  NOT NULL DEFAULT '',
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `cancel_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reason`        TEXT,
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hcd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Car bookings
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `car_booking_details` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_reference`  VARCHAR(100)   NOT NULL,
  `booking_status` VARCHAR(50)    NOT NULL DEFAULT 'pending',
  `pickup_loc`     VARCHAR(255)   NOT NULL DEFAULT '',
  `dropoff_loc`    VARCHAR(255)   NOT NULL DEFAULT '',
  `pickup_at`      DATETIME,
  `dropoff_at`     DATETIME,
  `total_fare`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `currency`       VARCHAR(10)    NOT NULL DEFAULT 'ZAR',
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
  `supplier`      VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_cbit_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_booking_pax_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `firstname`     VARCHAR(100)  NOT NULL DEFAULT '',
  `lastname`      VARCHAR(100)  NOT NULL DEFAULT '',
  `license_no`    VARCHAR(50)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_cbpd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `car_cancellation_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `cancel_status` VARCHAR(50)   NOT NULL DEFAULT 'requested',
  `cancel_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `requested_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ccd_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Insurance
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `insurance_details` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL,
  `policy_no`     VARCHAR(100)  NOT NULL DEFAULT '',
  `provider`      VARCHAR(100)  NOT NULL DEFAULT '',
  `amount`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status`        VARCHAR(50)   NOT NULL DEFAULT 'pending',
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ins_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Temporary bookings / session cache
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `temp_booking` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_key`   VARCHAR(100)  NOT NULL,
  `booking_type`  VARCHAR(50)   NOT NULL DEFAULT '',
  `data`          MEDIUMTEXT,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`    DATETIME      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tb_session` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Search history
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `search_history` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(100)  NOT NULL DEFAULT '',
  `search_type`  VARCHAR(50)   NOT NULL DEFAULT '',
  `params`       MEDIUMTEXT,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sh_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_flight_history` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`  VARCHAR(100)  NOT NULL DEFAULT '',
  `origin`      VARCHAR(10)   NOT NULL DEFAULT '',
  `destination` VARCHAR(10)   NOT NULL DEFAULT '',
  `dep_date`    DATE,
  `ret_date`    DATE,
  `adults`      TINYINT       NOT NULL DEFAULT 1,
  `children`    TINYINT       NOT NULL DEFAULT 0,
  `infants`     TINYINT       NOT NULL DEFAULT 0,
  `trip_type`   VARCHAR(20)   NOT NULL DEFAULT 'oneway',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sfh_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_hotel_history` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(100)  NOT NULL DEFAULT '',
  `destination`  VARCHAR(255)  NOT NULL DEFAULT '',
  `check_in`     DATE,
  `check_out`    DATE,
  `rooms`        TINYINT       NOT NULL DEFAULT 1,
  `adults`       TINYINT       NOT NULL DEFAULT 2,
  `children`     TINYINT       NOT NULL DEFAULT 0,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_shh_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_car_history` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(100)  NOT NULL DEFAULT '',
  `pickup_loc`   VARCHAR(255)  NOT NULL DEFAULT '',
  `dropoff_loc`  VARCHAR(255)  NOT NULL DEFAULT '',
  `pickup_at`    DATETIME,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sch_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Lookups & reference data
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `api_country_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(100)  NOT NULL,
  `country_code` VARCHAR(5)    NOT NULL,
  `iso2`         CHAR(2)       NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_acl_code` (`country_code`)
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
  PRIMARY KEY (`id`),
  KEY `idx_acityl_code` (`city_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `airport_code_list` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `airport_name` VARCHAR(255)  NOT NULL DEFAULT '',
  `iata_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `city_code`    VARCHAR(10)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_ial_iata` (`iata_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_code` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `hotel_name`  VARCHAR(255)  NOT NULL DEFAULT '',
  `hotel_code`  VARCHAR(100)  NOT NULL DEFAULT '',
  `city_code`   VARCHAR(20)   NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_hc_code` (`hotel_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hotel_image_url` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `hotel_code`  VARCHAR(100)  NOT NULL,
  `image_url`   TEXT          NOT NULL,
  `is_primary`  TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_hiu_code` (`hotel_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bus_stations_new` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `station_name` VARCHAR(255)  NOT NULL DEFAULT '',
  `station_code` VARCHAR(20)   NOT NULL DEFAULT '',
  `city`         VARCHAR(100)  NOT NULL DEFAULT '',
  `country_code` VARCHAR(5)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `country_code` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(100)  NOT NULL DEFAULT '',
  `iso2`         CHAR(2)       NOT NULL DEFAULT '',
  `phone_code`   VARCHAR(10)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_cc_iso` (`iso2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Logging & audit
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `transaction_log` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `app_reference` VARCHAR(100)  NOT NULL DEFAULT '',
  `log_type`      VARCHAR(50)   NOT NULL DEFAULT '',
  `log_message`   MEDIUMTEXT,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tl_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exception_logger` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `vertical`   VARCHAR(50)   NOT NULL DEFAULT '',
  `controller` VARCHAR(100)  NOT NULL DEFAULT '',
  `method`     VARCHAR(100)  NOT NULL DEFAULT '',
  `message`    MEDIUMTEXT,
  `trace`      MEDIUMTEXT,
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
  PRIMARY KEY (`id`),
  KEY `idx_pxl_ref` (`app_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Miscellaneous
-- ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_subscribtion` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255)  NOT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_es_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
