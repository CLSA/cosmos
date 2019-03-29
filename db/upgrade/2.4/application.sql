DROP PROCEDURE IF EXISTS patch_application;
DELIMITER //
CREATE PROCEDURE patch_application()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Adding application entry into application table" AS "";

    SET @sql = CONCAT(
      "INSERT IGNORE INTO ", @cenozo, ".application ",
      "( name, title, application_type_id, url, ",
        "version, cenozo, release_based, release_event_type_id, country, timezone ) ",
      "SELECT 'cosmos', 'Cosmos', application_type.id, REPLACE( app.url, 'mastodon', 'cosmos' ), ",
             "'2.2', app.cenozo, 0, NULL, 'Canada', 'Canada/Eastern' ",
      "FROM ", @cenozo, ".application_type, ",
               @cenozo, ".application AS app ",
      "WHERE application_type.name = 'cosmos' ",
      "AND app.name = 'mastodon'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_application();
DROP PROCEDURE IF EXISTS patch_application;
