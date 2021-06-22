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

$page->breadcrumbs->add(__('Manage Settings'));

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/settings_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('settingsManage', $session->get('absoluteURL').'/modules/'.$session->get('module').'/settings_manageProcess.php');

    $form->addHiddenValue('address', $session->get('address'));

    $row = $form->addRow()->addHeading(__('Export Settings'));

    $fileTypes = array(
        'Excel2007'    => __('Excel 2007 and above (.xlsx)', 'Query Builder'),
        'Excel5'       => __('Excel 95 and above (.xls)', 'Query Builder'),
        'OpenDocument' => __('OpenDocument (.ods)', 'Query Builder'),
        'CSV'          => __('Comma Separated (.csv)', 'Query Builder'),
    );
    $setting = getSettingByScope($connection2, 'Query Builder', 'exportDefaultFileType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
        $row->addSelect($setting['name'])->fromArray($fileTypes)->selected($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
