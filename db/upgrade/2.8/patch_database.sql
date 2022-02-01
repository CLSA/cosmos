-- Patch to upgrade database to version 2.8

SET AUTOCOMMIT=0;

SOURCE table_character_sets.sql

SOURCE outlier.sql
SOURCE opal_view.sql
SOURCE stage_type.sql
SOURCE stage.sql
SOURCE update_outlier.sql
SOURCE update_outlier_for_indicator.sql
SOURCE update_outlier_for_stage_type.sql
SOURCE update_outliers.sql
SOURCE stage_issue.sql
SOURCE stage_issue_has_stage.sql
SOURCE stage_issue_note.sql
SOURCE service.sql
SOURCE role_has_service.sql
CALL update_outliers();

SOURCE update_version_number.sql

COMMIT;
