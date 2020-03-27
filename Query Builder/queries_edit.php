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
use Gibbon\Module\QueryBuilder\Forms\QueryEditor;

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

    $search = isset($_GET['search'])? $_GET['search'] : '';
    if ($search != '') { echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
        echo '</div>';
    }

    //Check if school year specified
    $queryBuilderQueryID = isset($_GET['queryBuilderQueryID'])? $_GET['queryBuilderQueryID'] : '';
    if (empty($queryBuilderQueryID)) { 
        echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = "SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND NOT type='gibbonedu.com' AND gibbonPersonID=:gibbonPersonID";
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

            $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_editProcess.php?queryBuilderQueryID='.$queryBuilderQueryID.'&search='.$search);
                
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

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

            $queryEditor = new QueryEditor('query');

            $col = $form->addRow()->addColumn();
                $col->addLabel('query', __('Query'));
                $col->addWebLink('<img title="'.__('Help').'" src="./themes/'.$_SESSION[$guid]['gibbonThemeName'].'/img/help.png" style="margin-bottom:5px"/>')
                    ->setURL($_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_help_full.php&width=1100&height=550')
                    ->addClass('thickbox floatRight');
                $col->addElement($queryEditor)->isRequired();

            
            // BIND VALUES
            $bindValues = json_decode($values['bindValues'] ?? '', true);
            $types = [
                __('Basic') => [
                    'varchar'        => __('Text'),
                    'number'         => __('Number'),
                    'yesno'          => __('Yes/No'),
                    'date'           => __('Date'),
                ],
                __('System') => [
                    'reportingCycle' => __('Reporting Cycle'),
                    'schoolYear'     => __('School Year'),
                    'term'           => __('Term'),
                ],
            ];

            $missingValues = array_filter($bindValues ?? [], function ($bindValue) use ($values) {
                return strpos($values['query'], ':'.$bindValue['variable']) === false;
            });

            // Custom Block Template
            $addBlockButton = $form->getFactory()->createButton(__('Add Value'))->addClass('addBlock');

            $blockTemplate = $form->getFactory()->createTable()->setClass('blank');
            $row = $blockTemplate->addRow();
                $row->addTextField('name')
                    ->setClass('w-full m-0 title')
                    ->required()
                    ->placeholder(__m('Label Name'));
    
            $col = $blockTemplate->addRow()->addColumn()->addClass('flex mt-1');
                $col->addTextField('variable')
                    ->setClass('w-64')
                    ->required()
                    ->placeholder(__m('Variable Name'))
                    ->addValidation('Validate.Format', 'pattern: /^[A-Za-z0-9]+$/, failureMessage: "'.__m('Must be alphanumeric.').'"');
                $col->addSelect('type')->fromArray($types)->setClass('w-full float-none ml-1')->required()->placeholder();

            // Custom Blocks
            $col = $form->addRow()->addColumn();
                $col->addLabel('bindValues', __m('Variables'));
                $col->addContent(__m('You can optionally define named variables that a user can enter when running this query. Each variable name must be alphanumeric with no spaces or special symbols, and must be present in the query as :variableName'))->wrap('<span class="small emphasis">', '</span>');

                if (!empty($missingValues)) {
                    $col->addAlert(__m('SQL Error! The following variable names were not found in your query: {variables}', ['variables' => implode(', ', array_column($missingValues, 'variable'))]), 'error');
                }

                $customBlocks = $col->addCustomBlocks('bindValues', $gibbon->session)
                    ->fromTemplate($blockTemplate)
                    ->settings(array('inputNameStrategy' => 'object', 'addOnEvent' => 'click', 'sortable' => true))
                    ->placeholder(__m('Variables will be listed here...'))
                    ->addToolInput($addBlockButton);

            // Add existing bindValues
            foreach ($bindValues ?? [] as $index => $bindValue) {
                $customBlocks->addBlock($index, $bindValue);
            }

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
