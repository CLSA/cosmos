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
        "study_phase_id INT UNSIGNED NOT NULL, ",
        "stage_type_id INT UNSIGNED NOT NULL, ",
        "name VARCHAR(127) NOT NULL, ",
        "type ENUM('boolean', 'float', 'integer', 'string') NOT NULL, ",
        "minimum INT NULL, ",
        "maximum INT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_study_phase_id (study_phase_id ASC), ",
        "INDEX fk_stage_type_id (stage_type_id ASC), ",
        "UNIQUE INDEX uq_study_phase_id_stage_type_id_name (study_phase_id ASC, stage_type_id ASC, name ASC), ",
        "CONSTRAINT fk_indicator_study_phase_id ",
          "FOREIGN KEY (study_phase_id) ",
          "REFERENCES ", @cenozo, ".study_phase (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
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

-- carotid_intima
INSERT IGNORE INTO indicator( study_phase_id, stage_type_id, name, type )
SELECT study_phase.id, stage_type.id, col.name, col.type
FROM study_phase, stage_type, (
  SELECT "still_image_1_left" AS name, "integer" AS type UNION
  SELECT "still_image_1_right" AS name, "integer" AS type UNION
  SELECT "still_image_2_left" AS name, "integer" AS type UNION
  SELECT "still_image_2_right" AS name, "integer" AS type UNION
  SELECT "still_image_3_left" AS name, "integer" AS type UNION
  SELECT "still_image_3_right" AS name, "integer" AS type UNION
  SELECT "cineloop_1_left" AS name, "integer" AS type UNION
  SELECT "cineloop_1_right" AS name, "integer" AS type UNION
  SELECT "structured_report_1_left" AS name, "integer" AS type UNION
  SELECT "structured_report_1_right" AS name, "integer" AS type
) as col
WHERE study_phase.code = "f2"
AND stage_type.category = "DCS"
AND stage_type.name = "carotid_intima";

-- ecg
INSERT IGNORE INTO indicator( study_phase_id, stage_type_id, name, type )
SELECT study_phase.id, stage_type.id, col.name, col.type
FROM study_phase, stage_type, (
  SELECT "intrinsic_poor_quality" AS name, "integer" AS type UNION
  SELECT "xml_file_size" AS name, "integer" AS type
) as col
WHERE study_phase.code = "f2"
AND stage_type.category = "DCS"
AND stage_type.name = "ecg";
