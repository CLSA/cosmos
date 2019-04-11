SELECT "Creating new carotid_intima_data table" AS "";

CREATE TABLE IF NOT EXISTS carotid_intima_data (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  stage_id INT UNSIGNED NOT NULL,
  still_image_1_left INT NULL DEFAULT NULL,
  still_image_1_right INT NULL DEFAULT NULL,
  still_image_2_left INT NULL DEFAULT NULL,
  still_image_2_right INT NULL DEFAULT NULL,
  still_image_3_left INT NULL DEFAULT NULL,
  still_image_3_right INT NULL DEFAULT NULL,
  cineloop_1_left INT NULL DEFAULT NULL,
  cineloop_1_right INT NULL DEFAULT NULL,
  structured_report_1_left INT NULL DEFAULT NULL,
  structured_report_1_right INT NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX fk_stage_id (stage_id ASC),
  UNIQUE INDEX uq_stage_id (stage_id ASC),
  CONSTRAINT fk_carotid_intima_data_stage_id
    FOREIGN KEY (stage_id)
    REFERENCES stage (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
