<?php
	/****************************************************************************
	* check timeout file, requested by custom AJaX from start page in case      *
	* Session Timeout is set and equal to zero, in order to check the remaining *
	* Session Time that might have been changed by other AJaX requests. This    *
	* returns the remaining time in seconds in a safe sized string.             *
	* dependencies: session as $_session (login);                               *
	*               pgsql as $_pgobj (config).                                  *
	*****************************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
// ----- Defining Settings ----- //
		require("$_path->php/settings.php");
		$_settings = new settings();
		$timeout = (isset($_settings->system['Session Timeout'])) ? (intval($_settings->system['Session Timeout'])) : (0);
// ----- Starting PHP Session anyway ----- //
		session_start();
		$php_session_id = session_id();
		if(!isset($_SESSION['php_session_id'])) die("00\tminimal\n");
// ----- Getting and printing the remaining time if ----- //
		$_pgobj->query("SELECT date FROM at_session WHERE php_session_id = '$php_session_id' AND status = TRUE");
		if($_pgobj->rows == 0) die("00\tminimal\n");
		else {
			$diff = (time() - strtotime($_pgobj->result[0]['date']));
			if($diff > $timeout) die("00\tminimal\n");
			echo ($timeout - $diff) ."\tminimal\n";
		}
	} else die("00\tminimal\n");
?>
