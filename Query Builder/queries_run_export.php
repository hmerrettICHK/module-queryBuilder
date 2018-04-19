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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include '../../gibbon.php';


$queryBuilderQueryID = $_GET['queryBuilderQueryID'];
$query = $_POST['query'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_run.php') == false) {
    //Fail 0
    $URL = $URL.'&updateReturn=fail0';
    header("Location: {$URL}");
} else {
    if ($queryBuilderQueryID == '' or $query == '') {
        //Fail 1
        $URL = $URL.'?exportReturn=fail1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND (gibbonPersonID=:gibbonPersonID OR NOT type='Personal') AND active='Y'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail 0
            $URL = $URL.'?exportReturn=fail0';
            header("Location: {$URL}");
        }

        if ($result->rowCount() < 1) {
            //Fail 3
            $URL = $URL.'?exportReturn=fail3';
            header("Location: {$URL}");
        } else {
            //Proceed!
            $exp = new ExportToExcel();
            $exp->exportWithPage($guid, './queries_run_export_contents.php', 'queryBuilderExport.xls');
        }
    }
}
