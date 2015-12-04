USE SvenDatabase;
DROP TABLE IF EXISTS hours_work;
DROP TABLE IF EXISTS course_per_period;
DROP TABLE IF EXISTS hours_extra;
DROP TABLE IF EXISTS `type`;
DROP TABLE IF EXISTS employment;
DROP TABLE IF EXISTS person;
DROP TABLE IF EXISTS course;


CREATE TABLE person(
	id INT NOT NULL AUTO_INCREMENT,
	firstname VARCHAR(40) NOT NULL,
	lastname VARCHAR(40) NOT NULL,
	`sign` CHAR(4) UNIQUE NOT NULL,
	password VARCHAR(64) NOT NULL,
	`type` ENUM('user', 'organizer', 'superadmin') DEFAULT 'user' NOT NULL,
	datelastchange TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL, -- time for a persons last change
	datecreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, -- time when the person was created
	auth_key VARCHAR(64),
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE employment(
	percent DOUBLE, -- percent a person works per year, 100, 75, 50 etc.
	`year` INT NOT NULL,
	allocated_time INT,
	notification TEXT,
	datelastchange TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL, -- time for an employments last change
	id_person INT NOT NULL, -- to get the dude
	FOREIGN KEY(id_person) REFERENCES person(id),
	PRIMARY KEY(`year`, id_person) 
) ENGINE=InnoDB;

CREATE TABLE course(
	id INT NOT NULL AUTO_INCREMENT,
	code VARCHAR(32) UNIQUE NOT NULL, -- course code
	name TEXT NOT NULL, -- course name
	mainfield VARCHAR(32) NOT NULL, -- ex. DA, DV, KV, IS
	credits DOUBLE NOT NULL, -- course credits
	level VARCHAR(16) NOT NULL, -- course level
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE `type`(
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(40) NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE hours_extra(
	id INT NOT NULL AUTO_INCREMENT,
	id_person INT NOT NULL, -- to get the dude
	hours INT NOT NULL,
	title VARCHAR(40),
	`year` INT NOT NULL,
	description TEXT,
	id_type_name INT NOT NULL, -- to get the type
	display_area ENUM ('UpperField','LowerField') NOT NULL, #-- to get where to put the information
	FOREIGN KEY(id_person) REFERENCES person(id),
	FOREIGN KEY(id_type_name) REFERENCES `type`(id),
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE course_per_period(
	id INT NOT NULL AUTO_INCREMENT,
	start_period INT NOT NULL,
	end_period INT NOT NULL,
	year INT NOT NULL,
	speed INT NOT NULL, -- course speed ex. 100, 50
	expected_nr_of_students INT NOT NULL,
	nr_of_students INT,
	budget INT NOT NULL,
	id_examinator INT NOT NULL,
	id_course_admin INT NOT NULL,
	id_course INT NOT NULL,
	FOREIGN KEY(id_examinator) REFERENCES person(id),
	FOREIGN KEY(id_course_admin) REFERENCES person(id),
	FOREIGN KEY(id_course) REFERENCES course(id),
	PRIMARY KEY(id),
	UNIQUE(id_course, start_period, end_period, year)
) ENGINE=InnoDB;

CREATE TABLE hours_work(
	id_course_per_period INT NOT NULL,
	id_person INT NOT NULL,
	hours INT NOT NULL,
	description TEXT,
	color ENUM('green', 'orange', 'red', 'none') NOT NULL DEFAULT 'none',
	FOREIGN KEY(id_course_per_period) REFERENCES course_per_period(id),
	FOREIGN KEY(id_person) REFERENCES person(id),
	PRIMARY KEY(id_person, id_course_per_period)
) ENGINE=InnoDB;

-- Triggers to update datelastchange in employment when updating or inserting on hours_work and hours_extra --

delimiter |

CREATE TRIGGER hours_work_insert AFTER INSERT ON hours_work
	FOR EACH ROW BEGIN
	UPDATE employment
	SET datelastchange=CURRENT_TIMESTAMP
	WHERE id_person=NEW.id_person AND year=(
		SELECT year FROM course_per_period AS cpp WHERE cpp.id=NEW.id_course_per_period);
	END;
|

CREATE TRIGGER hours_work_update AFTER UPDATE ON hours_work
	FOR EACH ROW BEGIN
	UPDATE employment
	SET datelastchange=CURRENT_TIMESTAMP
	WHERE id_person=NEW.id_person AND year=(
		SELECT year FROM course_per_period AS cpp WHERE cpp.id=NEW.id_course_per_period);
	END;
|

CREATE TRIGGER hours_extra_insert AFTER INSERT ON hours_extra
	FOR EACH ROW BEGIN
	UPDATE employment
	SET datelastchange=CURRENT_TIMESTAMP
	WHERE id_person=NEW.id_person AND year=NEW.year;
	END;
|

CREATE TRIGGER hours_extra_update AFTER UPDATE ON hours_extra
	FOR EACH ROW BEGIN
	UPDATE employment
	SET datelastchange=CURRENT_TIMESTAMP
	WHERE id_person=NEW.id_person AND year=NEW.year;
	END;
|

delimiter ;

-- INSERT PERSONS --
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Ragnar", "Karlsson", "karr", "$5$HxGWcM03kAsRGhq$NMLtElA/VE/2bkedP6qBtokIyU1Z6Ttdo2mGFpCdrZ/", "user", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Gunnar", "Hinriksson", "hing", "$5$WNzDDQxirFeW4gb$cgmh4zMGrJyFlxK.7hsGJXeupWcNfbR0BUVGalj7Sp7", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Mimmie", "Diits", "diim", "$5$WNzDDQxirFeW4gb$cgmh4zMGrJyFlxK.7hsGJXeupWcNfbR0BUVGalj7Sp7", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Jesper", "Stråhle", "strj", "$5$HxGWcM03kAsRGhq$NMLtElA/VE/2bkedP6qBtokIyU1Z6Ttdo2mGFpCdrZ/", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Alexander", "Istanbullu", "ista", "$5$HxGWcM03kAsRGhq$NMLtElA/VE/2bkedP6qBtokIyU1Z6Ttdo2mGFpCdrZ/", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Christian", "Hasse-Lass", "hasc", "$5$HxGWcM03kAsRGhq$NMLtElA/VE/2bkedP6qBtokIyU1Z6Ttdo2mGFpCdrZ/", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Johan", "Nygren", "nygj", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Martin", "Hallnemo", "halm", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Andras", "Marki", "mara", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("David", "Synnergren", "synd", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Tobias", "Lööw", "loot", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "superadmin", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Henrik", "Gustavsson", "gush", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "organizer", CURRENT_TIMESTAMP);
INSERT INTO person(firstname, lastname, `sign`, password, `type`, datelastchange) VALUES ("Marcus", "Brohede", "brom", "$5$kQjHbfYbB0OzKkM$7ZvO6Vzm1OlAUIvRkTg3ZydVcmICTe1yd7w4p1dlai4", "organizer", CURRENT_TIMESTAMP);

#--INSERT EMPLOYMENT PERSONS--
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 1, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 2, 50, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 3, 75, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 4, 80, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 5, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 6, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 7, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 8, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 9, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 10, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 11, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 12, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2011, 13, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 1, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 2, 50, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 3, 75, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 4, 80, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 5, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 6, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 7, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 8, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 9, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 10, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 11, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 12, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2012, 13, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 1, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 2, 50, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 3, 75, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 4, 80, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 5, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 6, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 7, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 8, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 9, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 10, 100, 1980);
INSERT INTO employment(`year`, id_person, percent, allocated_time) VALUES (2013, 11, 100, 1980);
INSERT INTO employment(`year`, id_person) VALUES (2013, 12);
INSERT INTO employment(`year`, id_person) VALUES (2013, 13);

-- INSERT COURSES --
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV316G", "Programvaruutveckling", "DV", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("IS114G", "Databassystem", "IS", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV312G", "Webbutveckling - Databasgränssnitt", "DV", 7.5, "B");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV140G", "Studieteknik", "DV", 1.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA338G", "Projekt i webbutveckling", "DA", 15, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("KV104G", "Moralfilosofi", "KV", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("IS216G", "Informationssystem modellering", "IS", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA551G", "Distribuerade system", "DA", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA346G", "Algoritmer och datastrukturer", "DA", 7.5, "B");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA146G", "Datorgrafik", "DA", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA147G", "Grundläggande programmering med C++", "DA", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA327G", "Mjukvarukomponenter i C++", "DA", 7.5, "B");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA519G", "Realtidssystem", "DA", 7.5, "C");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA518G", "Systemadministration-forskning och utveckling", "DA", 7.5, "B");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DA524G", "Webbutveckling - content management och drift", "DA", 7.5, "B");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV721A", "Avancerade kognitiva och interaktiva system I", "DV", 7.5, "D");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV726A", "Beslutsfattande och beslutsstödsystem", "DV", 7.5, "D");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV731A", "Organisatoriska tillämpningar av kunskaps- och datadrivna beslutsstödssystem", "DV", 7.5, "D");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV146G", "Introduktion till speldesign", "DV", 7.5, "A");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV729A", "Web Intelligence ", "DV", 7.5, "D");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV727A", "Öppna system", "DV", 7.5, "D");
INSERT INTO course(code, name, mainfield, credits, level) VALUES ("DV116G", "Informationssäkerhet - Riskhantering", "DV", 7.5, "A");

