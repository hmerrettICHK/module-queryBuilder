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

if (isModuleAccessible($guid, $connection2)==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>" . _('Queries') . "</div>" ;
	print "</div>" ;
	
	$gibboneduComOrganisationName=getSettingByScope( $connection2, "System", "gibboneduComOrganisationName" ) ;
	$gibboneduComOrganisationKey=getSettingByScope( $connection2, "System", "gibboneduComOrganisationKey" ) ;
	
	print "<script type=\"text/javascript\">" ;
		print "$(document).ready(function(){" ;
			?>
			$.ajax({
				crossDomain: true,
				type:"GET",
				contentType: "application/json; charset=utf-8",
				async:false,
				url: "https://gibbonedu.org/gibboneducom/keyCheck.php?callback=?",
				data: "gibboneduComOrganisationName=<?php print $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php print $gibboneduComOrganisationKey ?>&service=queryBuilder",
				dataType: "jsonp",                
				jsonpCallback: 'fnsuccesscallback',
				jsonpResult: 'jsonpResult',
				success: function(data) {
					if (data['access']==='1') {
						$("#status").attr("class","success");
						$("#status").html('Success! Your system has a valid license to access value added Query Builder queries from gibbonedu.com. <a href=\'<?php print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Query Builder/queries_sync.php\'>Click here</a> to get the latest queries for your version of Gibbon.') ;
					}
					else {
						$("#status").attr("class","error");
						$("#status").html('Checking gibbonedu.com for a license to access value added Query Builder shows that you do not have access. You have either not set up access, or your access has expired or is invalid. Email <a href=\'mailto:support@gibbonedu.org\'>support@gibbonedu.org</a> to register for value added services, and then enter the name and key provided in reply, or to seek support as to why your key is not working. You may still you your own queries without a valid license.') ;
						$.ajax({
							url: "<?php print $_SESSION[$guid]["absoluteURL"] ?>/modules/Query Builder/queries_gibboneducom_remove_ajax.php",
							data: "gibboneduComOrganisationName=<?php print $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php print $gibboneduComOrganisationKey ?>&service=queryBuilder"
						});
					}
				},
				error: function (data, textStatus, errorThrown) {
					$("#status").attr("class","error");
					$("#status").html('Checking gibbonedu.com license for access to value added Query Builder queries has failed. You may still you your own queries.') ;
					$.ajax({
						url: "<?php print $_SESSION[$guid]["absoluteURL"] ?>/modules/Query Builder/queries_gibboneducom_remove_ajax.php",
						data: "gibboneduComOrganisationName=<?php print $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php print $gibboneduComOrganisationKey ?>&service=queryBuilder"
					});
				}
			});
			<?php
		print "});" ;
	print "</script>" ;
	
	print "<div id='output'>" ;
		print "<div id='status' class='warning'>" ;
			print "<div style='width: 100%; text-align: center'>" ;
				print "<img style='margin: 10px 0 5px 0' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/loading.gif' alt='Loading'/><br/>" ;
				print "Checking gibbonedu.com value added license status." ;
			print "</div>" ;
		print "</div>" ;
	
		try {
			$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
			$sql="SELECT * FROM queryBuilderQuery WHERE (gibbonPersonID IS NULL OR gibbonPersonID=:gibbonPersonID) ORDER BY category, gibbonPersonID, name" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}

		print "<div class='linkTop'>" ;
		print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_add.php&sidebar=false'><img title='" . _('Add New Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.png'/></a>" ;
		print "</div>" ;
	
		if ($result->rowCount()<1) {
			print "<div class='error'>" ;
			print _("There are no records to display.") ;
			print "</div>" ;
		}
		else {
			print "<table cellspacing='0' style='width: 100%'>" ;
				print "<tr class='head'>" ;
					print "<th>" ;
						print _("Type") ;
					print "</th>" ;
					print "<th>" ;
						print _("Category") ;
					print "</th>" ;
					print "<th>" ;
						print _("Name") ;
					print "</th>" ;
					print "<th>" ;
						print _("Active") ;
					print "</th>" ;
					print "<th>" ;
						print _("Actions") ;
					print "</th>" ;
				print "</tr>" ;
			
				$count=0;
				$rowNum="odd" ;
				while ($row=$result->fetch()) {
					if ($count%2==0) {
						$rowNum="even" ;
					}
					else {
						$rowNum="odd" ;
					}
					
					if ($row["active"]=="N") {
						$rowNum="error" ;
					}
				
					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
						print "<td>" ;
							if (is_null($row["queryID"])==FALSE) {
								print "gibbonedu.com" ;
							}
							else {
								print "Personal" ;
							}
						print "</td>" ;
						print "<td>" ;
							print $row["category"] ;
						print "</td>" ;
						print "<td>" ;
							print $row["name"] ;
						print "</td>" ;
						print "<td>" ;
							print $row["active"] ;
						print "</td>" ;
						print "<td>" ;
							if (is_null($row["queryID"])) {
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_edit.php&queryBuilderQueryID=" . $row["queryBuilderQueryID"] . "&sidebar=false'><img title='" . _('Edit Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_delete.php&queryBuilderQueryID=" . $row["queryBuilderQueryID"] . "'><img title='" . _('Delete Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
							}
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_duplicate.php&queryBuilderQueryID=" . $row["queryBuilderQueryID"] . "'><img title='Duplicate' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/copy.png'/></a>" ;
							if ($row["active"]=="Y") {
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/queries_run.php&queryBuilderQueryID=" . $row["queryBuilderQueryID"] . "&sidebar=false'><img style='margin-left: 6px' title='Run Query' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/run.png'/></a>" ;
							}
						print "</td>" ;
					print "</tr>" ;
				
					$count++ ;
				}
			print "</table>" ;
		}
	print "</div>" ;
	
}	
?>