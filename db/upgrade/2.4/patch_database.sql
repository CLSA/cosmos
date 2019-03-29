-- Patch to upgrade database to version 2.4

SET AUTOCOMMIT=0;

SOURCE application_type.sql
SOURCE application_type_has_role.sql
SOURCE application.sql
SOURCE application_has_site.sql

SOURCE access.sql
SOURCE service.sql
SOURCE role_has_service.sql
SOURCE setting.sql
SOURCE writelog.sql

SOURCE technician.sql
SOURCE interview.sql
SOURCE stage.sql
SOURCE indicator.sql
SOURCE stage_has_indicator.sql
SOURCE site_stage_cache.sql
SOURCE technician_stage_cache.sql
SOURCE technician_indicator_cache.sql
SOURCE site_indicator_cache.sql
SOURCE interview_has_indicator.sql

SOURCE update_version_number.sql

COMMIT;

