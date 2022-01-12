DROP PROCEDURE IF EXISTS patch_opal_view;
DELIMITER //
CREATE PROCEDURE patch_opal_view()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id"
    );

    SELECT "Creating new opal_view table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS opal_view ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "update_timestamp TIMESTAMP NOT NULL, ",
        "create_timestamp TIMESTAMP NOT NULL, ",
        "platform_id INT UNSIGNED NOT NULL, ",
        "study_phase_id INT UNSIGNED NOT NULL, ",
        "total INT UNSIGNED NOT NULL DEFAULT 0, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_opal_view_platform_id (platform_id ASC), ",
        "INDEX fk_study_phase_id (study_phase_id ASC), ",
        "UNIQUE INDEX `uq_study_phase_id_platform_id` (`study_phase_id` ASC, `platform_id` ASC), ",
        "CONSTRAINT fk_opal_view_platform_id ",
          "FOREIGN KEY (platform_id) ",
          "REFERENCES platform (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_opal_view_study_phase_id ",
          "FOREIGN KEY (study_phase_id) ",
          "REFERENCES ", @cenozo, ".study_phase (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB"
    );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;
    
    -- NOTE: dcs_home and dcs_phone do not have baseline views
    SET @sql = CONCAT(
      "INSERT IGNORE INTO opal_view( platform_id, study_phase_id, total ) ",
      "SELECT platform.id, study_phase.id, 0 ",
      "FROM platform, ", @cenozo, ".study_phase ",
      "WHERE NOT( platform.name = 'dcs_home' OR platform.name = 'dcs_phone' ) OR study_phase.code != 'bl' ",
      "ORDER BY platform.name, study_phase.rank"
    );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_opal_view();
DROP PROCEDURE IF EXISTS patch_opal_view;
