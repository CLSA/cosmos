DROP PROCEDURE IF EXISTS patch_application_has_site;
DELIMITER //
CREATE PROCEDURE patch_application_has_site()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SET @sql = CONCAT(
      "SELECT COUNT(*) INTO @total ",
      "FROM ", @cenozo, ".application_has_site ",
      "JOIN ", @cenozo, ".application ON application_has_site.application_id = application.id ",
      "WHERE application.name = 'cosmos'"
    );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    IF @total = 0 THEN
      SELECT "Adding sites to the new cosmos application" AS "";

      SET @sql = CONCAT(
        "CREATE TEMPORARY TABLE temp_add_sites ",
        "SELECT DISTINCT application_has_site.site_id ",
        "FROM ", @cenozo, ".application_type ",
        "JOIN ", @cenozo, ".application ON application_type.id = application.application_type_id ",
        "JOIN ", @cenozo, ".application_has_site ON application.id = application_has_site.application_id ",
        "WHERE application_type.name = 'beartooth'" );
      PREPARE statement FROM @sql;
      EXECUTE statement;
      DEALLOCATE PREPARE statement;

      SET @sql = CONCAT(
        "INSERT IGNORE INTO ", @cenozo, ".application_has_site( application_id, site_id ) ",
        "SELECT DISTINCT application.id, temp_add_sites.site_id ",
        "FROM ", @cenozo, ".application, temp_add_sites "
        "WHERE application.name = 'cosmos'" );
      PREPARE statement FROM @sql;
      EXECUTE statement;
      DEALLOCATE PREPARE statement;
    END IF;

  END //
DELIMITER ;

CALL patch_application_has_site();
DROP PROCEDURE IF EXISTS patch_application_has_site;
