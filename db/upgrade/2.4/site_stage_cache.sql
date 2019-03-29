DROP PROCEDURE IF EXISTS patch_site_stage_cache;
DELIMITER //
CREATE PROCEDURE patch_site_stage_cache()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new site_stage_cache table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS site_stage_cache ( ",
        "site_id INT UNSIGNED NOT NULL, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "missing INT NOT NULL DEFAULT 0, ",
        "contraindicated INT NOT NULL DEFAULT 0, ",
        "skip INT NOT NULL DEFAULT 0, ",
        "start_date DATE NOT NULL, ",
        "stage_name VARCHAR(45) NOT NULL, ",
        "PRIMARY KEY (site_id), ",
        "INDEX fk_site_id (site_id ASC), ",
        "CONSTRAINT fk_site_stage_cache_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_site_stage_cache();
DROP PROCEDURE IF EXISTS patch_site_stage_cache;
