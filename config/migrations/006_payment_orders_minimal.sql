-- 藍新 MPG 最小單元：一次付清訂單（與定期定額分表）

CREATE TABLE IF NOT EXISTS `payment_orders` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `merchant_order_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amt` int(10) UNSIGNED NOT NULL COMMENT '金額 TWD 整數',
  `status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_merchant_order` (`merchant_order_no`),
  KEY `idx_payment_user` (`user_id`),
  CONSTRAINT `fk_payment_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
