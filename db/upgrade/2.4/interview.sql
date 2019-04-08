DROP PROCEDURE IF EXISTS patch_interview;
DELIMITER //
CREATE PROCEDURE patch_interview()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new interview table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS interview ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "site_id INT UNSIGNED NOT NULL, ",
        "start_date DATE NOT NULL, ",
        "participant_id INT UNSIGNED NOT NULL, ",
        "barcode VARCHAR(20) NOT NULL, ",
        "rank TINYINT UNSIGNED NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_site_id (site_id ASC), ",
        "INDEX fk_participant_id (participant_id ASC), ",
        "UNIQUE INDEX uq_site_id_participant_id_barcode_rank (site_id ASC, participant_id ASC, barcode ASC, rank ASC), ",
        "CONSTRAINT fk_interview_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE CASCADE ",
          "ON UPDATE CASCADE, ",
        "CONSTRAINT fk_interview_participant_id ",
          "FOREIGN KEY (participant_id) ",
          "REFERENCES ", @cenozo, ".participant (id) ",
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
