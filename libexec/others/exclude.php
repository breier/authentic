<?php
	if(isset($_POST['ajax']) &&
		isset($_POST['type']) &&
		isset($_POST['ac']) &&
		isset($_POST['id']) &&
		isset($_POST['whois'])) {
		require("../config.php");
		include(I."/php/history.php");
		$whois = $_POST['whois'];

		switch($_POST['ac']) {
		// ### actions, enable/disable ### //
			case '1':
			case '2':
				switch($_POST['type']) {
				// ----- Internet Clients and Technicians ----- //
					case '1':
					case '6':
					case '3':
					case '7':
						$us = '0';
						$id = (strstr($_POST['id'], '|')) ? (explode('|', $_POST['id'])) : (array($_POST['id']));
						for($nu=0; $nu<sizeof($id); $nu++) {
							$q1 = 'SELECT usnm FROM at_userdata WHERE id = '.$id[$nu];
							if($r1 = pg_query($q1)) {
								if(pg_num_rows($r1)==1) {
									$a1 = pg_fetch_array($r1);
									$us = $a1['usnm'];
								}	else die('<p>ERRO</p>');
							}	else die('<p>ERRO</p>');
						// ### actions enable ### //
							if($_POST['ac']==1) {
								$qr = "UPDATE radcheck SET attribute = 'Cleartext-Password' WHERE username = '$us' AND attribute = 'User-Password'";
								if(!$rs = pg_query($qr)) die('<p>ERRO</p>');
								if(pg_affected_rows($rs) != 1) die('<p>ERRO</p>');
								history($whois, 4, 2, $us);
								die("<p>SUCESSO</p>\n");
							}
						// ### actions disable ### //
							$qr = "UPDATE radcheck SET attribute = 'User-Password' WHERE username = '$us' AND attribute = 'Cleartext-Password'";
							if(!$rs = pg_query($qr)) die('<p>ERRO</p>');
							if(pg_affected_rows($rs) != 1) die('<p>ERRO</p>');
							history($whois, 4, 3, $us);

							require_once(I."/php/cfgssh.php");
							$qsh = "SELECT * FROM at_equip WHERE type = 'srvr'";
							if($rsh = pg_query(CONN, $qsh)) {
								if(pg_num_rows($rsh)==1) {
									$hua = pg_fetch_array($rsh);
									if(stristr($hua['brnd'], 'MikroTik')) {
										$cmd = "/ppp active remove [find name=\"$us\"];";
										if($hua['srvc']=='ssh') {
											if(!cfgssh($hua['ipad'], $hua['usnm'], $hua['pass'], $cmd, $hua['port'])) {
												$cmd = "/ip hotspot active remove [find user=\"$us\"];";
												if(!cfgssh($hua['ipad'], $hua['usnm'], $hua['pass'], $cmd, $hua['port'])) die('<p>ERRO</p>');
											}
										}
									}
								}
							}
						} echo "<p>SUCESSO</p>\n";
					break;
					default: break;
				}
			break;
		// ### action remove ### //
			case '3':
				switch($_POST['type']) {
				// ----- Internet Clients and Technicians ----- //
					case '1':
					case '5':
					case '6':
					case '3':
					case '7':
						$us = '0';
						$id = (strstr($_POST['id'], '|')) ? (explode('|', $_POST['id'])) : (array($_POST['id']));
						for($nu=0; $nu<sizeof($id); $nu++) {
							$q1 = 'SELECT usnm FROM at_userdata WHERE id = '.$id[$nu];
							if($r1 = pg_query($q1)) {
								if(pg_num_rows($r1)==1) {
									$a1 = pg_fetch_array($r1);
									$us = $a1['usnm'];
								}	else die('<p>ERRO</p>');
							}	else die('<p>ERRO</p>');

							$qr = array(	"DELETE FROM radusergroup WHERE username = '$us'",
												"DELETE FROM radcheck WHERE username = '$us'",
												"DELETE FROM radreply WHERE username = '$us'",
												"DELETE FROM at_userdata WHERE usnm = '$us'"	);
							foreach($qr as $cm) {
								if(!$rs = pg_query($cm)) die('<p>ERRO</p>');
							//	if(pg_affected_rows($rs) < 1) die('<p>ERRO</p>');
								history($whois, 4, 6, $us);
							}

							require_once(I."/php/cfgssh.php");
							$qsh = "SELECT * FROM at_equip WHERE type = 'srvr'";
							if($rsh = pg_query(CONN, $qsh)) {
								if(pg_num_rows($rsh)==1) {
									$hua = pg_fetch_array($rsh);
									if(stristr($hua['brnd'], 'MikroTik')) {
										$cmd = "/ppp active remove [find name=\"$us\"];";
										if($hua['srvc']=='ssh') {
											if(!cfgssh($hua['ipad'], $hua['usnm'], $hua['pass'], $cmd, $hua['port'])) {
												$cmd = "/ip hotspot active remove [find user=\"$us\"];";
												if(!cfgssh($hua['ipad'], $hua['usnm'], $hua['pass'], $cmd, $hua['port'])) die('<p>ERRO</p>');
											}
										}
									}
								}
							}
						} echo "<p>SUCESSO</p>\n";
					break;
					default: break;
				}
			break;
			default: break;
		}
	}
?>
