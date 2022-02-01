SELECT "Creating new stage_issue table" AS "";

CREATE TABLE IF NOT EXISTS stage_issue (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  technician_id INT(10) UNSIGNED NOT NULL,
  stage_type_id INT(10) UNSIGNED NOT NULL,
  date DATE NOT NULL,
  closed TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX fk_technician_id (technician_id ASC),
  INDEX fk_stage_type_id (stage_type_id ASC),
  UNIQUE INDEX uq_technician_id_stage_type_id_date (technician_id ASC, stage_type_id ASC, date ASC),
  CONSTRAINT fk_stage_issue_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_stage_issue_stage_type_id
    FOREIGN KEY (stage_type_id)
    REFERENCES stage_type (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
