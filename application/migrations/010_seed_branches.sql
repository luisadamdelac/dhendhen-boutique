-- Migration: Seed default branches
-- Purpose: dropsell_db.sql ships with zero rows in `branches`, so the
--          per-branch inventory feature has nothing to assign products to.
--          Seeds two starter branches (rename via Admin > Branches).

INSERT INTO `branches` (`branch_name`, `city`, `status`)
SELECT 'Main Branch', 'Calapan City', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `branches`);

INSERT INTO `branches` (`branch_name`, `city`, `status`)
SELECT 'Branch 2', 'Calapan City', 'active'
WHERE (SELECT COUNT(*) FROM `branches`) < 2;
