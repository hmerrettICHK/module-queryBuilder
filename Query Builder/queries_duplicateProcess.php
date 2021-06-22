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


$search = $_GET['search'] ?? null;
$queryBuilderQueryID = $_GET['queryBuilderQueryID'] ?? '';
$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/queries_duplicate.php&queryBuilderQueryID='.$queryBuilderQueryID."&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_duplicate.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($queryBuilderQueryID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID);
            $sql = 'SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL = $URL.'&deleteReturn=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

            //Validate Inputs
            $name = $_POST['name'] ?? '';
            $type = $_POST['type'] ?? '';
            $category = $row['category'];
            $moduleName = $row['moduleName'];
            $actionName = $row['actionName'];
            $active = $row['active'];
            $description = $row['description'];
            $query = $row['query'];
            $bindValues = $row['bindValues'];
            $gibbonPersonID = $session->get('gibbonPersonID');

            if ($name == '' or $category == '' or $active == '' or $query == '') {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('type' => $type, 'name' => $name, 'category' => $category, 'moduleName' => $moduleName, 'actionName' => $actionName, 'active' => $active, 'description' => $description, 'query' => $query, 'bindValues' => $bindValues, 'gibbonPersonID' => $gibbonPersonID);
                    $sql = 'INSERT INTO queryBuilderQuery SET type=:type, name=:name, category=:category, moduleName=:moduleName, actionName=:actionName, active=:active, description=:description, query=:query, bindValues=:bindValues, gibbonPersonID=:gibbonPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail 2
                    $URL = $URL.'&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
