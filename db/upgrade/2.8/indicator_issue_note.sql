DROP PROCEDURE IF EXISTS patch_indicator_issue_note;
DELIMITER //
CREATE PROCEDURE patch_indicator_issue_note()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id"
    );

    SELECT "Creating new indicator_issue_note table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS indicator_issue_note ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "indicator_issue_id INT UNSIGNED NOT NULL, ",
        "user_id INT(10) UNSIGNED NOT NULL, ",
        "datetime DATETIME NOT NULL, ",
        "note MEDIUMTEXT NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_indicator_issue_id (indicator_issue_id ASC), ",
        "INDEX fk_user_id (user_id ASC), ",
        "UNIQUE INDEX uq_indicator_issue_id_user_id_datetime (indicator_issue_id ASC, user_id ASC, datetime ASC), ",
        "CONSTRAINT fk_indicator_issue_note_indicator_issue_id ",
          "FOREIGN KEY (indicator_issue_id) ",
          "REFERENCES indicator_issue (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_indicator_issue_note_user_id ",
          "FOREIGN KEY (user_id) ",
          "REFERENCES ", @cenozo, ".user (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB"
    );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;
    
  END //
DELIMITER ;

CALL patch_indicator_issue_note();
DROP PROCEDURE IF EXISTS patch_indicator_issue_note;
