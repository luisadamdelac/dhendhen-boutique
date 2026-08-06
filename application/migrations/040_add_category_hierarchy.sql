-- Adds category > subcategory hierarchy so products can be sectioned
-- properly (e.g. Beauty vs Furniture vs Food no longer sit as flat,
-- unrelated siblings). Self-referencing parent_id on product_category
-- (NULL = top-level category); product_tbl gets an optional
-- subcategory_id pointing to a child category row. No FK constraints,
-- matching this schema's existing app-enforced-only convention for
-- category_id.

ALTER TABLE product_category
    ADD COLUMN parent_id INT NULL DEFAULT NULL AFTER category_id;

ALTER TABLE product_tbl
    ADD COLUMN subcategory_id INT NULL DEFAULT NULL AFTER category_id;
