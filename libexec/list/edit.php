<?php
	/****************************************************************
	* edit file, requested by custom AJaX from list page at details *
	* modal in order to save modified information for Customers,    *
	* Users and Equipments.                                         *
	* dependencies: messages as $_msg (config);                     *
	*               session as $_session (login);                   *
	*               pgsql as $_pgobj (config);                      *
	*               path as $_path (config).                        *
	*****************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['type']) && isset($_POST['id'])) {
		require("../../config.php");
		include("../../login.php");
	// --- Defining Variables
		$list_type = pg_escape_string($_POST['type']);
		$edit_id = intval($_POST['id']);
	// --- Checking Permissions
		if($_session->groupname == 'tech' && $list_type != 'inet') die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
		if($_session->groupname == 'admn' && ($list_type == 'admn' || $list_type == 'equipment')) die(json_encode(array("alert-danger", $_msg->lang("Unauthorized Query!"))));
	// --- Defining Array
		$edit_array = array_diff_key($_POST, array("ajax" => '', "type" => '', "id" => 0));
		if(!count($edit_array)) die(json_encode(array("alert-danger", $_msg->lang("Nothing has changed!"))));
	// --- Editing Equipment
		if($list_type == 'equipment') {
			$counter = 0;
			$query = 'UPDATE at_equipments SET';
			foreach($edit_array as $key => $value) {
				if($counter) $query.= ",";
				$query.= " $key = \$". ++$counter;
			} $query.= ' WHERE id = $'. ++$counter;
			$edit_array[] = $edit_id;
			$_pgobj->query_params($query, $edit_array);
			if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
			die(json_encode(array("alert-success", $_msg->lang("Equipment successfully edited!"))));
		} else {
	// --- Editing Customer / User
			$_pgobj->query_params('SELECT username FROM at_userdata WHERE id = $1', array($edit_id));
			if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("Couldn't locate username!"))));
			$edit_username = $_pgobj->result[0]['username'];
			if(isset($edit_array['groupname'])) {
				$_pgobj->query_params('UPDATE radusergroup SET groupname = $1 WHERE username = $2', array($edit_array['groupname'], $edit_username));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				unset($edit_array['groupname']);
			} if(isset($edit_array['framedipaddress'])) {
				//implement fixed IP address later
				unset($edit_array['framedipaddress']);
			} if(isset($edit_array['mac_address'])) {
				$_pgobj->query_params('UPDATE radcheck SET "value" = $1 WHERE attribute = \'Calling-Station-Id\' AND username = $2', array($edit_array['mac_address'], $edit_username));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				unset($edit_array['mac_address']);
			} if(isset($edit_array['password'])) {
				$_pgobj->query_params('UPDATE radcheck SET "value" = $1 WHERE attribute = \'Cleartext-Password\' AND username = $2', array($edit_array['password'], $edit_username));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				unset($edit_array['password']);
			} if(isset($edit_array['username'])) {
				$_pgobj->query_params('UPDATE radusergroup SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				$_pgobj->query_params('UPDATE radreply SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				$_pgobj->query_params('UPDATE radacct SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				$_pgobj->query_params('UPDATE radpostauth SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				$_pgobj->query_params('UPDATE radcheck SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				if($_pgobj->rows < 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				$_pgobj->query_params('UPDATE at_userdata SET username = $1 WHERE username = $2', array($edit_array['username'], $edit_username));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				unset($edit_array['username']);
			} if(isset($edit_array['higher_id'])) {
				$_pgobj->query_params('UPDATE at_userdata SET higher_id = $1 WHERE id = $2', array($edit_array['higher_id'], $edit_id));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
				unset($edit_array['higher_id']);
			} if(count($edit_array)) {
				$_pgobj->query_params('SELECT data FROM at_userdata WHERE id = $1', array($edit_id));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("Couldn't locate user data!"))));
				$original_data = unserialize($_pgobj->result[0]['data']);
				$user_data = array_merge($original_data, $edit_array);
				$_pgobj->query_params('UPDATE at_userdata SET data = $1 WHERE id = $2', array(serialize($user_data), $edit_id));
				if($_pgobj->rows != 1) die(json_encode(array("alert-danger", $_msg->lang("The setting could not be saved!"))));
			}
		// --- End with success
			if($list_type == 'inet') die(json_encode(array("alert-success", $_msg->lang("Customer successfully edited!"))));
			else die(json_encode(array("alert-success", $_msg->lang("User successfully edited!"))));
		}
	} else header("Location: /");
?>
