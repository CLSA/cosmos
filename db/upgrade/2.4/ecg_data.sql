SELECT "Creating new ecg_data table" AS "";

CREATE TABLE IF NOT EXISTS ecg_data (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  stage_id INT UNSIGNED NOT NULL,
  intrinsic_poor_quality INT NULL DEFAULT NULL,
  xml_file_size INT NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX fk_stage_id (stage_id ASC),
  UNIQUE INDEX uq_stage_id (stage_id ASC),
  CONSTRAINT fk_ecg_data_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;