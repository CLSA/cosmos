SELECT "Creating new stage table" AS "";

CREATE TABLE IF NOT EXISTS stage (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  interview_id INT UNSIGNED NOT NULL,
  stage_type_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX fk_interview_id (interview_id ASC),
  INDEX fk_stage_type_id (stage_type_id ASC),
  UNIQUE INDEX uq_interview_id_stage_type_id (interview_id ASC, stage_type_id ASC),
  CONSTRAINT fk_stage_interview_id
    FOREIGN KEY (interview_id)
    REFERENCES interview (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_stage_type_id1
    FOREIGN KEY (stage_type_id)
    REFERENCES stage_type (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
