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

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isModuleAccessible($guid, $connection2) == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>".__($guid, 'Queries').'</div>';
    echo '</div>';

    $returns = array();
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], NULL, $returns);
    }

    $gibboneduComOrganisationName = getSettingByScope($connection2, 'System', 'gibboneduComOrganisationName');
    $gibboneduComOrganisationKey = getSettingByScope($connection2, 'System', 'gibboneduComOrganisationKey');

    echo '<script type="text/javascript">';
		echo '$(document).ready(function(){';
			?>
			$.ajax({
				crossDomain: true,
				type:"GET",
				contentType: "application/json; charset=utf-8",
				async:false,
				url: "https://gibbonedu.org/gibboneducom/keyCheck.php?callback=?",
				data: "gibboneduComOrganisationName=<?php echo $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php echo $gibboneduComOrganisationKey ?>&service=queryBuilder",
				dataType: "jsonp",
				jsonpCallback: 'fnsuccesscallback',
				jsonpResult: 'jsonpResult',
				success: function(data) {
					if (data['access']==='1') {
						$("#status").attr("class","success");
						$("#status").html('Success! Your system has a valid license to access value added Query Builder queries from gibbonedu.com. <a href=\'<?php echo $_SESSION[$guid]['absoluteURL'] ?>/index.php?q=/modules/Query Builder/queries_sync.php\'>Click here</a> to get the latest queries for your version of Gibbon.') ;
					}
					else {
						$("#status").attr("class","error");
						$("#status").html('Checking gibbonedu.com for a license to access value added Query Builder shows that you do not have access. You have either not set up access, or your access has expired or is invalid. Email <a href=\'mailto:support@gibbonedu.org\'>support@gibbonedu.org</a> to register for value added services, and then enter the name and key provided in reply, or to seek support as to why your key is not working. You may still use your own queries without a valid license.') ;
						$.ajax({
							url: "<?php echo $_SESSION[$guid]['absoluteURL'] ?>/modules/Query Builder/queries_gibboneducom_remove_ajax.php",
							data: "gibboneduComOrganisationName=<?php echo $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php echo $gibboneduComOrganisationKey ?>&service=queryBuilder"
						});
					}
				},
				error: function (data, textStatus, errorThrown) { }
			});
			<?php
        echo '});';
    echo '</script>';

    echo "<div id='output'>";
    echo "<div id='status' class='warning'>";
    echo "<div style='width: 100%; text-align: center'>";
    echo "<img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif' alt='Loading'/><br/>";
    echo 'Checking gibbonedu.com value added license status.';
    echo '</div>';
    echo '</div>';

    $search = null;
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    echo '<h3>';
    echo __($guid, 'Search');
    echo '</h3>';
    ?>
		<form method="get" action="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php">
			<table class='noIntBorder' cellspacing='0' style="width: 100%">
				<tr><td style="width: 30%"></td><td></td></tr>
				<tr>
					<td>
						<b><?php echo __($guid, 'Search For') ?></b><br/>
						<span style="font-size: 90%"><i><?php echo __($guid, 'Query name and category.') ?></i></span>
					</td>
					<td class="right">
						<input name="search" id="search" maxlength=20 value="<?php echo $search; ?>" type="text" style="width: 300px">
					</td>
				</tr>
				<tr>
					<td colspan=2 class="right">
						<input type="hidden" name="q" value="/modules/<?php echo $_SESSION[$guid]['module'] ?>/queries.php">
						<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
						<?php
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/queries.php'>".__($guid, 'Clear Search').'</a>';
    					?>
						<input type="submit" value="<?php echo __($guid, 'Submit'); ?>">
					</td>
				</tr>
			</table>
		</form>
		<?php

    echo '<h3>';
    echo __($guid, 'Queries');
    echo '</h3>';

    try {
        $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
        $sql = "SELECT * FROM queryBuilderQuery WHERE ((type='Personal' AND gibbonPersonID=:gibbonPersonID) OR type='School' OR type='gibbonedu.com') ORDER BY category, gibbonPersonID, name";
        if ($search != '') {
            $data['search'] = "%$search%";
            $data['search2'] = "%$search%";
            $sql = "SELECT * FROM queryBuilderQuery WHERE ((type='Personal' AND gibbonPersonID=:gibbonPersonID) OR type='School' OR type='gibbonedu.com') AND (name LIKE :search OR category LIKE :search2) ORDER BY category, gibbonPersonID, name";
        }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { echo "<div class='error'>".$e->getMessage().'</div>';
    }

    echo "<div class='linkTop'>";
    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/queries_add.php&sidebar=false&search=$search'><img title='".__($guid, 'Add New Record')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
    echo '</div>';

    if ($result->rowCount() < 1) { echo "<div class='error'>";
        echo __($guid, 'There are no records to display.');
        echo '</div>';
    } else {
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __($guid, 'Type');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Category');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Name');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Active');
        echo '</th>';
        echo '<th style=\'width: 130px\'>';
        echo __($guid, 'Actions');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }

            if ($row['active'] == 'N') {
                $rowNum = 'error';
            }

			//COLOR ROW BY STATUS!
			echo "<tr class=$rowNum>";
            echo '<td>';
            if (is_null($row['queryID']) == false) {
                echo 'gibbonedu.com';
            } else {
                echo $row['type'];
            }
            echo '</td>';
            echo '<td>';
            echo $row['category'];
            echo '</td>';
            echo '<td>';
            echo $row['name'];
            echo '</td>';
            echo '<td>';
            echo $row['active'];
            echo '</td>';
            echo '<td>';
            if ($row['type'] == 'Personal' or ($row['type'] == 'School' and $row['gibbonPersonID'] == $_SESSION[$guid]['gibbonPersonID'])) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_edit.php&queryBuilderQueryID='.$row['queryBuilderQueryID']."&sidebar=false&search=$search'><img title='".__($guid, 'Edit Record')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_delete.php&queryBuilderQueryID='.$row['queryBuilderQueryID']."&search=$search'><img title='".__($guid, 'Delete Record')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
            }
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_duplicate.php&queryBuilderQueryID='.$row['queryBuilderQueryID']."&search=$search'><img title='Duplicate' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/copy.png'/></a>";
            if ($row['active'] == 'Y') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/queries_run.php&queryBuilderQueryID='.$row['queryBuilderQueryID']."&sidebar=false&search=$search'><img style='margin-left: 6px' title='Run Query' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/run.png'/></a>";
            }
            echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';
    }
    echo '</div>';
}
?>
