DROP PROCEDURE IF EXISTS patch_interview;
DELIMITER //
CREATE PROCEDURE patch_interview()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new interview table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS stage_type ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "study_phase_id INT UNSIGNED NOT NULL, ",
        "platform_id INT UNSIGNED NOT NULL, ",
        "name VARCHAR(45) NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_study_phase_id (study_phase_id ASC), ",
        "INDEX fk_platform_id (platform_id ASC), ",
        "UNIQUE INDEX uq_study_phase_id_platform_id_name (study_phase_id ASC, platform_id ASC, name ASC), ",
        "CONSTRAINT fk_stage_type_study_phase_id ",
          "FOREIGN KEY (study_phase_id) ",
          "REFERENCES ", @cenozo, ".study_phase (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_stage_type_platform_id ",
          "FOREIGN KEY (platform_id) ",
          "REFERENCES platform (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_interview();
DROP PROCEDURE IF EXISTS patch_interview;
