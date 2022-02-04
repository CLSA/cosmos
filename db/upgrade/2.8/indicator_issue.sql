SELECT "Creating new indicator_issue table" AS "";

CREATE TABLE IF NOT EXISTS indicator_issue (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  technician_id INT(10) UNSIGNED NOT NULL,
  indicator_id INT(10) UNSIGNED NOT NULL,
  date DATE NOT NULL,
  closed TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX fk_technician_id (technician_id ASC),
  INDEX fk_indicator_id (indicator_id ASC),
  UNIQUE INDEX uq_technician_id_indicator_id_date (technician_id ASC, indicator_id ASC, date ASC),
  CONSTRAINT fk_indicator_issue_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_indicator_issue_indicator_id
    FOREIGN KEY (indicator_id)
    REFERENCES indicator (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
