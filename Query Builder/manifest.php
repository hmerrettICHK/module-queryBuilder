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

//This file describes the module, including database tables

//Basic variables
$name = 'Query Builder';
$description = 'A module to provide SQL queries for pulling data out of Gibbon and exporting it to Excel.';
$entryURL = 'queries.php';
$type = 'Additional';
$category = 'Admin';
$version = '1.10.02';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Module tables & gibbonSettings entries
$moduleTables[0] = "CREATE TABLE `queryBuilderQuery` (`queryBuilderQueryID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT, `type` enum('gibbonedu.com','Personal','School') NOT NULL DEFAULT 'gibbonedu.com', `scope` varchar(30) NOT NULL DEFAULT 'Core', `name` varchar(255) NOT NULL,  `category` varchar(50) NOT NULL,  `description` text NOT NULL,  `query` text NOT NULL, `bindValues` TEXT NULL DEFAULT NULL, `active` enum('Y','N') NOT NULL DEFAULT 'Y',  `queryID` int(10) unsigned zerofill DEFAULT NULL COMMENT 'If based on a gibbonedu.org query.',  `gibbonPersonID` int(10) unsigned zerofill DEFAULT NULL,  PRIMARY KEY (`queryBuilderQueryID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";

//gibbonSettings entries
$gibbonSetting[0] = "INSERT INTO `gibbonSetting` (`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES ('Query Builder', 'exportDefaultFileType', 'Default Export File Type', '', 'Excel2007');";

//Action rows
$actionRows[0]['name'] = 'Manage Queries_viewEditAll';
$actionRows[0]['precedence'] = '1';
$actionRows[0]['category'] = 'Queries';
$actionRows[0]['description'] = 'Allows a user to register with gibbonedu.org to gain access to managed queries.';
$actionRows[0]['URLList'] = 'queries.php, queries_add.php, queries_edit.php, queries_duplicate.php, queries_delete.php, queries_run.php, queries_sync.php, queries_help_full.php';
$actionRows[0]['entryURL'] = 'queries.php';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'N';
$actionRows[0]['defaultPermissionStudent'] = 'N';
$actionRows[0]['defaultPermissionParent'] = 'N';
$actionRows[0]['defaultPermissionSupport'] = 'N';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'N';
$actionRows[0]['categoryPermissionParent'] = 'N';
$actionRows[0]['categoryPermissionOther'] = 'N';

$actionRows[1]['name'] = 'Manage Settings';
$actionRows[1]['precedence'] = '0';
$actionRows[1]['category'] = 'Queries';
$actionRows[1]['description'] = 'Allows a privileged user to manage Query Builder settings.';
$actionRows[1]['URLList'] = 'settings_manage.php';
$actionRows[1]['entryURL'] = 'settings_manage.php';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'N';
$actionRows[1]['defaultPermissionStudent'] = 'N';
$actionRows[1]['defaultPermissionParent'] = 'N';
$actionRows[1]['defaultPermissionSupport'] = 'N';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'N';
$actionRows[1]['categoryPermissionParent'] = 'N';
$actionRows[1]['categoryPermissionOther'] = 'N';

$actionRows[2]['name'] = 'Manage Queries_run';
$actionRows[2]['precedence'] = '0';
$actionRows[2]['category'] = 'Queries';
$actionRows[2]['description'] = 'Allows a user to run queries but not add or edit them.';
$actionRows[2]['URLList'] = 'queries.php, queries_run.php';
$actionRows[2]['entryURL'] = 'queries.php';
$actionRows[2]['defaultPermissionAdmin'] = 'N';
$actionRows[2]['defaultPermissionTeacher'] = 'N';
$actionRows[2]['defaultPermissionStudent'] = 'N';
$actionRows[2]['defaultPermissionParent'] = 'N';
$actionRows[2]['defaultPermissionSupport'] = 'N';
$actionRows[2]['categoryPermissionStaff'] = 'Y';
$actionRows[2]['categoryPermissionStudent'] = 'N';
$actionRows[2]['categoryPermissionParent'] = 'N';
$actionRows[2]['categoryPermissionOther'] = 'N';
