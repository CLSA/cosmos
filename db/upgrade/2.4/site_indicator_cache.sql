DROP PROCEDURE IF EXISTS patch_site_indicator_cache;
DELIMITER //
CREATE PROCEDURE patch_site_indicator_cache()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new site_indicator_cache table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS site_indicator_cache ( ",
        "site_id INT UNSIGNED NOT NULL, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "par_under INT NOT NULL DEFAULT 0, ",
        "par_within INT NOT NULL DEFAULT 0, ",
        "par_above INT NOT NULL DEFAULT 0, ",
        "start_date DATE NOT NULL, ",
        "stage_name VARCHAR(45) NOT NULL, ",
        "type VARCHAR(45) NOT NULL, ",
        "side VARCHAR(45) NOT NULL, ",
        "name VARCHAR(45) NOT NULL, ",
        "cumulative_average FLOAT NOT NULL, ",
        "running_average FLOAT NOT NULL, ",
        "count FLOAT NOT NULL, ",
        "PRIMARY KEY (site_id), ",
        "INDEX fk_site_id (site_id ASC), ",
        "CONSTRAINT fk_site_indicator_cache_site_id ",
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

CALL patch_site_indicator_cache();
DROP PROCEDURE IF EXISTS patch_site_indicator_cache;
