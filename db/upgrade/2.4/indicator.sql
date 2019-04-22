DROP PROCEDURE IF EXISTS patch_indicator;
DELIMITER //
CREATE PROCEDURE patch_indicator()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new indicator table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS indicator ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "stage_type_id INT UNSIGNED NOT NULL, ",
        "name VARCHAR(127) NOT NULL, ",
        "type ENUM('boolean', 'float', 'integer', 'string') NOT NULL, ",
        "minimum INT NULL, ",
        "maximum INT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_stage_type_id (stage_type_id ASC), ",
        "UNIQUE INDEX uq_stage_type_id_name (stage_type_id ASC, name ASC), ",
        "CONSTRAINT fk_indicator_stage_type_id ",
          "FOREIGN KEY (stage_type_id) ",
          "REFERENCES stage_type (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_indicator();
DROP PROCEDURE IF EXISTS patch_indicator;
