DROP TABLE IF EXISTS wcf1_tour_point;
CREATE TABLE wcf1_tour_point (
	tourPointID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	tourID INT(10) NOT NULL,
	step MEDIUMINT NOT NULL UNIQUE,
	elementName VARCHAR(255) NOT NULL,
	pointText MEDIUMTEXT NOT NULL,
	position VARCHAR(255) NOT NULL
);
DROP TABLE IF EXISTS wcf1_tour;
CREATE TABLE wcf1_tour (
	tourID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	tourName VARCHAR(255) NOT NULL UNIQUE
);
