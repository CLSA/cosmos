DROP PROCEDURE IF EXISTS patch_technician;
DELIMITER //
CREATE PROCEDURE patch_technician()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new technician table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS technician ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "site_id INT UNSIGNED NOT NULL, ",
        "name VARCHAR(45) NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_site_id (site_id ASC), ",
        "UNIQUE INDEX uq_site_id_name (site_id ASC, name ASC), ",
        "CONSTRAINT fk_technician_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_technician();
DROP PROCEDURE IF EXISTS patch_technician;
