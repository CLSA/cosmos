DROP PROCEDURE IF EXISTS patch_stage_type;
DELIMITER //
CREATE PROCEDURE patch_stage_type()
  BEGIN

    SELECT "Replacing study_phase_id and platform_id columns in stage_type table" AS "";

    SELECT COUNT(*) INTO @test
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
    AND table_name = "stage_type"
    AND column_name = "opal_view_id";

    IF @test = 0 THEN
      ALTER TABLE stage_type
        ADD COLUMN opal_view_id int(10) unsigned NOT NULL AFTER create_timestamp,
        ADD INDEX fk_opal_view_id (opal_view_id);

      UPDATE stage_type
      JOIN opal_view USING( study_phase_id, platform_id )
      SET stage_type.opal_view_id = opal_view.id;

      ALTER TABLE stage_type
        ADD UNIQUE INDEX uq_opal_view_id_name (opal_view_id, name),
        ADD CONSTRAINT fk_stage_type_opal_view_id FOREIGN KEY (opal_view_id)
        REFERENCES opal_view (id) ON DELETE CASCADE ON UPDATE NO ACTION;
    END IF;

    SELECT COUNT(*) INTO @test
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
    AND table_name = "stage_type"
    AND column_name = "study_phase_id";

    IF @test = 1 THEN
      ALTER TABLE stage_type
        DROP INDEX uq_study_phase_id_platform_id_name,
        DROP CONSTRAINT fk_stage_type_study_phase_id,
        DROP CONSTRAINT fk_stage_type_platform_id,
        DROP INDEX fk_study_phase_id,
        DROP INDEX fk_platform_id,
        DROP COLUMN study_phase_id,
        DROP COLUMN platform_id;
    END IF;
  END //
DELIMITER ;

CALL patch_stage_type();
DROP PROCEDURE IF EXISTS patch_stage_type;
