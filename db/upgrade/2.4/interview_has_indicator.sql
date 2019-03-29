SELECT "Creating new interview_has_indicator table" AS "";

CREATE TABLE IF NOT EXISTS interview_has_indicator (
  interview_id INT UNSIGNED NOT NULL,
  indicator_id INT UNSIGNED NOT NULL,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  PRIMARY KEY (interview_id, indicator_id),
  INDEX fk_indicator_id (indicator_id ASC),
  INDEX fk_interview_id (interview_id ASC),
  CONSTRAINT fk_interview_has_indicator_interview_id
    FOREIGN KEY (interview_id)
    REFERENCES interview (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_interview_has_indicator_indicator_id
    FOREIGN KEY (indicator_id)
    REFERENCES indicator (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
