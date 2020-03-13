DROP procedure IF EXISTS update_outlier;
DELIMITER $$
CREATE PROCEDURE update_outlier(IN proc_indicator_id INT(10) UNSIGNED)
BEGIN

  DELETE FROM outlier WHERE indicator_id = indicator_id_val;

  INSERT INTO outlier( indicator_id, stage_id, create_timestamp, site_id, technician_id, date, type )
  SELECT indicator.id, stage.id, NULL, interview.site_id, stage.technician_id, interview.start_date,
         IF( JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum, "low", "high" )
  FROM indicator
  JOIN stage ON indicator.stage_type_id = stage.stage_type_id AND (
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) < indicator.minimum OR
    JSON_EXTRACT( stage.data, CONCAT( "$.", indicator.name ) ) > indicator.maximum
  )
  JOIN interview on stage.interview_id = interview.id
  WHERE indicator.id = indicator_id_val;

END$$
DELIMITER ;
