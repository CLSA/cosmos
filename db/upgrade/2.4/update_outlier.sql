DROP procedure IF EXISTS update_outlier;
DELIMITER $$
CREATE PROCEDURE update_outlier(IN proc_indicator_id INT(10) UNSIGNED)
BEGIN

  DROP TEMPORARY TABLE IF EXISTS temp_cursor_table;

  SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

  SET @sql = CONCAT(
    "CREATE TEMPORARY TABLE temp_cursor_table ",
    "SELECT indicator.id, ",
           "CONCAT_WS( '_', study_phase.code, platform.name, stage_type.name, 'data' ) AS table_name, ",
           "indicator.name AS column_name ",
    "FROM indicator ",
    "JOIN stage_type ON indicator.stage_type_id = stage_type.id ",
    "JOIN platform ON stage_type.platform_id = platform.id ",
    "JOIN ", @cenozo, ".study_phase ON stage_type.study_phase_id = study_phase.id ",
    "WHERE indicator.id = ", proc_indicator_id, " ",
    "ORDER BY study_phase.code, platform.name, stage_type.name, indicator.name"
  );
  PREPARE statement FROM @sql;
  EXECUTE statement;
  DEALLOCATE PREPARE statement;

  BEGIN

    DECLARE indicator_id_val INT(10) UNSIGNED;
    DECLARE table_name_val VARCHAR(255);
    DECLARE column_name_val VARCHAR(255);

    DECLARE no_more_rows BOOLEAN;
    DECLARE loop_cntr INT DEFAULT 0;
    DECLARE num_rows INT DEFAULT 0;

    DECLARE the_cursor CURSOR FOR
    SELECT * FROM temp_cursor_table;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows = TRUE;

    OPEN the_cursor;
    SELECT FOUND_ROWS() INTO num_rows;

    the_loop: LOOP

      FETCH the_cursor
      INTO indicator_id_val, table_name_val, column_name_val;

      IF no_more_rows THEN
        CLOSE the_cursor;
        LEAVE the_loop;
      END IF;

      DELETE FROM outlier WHERE indicator_id = indicator_id_val;

      SET @sql = CONCAT(
        "INSERT INTO outlier( indicator_id, stage_id, create_timestamp, site_id, technician_id, date, type ) ",
        "SELECT indicator.id, ",
                table_name_val, ".stage_id, ",
                "NULL, ",
                "interview.site_id, ",
                "stage.technician_id, ",
                "interview.start_date, ",
                "IF( ", column_name_val, " < indicator.minimum, 'low', 'high' ) ",
        "FROM indicator, ", table_name_val, " ",
        "JOIN stage ON ", table_name_val, ".stage_id = stage.id ",
        "JOIN interview ON stage.interview_id = interview.id ",
        "WHERE indicator.id = ", indicator_id_val, " AND (",
          column_name_val, " < indicator.minimum OR ",
          column_name_val, " > indicator.maximum "
        ")"
      );
      PREPARE statement FROM @sql;
      EXECUTE statement;
      DEALLOCATE PREPARE statement;

      -- count the number of times looped
      SET loop_cntr = loop_cntr + 1;

    END LOOP the_loop;

  END;

  DROP TABLE IF EXISTS temp_cursor_table;

END$$
DELIMITER ;
