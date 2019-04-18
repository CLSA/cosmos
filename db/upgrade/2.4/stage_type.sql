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
        "UNIQUE INDEX uq_category_name (name ASC), ",
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

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "inhome_1" UNION
      SELECT "inhome_2" UNION
      SELECT "inhome_3" UNION
      SELECT "inhome_cognition_recording" UNION
      SELECT "inhome_conclusion_qnaire" UNION
      SELECT "inhome_id" UNION
      SELECT "inhome_scoring" UNION
      SELECT "interview"    
    ) AS stage
    WHERE study_phase.code = "bl"
    AND platform.name = "inhome";

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "inhome_1" UNION
      SELECT "inhome_2" UNION
      SELECT "inhome_3" UNION
      SELECT "inhome_4" UNION
      SELECT "inhome_cognition_recording" UNION
      SELECT "inhome_conclusion_qnaire" UNION
      SELECT "interview"
    ) AS stage
    WHERE study_phase.code = "f1"
    AND platform.name = "inhome";

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "inhome_1" UNION
      SELECT "inhome_2" UNION
      SELECT "inhome_3" UNION
      SELECT "inhome_4" UNION
      SELECT "inhome_cognition_recording" UNION
      SELECT "inhome_conclusion_qnaire" UNION
      SELECT "interview"
    ) AS stage
    WHERE study_phase.code = "f2"
    AND platform.name = "inhome";

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "auxiliary" UNION
      SELECT "blood" UNION
      SELECT "blood_pressure" UNION
      SELECT "carotid_intima" UNION
      SELECT "chair_rise" UNION
      SELECT "cognitive_test" UNION
      SELECT "conclusion_qnaire" UNION
      SELECT "contraindication_qnaire" UNION
      SELECT "dexa" UNION
      SELECT "dexa_forearm" UNION
      SELECT "dexa_hip" UNION
      SELECT "dexa_lateral" UNION
      SELECT "dexa_qnaire" UNION
      SELECT "dexa_whole_body" UNION
      SELECT "disease_qnaire" UNION
      SELECT "ecg" UNION
      SELECT "event_pmt" UNION
      SELECT "functional_status" UNION
      SELECT "grip_strength" UNION
      SELECT "hearing" UNION
      SELECT "hips_waist" UNION
      SELECT "interview" UNION
      SELECT "neuro_scoring" UNION
      SELECT "retinal_scan" UNION
      SELECT "sitting_height" UNION
      SELECT "spirometry" UNION
      SELECT "standing_balance" UNION
      SELECT "standing_height" UNION
      SELECT "stroop_fas" UNION
      SELECT "time_based_pmt" UNION
      SELECT "tonometer" UNION
      SELECT "tug" UNION
      SELECT "urine" UNION
      SELECT "vision_acuity" UNION
      SELECT "walk" UNION
      SELECT "weight" UNION
    ) AS stage
    WHERE study_phase.code = "f2"
    AND platform.name = "inhome";

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "auxiliary" UNION
      SELECT "blood" UNION
      SELECT "blood_pressure" UNION
      SELECT "carotid_intima" UNION
      SELECT "chair_rise" UNION
      SELECT "cognitive_test" UNION
      SELECT "conclusion_qnaire" UNION
      SELECT "contraindication_qnaire" UNION
      SELECT "deviation_aecrf" UNION
      SELECT "dexa" UNION
      SELECT "dexa_forearm" UNION
      SELECT "dexa_hip" UNION
      SELECT "dexa_lateral" UNION
      SELECT "dexa_qnaire" UNION
      SELECT "dexa_spine" UNION
      SELECT "dexa_whole_body" UNION
      SELECT "disease_qnaire" UNION
      SELECT "ecg" UNION
      SELECT "event_pmt" UNION
      SELECT "frax" UNION
      SELECT "functional_status" UNION
      SELECT "general_health" UNION
      SELECT "grip_strength" UNION
      SELECT "hearing" UNION
      SELECT "hips_waist" UNION
      SELECT "inhome_qa" UNION
      SELECT "interview" UNION
      SELECT "neuro_scoring" UNION
      SELECT "osea" UNION
      SELECT "retinal_scan_left" UNION
      SELECT "retinal_scan_right" UNION
      SELECT "spirometry" UNION
      SELECT "standing_balance" UNION
      SELECT "standing_height" UNION
      SELECT "stroop_fas" UNION
      SELECT "time_based_pmt" UNION
      SELECT "tonometer" UNION
      SELECT "tug" UNION
      SELECT "urine" UNION
      SELECT "vision_acuity" UNION
      SELECT "walk" UNION
      SELECT "weight" UNION
    ) AS stage
    WHERE study_phase.code = "f2"
    AND platform.name = "inhome";

    INSERT IGNORE INTO stage_type( study_phase_id, platform_id, name )
    SELECT study_phase.id, platform.id, stage.name
    FROM study_phase, platform, (
      SELECT "auxiliary" UNION
      SELECT "blood" UNION
      SELECT "blood_pressure" UNION
      SELECT "carotid_intima" UNION
      SELECT "cdtt" UNION
      SELECT "chair_rise" UNION
      SELECT "cognitive_test" UNION
      SELECT "conclusion_qnaire" UNION
      SELECT "contraindication_qnaire" UNION
      SELECT "deviation_aecrf" UNION
      SELECT "dexa" UNION
      SELECT "dexa_forearm" UNION
      SELECT "dexa_hip" UNION
      SELECT "dexa_lateral" UNION
      SELECT "dexa_qnaire" UNION
      SELECT "dexa_spine" UNION
      SELECT "dexa_whole_body" UNION
      SELECT "disease_qnaire" UNION
      SELECT "ecg" UNION
      SELECT "event_pmt" UNION
      SELECT "frax" UNION
      SELECT "general_health" UNION
      SELECT "grip_strength" UNION
      SELECT "hearing" UNION
      SELECT "hips_waist" UNION
      SELECT "inhome_qa" UNION
      SELECT "interview" UNION
      SELECT "neuro_scoring" UNION
      SELECT "osipv" UNION
      SELECT "retinal_scan_left" UNION
      SELECT "retinal_scan_right" UNION
      SELECT "sitting_height" UNION
      SELECT "social_network" UNION
      SELECT "spirometry" UNION
      SELECT "standing_balance" UNION
      SELECT "standing_height" UNION
      SELECT "stroop_fas" UNION
      SELECT "time_based_pmt" UNION
      SELECT "tonometer" UNION
      SELECT "tug" UNION
      SELECT "urine" UNION
      SELECT "vision_acuity" UNION
      SELECT "walk" UNION
      SELECT "weight" UNION
    ) AS stage
    WHERE study_phase.code = "f2"
    AND platform.name = "inhome";

  END //
DELIMITER ;

CALL patch_interview();
DROP PROCEDURE IF EXISTS patch_interview;
