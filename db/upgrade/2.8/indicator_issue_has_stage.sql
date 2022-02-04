SELECT "Creating new indicator_issue_has_stage table" AS "";

CREATE TABLE IF NOT EXISTS indicator_issue_has_stage (
  indicator_issue_id INT UNSIGNED NOT NULL,
  stage_id INT(10) UNSIGNED NOT NULL,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  PRIMARY KEY (indicator_issue_id, stage_id),
  INDEX fk_stage_id (stage_id ASC),
  INDEX fk_indicator_issue_id (indicator_issue_id ASC),
  UNIQUE INDEX uq_indicator_issue_id_stage_id (indicator_issue_id ASC, stage_id ASC),
  CONSTRAINT fk_indicator_issue_has_stage_indicator_issue_id
    FOREIGN KEY (indicator_issue_id)
    REFERENCES indicator_issue (id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT fk_indicator_issue_has_stage_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
