DROP PROCEDURE IF EXISTS patch_comment;
DELIMITER //
CREATE PROCEDURE patch_comment()
  BEGIN

    SELECT "Adding rank column to comment table" AS "";

    SELECT COUNT(*) INTO @test
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
    AND table_name = "comment"
    AND column_name = "rank";

    IF @test = 0 THEN
      ALTER TABLE comment
        ADD COLUMN rank INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER stage_id,
        ADD UNIQUE INDEX uq_stage_id_rank (stage_id ASC, rank ASC);
    END IF;
  END //
DELIMITER ;

CALL patch_comment();
DROP PROCEDURE IF EXISTS patch_comment;
