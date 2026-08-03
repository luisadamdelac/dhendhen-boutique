-- Walk-in (in-store, cash-register style) sales — staff/admin sell directly
-- to a customer standing at the counter. These never go through the
-- customer-facing checkout (no delivery address, no online payment proof,
-- no reseller commission — it's a direct sale by the store itself), so they
-- get their own minimal pair of tables instead of forcing fake data through
-- order_tbl/order_details (which require a real customer_id + reseller_id).
--
-- Stock is still deducted through the normal StockService ledger
-- (inventory_batches/inventory_movements), and these totals are folded into
-- the admin/staff dashboards' Total Sales / Total Orders stats, so walk-in
-- revenue isn't invisible in reporting.

CREATE TABLE `walkin_sale_tbl` (
  `walkin_sale_id` INT(11) NOT NULL AUTO_INCREMENT,
  `sale_number` VARCHAR(20) NOT NULL,
  `branch_id` INT(11) NOT NULL,
  `recorded_by_role` ENUM('admin','staff') NOT NULL,
  `recorded_by_id` INT(11) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash','gcash') NOT NULL DEFAULT 'cash',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`walkin_sale_id`),
  KEY `idx_walkin_branch` (`branch_id`),
  KEY `idx_walkin_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `walkin_sale_details_tbl` (
  `walkin_sale_detail_id` INT(11) NOT NULL AUTO_INCREMENT,
  `walkin_sale_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `variation_id` INT(11) NULL,
  `variation_label` VARCHAR(150) NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`walkin_sale_detail_id`),
  KEY `idx_wsd_sale` (`walkin_sale_id`),
  KEY `idx_wsd_product` (`product_id`),
  CONSTRAINT `fk_wsd_sale` FOREIGN KEY (`walkin_sale_id`) REFERENCES `walkin_sale_tbl` (`walkin_sale_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
