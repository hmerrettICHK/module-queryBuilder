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

use Gibbon\Domain\System\ModuleGateway;

//Gibbon system-wide includes
include '../../gibbon.php';

//Module includes
include $_SESSION[$guid]['absolutePath'].'/modules/Query Builder/moduleFunctions.php';

//Setup variables
$gibboneduComOrganisationName = $_POST['gibboneduComOrganisationName'];
$gibboneduComOrganisationKey = $_POST['gibboneduComOrganisationKey'];
$service = $_POST['service'];
$queries = json_decode($_POST['queries'], true);

if (count($queries) < 1) { //We have a problem, report it.
    echo 'fail';
} else { //Success, let's write them to the database.
    //But first let's remove all of the gibbonedu.com old queries
    try {
        $data = array();
        $sql = "DELETE FROM queryBuilderQuery WHERE type='gibbonedu.com'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    //Prep additional module array
    $moduleGateway = $container->get(ModuleGateway::class);

    $criteria = $moduleGateway->newQueryCriteria(true)
        ->sortBy('name')
        ->filterBy('type', 'Additional')
        ->fromPOST();
    $modules = $moduleGateway->queryModules($criteria)->toArray();

    $modulesArray = array() ;
    foreach ($modules AS $module) {
        $modulesArray[$module['name']] = $module['version'];
    }

    //Now let's get them in
    foreach ($queries as $query) {
        $insert = ($query['scope'] == 'Core') ? true : false;
        if ($query['scope'] != 'Core') {
            if (version_compare($query["versionFirst"],$modulesArray[$query['scope']], "<=") AND ((version_compare($query["versionLast"],$modulesArray[$query['scope']], ">=") OR empty($query["versionLast"])))) {
                $insert = true;
            }
        }

        if ($insert) {
            try {
                $data = array('queryID' => $query['queryID'], 'scope' => $query['scope'], 'name' => $query['name'], 'category' => $query['category'], 'description' => $query['description'], 'query' => $query['query'], 'bindValues' => $query['bindValues'] ?? '');
                $sql = "INSERT INTO queryBuilderQuery SET type='gibbonedu.com', queryID=:queryID, scope=:scope, name=:name, category=:category, description=:description, query=:query, bindValues=:bindValues";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
}
