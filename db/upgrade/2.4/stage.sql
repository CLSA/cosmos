SELECT "Creating new stage table" AS "";

CREATE TABLE IF NOT EXISTS stage (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  interview_id INT UNSIGNED NOT NULL,
  technician_id INT UNSIGNED NULL DEFAULT NULL,
  name VARCHAR(45) NOT NULL,
  missing TINYINT(1) NULL DEFAULT NULL,
  contraindicated TINYINT(1) NULL DEFAULT NULL,
  comment VARCHAR(1028) NULL DEFAULT NULL,
  skip VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX fk_interview_id (interview_id ASC),
  INDEX fk_technician_id (technician_id ASC),
  UNIQUE INDEX uq_interview_id_technician_id_name (interview_id ASC, technician_id ASC, name ASC),
  CONSTRAINT fk_stage_interview_id
    FOREIGN KEY (interview_id)
    REFERENCES interview (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_stage_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