-- INSERT TYPES FOR HOURS EXTRA --
INSERT INTO `type`(name) VALUES ("Övrigt");
INSERT INTO `type`(name) VALUES ("Semester");
INSERT INTO `type`(name) VALUES ("Institutionstid");
INSERT INTO `type`(name) VALUES ("Föräldraledighet");
INSERT INTO `type`(name) VALUES ("Projekt");


-- INSERT HOURS EXTRA --
INSERT INTO hours_extra(`year`, id_person, hours, title, id_type_name, display_area) VALUES(2012, 1, 50, "Examination exjobb MEB", 1 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, title, id_type_name, display_area) VALUES(2012, 1, 50, "Ämnesgruppsmöte DAO", 1 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 1, 50, 1 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 1, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 1, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 1, 20, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 2, 40, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 2, 40, 4 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 2, 10, 4 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 3, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 3, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 3, 300, 4 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 3, 30, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 4, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 4, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 4, 40, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 5, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 5, 400, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 5, 160, 4 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 6, 60, 2,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 6, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 6, 15, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 7, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 7, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 7, 10, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 8, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 8, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 8, 20, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 9, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 9, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 9, 50, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 10, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 10, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 10, 144, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 10, 24, 3 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 11, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 11, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 11, 122, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 11, 36, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 12, 10, 1 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 12, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 12, 10, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 12, 100, 5 ,"LowerField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 13, 60, 2 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 13, 40, 3 ,"UpperField");
INSERT INTO hours_extra(`year`, id_person, hours, id_type_name, display_area) VALUES(2012, 13, 50, 5 ,"LowerField");


-- INSERT COURSES PER PERIOD --
-- 2011 --
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2011, 50, 43, 50, 234, 7, 8, 4);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2011, 10, 112, 97, 102, 9, 12, 15);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2011, 100, 10, 8, 412, 13, 12, 6);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 5, 2011, 100, 27, 23, 317, 3, 2, 7);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2011, 50, 99, 85, 300, 8, 7, 17);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2011, 50, 99, 104, 300, 9, 7, 18);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2011, 50, 99, 89, 300, 8, 9, 19);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2011, 50, 99, 91, 300, 12, 13, 20);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2011, 50, 99, 80, 300, 12, 13, 21);

