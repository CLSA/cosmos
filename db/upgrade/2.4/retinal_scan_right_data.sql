SELECT "Creating new retinal_scan_right_data table" AS "";

CREATE TABLE IF NOT EXISTS retinal_scan_right_data (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  stage_id INT UNSIGNED NOT NULL,
  technician_id INT UNSIGNED NOT NULL,
  contraindicated TINYINT(1) NOT NULL,
  missing TINYINT(1) NOT NULL,
  skip VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX fk_stage_id (stage_id ASC),
  INDEX fk_technician_id (technician_id ASC),
  UNIQUE INDEX uq_stage_id_technician_id (stage_id ASC, technician_id ASC),
  CONSTRAINT fk_retinal_scan_right_data_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_retinal_scan_right_data_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
