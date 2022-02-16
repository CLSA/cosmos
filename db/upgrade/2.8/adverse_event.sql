SELECT "Creating new adverse_event table" AS "";

CREATE TABLE IF NOT EXISTS adverse_event (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  stage_id INT(10) UNSIGNED NOT NULL,
  type VARCHAR(255) NULL DEFAULT NULL,
  followup VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX fk_stage_id (stage_id ASC),
  UNIQUE INDEX uq_stage_id (stage_id ASC),
  CONSTRAINT fk_adverse_event_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
