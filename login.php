<?php
	/******************************************************
	* login is required by index and any AJaX file to get *
	* the objects: settings as $_settings;                *
	*              session as $_session;                  *
	*              messages as $_msg set by _SESSION.     *
	* dependencies: all classes in the config file.       *
	*******************************************************/

// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_path)) $_msg->error("Class Paths not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
// ----- Defining Settings ----- //
	include("$_path->php/settings.php");
	$_settings = new settings();
// ----- Setting Session Variables ----- //
	class session {
		function __construct() {
			global $_settings;
			$this->id = FALSE;
			$this->username = FALSE;
			$this->groupname = FALSE;
			$this->ip_address = FALSE;
			$this->mac_address = FALSE;
			$this->php_session_id = FALSE;
			$this->error = FALSE;
			$this->timeout = intval($_settings->system['Session Timeout']);
		}
	} $_session = new session();
	$posted_form = (isset($_POST['username']) && isset($_POST['password'])) ? (TRUE) : (FALSE);
// ----- Starting PHP Session anyway ----- //
	session_start();
	$_session->php_session_id = session_id();
// ----- Setting Session Language if set ----- //
	if(isset($_SESSION['lang'])) $_msg->lang_set($_SESSION['lang']);
// ----- Trying to sign out? ----- //
	if(isset($_GET['p'])) {
		if($_GET['p']=='off') {
			$_pgobj->query("SELECT username FROM at_session WHERE php_session_id = '$_session->php_session_id' AND status = TRUE");
			$_session->username = ($_pgobj->rows == 1) ? ($_pgobj->result[0]['username']) : (FALSE);
			if($_pgobj->query("UPDATE at_session SET status = FALSE WHERE php_session_id = '$_session->php_session_id' AND status = TRUE")) {
				$_record->write("session", "successfully signed out", "ip=$_SERVER[REMOTE_ADDR]");
				unset($_SESSION['php_session_id']);
			} header("Location: $_path->root/");
		}
	}
// ----- Going ahead if not signed in yet ----- //
	if(!isset($_SESSION['php_session_id'])) {
		$_pgobj->query("SELECT id FROM at_session WHERE php_session_id = '$_session->php_session_id'");
		if($_pgobj->rows == 0) {
			$query = 'INSERT INTO at_session (username, php_session_id, status) VALUES ($1, $2, FALSE )';
			$_pgobj->query_params($query, array(($posted_form) ? ($_POST['username']) : (NULL), $_session->php_session_id));
		}
// ----- Trying to sign in if is there a form submited ----- //
		if($posted_form) {
			$query = 'SELECT aud.id, rug.username, rug.groupname';
			$query.= ' FROM radusergroup rug LEFT JOIN radcheck rc ON rug.username = rc.username LEFT JOIN at_userdata aud ON rc.username = aud.username';
			$query.= ' WHERE rug.username = $1 AND rug.groupname IN (\'tech\', \'admn\', \'full\') AND rc.attribute = \'Cleartext-Password\' AND rc.value = $2';
			$_pgobj->query_params($query, array($_POST['username'], $_POST['password']));
			if($_pgobj->rows == 1) {
				$_session->id = $_pgobj->result[0]['id'];
				$_session->username = $_pgobj->result[0]['username'];
				$_session->groupname = $_pgobj->result[0]['groupname'];
				$_session->ip_address = $_SERVER['REMOTE_ADDR'];
				$_pgobj->query("UPDATE at_session SET status = FALSE WHERE username = '$_session->username' AND php_session_id <> '$_session->php_session_id'");
				$query = 'UPDATE at_session SET status = TRUE, date = CURRENT_TIMESTAMP, username = $1, ip_address = $2 WHERE php_session_id = $3';
				$_pgobj->query_params($query, array($_session->username, $_session->ip_address, $_session->php_session_id));
				if($_pgobj->rows == 1) {
					$_SESSION['php_session_id'] = $_session->php_session_id;
					if(!isset($_SESSION['side-menu'])) $_SESSION['side-menu'] = 'nav-md';
					$_record->write("session", "successfully signed in", "ip=$_session->ip_address");
				}
			} else {
				$_session->error = $_msg->lang("Invalid Username or Password!");
				$_record->write("session", "error invalid username/password", "ip=$_SERVER[REMOTE_ADDR]");
			}
		}
// ----- Already signed in OR signing out ----- //
	} else {
		$query = "SELECT aud.id, rug.username, rug.groupname, ats.date, ats.ip_address, ats.mac_address";
		$query.= " FROM radusergroup rug LEFT JOIN at_session ats ON rug.username = ats.username LEFT JOIN at_userdata aud ON rug.username = aud.username";
		$query.= " WHERE ats.php_session_id = '$_session->php_session_id' AND ats.status = TRUE";
		$_pgobj->query($query);
		if($_pgobj->rows == 1) {
			$_session->id = $_pgobj->result[0]['id'];
			$_session->username = $_pgobj->result[0]['username'];
			$_session->groupname = $_pgobj->result[0]['groupname'];
			$_session->ip_address = $_pgobj->result[0]['ip_address'];
			$_session->mac_address = $_pgobj->result[0]['mac_address'];
		} else header("Location: ./?p=off");
	// ----- Session Timeout Expired? ----- //
		if($_session->timeout) {
			$time_diff = time() - strtotime($_pgobj->result[0]['date']);
			if($time_diff > $_session->timeout) header("Location: ./?p=off");
		} $_pgobj->query("UPDATE at_session SET date = CURRENT_TIMESTAMP WHERE php_session_id = '$_session->php_session_id'");
	}
?>