-- 2012 --
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 5, 3, 8);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2012, 50, 99, 80, 300, 1, 2, 9);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2012, 50, 99, 80, 300, 1, 2, 10);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2012, 50, 99, 80, 300, 1, 2, 11);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 3, 4, 12);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 7, 8, 4);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 9, 12, 5);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2012, 50, 99, 80, 300, 13, 12, 6);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 3, 2, 7);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2012, 50, 99, 80, 300, 5, 3, 8);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 5, 2012, 50, 99, 80, 300, 5, 3, 8);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 1, 2, 9);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 1, 2, 10);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 3, 2012, 50, 99, 80, 300, 1, 2, 11);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 5, 2012, 50, 99, 80, 300, 3, 4, 12);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 3, 4, 13);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 4, 5, 14);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2012, 50, 99, 80, 300, 4, 5, 14);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 4, 5, 15);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 4, 5, 16);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 8, 7, 17);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 9, 7, 18);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 8, 9, 19);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 12, 13, 20);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2012, 50, 99, 80, 300, 12, 13, 21);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2012, 50, 99, 80, 300, 2, 13, 22);

-- 2013 --
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2013, 50, 99, 80, 300, 9, 7, 18);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2013, 50, 99, 80, 300, 8, 9, 19);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2013, 50, 99, 80, 300, 12, 13, 20);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2013, 50, 99, 80, 300, 12, 13, 21);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2013, 50, 40, 32, 300, 5, 3, 8);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2013, 50, 99, 80, 300, 1, 2, 9);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 1, 2013, 50, 99, 80, 300, 1, 2, 10);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (2, 2, 2013, 50, 99, 80, 300, 1, 2, 11);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (4, 4, 2013, 50, 99, 80, 300, 3, 4, 12);
INSERT INTO course_per_period(start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course)
VALUES (1, 2, 2013, 50, 99, 80, 300, 2, 13, 22);



