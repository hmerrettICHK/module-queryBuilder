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
use Gibbon\Module\QueryBuilder\Forms\BindValues;

$page->breadcrumbs
  ->add(__('Manage Queries'), 'queries.php')
  ->add(__('Edit Query'));

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $queryBuilderQueryID = isset($_GET['queryBuilderQueryID'])? $_GET['queryBuilderQueryID'] : '';
    $search = isset($_GET['search'])? $_GET['search'] : '';

    //Check if school year specified
    if (empty($queryBuilderQueryID)) {
        echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND NOT type='gibbonedu.com' AND (type='School' OR (type='Personal' AND gibbonPersonID=:gibbonPersonID) )";
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

            echo "<div class='linkTop'>";
            $pipe = false ;
            if ($search != '') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
                $pipe = true;
            }
            echo '</div>';

            $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_editProcess.php?queryBuilderQueryID='.$queryBuilderQueryID.'&search='.$search);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addHeaderAction('help', __('Help'))
                ->setURL('/modules/Query Builder/queries_help_full.php')
                ->setIcon('help')
                ->addClass('underline')
                ->displayLabel()
                ->modalWindow();

            if ($values['active'] == 'Y') {
                $form->addHeaderAction('run', __('Run Query'))
                    ->setURL('/modules/Query Builder/queries_run.php')
                    ->addParam('search', $search)
                    ->addParam('queryBuilderQueryID', $queryBuilderQueryID)
                    ->addParam('sidebar', 'false')
                    ->setIcon('run')
                    ->displayLabel()
                    ->prepend(" | ");
            }

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addTextField('type')->isRequired()->readonly();

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->maxLength(255)->isRequired();

            $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT DISTINCT category FROM queryBuilderQuery WHERE type='School' OR type='gibbonedu.com' OR (type='Personal' AND gibbonPersonID=:gibbonPersonID) ORDER BY category";
            $result = $pdo->executeQuery($data, $sql);
            $categories = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN, 0) : array();

            $row = $form->addRow();
                $row->addLabel('category', __('Category'));
                $row->addTextField('category')->isRequired()->maxLength(100)->autocomplete($categories);

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->isRequired();

            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextArea('description')->setRows(8);

            $col = $form->addRow()->addColumn();
                $col->addLabel('query', __('Query'));
                $col->addCodeEditor('query')->setMode('mysql')->isRequired();

            $bindValues = new BindValues($form->getFactory(), 'bindValues', $values, $gibbon->session);
            $form->addRow()->addElement($bindValues);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
