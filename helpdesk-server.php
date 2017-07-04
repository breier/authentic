<?php
	/******************************************************
	* to be filled...                                     *
	*******************************************************/
	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['request'])) {
		require("./config.php");				// main config file
		include("$_path->php/record.php");	// internal log class
		$_record = new record();				// internal log object
	// manual login the user
		$query = 'SELECT aud.id, rug.username, rug.groupname';
		$query.= ' FROM radusergroup rug LEFT JOIN radcheck rc ON rug.username = rc.username LEFT JOIN at_userdata aud ON rc.username = aud.username';
		$query.= ' WHERE rug.username = $1 AND rug.groupname IN (\'tech\', \'admn\') AND rc.attribute = \'Cleartext-Password\' AND rc.value = $2';
		$_pgobj->query_params($query, array($_POST['username'], $_POST['password']));
		if($_pgobj->rows == 1) {
			$_session_id = $_pgobj->result[0]['id'];
			$_session_username = $_pgobj->result[0]['username'];
			$_session_groupname = $_pgobj->result[0]['groupname'];
			$_session_ip_address = $_SERVER['REMOTE_ADDR'];
		} else {
			echo $_msg->lang("Invalid Username or Password!");
			$_record->write("session", "error invalid username/password", "ip=$_SERVER[REMOTE_ADDR];username=$_POST[username];password=$_POST[password]");
		}
	// check for request
		if(isset($_session_id)) {
			$request = pg_escape_string($_POST['request']);
			switch($request) {
				case 'login': echo "SUCCESS"; break;
				case 'send':
					echo "working on the database"; // I should
				break;
				case 'sync':
					echo "donwloading from the database"; // I should
				break;
				default:
					echo $_msg->lang("Unknown request!");
					$_record->write("app", "error unknown request", "ip=$_session_ip_address;user_id=$_session_id");
				break;
			}
		}
	// finish
		$_record->close(); // close log file
	} else print_r($_POST);
?>
