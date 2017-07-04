<?php
	/*************************************************************
	* register check_username, requested by "register_customers" *
	* and "register_users" files in order to check if the given  *
	* username already exists.                                   *
	* dependencies: session as $_session (login);                *
	*               pgsql as $_pgobj (config).                   *
	**************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['username'])) {
		require("../../config.php");
		require("../../login.php");
		// ----- Checking Permissions ----- //
		if($_session->groupname != 'full' && $_session->groupname != 'admn' && $_session->groupname != 'tech') die("UNAUTHORIZED");
		// ----- Try to Select an Existing User
		if($_pgobj->select("radusergroup", array("username" => pg_escape_string($_POST["username"])))) echo "TAKEN";
		else echo "OK";
	} else header("Location: /");
?>
