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

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Query Builder/queries_add.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/queries.php'>" . _('Manage Queries') . "</a> > </div><div class='trailEnd'>" . _('Add Query') . "</div>" ;
	print "</div>" ;
	
	if (isset($_GET["addReturn"])) { $addReturn=$_GET["addReturn"] ; } else { $addReturn="" ; }
	$addReturnMessage="" ;
	$class="error" ;
	if (!($addReturn=="")) {
		if ($addReturn=="fail0") {
			$addReturnMessage=_("Your request failed because you do not have access to this action.") ;	
		}
		else if ($addReturn=="fail2") {
			$addReturnMessage=_("Your request failed due to a database error.") ;	
		}
		else if ($addReturn=="fail3") {
			$addReturnMessage=_("Your request failed because your inputs were invalid.") ;	
		}
		else if ($addReturn=="fail4") {
			$addReturnMessage=_("Your request failed because your inputs were invalid.") ;	
		}
		else if ($addReturn=="success0") {
			$addReturnMessage=_("Your request was completed successfully.You can now add another record if you wish.") ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $addReturnMessage;
		print "</div>" ;
	} 
	
	?>
	<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/queries_addProcess.php" ?>">
		<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
			<tr>
				<td> 
					<b><?php print _('Name') ?> *</b><br/>
				</td>
				<td class="right">
					<input name="name" id="name" maxlength=255 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						var name=new LiveValidation('name');
						name.add(Validate.Presence);
					 </script> 
				</td>
			</tr>
			<tr>
				<td> 
					<?php print "<b>" . _('Category') . " *</b><br/>" ; ?>
					<span style="font-size: 90%"><i></i></span>
				</td>
				<td class="right">
					<input name="category" id="category" maxlength=50 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						var category=new LiveValidation('category');
						category.add(Validate.Presence);
					 </script> 
				</td>
			</tr>
			<tr>
				<td> 
					<b><?php print _('Active') ?> *</b><br/>
					<span style="font-size: 90%"><i></i></span>
				</td>
				<td class="right">
					<select name="active" id="active" style="width: 302px">
						<option value="Y"><?php print _('Y') ?></option>
						<option value="N"><?php print _('N') ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td> 
					<b><?php print _('Description') ?></b><br/>
				</td>
				<td class="right">
					<textarea name="description" id="description" rows=8 style="width: 300px"></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2> 
					<b>Query *</b>
					<?php
					print "<div class='linkTop' style='margin-top: 0px'>" ;
						print "<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_help_full.php&width=1100&height=550'><img title='Query Help' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/help.png'/></a>" ;
					print "</div>" ;
					?>
					<textarea name="query" id='query' style="display: none;"></textarea>
					
					<div id="editor" style='width: 1058px; height: 400px;'></div>
	
					<script src="<?php print $_SESSION[$guid]["absoluteURL"] ?>/modules/Query Builder/lib/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
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
					<span style="font-size: 90%"><i>* <?php print _("denotes a required field") ; ?></i></span>
				</td>
				<td class="right">
					<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
					<input type="submit" value="<?php print _("Submit") ; ?>">
				</td>
			</tr>
		</table>
	</form>
	<?php
}
?>