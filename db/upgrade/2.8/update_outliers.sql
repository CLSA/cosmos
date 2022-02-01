SELECT "Creating new update_outliers procedure" AS "";

DROP procedure IF EXISTS update_outliers;
DELIMITER $$
CREATE PROCEDURE update_outliers()
BEGIN

  TRUNCATE outlier;

  INSERT INTO outlier( create_timestamp, stage_id, indicator_id, site_id, technician_id, date, type )
  SELECT NULL, stage.id, indicator.id, interview.site_id, stage.technician_id, interview.start_date, 
         IF( JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum, "low", "high" )
  FROM indicator
  JOIN stage ON indicator.stage_type_id = stage.stage_type_id
  JOIN interview on stage.interview_id = interview.id
  WHERE (
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum OR
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) > indicator.maximum
  );

  INSERT INTO outlier( create_timestamp, stage_id, indicator_id, site_id, technician_id, date, type )
  SELECT NULL, stage.id, NULL, interview.site_id, stage.technician_id, interview.start_date,
         IF( stage.duration < stage_type.duration_low, "low", "high" )
  FROM stage
  JOIN stage_type ON stage.stage_type_id = stage_type.id
  JOIN interview on stage.interview_id = interview.id
  WHERE ( stage.duration < stage_type.duration_low OR stage.duration > stage_type.duration_high );


END$$
DELIMITER ;
