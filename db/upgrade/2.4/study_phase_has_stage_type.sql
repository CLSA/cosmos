DROP PROCEDURE IF EXISTS patch_study_phase_has_stage_type;
DELIMITER //
CREATE PROCEDURE patch_study_phase_has_stage_type()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new study_phase_has_stage_type table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS study_phase_has_stage_type ( ",
        "study_phase_id INT UNSIGNED NOT NULL, ",
        "stage_type_id INT UNSIGNED NOT NULL, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "PRIMARY KEY (study_phase_id, stage_type_id), ",
        "INDEX fk_stage_type_id (stage_type_id ASC), ",
        "INDEX fk_study_phase_id (study_phase_id ASC), ",
        "CONSTRAINT fk_study_phase_has_stage_type_study_phase_id ",
          "FOREIGN KEY (study_phase_id) ",
          "REFERENCES ", @cenozo, ".study_phase (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_study_phase_has_stage_type_stage_type_id ",
          "FOREIGN KEY (stage_type_id) ",
          "REFERENCES stage_type (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_study_phase_has_stage_type();
DROP PROCEDURE IF EXISTS patch_study_phase_has_stage_type;
