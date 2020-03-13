-- Patch to upgrade database to version 2.5

SET AUTOCOMMIT=0;

SOURCE drop_tables.sql
SOURCE opal_view.sql
SOURCE stage_type.sql
SOURCE interview.sql
SOURCE stage.sql
SOURCE indicator.sql
SOURCE outlier.sql

SOURCE update_outlier.sql
SOURCE update_outliers.sql

SOURCE update_version_number.sql

COMMIT;
