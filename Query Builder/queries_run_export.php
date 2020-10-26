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

use Gibbon\Tables\DataTable;
use Gibbon\Tables\Renderer\SpreadsheetRenderer;

// System-wide include
include '../../gibbon.php';

// Module include
include './moduleFunctions.php';

//Increase memory limit
ini_set('memory_limit','256M');

$queryBuilderQueryID = $_GET['queryBuilderQueryID'] ?? '';
$hash = $_GET['hash'] ?? '';
$query = $gibbon->session->get($hash)['query'] ?? '';
$bindValues = $gibbon->session->get($hash)['bindValues'] ?? [];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Query Builder/queries_run.php&sidebar=false&queryBuilderQueryID='.$queryBuilderQueryID;

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_run.php') == false) {
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    if ($queryBuilderQueryID == '' or $hash == '' or $query == '') {
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT name, bindValues FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND ((gibbonPersonID=:gibbonPersonID AND type='Personal') OR type='School' OR type='gibbonedu.com') AND active='Y'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
            exit;
        }

        if ($result->rowCount() < 1) {
            $URL = $URL.'&return=error1';
            header("Location: {$URL}");
            exit;
        }

        //Security check
        $illegal = false;
        $illegalList = '';
        foreach (getIllegals() as $ill) {
            if (preg_match('/\b('.$ill.')\b/i', $query)) {
                $illegal = true;
                $illegalList .= $ill.', ';
            }
        }
        if ($illegal) {
            $URL = $URL.'&return=error3&illegals='.urlencode($illegalList);
            header("Location: {$URL}");
            exit;
        } else {
            $queryDetails = $result->fetch();

            // Run the query
            $result = $pdo->select($query, $bindValues);

            if (!$pdo->getQuerySuccess()) {
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit;
            }

            //Proceed!
            $renderer = new SpreadsheetRenderer($_SESSION[$guid]['absolutePath']);
            $table = DataTable::create('queryBuilderExport', $renderer);

            $filename = substr(preg_replace('/[^a-zA-Z0-9]/', '', $queryDetails['name']), 0, 30);

            $table->addMetaData('filename', 'queryExport_'.$filename);
            $table->addMetaData('filetype', getSettingByScope($connection2, 'Query Builder', 'exportDefaultFileType'));
            $table->addMetaData('creator', formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff'));
            $table->addMetaData('name', $queryDetails['name']);

            for ($i = 0; $i < $result->columnCount(); ++$i) {
                $col = $result->getColumnMeta($i);
                $width = stripos($col['native_type'], 'text') !== false ? '25' : 'auto';

                $table->addColumn($col['name'], $col['name'])->width($width);
            }

            echo $table->render($result->toDataSet());
        }

        $gibbon->session->remove($hash);
    }
}
