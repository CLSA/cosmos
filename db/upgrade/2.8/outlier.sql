DROP PROCEDURE IF EXISTS patch_outlier;
DELIMITER //
CREATE PROCEDURE patch_outlier()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id"
    );

    SELECT "Recreating outlier table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS outlier ( ",
        "indicator_id INT UNSIGNED NOT NULL, ",
        "stage_id INT UNSIGNED NOT NULL, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "site_id INT UNSIGNED NOT NULL, ",
        "technician_id INT UNSIGNED NOT NULL, ",
        "date DATE NOT NULL, ",
        "type ENUM('low', 'high') NOT NULL, ",
        "PRIMARY KEY (indicator_id, stage_id), ",
        "INDEX fk_stage_id (stage_id ASC), ",
        "INDEX fk_indicator_id (indicator_id ASC), ",
        "INDEX fk_site_id (site_id ASC), ",
        "INDEX fk_technician_id (technician_id ASC), ",
        "INDEX dk_date (date ASC), ",
        "CONSTRAINT fk_outlier_indicator_id ",
          "FOREIGN KEY (indicator_id) ",
          "REFERENCES indicator (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_outlier_stage_id ",
          "FOREIGN KEY (stage_id) ",
          "REFERENCES stage (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_outlier_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_outlier_technician_id ",
          "FOREIGN KEY (technician_id) ",
          "REFERENCES technician (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE) ",
      "ENGINE = InnoDB"
    );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_outlier();
DROP PROCEDURE IF EXISTS patch_outlier;
