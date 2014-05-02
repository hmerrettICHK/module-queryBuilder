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

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Query Builder/queries_run.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/queries.php'>" . _('Manage Queries') . "</a> > </div><div class='trailEnd'>" . _('Run Query') . "</div>" ;
	print "</div>" ;
	
	//Check if school year specified
	$queryBuilderQueryID=$_GET["queryBuilderQueryID"] ;
	$save=NULL ;
	if (isset($_POST["save"])) {
		$save=$_POST["save"] ;
	}
	
	if ($queryBuilderQueryID=="") {
		print "<div class='error'>" ;
			print _("You have not specified one or more required parameters.") ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("queryBuilderQueryID"=>$queryBuilderQueryID, "gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
			$sql="SELECT * FROM queryBuilderQuery WHERE queryBuilderQueryID=:queryBuilderQueryID AND (gibbonPersonID=:gibbonPersonID OR gibbonPersonID IS NULL) AND active='Y'" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print _("The specified record cannot be found.") ;
			print "</div>" ;
		}
		else {
			//Let's go!
			$row=$result->fetch() ;
			print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
				print "<tr>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>" ;
						print "<i>" . $row["name"] . "</i>" ;
					print "</td>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Category</span><br/>" ;
						print "<i>" . $row["category"] . "</i>" ;
					print "</td>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Active</span><br/>" ;
						print "<i>" . $row["active"] . "</i>" ;
					print "</td>" ;
				print "</tr>" ;
				if ($row["description"]!="") {
					print "<tr>" ;
						print "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>" ;
							print "<span style='font-size: 115%; font-weight: bold'>Description</span><br/>" ;
							print $row["description"] ;
						print "</td>" ;
					print "</tr>" ;
				}
			print "</table>" ;
			?>
			<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_run.php&queryBuilderQueryID=$queryBuilderQueryID&sidebar=false" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td colspan=2> 
							<b>Query *</b>
							<?php
							print "<div class='linkTop' style='margin-top: 0px'>" ;
								print "<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_help_full.php&width=1100&height=550'><img title='Query Help' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/help.png'/></a>" ;
							print "</div>" ;
							?>
							<textarea name="query" id='query' style="display: none;"><?php if (isset($_POST["query"])) { print htmlPrep($_POST["query"]) ; } else { print htmlPrep($row["query"]) ; } ?></textarea>
					
							<div id="editor" style='width: 1058px; height: 400px;'><?php if (isset($_POST["query"])) { print htmlPrep($_POST["query"]) ; } else { print htmlPrep($row["query"]) ; } ?></div>
	
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
							<?php
							if (is_null($row["queryID"])) {
								print "Save Query? <input " ;
								if ($save=="Y") {
									print "checked " ;
								}
								print "type='checkbox' name='save' value='Y'/> " ;
							}
							?>
							<input type="submit" value="<?php print _("Run Query") ; ?>">
						</td>
					</tr>
				</table>
			</form>
			<?php
			
			
			//PROCESS QUERY
			$query=NULL ;
			if (isset($_POST["query"])) {
				$query=$_POST["query"] ;
			}
			if (!is_null($query)) {
				print "<h3>" ;
					print "Query Results" ;
				print "</h3>" ;
				
				//Strip multiple whitespaces from string
				$query=preg_replace('/\s+/', ' ', $query);
				
				//Security check
				$illegal=FALSE ;
				$illegals=getIllegals() ;
				$illegalList="" ;
				foreach ($illegals AS $ill) {
					if (!(strpos($query, $ill)===FALSE)) {
						$illegal=TRUE ;
						$illegalList.=$ill . ", " ;
					}
				}
				if ($illegal) {
					print "<div class='error'>" ;
						print _("Your query contains the following illegal term(s), and so cannot be run:") . " <b>" . substr($illegalList, 0, -2) . "</b>." ;
					print "</div>" ;
				}
				else {
					//Save the query
					if ($save=="Y") {
						try {
							$data=array("queryBuilderQueryID"=>$queryBuilderQueryID, "query"=>$query); 
							$sql="UPDATE queryBuilderQuery SET query=:query WHERE queryBuilderQueryID=:queryBuilderQueryID" ;
							$result=$connection2->prepare($sql);
							$result->execute($data);
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
					}
					
					//Run the query
					try {
						$data=array(); 
						$result=$connection2->prepare($query);
						$result->execute($data);
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}
		
					if ($result->rowCount()<1) {
						print "<div class='warning'>Your query has returned 0 rows.</div>" ; 
					}
					else {
						print "<div class='success'>Your query has returned " . $result->rowCount() . " rows, which are displayed below.</div>" ; 
					
						print "<div class='linkTop'>" ;
							print "<form id='queryExport' method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/queries_run_export.php?queryBuilderQueryID=$queryBuilderQueryID'>" ;
								print "<input name='query' value=\"" . $query . "\" type='hidden'>" ;
								print "<input style='background:url(./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/download.png) no-repeat; cursor:pointer; min-width: 25px!important; max-width: 25px!important; max-height: 25px; border: none;' type='submit' value=''>" ;
							print "</form>" ;
						print "</div>" ;
						
						print "<div style='overflow-x:auto;'>" ;
							print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;	
								print "<tr>" ;
									for ($i=0; $i<$result->columnCount(); $i++) {
										$col=$result->getColumnMeta($i);
										if ($col["name"]!="password" AND $col["name"]!="passwordStrong" AND $col["name"]!="passwordStrongSalt") {
											print "<th style='min-width: 72px'>" ;
												print $col["name"] ;
											print "</th>" ;
										}
									}
								print "</tr>" ;
								while ($row=$result->fetch()) {
									print "<tr>" ;
										for ($i=0; $i<$result->columnCount(); $i++) {
											$col=$result->getColumnMeta($i);		
											if ($col["name"]!="password" AND $col["name"]!="passwordStrong" AND $col["name"]!="passwordStrongSalt") {
												print "<td>" ;
													if (strlen($row[$col["name"]])>50) {
														print substr($row[$col["name"]],0,50) . "..." ; 
													}
													else {
														print $row[$col["name"]] ;
													}
												print "</td>" ;
											}
										}
									print "</tr>" ;
								}
							print "</table>" ;
						print "</div>" ;
					}
				}
			}
		}
	}
}
?>