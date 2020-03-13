SELECT "Recreating the stage_type table" AS "";

CREATE TABLE IF NOT EXISTS stage_type (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  opal_view_id INT UNSIGNED NOT NULL,
  name VARCHAR(45) NOT NULL,
  duration_low FLOAT NOT NULL DEFAULT 0,
  duration_high FLOAT NOT NULL DEFAULT 3600,
  PRIMARY KEY (id),
  INDEX fk_opal_view_id (opal_view_id ASC),
  UNIQUE INDEX uq_opal_view_id_name (opal_view_id ASC, name ASC),
  CONSTRAINT fk_stage_type_opal_view_id
    FOREIGN KEY (opal_view_id)
    REFERENCES opal_view (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
