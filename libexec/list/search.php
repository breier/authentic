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
			case '5d': $order_by = ($list_type == 'equipment') ? ("aeq.ip_address") : ("afa.framedipaddress"); break;
			case '4a':
			case '4d': $order_by = ($list_type == 'equipment') ? ("aeq.ip_address") : ("aua.groupname"); break;
			case '3a':
			case '3d': $order_by = ($list_type == 'equipment') ? ("aeq.brand_name") : ("aua.mac_address"); break;
			case '2a':
			case '2d': $order_by = ($list_type == 'equipment') ? ("aeq.location") : ("aud.date"); break;
			default: $order_by = ($list_type == 'equipment') ? ("amt.equipment_name") : ("full_name"); break;
		} $sort_type = ($sortOrder[1] == 'a') ? ("ASC") : ("DESC");
		// ----- Query Equipments ----- //
		if($list_type == 'equipment') {
			$query = "WITH amt AS (SELECT DISTINCT ON (equipment_id) equipment_id, equipment_name, date FROM at_monitor ORDER BY equipment_id, date DESC)";
			if($user_id) {
				$query.= " SELECT amt.equipment_name, aeq.id, aeq.date, aeq.brand_name, aeq.service_type, aeq.service_port, aeq.username, aeq.password";
				$query.= ", aeq.category, aeq.groupname, aeq.ip_address, array_to_json(aeq.mac_address) AS mac_address, aeq.location, aeq.comments";
			} else $query.= " SELECT aeq.id, amt.date, amt.equipment_name, aeq.location, aeq.brand_name, aeq.ip_address";
			$query.= " FROM at_equipments aeq LEFT OUTER JOIN amt ON amt.equipment_id = aeq.id";
			if($user_id) $query.= " WHERE aeq.id = $user_id";
			$query.= " ORDER BY $order_by $sort_type";
		} else {
		// ----- Query Technicians and Administrators ----- //
			if($list_type != 'inet') {
				$query = "SELECT aua.username, array_to_json(aua.groupname) AS groupname, array_to_json(aua.priority) AS priority";
				$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
				$query.= ", afa.framedipaddress, aud.id, aud.date, NULL AS mac_address";
				if($user_id) $query.= ", aua.password, aud.data";
				$query.= " FROM at_userauth aua LEFT JOIN at_userdata aud ON aua.username = aud.username";
				$query.= " LEFT JOIN at_framedipaddress_accounts afa ON aud.username = afa.username";
				$query.= " WHERE ( aua.groupname && ARRAY['$list_type']";
				if($user_id) $query.= " AND aud.id = $user_id";
				$query.= " ) ORDER BY $order_by $sort_type";
			} else {
			// ----- Query Users ----- //
				if(!$user_id) {
					$query = "SELECT aua.username, array_to_json(aua.groupname) AS groupname, array_to_json(aua.priority) AS priority";
					$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
					$query.= ", afa.framedipaddress, aud.id, aud.date, aua.mac_address";
					$query.= " FROM at_userauth aua LEFT JOIN at_userdata aud ON aua.username = aud.username";
					$query.= " LEFT JOIN at_framedipaddress_accounts afa ON aud.username = afa.username";
					$query.= " WHERE NOT aua.groupname && ARRAY['full', 'admn', 'tech']";
					if(isset($_POST['search'])) {
						$search_string = pg_escape_string($_POST['search']);
						if(isset($_settings->system['Date Format']))
							$pg_date_format = str_replace(array('d', 'm', 'Y'), array('DD', 'MM', 'YYYY'), $_settings->system['Date Format']);
						else $pg_date_format = 'MM/DD/YYYY';
						$query.= " AND ( CASE WHEN aua.groupname && ARRAY['disabled'] THEN '". strtolower($_msg->lang("Disabled")) ."' ILIKE '%";
						$query.= $search_string ."%' ELSE array_to_string(aua.groupname, ' ') ILIKE '%". $search_string ."%' END";
						$query.= ' OR substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') ILIKE \'%'. $search_string .'%\'';
						$query.= " OR to_char(aud.date, '$pg_date_format') ILIKE '%". $search_string ."%'";
						$query.= " OR text(aua.mac_address) ILIKE '%". $search_string ."%'";
						$query.= " OR CASE WHEN afa.framedipaddress IS NULL THEN '". $_msg->lang("disconnected") ."' ILIKE '%". $search_string;
						$query.= "%' ELSE '". $_msg->lang("connected") ."' ILIKE '%". $search_string ."%' END )";
					} $query.= " ORDER BY $order_by $sort_type";
				} else {
				// ----- Query One User ----- //
					$query = "SELECT aua.username, array_to_json(aua.groupname) AS groupname, array_to_json(aua.priority) AS priority";
					$query.= ', substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS full_name';
					$query.= ", afa.framedipaddress, aud.id, aud.date, aua.password, aud.data";
					$query.= ", aua.mac_address, awt.name AS higher_name";
					$query.= " FROM at_userauth aua LEFT JOIN at_userdata aud ON aua.username = aud.username";
					$query.= " LEFT JOIN at_framedipaddress_accounts afa ON aud.username = afa.username";
					$query.= " LEFT JOIN at_technicians awt ON aud.higher_id = awt.id";
					$query.= " WHERE NOT aua.groupname && ARRAY['full', 'admn', 'tech'] AND aud.id = $user_id";
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
