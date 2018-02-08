<?php
	/**********************************************************************
	* system initdb, included by config when the settings table is empty. *
	* This tool is meant to check for essenstial data for authentic and   *
	* fill all the missing information.                                   *
	* dependencies: pgsql as $_pgobj (config);                            *
	*               messages as $_msg (config);                           *
	***********************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	// ----- Check for at least one user ----- //
	if(!$_pgobj->select("radusergroup", array("groupname" => "full"))) {
		$_pgobj->query("INSERT INTO radusergroup (username, groupname) VALUES ('overlord', 'full')");
		$_pgobj->query("INSERT INTO radcheck (username, attribute, op, value) VALUES ('overlord', 'Cleartext-Password', ':=', 'authentic')");
	}
	// ----- Check for Main Settings ----- //
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Session Timeout")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Session Timeout', '0', 1)");
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Date Format")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Date Format', 'm/d/Y', 2)");
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Rows Per Page")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Rows Per Page', '10', 3)");
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Main Chart")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Main Chart', 'main_throughput', 4)");
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Data Points")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Data Points', '16', 5)");
	if(!$_pgobj->select("at_settings", array("category" => "system", "label" => "Currency")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('system', 'Currency', 'USD', 6)");
	// ----- Check for Today's List Settings ----- //
	if(!$_pgobj->select("at_settings", array("category" => "ticket_category")))
		$_pgobj->query("INSERT INTO at_settings (category, label, data, sequence) VALUES ('ticket_category', '1', 'Technical - Install', 1),
																													('ticket_category', '2', 'Technical - Customer', 2),
																													('ticket_category', '3', 'Technical - System', 3),
																													('ticket_category', '4', 'Technical - Infrastructure', 4),
																													('ticket_category', '5', 'Financial', 5)");
	if(!$_pgobj->select("at_settings", array("category" => "ticket_priority")))
		$_pgobj->query('INSERT INTO at_settings (category, label, data, sequence) VALUES (\'ticket_priority\', \'1\', \'a:2:{s:5:"title";s:4:"None";s:5:"color";s:4:"#CCC";}\', 1),
																													(\'ticket_priority\', \'2\', \'a:2:{s:5:"title";s:13:"Non-Important";s:5:"color";s:4:"#0CC";}\', 2),
																													(\'ticket_priority\', \'3\', \'a:2:{s:5:"title";s:8:"Standard";s:5:"color";s:4:"#0C0";}\', 3),
																													(\'ticket_priority\', \'4\', \'a:2:{s:5:"title";s:9:"Important";s:5:"color";s:4:"#F80";}\', 4),
																													(\'ticket_priority\', \'5\', \'a:2:{s:5:"title";s:6:"Urgent";s:5:"color";s:4:"#C00";}\', 5)');
?>
