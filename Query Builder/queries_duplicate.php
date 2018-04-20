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

use Gibbon\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_duplicate.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/queries.php'>".__($guid, 'Manage Queries')."</a> > </div><div class='trailEnd'>".__($guid, 'Duplicate Query').'</div>';
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = null;
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }
    if ($search != '') { echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
        echo '</div>';
    }

    //Check if school year specified
    $queryBuilderQueryID = $_GET['queryBuilderQueryID'];
    if ($queryBuilderQueryID == '') { echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID);
            $sql = 'SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __($guid, 'The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_duplicateProcess.php?queryBuilderQueryID='.$queryBuilderQueryID.'&search='.$search);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('name', __('New Name'));
                $row->addTextField('name')->maxLength(255)->isRequired()->loadFrom($values);

            $types = array(
                'Personal' => __('Personal'),
                'School' => __('School'),
            );
            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($types)->isRequired();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
