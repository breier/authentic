<?php
	/**************************************************************************
	* sync online file, requested by custom AJaX from start page every 30s in *
	* order to check for customers connected in NAS but disconnected in DB.   *
	* dependencies: session as $_session (login);                             *
	*               pgsql as $_pgobj (config).                                *
	**************************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");

		//include ssh
		//include proto
		//get ppp active -> array(
		//              "id" => "*80013ae5",
		//              "address" => "10.68.4.93",
		//              "caller-id" => "44:D9:E7:D0:3D:7C",
		//              "encoding" => "",
		//              "name" => "iliane.aguiar@attoweb.com.br",
		//              "service" => "pppoe",
		//              "uptime" => "1 year 2 month 1 week 2 day 15:04:01"
		//      );
		//discard service != "pppoe"
		//discard username found connected
		//insert into radacct (
		//              AcctSessionId => "80013ae5",
		//              AcctUniqueId => md5("80013ae5"),
		//              UserName => array["name"],
		//              NASIPAddress => "10.13.80.9", //analyse
		//              AcctStartTime => now() - interval 'array["uptime"]',
		//              AcctUpdateTime => now(),
		//              CalledStationId => "//unknown//interface//nas",
		//              CallingStationId => array["caller-id"],
		//              ServiceType => array["service"],
		//              FramedProtocol => "ppp",
		//              FramedIPAddress => array["address"]
		//      );
	}
?>
