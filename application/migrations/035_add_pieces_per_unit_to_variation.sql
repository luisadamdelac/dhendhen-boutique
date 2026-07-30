-- A variation value like "1 Set (10 pcs)" previously only carried that
-- "10 pcs" as descriptive text inside variation_value — nothing about it was
-- structured, so selling 1 "Set" only ever deducted 1 unit from that
-- variant's own stock, the same as selling 1 "Sachet". pieces_per_unit lets
-- the admin declare the actual multiplier per value (default 1, so every
-- existing/simple variation is unaffected); a variant combination's
-- effective multiplier is the product of pieces_per_unit across all of its
-- axis values (see StockService::getVariantPiecesPerUnit()).

ALTER TABLE `product_variation_tbl`
  ADD COLUMN `pieces_per_unit` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `price_adjustment`;
