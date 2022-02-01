SELECT "Creating new update_outlier_for_indicator procedure" AS "";

DROP procedure IF EXISTS update_outlier_for_indicator;
DELIMITER $$
CREATE PROCEDURE update_outlier_for_indicator(IN proc_indicator_id INT(10) UNSIGNED)
BEGIN

  DELETE FROM outlier WHERE indicator_id = proc_indicator_id;

  INSERT INTO outlier( create_timestamp, stage_id, indicator_id, site_id, technician_id, date, type )
  SELECT NULL, stage.id, indicator.id, interview.site_id, stage.technician_id, interview.start_date,
         IF( JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum, "low", "high" )
  FROM indicator
  JOIN stage ON indicator.stage_type_id = stage.stage_type_id
  JOIN interview on stage.interview_id = interview.id
  WHERE indicator.id = proc_indicator_id
  AND (
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum OR
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) > indicator.maximum
  );

END$$
DELIMITER ;
