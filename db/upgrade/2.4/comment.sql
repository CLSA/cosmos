SELECT "Creating new comment table" AS "";

CREATE TABLE IF NOT EXISTS comment (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  stage_id INT UNSIGNED NOT NULL,
  type VARCHAR(45) NOT NULL,
  note VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX fk_stage_id (stage_id ASC),
  CONSTRAINT fk_comment_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
