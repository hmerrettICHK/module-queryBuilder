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

if (isActionAccessible($guid, $connection2, "/modules/Query Builder/queries_sync.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/queries.php'>" . _('Manage Queries') . "</a> > </div><div class='trailEnd'>" . _('Sync Queries') . "</div>" ;
	print "</div>" ;
	
	print "<p>" ;
		print "This page will automatically attempt to sync queries from the gibbonedu.com Query Builder valued added service. The results of the sync will be given below." ;
	print "<p>" ;
	
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
				url: "https://gibbonedu.org/gibboneducom/queryBuilder.php?callback=?",
				data: "gibboneduComOrganisationName=<?php print $gibboneduComOrganisationName ?>&gibboneduComOrganisationKey=<?php print $gibboneduComOrganisationKey ?>&service=queryBuilder&version=<?php print $version ?>",
				dataType: "jsonp",                
				jsonpCallback: 'fnsuccesscallback',
				jsonpResult: 'jsonpResult',
				success: function(data) {
					if (data['access']==='0') {
						$("#status").attr("class","error");
						$("#status").html('Checking gibbonedu.com for a license to access value added Query Builder shows that you do not have access. You have either not set up access, or your access has expired or is invalid. Email <a href=\'mailto:support@gibbonedu.org\'>support@gibbonedu.org</a> to register for value added services, and then enter the name and key provided in reply, or to seek support as to why your key is not working. You may still you your own queries without a valid license.') ;
					}
					else {
						$("#status").attr("class","success");
						$("#status").html('Success! Your system has a valid license to access value added Query Builder queries from gibbonedu.com. We are now syncing your queries. Watch here for results.') ;
						$.ajax({
							type: "POST",
            				url: "<?php print $_SESSION[$guid]["absoluteURL"] ?>/modules/Query Builder/queries_gibboneducom_sync_ajax.php",
							data: { gibboneduComOrganisationName: "<?php print $gibboneduComOrganisationName ?>", gibboneduComOrganisationKey: "<?php print $gibboneduComOrganisationKey ?>", service: "queryBuilder", queries: JSON.stringify(data) },
							success: function(data) {
								if (data==="fail") {
									$("#status").attr("class","error");
									$("#status").html('We could not sync your queries. Try again later.') ;
								}
								else {
									$("#status").attr("class","success");
									$("#status").html('Your queries have been successfully synced. Please <a href=\'<?php print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Query Builder/queries.php\'>click here</a> to return to your query list.') ;
								}
							},
							error: function (data, textStatus, errorThrown) {
								$("#status").attr("class","error");
								$("#status").html('We could not sync your queries. Try again later.') ;
							}
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
	
	print "<div id='status' class='warning'>" ;
		print "<div style='width: 100%; text-align: center'>" ;
			print "<img style='margin: 10px 0 5px 0' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/loading.gif' alt='Loading'/><br/>" ;
			print "Checking gibbonedu.com value added license status." ;
		print "</div>" ;
	print "</div>" ;
	
}
?>