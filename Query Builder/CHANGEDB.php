<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00
$sql[$count][0] = '1.0.00';
$sql[$count][1] = '-- First version, nothing to update';

//v1.0.01
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.0.03
++$count;
$sql[$count][0] = '1.0.03';
$sql[$count][1] = '';

//v1.0.04
++$count;
$sql[$count][0] = '1.0.04';
$sql[$count][1] = '';

//v1.0.05
++$count;
$sql[$count][0] = '1.0.05';
$sql[$count][1] = '';

//v1.0.06
++$count;
$sql[$count][0] = '1.0.06';
$sql[$count][1] = '';

//v1.0.07
++$count;
$sql[$count][0] = '1.0.07';
$sql[$count][1] = '';

//v1.0.08
++$count;
$sql[$count][0] = '1.0.08';
$sql[$count][1] = '';

//v1.1.00
++$count;
$sql[$count][0] = '1.1.00';
$sql[$count][1] = '';

//v1.2.00
++$count;
$sql[$count][0] = '1.2.00';
$sql[$count][1] = "
ALTER TABLE `queryBuilderQuery` ADD `type` ENUM('gibbonedu.com','Personal','School') NOT NULL DEFAULT 'gibbonedu.com' AFTER `queryBuilderQueryID`;end
UPDATE queryBuilderQuery SET type='Personal' WHERE queryID IS NULL;end
";

//v1.2.01
++$count;
$sql[$count][0] = '1.2.01';
$sql[$count][1] = '';

//v1.2.02
++$count;
$sql[$count][0] = '1.2.02';
$sql[$count][1] = '';

//v1.2.03
++$count;
$sql[$count][0] = '1.2.03';
$sql[$count][1] = '';

//v1.2.04
++$count;
$sql[$count][0] = '1.2.04';
$sql[$count][1] = '';

//v1.2.05
++$count;
$sql[$count][0] = '1.2.05';
$sql[$count][1] = '';

//v1.2.06
++$count;
$sql[$count][0] = '1.2.06';
$sql[$count][1] = '';

//v1.2.07
++$count;
$sql[$count][0] = '1.2.07';
$sql[$count][1] = '';

//v1.2.08
++$count;
$sql[$count][0] = '1.2.08';
$sql[$count][1] = '';

//v1.2.09
++$count;
$sql[$count][0] = '1.2.09';
$sql[$count][1] = '';

//v1.2.10
++$count;
$sql[$count][0] = '1.2.10';
$sql[$count][1] = '';

//v1.2.11
++$count;
$sql[$count][0] = '1.2.11';
$sql[$count][1] = '';

//v1.2.12
++$count;
$sql[$count][0] = '1.2.12';
$sql[$count][1] = "
UPDATE gibbonAction SET category='Queries' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Query Builder');end
";

//v1.2.13
++$count;
$sql[$count][0] = '1.2.13';
$sql[$count][1] = '';

//v1.2.14
++$count;
$sql[$count][0] = '1.2.14';
$sql[$count][1] = '';

//v1.2.15
++$count;
$sql[$count][0] = '1.2.15';
$sql[$count][1] = '';

//v1.2.16
++$count;
$sql[$count][0] = '1.2.16';
$sql[$count][1] = '';

//v1.2.17
++$count;
$sql[$count][0] = '1.2.17';
$sql[$count][1] = '';

//v1.3.00
++$count;
$sql[$count][0] = '1.3.00';
$sql[$count][1] = '';

//v1.4.00
++$count;
$sql[$count][0] = '1.4.00';
$sql[$count][1] = '';

//v1.4.01
++$count;
$sql[$count][0] = '1.4.01';
$sql[$count][1] = '';
