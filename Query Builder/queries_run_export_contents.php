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

include '../../config.php';

include 'moduleFunctions.php';

global $connection2;

$queryBuilderQueryID = $_GET['queryBuilderQueryID'];
$query = $_POST['query'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_run.php') == false) {
    echo "<div class='error'>";
    echo __($guid, 'Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    if ($queryBuilderQueryID == '' or $query == '') { echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND (gibbonPersonID=:gibbonPersonID OR NOT type='Personal') AND active='Y'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo __($guid, 'Your request failed due to a database error.');
            echo '</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo __($guid, 'The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            //Security check
            $illegal = false;
            $illegals = getIllegals();
            $illegalList = '';
            foreach ($illegals as $ill) {
                if (!(strpos($query, $ill) === false)) {
                    $illegal = true;
                    $illegalList .= $ill.', ';
                }
            }
            if ($illegal) {
                echo "<div class='error'>";
                echo __($guid, 'Your query contains the following illegal term(s), and so cannot be run:').' <b>'.substr($illegalList, 0, -2).'</b>.';
                echo '</div>';
            } else {
                try {
                    $data = array();
                    $result = $connection2->prepare($query);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>";
                    echo __($guid, 'Your request failed due to a database error.');
                    echo '</div>';
                }

                if ($result->rowCount() < 1) {
                    echo "<div class='warning'>Your query has returned 0 rows.</div>";
                } else {
                    echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                    echo '<tr>';
                    for ($i = 0; $i < $result->columnCount(); ++$i) {
                        $col = $result->getColumnMeta($i);
                        if ($col['name'] != 'password' and $col['name'] != 'passwordStrong' and $col['name'] != 'passwordStrongSalt' and $col['table'] != 'gibbonStaffContract' and $col['table'] != 'gibbonStaffApplicationForm' and $col['table'] != 'gibbonStaffApplicationFormFile') {
                            echo "<th style='min-width: 72px'>";
                            echo $col['name'];
                            echo '</th>';
                        }
                    }
                    echo '</tr>';
                    while ($row = $result->fetch()) {
                        echo '<tr>';
                        for ($i = 0; $i < $result->columnCount(); ++$i) {
                            $col = $result->getColumnMeta($i);
                            if ($col['name'] != 'password' and $col['name'] != 'passwordStrong' and $col['name'] != 'passwordStrongSalt' and $col['table'] != 'gibbonStaffContract' and $col['table'] != 'gibbonStaffApplicationForm' and $col['table'] != 'gibbonStaffApplicationFormFile') {
                                echo '<td>';
                                echo $row[$col['name']];
                                echo '</td>';
                            }
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                }

            }
        }
    }
}
