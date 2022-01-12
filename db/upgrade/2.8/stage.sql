DROP PROCEDURE IF EXISTS patch_stage;
DELIMITER //
CREATE PROCEDURE patch_stage()
  BEGIN

    SELECT "Adding data column to stage table" AS "";

    SELECT COUNT(*) INTO @test
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
    AND table_name = "stage"
    AND column_name = "data";

    IF @test = 0 THEN
      ALTER TABLE stage ADD COLUMN data JSON NOT NULL CHECK( JSON_VALID( data ) ) AFTER duration;
    END IF;
  END //
DELIMITER ;

CALL patch_stage();
DROP PROCEDURE IF EXISTS patch_stage;
