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
  ->add(__('Add Query'));

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $returns = array();
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Query Builder/queries_edit.php&queryBuilderQueryID='.$_GET['editID'].'&search='.$_GET['search'].'&sidebar=false';
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';
    if ($search != '') { echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
        echo '</div>';
    }

    $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_addProcess.php?search='.$search);
                
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $types = array(
        'Personal' => __('Personal'),
        'School' => __('School'),
    );
    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($types)->isRequired();

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

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
