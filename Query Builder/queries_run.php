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
use Gibbon\Tables\DataTable;
use Gibbon\Module\QueryBuilder\Forms\QueryEditor;

//Module includes
include __DIR__.'/moduleFunctions.php';

$page->breadcrumbs
  ->add(__('Manage Queries'), 'queries.php')
  ->add(__('Run Query'));

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_run.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $search = isset($_GET['search'])? $_GET['search'] : '';
    if ($search != '') { echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
        echo '</div>';
    }

    if (isset($_GET['return'])) {
        $illegals = isset($_GET['illegals'])? urldecode($_GET['illegals']) : '';
        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your query contains the following illegal term(s), and so cannot be run:', 'Query Builder').' <b>'.substr($illegals, 0, -2).'</b>.'));
    }

    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);

    //Check if school year specified
    $queryBuilderQueryID = isset($_GET['queryBuilderQueryID'])? $_GET['queryBuilderQueryID'] : '';
    $save = isset($_POST['save'])? $_POST['save'] : '';
    $query = isset($_POST['query'])? $_POST['query'] : '';

    if (empty($queryBuilderQueryID)) { 
        echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
        $sql = "SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND ((gibbonPersonID=:gibbonPersonID AND type='Personal') OR type='School' OR type='gibbonedu.com') AND active='Y'";
        $result = $pdo->select($sql, $data);

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __($guid, 'The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
            echo '<tr>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
            echo '<i>'.$values['name'].'</i>';
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>Category</span><br/>";
            echo '<i>'.$values['category'].'</i>';
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>Active</span><br/>";
            echo '<i>'.$values['active'].'</i>';
            echo '</td>';
            echo '</tr>';
            if ($values['description'] != '') {
                echo '<tr>';
                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                echo "<span style='font-size: 115%; font-weight: bold'>Description</span><br/>";
                echo $values['description'];
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($highestAction == 'Manage Queries_viewEditAll') {
                $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_run.php&queryBuilderQueryID='.$queryBuilderQueryID.'&sidebar=false&search='.$search);
                    
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $queryEditor = new QueryEditor('query');
                $queryText = !empty($query)? $query : $values['query'];

                $col = $form->addRow()->addColumn();
                    $col->addLabel('query', __('Query'));
                    $col->addWebLink('<img title="'.__('Help').'" src="./themes/'.$_SESSION[$guid]['gibbonThemeName'].'/img/help.png" style="margin-bottom:5px"/>')
                        ->setURL($_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_help_full.php&width=1100&height=550')
                        ->addClass('thickbox floatRight');
                    $col->addElement($queryEditor)->isRequired()->setValue($queryText);

                $row = $form->addRow();
                    $row->addFooter();
                    $col = $row->addColumn()->addClass('inline right');
                    if ($values['type'] == 'Personal' or ($values['type'] == 'School' and $values['gibbonPersonID'] == $_SESSION[$guid]['gibbonPersonID'])) {
                        $col->addCheckbox('save')->description(__('Save Query?'))->setValue('Y')->checked($save)->wrap('<span class="displayInlineBlock">', '</span>&nbsp;&nbsp;');
                    }
                    $col->addSubmit(__('Run Query'));

                echo $form->getOutput();
            } else {
                $query = $values['query'];
            }

            //PROCESS QUERY
            if (!empty($query)) {
                echo '<h3>';
                echo __('Query Results');
                echo '</h3>';

                //Strip multiple whitespaces from string
                $query = preg_replace('/\s+/', ' ', $query);

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
                    echo "<div class='error'>";
                    echo __('Your query contains the following illegal term(s), and so cannot be run:').' <b>'.substr($illegalList, 0, -2).'</b>.';
                    echo '</div>';
                } else {
                    //Save the query
                    if ($highestAction == 'Manage Queries_viewEditAll' && $save == 'Y') {
                        $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'query' => $query);
                        $sql = "UPDATE queryBuilderQuery SET query=:query WHERE queryBuilderQueryID=:queryBuilderQueryID";
                        $pdo->update($sql, $data);
                    }

                    //Run the query
                    $result = $pdo->select($query);

                    if (!$pdo->getQuerySuccess()) {
                        echo '<div class="error">'.__('Your request failed due to a syntax error in the SQL query.').'</div>';
                    } else if ($result->rowCount() < 1) {
                        echo '<div class="warning">'.__('Your query has returned 0 rows.').'</div>';
                    } else {
                        echo '<div class="success">'.sprintf(__('Your query has returned %1$s rows, which are displayed below.'), $result->rowCount()).'</div>';

                        echo "<div class='linkTop'>";

                        $form = Form::create('queryExport', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_run_export.php?queryBuilderQueryID='.$queryBuilderQueryID)->setClass('blank fullWidth');
                        $form->addHiddenValue('query', $query);

                        $row = $form->addRow();
                            $row->addContent("<input style='background:url(./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/download.png) no-repeat; cursor:pointer; min-width: 25px!important; max-width: 25px!important; max-height: 25px; border: none; float: right' type='submit' value=''>");

                        echo $form->getOutput();

                        echo '</div>';

                        $invalidColumns = ['password', 'passwordStrong', 'passwordStrongSalt', 'gibbonStaffContract', 'gibbonStaffApplicationForm', 'gibbonStaffApplicationFormFile'];

                        $table = DataTable::create('queryResults');
                        $table->getRenderer()->addClass('smallIntBorder');

                        $count = 1;
                        $table->addColumn('count', '')->width('35px')->format(function($row) use (&$count) {
                            return '<span class="subdued">'.$count++.'</span>';
                        });

                        for ($i = 0; $i < $result->columnCount(); ++$i) {
                            $col = $result->getColumnMeta($i);
                            $colName = $col['name'];
                            if (!in_array($colName, $invalidColumns)) {
                                $table->addColumn($colName, $colName)->format(function($row) use ($colName) {
                                    if (strlen($row[$colName]) > 50 && $colName !='image' && $colName!='image_240') {
                                        return substr($row[$colName], 0, 50).'...';
                                    } else {
                                        return $row[$colName];
                                    }
                                });
                            }
                        }

                        echo "<div style='overflow-x:auto;'>";
                        echo $table->render($result->toDataSet());
                        echo '</div>';
                    }
                }
            }
        }
    }
}
