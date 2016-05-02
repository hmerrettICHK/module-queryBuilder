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

@session_start();

if (isActionAccessible($guid, $connection2, '/modules/Query Builder/queries_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/queries.php'>".__($guid, 'Manage Queries')."</a> > </div><div class='trailEnd'>".__($guid, 'Edit Query').'</div>';
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = null;
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }
    if ($search != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Query Builder/queries.php&search=$search'>".__($guid, 'Back to Search Results').'</a>';
        echo '</div>';
    }

    //Check if school year specified
    $queryBuilderQueryID = $_GET['queryBuilderQueryID'];
    if ($queryBuilderQueryID == '') {
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
            $row = $result->fetch();
            ?>
			<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/queries_editProcess.php?queryBuilderQueryID=$queryBuilderQueryID&search=$search" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">
					<tr>
						<td>
							<b><?php echo __($guid, 'Type') ?> *</b><br/>
						</td>
						<td class="right">
							<input readonly name="type" id="type" maxlength=255 value="<?php echo htmlPrep($row['type']) ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td>
							<b><?php echo __($guid, 'Name') ?> *</b><br/>
						</td>
						<td class="right">
							<input name="name" id="name" maxlength=255 value="<?php echo htmlPrep($row['name']) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var name=new LiveValidation('name');
								name.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo '<b>'.__($guid, 'Category').' *</b><br/>';
            ?>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input name="category" id="category" maxlength=50 value="<?php echo htmlPrep($row['category']) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var category=new LiveValidation('category');
								category.add(Validate.Presence);
							 </script>
						</td>
						<script type="text/javascript">
							$(function() {
								var availableTags=[
									<?php
                                    try {
                                        $dataAuto = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
                                        $sqlAuto = "SELECT DISTINCT category FROM queryBuilderQuery WHERE type='School' OR type='gibbonedu.com' OR (type='Personal' AND gibbonPersonID=:gibbonPersonID) ORDER BY category";
                                        $resultAuto = $connection2->prepare($sqlAuto);
                                        $resultAuto->execute($dataAuto);
                                    } catch (PDOException $e) {
                                    }
            while ($rowAuto = $resultAuto->fetch()) {
                echo '"'.$rowAuto['category'].'", ';
            }
            ?>
								];
								$( "#category" ).autocomplete({source: availableTags});
							});
						</script>
					</tr>
					<tr>
						<td>
							<b><?php echo __($guid, 'Active') ?> *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="active" id="active" style="width: 302px">
								<option <?php if ($row['active'] == 'Y') {
    echo 'selected';
}
            ?> value="Y"><?php echo __($guid, 'Y') ?></option>
								<option <?php if ($row['active'] == 'N') {
    echo 'selected';
}
            ?> value="N"><?php echo __($guid, 'N') ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<b><?php echo __($guid, 'Description') ?></b><br/>
						</td>
						<td class="right">
							<textarea name="description" id="description" rows=8 style="width: 300px"><?php echo htmlPrep($row['description']) ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<b>Query *</b>
							<?php
                            echo "<div class='linkTop' style='margin-top: 0px'>";
            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/queries_help_full.php&width=1100&height=550'><img title='Query Help' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/help.png'/></a>";
            echo '</div>';
            ?>
							<textarea name="query" id='query' style="display: none;"><?php echo htmlPrep($row['query']) ?></textarea>

							<div id="editor" style='width: 1058px; height: 400px;'><?php echo htmlPrep($row['query']) ?></div>

							<script src="<?php echo $_SESSION[$guid]['absoluteURL'] ?>/modules/Query Builder/lib/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
							<script>
								var editor = ace.edit("editor");
								editor.getSession().setMode("ace/mode/mysql");
								editor.getSession().setUseWrapMode(true);
								editor.getSession().on('change', function(e) {
									$('#query').val(editor.getSession().getValue());
								});
							</script>
							<script type="text/javascript">
								var query=new LiveValidation('query');
								query.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td>
							<span style="font-size: 90%"><i>* <?php echo __($guid, 'denotes a required field');
            ?></i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
							<input type="submit" value="<?php echo __($guid, 'Submit');
            ?>">
						</td>
					</tr>
				</table>
			</form>
			<?php

        }
    }
}
?>
