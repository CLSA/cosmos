SELECT "Creating new stage_has_indicator table" AS "";

CREATE TABLE IF NOT EXISTS stage_has_indicator (
  stage_id INT UNSIGNED NOT NULL,
  indicator_id INT UNSIGNED NOT NULL,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  PRIMARY KEY (stage_id, indicator_id),
  INDEX fk_indicator_id (indicator_id ASC),
  INDEX fk_stage_id (stage_id ASC),
  CONSTRAINT fk_stage_has_indicator_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_stage_has_indicator_indicator_id
    FOREIGN KEY (indicator_id)
    REFERENCES indicator (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
