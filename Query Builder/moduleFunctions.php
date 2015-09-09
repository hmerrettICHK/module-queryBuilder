<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Returns are array with illeagel SQL keywords
function getIllegals() {
	$illegals=array() ;
	
	$illegals[0]="USE" ;
	$illegals[1]="SHOW DATABASES" ;
	$illegals[2]="SHOW TABLES" ;
	$illegals[3]="DESCRIBE" ;
	$illegals[4]="SHOW FIELDS FROM" ;
	$illegals[5]="SHOW COLUMNS FROM" ;
	$illegals[6]="SHOW INDEX FROM" ;
	$illegals[7]="SET PASSWORD" ;
	$illegals[8]="CREATE TABLE" ;
	$illegals[9]="DROP TABLE" ;
	$illegals[10]="ALTER TABLE" ;
	$illegals[11]="CREATE INDEX" ;
	$illegals[12]="INSERT" ;
	$illegals[13]="INSERT INTO" ;
	$illegals[14]="DELETE FROM" ;
	$illegals[15]="UPDATE" ;
	$illegals[16]="LOAD DATA LOCAL INFILE" ;
	$illegals[17]="GRANT USAGE ON" ;
	$illegals[18]="GRANT SELECT ON" ;
	$illegals[19]="GRANT ALL ON" ;
	$illegals[20]="FLUSH PRIVILEGES" ;
	$illegals[21]="REVOKE ALL ON" ;
	$illegals[22]="UPDATE" ;
	
	return $illegals ;
}
?>
