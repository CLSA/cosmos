SELECT "Creating new technician_indicator_cache table" AS "";

CREATE TABLE IF NOT EXISTS technician_indicator_cache (
  technician_id INT UNSIGNED NOT NULL,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  par_under INT NOT NULL DEFAULT 0,
  par_within INT NOT NULL DEFAULT 0,
  par_above INT NOT NULL DEFAULT 0,
  start_date DATE NOT NULL,
  stage_name VARCHAR(45) NOT NULL,
  type VARCHAR(45) NOT NULL,
  side VARCHAR(45) NOT NULL,
  name VARCHAR(45) NOT NULL,
  cumulative_average FLOAT NOT NULL,
  running_average FLOAT NOT NULL,
  count FLOAT NOT NULL,
  PRIMARY KEY (technician_id),
  INDEX fk_technician_id (technician_id ASC),
  CONSTRAINT fk_technician_indicator_cache_technician_id
    FOREIGN KEY (technician_id)
    REFERENCES technician (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
