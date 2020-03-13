DROP PROCEDURE IF EXISTS patch_interview;
DELIMITER //
CREATE PROCEDURE patch_interview()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Recreating interview table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS interview ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "participant_id INT UNSIGNED NOT NULL, ",
        "study_phase_id INT UNSIGNED NOT NULL, ",
        "platform_id INT UNSIGNED NOT NULL, ",
        "site_id INT UNSIGNED NOT NULL, ",
        "start_date DATE NOT NULL, ",
        "barcode VARCHAR(20) NOT NULL, ",
        "duration INT UNSIGNED NOT NULL, ",
        "total_stage_duration INT UNSIGNED NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_site_id (site_id ASC), ",
        "INDEX fk_participant_id (participant_id ASC), ",
        "INDEX fk_study_phase_id (study_phase_id ASC), ",
        "INDEX fk_platform_id (platform_id ASC), ",
        "UNIQUE INDEX uq_participant_id_study_phase_id_platform_id (participant_id ASC, study_phase_id ASC, platform_id ASC), ",
        "CONSTRAINT fk_interview_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_interview_participant_id ",
          "FOREIGN KEY (participant_id) ",
          "REFERENCES ", @cenozo, ".participant (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_interview_study_phase_id ",
          "FOREIGN KEY (study_phase_id) ",
          "REFERENCES ", @cenozo, ".study_phase (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_interview_platform_id ",
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
