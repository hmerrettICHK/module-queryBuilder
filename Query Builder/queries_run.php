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

use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;

//Module includes
include __DIR__.'/moduleFunctions.php';

//Increase memory limit
ini_set('memory_limit','256M');

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
    $queryBuilderQueryID = isset($_GET['queryBuilderQueryID'])? $_GET['queryBuilderQueryID'] : '';
    $search = isset($_GET['search'])? $_GET['search'] : '';

    if (isset($_GET['return'])) {
        $illegals = isset($_GET['illegals'])? urldecode($_GET['illegals']) : '';
        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your query contains the following illegal term(s), and so cannot be run:', 'Query Builder').' <b>'.substr($illegals, 0, -2).'</b>.'));
    }

    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);

    //Check if school year specified
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

            echo "<div class='linkTop'>";
            $pipe = false ;
            if ($search != '') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
                $pipe = true;
            }
            echo '</div>';

            $table = DataTable::createDetails('query');

            if ($highestAction == 'Manage Queries_viewEditAll') {
                $table->addHeaderAction('help', __('Help'))
                    ->setURL('/modules/Query Builder/queries_help_full.php')
                    ->setIcon('help')
                    ->addClass('underline')
                    ->displayLabel()
                    ->modalWindow();

                if ($values['type'] != 'gibbonedu.com') {
                    $table->addHeaderAction('edit', __('Edit Query'))
                        ->setURL('/modules/Query Builder/queries_edit.php')
                        ->addParam('search', $search)
                        ->addParam('queryBuilderQueryID', $queryBuilderQueryID)
                        ->addParam('sidebar', 'false')
                        ->setIcon('config')
                        ->displayLabel()
                        ->prepend(" | ");
                }
            }

            $table->addColumn('name', __('Name'));
            $table->addColumn('category', __('Category'));
            $table->addColumn('active', __('Active'));
            $table->addColumn('description', __('Description'))->width(100);

            echo $table->render([$values]);

            $form = Form::create('queryBuilder', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_run.php&queryBuilderQueryID='.$queryBuilderQueryID.'&sidebar=false&search='.$search);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            if ($highestAction == 'Manage Queries_viewEditAll') {
                $queryText = !empty($query)? $query : $values['query'];

                $col = $form->addRow()->addColumn();
                    $col->addLabel('query', __('Query'));
                    $col->addCodeEditor('query')->setMode('mysql')->isRequired()->setValue($queryText);
            } else {
                $form->addHiddenValue('query', $values['query']);
            }

            // Add custom bind values to the form
            $bindValues = json_decode($values['bindValues'] ?? '', true);
            if (!empty($bindValues) && is_array($bindValues)) {
                foreach ($bindValues as $bindValue) {
                    $bindValue['required'] = 'Y';
                    $fieldValue = $_POST[$bindValue['variable']] ?? null;

                    if ($bindValue['type'] == 'date' && !empty($fieldValue)) {
                        $fieldValue = Format::dateConvert($fieldValue);
                    }

                    $row = $form->addRow();
                    $row->addLabel($bindValue['variable'], $bindValue['name'])->description($bindValue['variable']);

                    if ($bindValue['type'] == 'schoolYear') {
                        $row->addSelectSchoolYear($bindValue['variable'])->selected($fieldValue ?? $gibbon->session->get('gibbonSchoolYearID'))->required();
                    } elseif ($bindValue['type'] == 'schoolYear') {
                        $row->addSelectSchoolYearTerm($bindValue['variable'], $gibbon->session->get('gibbonSchoolYearID'))->selected($fieldValue)->required();
                    } elseif ($bindValue['type'] == 'reportingCycle') {
                        $row->addSelectReportingCycle($bindValue['variable'])->selected($fieldValue)->required();
                    } elseif ($bindValue['type'] == 'yearGroups') {
                        $row->addCheckboxYearGroup($bindValue['variable'])->checked($fieldValue)->required();
                    } else {
                        $row->addCustomField($bindValue['variable'], $bindValue)->setValue($fieldValue);
                    }
                }
            }

            $row = $form->addRow();
                $row->addFooter();
                $col = $row->addColumn()->addClass('inline right');
                if ($highestAction == 'Manage Queries_viewEditAll' && ($values['type'] == 'Personal' or ($values['type'] == 'School' and $values['gibbonPersonID'] == $_SESSION[$guid]['gibbonPersonID']))) {
                    $col->addCheckbox('save')->description(__('Save Query?'))->setValue('Y')->checked($save)->wrap('<span class="displayInlineBlock">', '</span>&nbsp;&nbsp;');
                }
                else {
                    $col->addContent('');
                }
                $col->addSubmit(__('Run Query'));

            echo $form->getOutput();

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
                        $rawQuery = $_POST['query'] ?? '';
                        $data = array('queryBuilderQueryID' => $queryBuilderQueryID, 'query' => $rawQuery);
                        $sql = "UPDATE queryBuilderQuery SET query=:query WHERE queryBuilderQueryID=:queryBuilderQueryID";
                        $pdo->update($sql, $data);
                    }

                    // Get bind values, if they exist
                    $data = [];
                    $bindValues = json_decode($values['bindValues'] ?? '', true);
                    if (!empty($bindValues) && is_array($bindValues)) {
                        foreach ($bindValues as $bindValue) {
                            $fieldValue = $_POST[$bindValue['variable']] ?? '';
                            if ($bindValue['type'] == 'date' && !empty($fieldValue)) {
                                $fieldValue = Format::dateConvert($fieldValue);
                            } elseif (is_array($fieldValue)) {
                                $fieldValue = implode(',', $fieldValue);
                            }
                            $data[$bindValue['variable']] = $fieldValue;
                        }
                    }

                    // Run the query
                    $result = $pdo->select($query, $data);

                    if (!$pdo->getQuerySuccess()) {
                        echo '<div class="error">'.__('Your request failed with the following error: ').$pdo->getErrorMessage().'</div>';
                    } else if ($result->rowCount() < 1) {
                        echo '<div class="warning">'.__('Your query has returned 0 rows.').'</div>';
                    } else {
                        echo '<div class="success">'.sprintf(__('Your query has returned %1$s rows, which are displayed below.'), $result->rowCount()).'</div>';

                        echo "<div class='linkTop'>";

                        $form = Form::create('queryExport', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/queries_run_export.php?queryBuilderQueryID='.$queryBuilderQueryID.'&'.http_build_query($data))->setClass('blank fullWidth');
                        $form->addHiddenValue('query', $query);

                        $row = $form->addRow();
                            $row->addContent("<input style='background:url(./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/download.png) no-repeat; cursor:pointer; min-width: 25px!important; max-width: 25px!important; max-height: 25px; border: none; float: right' type='submit' value=''>");

                        echo $form->getOutput();

                        echo '</div>';

                        $invalidColumns = ['password', 'passwordStrong', 'passwordStrongSalt', 'gibbonStaffContract', 'gibbonStaffApplicationForm', 'gibbonStaffApplicationFormFile'];

                        $table = DataTable::create('queryResults');
                        $table->getRenderer();

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