-- INSERT HOURS WORK --
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (1, 1, 78);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (1, 2, 90);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (2, 3, 45);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (2, 4, 93);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (3, 5, 35);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (3, 7, 52);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (4, 7, 99);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (4, 8, 77);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (5, 9, 69);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (5, 12, 83);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (6, 12, 74);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (6, 13, 63);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (7, 3, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (7, 2, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (8, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (8, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (9, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (9, 5, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (10, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (10, 5, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (11, 1, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (11, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (12, 1, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (12, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (13, 1, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (13, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (14, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (14, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (15, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (15, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (16, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (16, 5, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (17, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (17, 5, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (18, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (18, 5, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (19, 4, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (19, 5, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (20, 8, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (20, 7, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (21, 9, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (21, 7, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (22, 8, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (22, 9, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (23, 12, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (23, 13, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (24, 12, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (24, 13, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (25, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (25, 13, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (26, 1, 78);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (26, 2, 90);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (27, 3, 45);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (27, 4, 93);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (28, 5, 35);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (28, 7, 52);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (29, 7, 99);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (29, 8, 77);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (30, 9, 69);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (30, 12, 83);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (31, 12, 74);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (31, 13, 63);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (32, 3, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (32, 2, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (33, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (33, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (34, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (34, 5, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (35, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (35, 5, 40);

INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (36, 1, 78);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (36, 2, 90);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (37, 3, 45);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (37, 4, 93);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (38, 5, 35);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (38, 7, 52);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (39, 7, 99);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (39, 8, 77);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (40, 9, 69);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (40, 12, 83);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (41, 12, 74);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (41, 13, 63);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (42, 3, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (42, 2, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (43, 2, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (43, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (44, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (44, 5, 40);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (45, 3, 80);
INSERT INTO hours_work(id_course_per_period, id_person, hours) VALUES (45, 5, 40);
