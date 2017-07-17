<?php
	/*********************************************************
	* actions file, requested by custom AJaX from list page  *
	* at details modal in order to Enable / Disable / Delete *
	* - Customers / Users / Equipments.                      *
	* dependencies: messages as $_msg (config);              *
	*               session as $_session (login);            *
	*               pgsql as $_pgobj (config);               *
	*               path as $_path (config).                 *
	**********************************************************/

	if(isset($_POST['ajax']) && isset($_POST['action']) && isset($_POST['id'])) {
		require("../../config.php");
		include("../../login.php");
	// --- Defining Variables
		$action = pg_escape_string($_POST['action']);
		$list_type = (isset($_POST['type'])) ? (pg_escape_string($_POST['type'])) : ('user/customer');
		$user_id = intval($_POST['id']);
	// --- Checking Permissions
		if($_session->groupname == 'tech') die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
		if($list_type != 'equipment') {
			if(isset($_POST['username']) && $action == 'reset') {
				// Handle Post Deletion Reset
				$groupname_array = array();
				$username = pg_escape_string($_POST['username']);
			} else {
				$query = "SELECT array_to_json(aua.groupname) AS groupname, aud.username FROM at_userauth aua";
				$query.= " LEFT JOIN at_userdata aud ON aua.username = aud.username WHERE aud.id = $user_id";
				$_pgobj->query($query);
				if(!$_pgobj->rows) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
				$groupname_array = json_decode($_pgobj->result[0]['groupname']);
				$username = $_pgobj->result[0]['username'];
				if($_session->groupname == 'admn') {
					if(array_search('full', $groupname_array) !== FALSE) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
					if(array_search('admn', $groupname_array) !== FALSE) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
				}
			}
		} elseif($_session->groupname != 'full') die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
	// --- Defining Group Target
		if($list_type != 'equipment') {
			$user_customer = 'Customer';
			if(array_search('full', $groupname_array) !== FALSE) $user_customer = 'User';
			if(array_search('admn', $groupname_array) !== FALSE) $user_customer = 'User';
			if(array_search('tech', $groupname_array) !== FALSE) $user_customer = 'User';
		}
	// --- Disabling User / Customer
		if($action == 'disable') {
			if(array_search('disabled', $groupname_array) !== FALSE) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
			$_pgobj->query("INSERT INTO radusergroup (username, groupname, priority) VALUES ('$username', 'disabled', -2)");
			if($_pgobj->rows == 1) die(json_encode(array("alert-success", $_msg->lang("$user_customer successfully disabled!"))));
	// --- Enabling User / Customer
		} elseif($action == 'enable') {
			if(array_search('disabled', $groupname_array) === FALSE) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
			$_pgobj->query("DELETE FROM radusergroup WHERE username = '$username' AND groupname = 'disabled'");
			if($_pgobj->rows == 1) die(json_encode(array("alert-success", $_msg->lang("$user_customer successfully enabled!"))));
	// --- Deleting User / Customer / Equipment
		} elseif($action == 'delete') {
			// Deleting Equipment
			if($list_type == 'equipment') {
				$_pgobj->query_params('DELETE FROM at_equipments WHERE id = $1', array($user_id));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("Equipment not deleted!"))));
				else die(json_encode(array("alert-success", $_msg->lang("Equipment successfully deleted!"))));
			} else {
			// Deleting Customer / User
				$delete_count = 0;
				$_pgobj->query("DELETE FROM radusergroup WHERE username = '$username'");
				$delete_count += $_pgobj->rows;
				$_pgobj->query("DELETE FROM radreply WHERE username = '$username'");
				$_pgobj->query("DELETE FROM radcheck WHERE username = '$username'");
				$delete_count += $_pgobj->rows;
				$_pgobj->query("DELETE FROM at_userdata WHERE username = '$username'");
				$delete_count += $_pgobj->rows;
				if($delete_count >= 3) die(json_encode(array("alert-success", $_msg->lang("$user_customer successfully deleted!"), $username)));
			}
		} elseif($action == 'reset') {
	// --- Reseting User's Connection
			$_pgobj->query("SELECT nasipaddress FROM radacct WHERE username = '$username' ORDER BY acctstarttime DESC LIMIT 1");
			if($_pgobj->rows == 0) die();
			$nas_ip_address = $_pgobj->result[0]['nasipaddress'];
			$_pgobj->query("SELECT brand_name, service_type, service_port, username, password FROM at_equipments WHERE ip_address = '$nas_ip_address'");
			if($_pgobj->rows == 0) die(json_encode(array("alert-danger", $_msg->lang("NAS not found!"))));
			$nas_protocol_file = $_pgobj->result[0]['service_type'] .'-'. $_pgobj->result[0]['brand_name'] .'.php';
			if(!file_exists("$_path->proto/$nas_protocol_file")) die(json_encode(array("alert-danger", $_msg->lang("NAS protocol not found!"))));
		// Accessing Router
			if($_pgobj->result[0]['service_type'] == 'ssh') {
				require("$_path->php/sshc.php");
				$router_conn = new sshc($nas_ip_address, $_pgobj->result[0]['username'], $_pgobj->result[0]['password'], $_pgobj->result[0]['service_port']);
			} else die(json_encode(array("alert-danger", $_msg->lang("Service not yet supported!"))));
			require("$_path->proto/$nas_protocol_file");
			$router_parse = new device_proto($router_conn);
			if(!$router_parse->ppp_drop_user($username)) die(json_encode(array("alert-danger", $_msg->lang("$user_customer couldn't be reset!"))));
		}
	} else header("Location: /");
?>
