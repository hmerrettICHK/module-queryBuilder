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

include './config.php';

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_help_full.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this page.';
    echo '</div>';
} else {
    //Proceed!
    echo '<h1>';
    echo __m('Help');
    echo '</h1>';
    echo '<p>';
    echo "This help page gives a listing of all database tables contained within your Gibbon database ($databaseName). For each table there is a listing of all of the columns available. Where the same column name is found two tables, it generally infers a relationship, which can be queried with an SQL JOIN statement.";
    echo '</p>';

    //Get class variable
    try {
        $data = array();
        $sql = 'SHOW TABLES';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { echo "<div class='error'>".$e->getMessage().'</div>';
    }
    if ($result->rowCount() < 1) { echo "<div class='error'>";
        echo __($guid, 'There are no tables to show.');
        echo '</div>';
    } else {
        while ($row = $result->fetch()) {
            echo '<h2 style="text-transform: none;">';
            echo $row['Tables_in_'.$databaseName];
            echo '</h2>';

            try {
                $dataTable = array();
                $sqlTable = 'SHOW COLUMNS FROM '.$row['Tables_in_'.$databaseName];
                $resultTable = $connection2->prepare($sqlTable);
                $resultTable->execute($dataTable);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($resultTable->rowCount() < 1) {
                echo "<div class='error'>";
                echo __($guid, 'There are no columns to show.');
                echo '</div>';
            } else {
                echo '<ol>';
                while ($rowTable = $resultTable->fetch()) {
                    echo '<li><b>'.$rowTable['Field'].'</b> - '.$rowTable['Type'].'</li>';
                }
                echo '</ol>';
            }
        }
    }
}
