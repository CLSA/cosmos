DROP PROCEDURE IF EXISTS patch_stage_issue_note;
DELIMITER //
CREATE PROCEDURE patch_stage_issue_note()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id"
    );

    SELECT "Creating new stage_issue_note table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS stage_issue_note ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "stage_issue_id INT UNSIGNED NOT NULL, ",
        "user_id INT(10) UNSIGNED NOT NULL, ",
        "datetime DATETIME NOT NULL, ",
        "note MEDIUMTEXT NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_stage_issue_id (stage_issue_id ASC), ",
        "INDEX fk_user_id (user_id ASC), ",
        "UNIQUE INDEX uq_stage_issue_id_user_id_datetime (stage_issue_id ASC, user_id ASC, datetime ASC), ",
        "CONSTRAINT fk_stage_issue_note_stage_issue_id ",
          "FOREIGN KEY (stage_issue_id) ",
          "REFERENCES stage_issue (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_stage_issue_note_user_id ",
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

CALL patch_stage_issue_note();
DROP PROCEDURE IF EXISTS patch_stage_issue_note;
