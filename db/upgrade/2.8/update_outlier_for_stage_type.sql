SELECT "Creating new update_outlier_for_stage_type procedure" AS "";

DROP procedure IF EXISTS update_outlier_for_stage_type;
DELIMITER $$
CREATE PROCEDURE update_outlier_for_stage_type(IN proc_stage_type_id INT(10) UNSIGNED)
BEGIN

  DELETE FROM outlier
  WHERE stage_id IN ( SELECT id FROM stage WHERE stage_type_id = proc_stage_type_id )
  AND indicator_id IS NULL;

  INSERT INTO outlier( create_timestamp, stage_id, indicator_id, site_id, technician_id, date, type )
  SELECT NULL, stage.id, NULL, interview.site_id, stage.technician_id, interview.start_date,
         IF( stage.duration < stage_type.duration_low, "low", "high" )
  FROM stage
  JOIN stage_type on stage.stage_type_id = stage_type.id
  JOIN interview on stage.interview_id = interview.id
  WHERE stage_type.id = proc_stage_type_id
  AND ( stage.duration < stage_type.duration_low OR stage.duration > stage_type.duration_high );

END$$
DELIMITER ;
