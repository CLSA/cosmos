SELECT "Creating new indicator table" AS "";

CREATE TABLE IF NOT EXISTS indicator (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  name VARCHAR(45) NOT NULL,
  type ENUM('integer', 'float', 'boolean') NOT NULL,
  value FLOAT NULL DEFAULT NULL,
  side ENUM('left', 'right', 'none') NULL DEFAULT 'none',
  par_min FLOAT NULL DEFAULT NULL,
  par_max FLOAT NULL DEFAULT NULL,
  start_date DATE NULL DEFAULT NULL,
  end_date DATE NULL DEFAULT NULL,
  PRIMARY KEY (id))
ENGINE = InnoDB;
