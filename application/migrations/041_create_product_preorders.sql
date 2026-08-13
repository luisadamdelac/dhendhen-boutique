-- A customer can express interest in a product no reseller has published
-- yet (Shop grid's "Pre Order" button) instead of hitting a dead end. When
-- a reseller later publishes that product, Inventory::publish() looks up
-- any unnotified rows here, notifies the reseller, and stamps
-- reseller_notified_at so the same pre-order isn't announced twice on a
-- later republish.

CREATE TABLE `product_preorders_tbl` (
  `preorder_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reseller_notified_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`preorder_id`),
  UNIQUE KEY `uniq_preorder_product_customer` (`product_id`, `customer_id`),
  KEY `idx_preorder_product` (`product_id`),
  CONSTRAINT `fk_preorder_product` FOREIGN KEY (`product_id`) REFERENCES `product_tbl` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_preorder_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_tbl` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
