DROP PROCEDURE IF EXISTS patch_role_has_service;
DELIMITER //
CREATE PROCEDURE patch_role_has_service()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new role_has_service table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS role_has_service ( ",
        "role_id INT UNSIGNED NOT NULL, ",
        "service_id INT UNSIGNED NOT NULL, ",
        "update_timestamp TIMESTAMP NULL DEFAULT NULL, ",
        "create_timestamp TIMESTAMP NULL DEFAULT NULL, ",
        "PRIMARY KEY (role_id, service_id), ",
        "INDEX fk_role_id (role_id ASC), ",
        "INDEX fk_service_id (service_id ASC), ",
        "CONSTRAINT fk_role_has_service_role_id ",
          "FOREIGN KEY (role_id) ",
          "REFERENCES ", @cenozo, ".role (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_role_has_service_service_id ",
          "FOREIGN KEY (service_id) ",
          "REFERENCES service (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    -- populate table
    TRUNCATE role_has_service;

    -- administrator
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name = 'administrator' ",
      "AND service.restricted = 1" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    -- coordinator
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name = 'coordinator' ",
      "AND service.restricted = 1 ",
      "AND service.id NOT IN ( ",
        "SELECT id FROM service ",
        "WHERE subject IN( 'access', 'activity', 'application', 'indicator', 'report', ", 
                          "'stage_type', 'system_message', 'technician', 'user' ) ",
        "OR ( subject = 'report_restriction' AND method IN( 'DELETE', 'PATCH', 'POST' ) ) ",
        "OR ( subject = 'report_type' AND method IN( 'DELETE', 'PATCH', 'POST' ) ) ",
        "OR ( subject = 'setting' AND method = 'GET' ) ",
        "OR ( subject = 'site' AND method IN ( 'DELETE', 'POST' ) ) ",
      ")" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_role_has_service();
DROP PROCEDURE IF EXISTS patch_role_has_service;
