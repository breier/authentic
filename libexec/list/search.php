<?php
	/****************************************************************
	* search file, requested by custom AJaX from list page in order *
	* to fetch information for users_table (also equipment_table).  *
	* dependencies: messages as $_msg (config);                     *
	*               session as $_session (login);                   *
	*               pgsql as $_pgobj (config);                      *
	*               path as $_path (config).                        *
	*****************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['type'])) {
		require("../../config.php");
		include("../../login.php");
		$list_type = $_POST['type'];
		if($_session->groupname!='full' && $list_type=='admn') die($_msg->lang("Unauthorized Query!"));

		$user_id = (isset($_POST['user_id'])) ? (intval($_POST['user_id'])) : (FALSE);
		$sortOrder = (isset($_POST['sort'])) ? ($_POST['sort']) : ('1a');
		switch($sortOrder) {
			case '5a':
			case '5d': $order_by = ($list_type == 'equipment') ? ("aeq.ip_address") : ("fa.framedipaddress"); break;
			case '4a':
			case '4d': $order_by = ($list_type == 'equipment') ? ("aeq.ip_address") : ("rug.groupname"); break;
			case '3a':
			case '3d': $order_by = ($list_type == 'equipment') ? ("aeq.brand_name") : ("ac.mac_address"); break;
			case '2a':
			case '2d': $order_by = ($list_type == 'equipment') ? ("aeq.location") : ("aud.date"); break;
			default: $order_by = ($list_type == 'equipment') ? ("amt.equipment_name") : ("full_name"); break;
		} $sort_type = ($sortOrder[1] == 'a') ? ("ASC") : ("DESC");
		// ----- Query Equipments ----- //
		if($list_type == 'equipment') {
			$query = "WITH amt AS (SELECT DISTINCT ON (equipment_id) equipment_id, equipment_name, date FROM at_monitor ORDER BY equipment_id, date DESC)";
			if($user_id) $query.= " SELECT amt.equipment_id, amt.equipment_name, aeq.*";
			else $query.= " SELECT amt.equipment_id AS id, amt.date, amt.equipment_name, aeq.location, aeq.brand_name, aeq.ip_address";
			$query.= " FROM at_equipments aeq LEFT OUTER JOIN amt ON amt.equipment_id = aeq.id";
			if($user_id) $query.= " WHERE amt.equipment_id = $user_id";
			$query.= " ORDER BY $order_by $sort_type";
		} else {
		// ----- Query Technicians and Administrators ----- //
			if($list_type != 'inet') {
				$query = "SELECT rug.username, rug.groupname";
				$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
				$query.= ", fa.framedipaddress, aud.id, aud.date, ac.passtype";
				if($user_id) $query.= ", ac.password, aud.data";
				$query.= ", NULL AS mac_address FROM radusergroup rug, at_check ac, at_userdata aud, at_framedipaddress_accounts fa";
				$query.= " WHERE ( rug.groupname = '$list_type' AND ac.username = rug.username";
				$query.= " AND aud.username = rug.username AND fa.username = rug.username";
				if($user_id) $query.= " AND aud.id = $user_id";
				$query.= " ) ORDER BY $order_by $sort_type";
			} else {
			// ----- Query Users ----- //
				if(!$user_id) {
					$query = "SELECT rug.username, rug.groupname";
					$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
					$query.= ", fa.framedipaddress, aud.id, aud.date, ac.passtype, ac.mac_address";
					$query.= " FROM radusergroup rug, at_check ac, at_userdata aud, at_framedipaddress_accounts fa";
					$query.= " WHERE ( rug.groupname NOT IN ('full', 'admn', 'tech') AND ac.username = rug.username";
					$query.= " AND aud.username = rug.username AND fa.username = rug.username )";
					if(isset($_POST['search'])) {
						$search_string = pg_escape_string($_POST['search']);
						$pg_date_format = (isset($_settings->system['Date Format'])) ? (str_replace(array('d', 'm', 'Y'), array('DD', 'MM', 'YYYY'), $_settings->system['Date Format'])) : ('MM/DD/YYYY');
						$query.= " AND ( rug.groupname ILIKE '%". $search_string ."%'";
						$query.= ' OR substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') ILIKE \'%'. $search_string .'%\'';
						$query.= " OR to_char(aud.date, '$pg_date_format') ILIKE '%". $search_string ."%'";
						$query.= " OR text(ac.mac_address) ILIKE '%". $search_string ."%'";
						$query.= " OR CASE WHEN fa.framedipaddress IS NULL THEN '". $_msg->lang("disconnected") ."' ILIKE '%". $search_string;
						$query.= "%' ELSE '". $_msg->lang("connected") ."' ILIKE '%". $search_string ."%' END )";
					} $query.= " ORDER BY $order_by $sort_type";
				} else {
				// ----- Query One User ----- //
					$query = "SELECT DISTINCT ON ($order_by) rug.username, rug.groupname";
					$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
					$query.= ", fa.framedipaddress, aud.id, aud.date, ac.passtype, ac.password, aud.data";
					$query.= ", ac.mac_address, awt.name AS higher_name";
					$query.= " FROM radusergroup rug, at_check ac, at_userdata aud, at_framedipaddress_accounts fa, at_technicians awt";
					$query.= " WHERE ( rug.groupname NOT IN ('full', 'admn', 'tech') AND ac.username = rug.username AND aud.higher_id = awt.id";
					$query.= " AND aud.username = rug.username AND fa.username = rug.username AND aud.id = $user_id ) ORDER BY $order_by $sort_type";
				}
			}
		}
// ----- Starting query loop ----- //
		if($_pgobj->query($query)) {
			if(!$user_id) include("$_path->ajax/list/view.php");
			else include("$_path->ajax/list/details.php");
		} else die($_msg->lang("Invalid Query!"));
	} else header("Location: /");
?>
