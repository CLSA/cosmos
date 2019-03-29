SELECT "Creating new technician_stage_cache table" AS "";

CREATE TABLE IF NOT EXISTS technician_stage_cache (
  technician_id INT UNSIGNED NOT NULL,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  missing INT NOT NULL DEFAULT 0,
  contraindicated INT NOT NULL DEFAULT 0,
  skip INT NOT NULL DEFAULT 0,
  start_date DATE NOT NULL,
  stage_name VARCHAR(45) NOT NULL,
  PRIMARY KEY (technician_id),
  INDEX fk_technician_id (technician_id ASC),
  CONSTRAINT fk_technician_stage_cache_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
