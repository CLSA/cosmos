SELECT "Creating new platform table" AS "";

CREATE TABLE IF NOT EXISTS platform (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  name VARCHAR(45) NOT NULL,
  PRIMARY KEY (id))
ENGINE = InnoDB;

INSERT IGNORE INTO platform( name )
VALUES ( "inhome" ), ( "dcs" ), ( "dcs_home" ), ( "dcs_phone" );
