-- Patch to upgrade database to version 2.8

SET AUTOCOMMIT=0;

SOURCE table_character_sets.sql

SOURCE opal_view.sql
SOURCE stage_type.sql
SOURCE stage.sql
SOURCE update_outlier.sql
SOURCE update_outliers.sql
SOURCE outlier.sql

SOURCE update_version_number.sql

COMMIT;
