-- Hardening patch for Stripe payments (idempotency + audit trail)

CREATE TABLE IF NOT EXISTS `event_payments` (
  `id` CHAR(36) NOT NULL,
  `event_id` CHAR(36) NOT NULL,
  `payment_provider` VARCHAR(30) NOT NULL,
  `payment_reference` VARCHAR(120) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` CHAR(3) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'completed',
  `customer_email` VARCHAR(255) NULL,
  `customer_name` VARCHAR(255) NULL,
  `payment_method` VARCHAR(50) NULL,
  `paid_at` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `webhook_received_at` DATETIME NULL,
  `provider_event_id` VARCHAR(120) NULL,
  `webhook_payload` LONGTEXT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_event_payments_provider_reference` (`payment_provider`, `payment_reference`),
  KEY `idx_event_payments_event_id` (`event_id`),
  KEY `idx_event_payments_provider_event_id` (`provider_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `events`
  ADD COLUMN IF NOT EXISTS `payment_provider` VARCHAR(30) NULL AFTER `paid_until`,
  ADD COLUMN IF NOT EXISTS `payment_reference` VARCHAR(120) NULL AFTER `payment_provider`;
