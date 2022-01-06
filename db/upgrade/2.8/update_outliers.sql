SELECT "Creating new update_outliers procedure" AS "";

DROP procedure IF EXISTS update_outliers;
DELIMITER $$
CREATE PROCEDURE update_outliers()
BEGIN

  TRUNCATE outlier;

  INSERT INTO outlier( indicator_id, stage_id, create_timestamp, site_id, technician_id, date, type )
  SELECT indicator.id, stage.id, NULL, interview.site_id, stage.technician_id, interview.start_date, 
         IF( JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum, "low", "high" )
  FROM indicator
  JOIN stage ON indicator.stage_type_id = stage.stage_type_id AND (
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum OR
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) > indicator.maximum
  )
  JOIN interview on stage.interview_id = interview.id;

END$$
DELIMITER ;
