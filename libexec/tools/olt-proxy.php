<?php
	/***********************************************************
	* tools ol proxy, requested by custom AJaX from tools page *
	* in order to get ONTs and activate them.                  *
	************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['action'])) {
		require("../../config.php");
		require("../../login.php");

		$_pgobj->query("SELECT ip_address, username, password, service_port FROM at_equipments WHERE category = 'olt'");
		if($_pgobj->rows == 0) die($_msg->lang("OLT not Found!"));

		require("$_path->php/sshc.php");
		$ssh_conn = new sshc($_pgobj->result[0]['ip_address'], $_pgobj->result[0]['username'], $_pgobj->result[0]['password'], $_pgobj->result[0]['service_port']);
		if($ssh_conn->error) die("$ssh_conn->error");

		$ssh_conn->shell("enable");
		$ssh_conn->shell("config");
		switch($_POST['action']) {
			case 'autofind':
				$output = "";
				$ssh_conn->shell("display ont autofind all");
				$output.= "$ssh_conn->output\n";
				while(strstr($ssh_conn->result, "{ <cr>|")) {
					$ssh_conn->shell(chr(13));
					$output.= "$ssh_conn->output\n";
				} while(strstr($ssh_conn->result, "---- More ( Press 'Q' to break ) ----")) {
					$ssh_conn->shell(chr(32));
					$output.= "$ssh_conn->output\n";
				}

				$ont_array = array();
				$next_ont_position = 0;
				while($next_ont_position = strpos($output, 'F/S/P', ($next_ont_position + 10))) {
					$next_endline_position = strpos($output, "\n", $next_ont_position);
					$next_gpon_substring = trim(substr($output, $next_ont_position, ($next_endline_position - $next_ont_position)));
					$last_space_position = strrpos($next_gpon_substring, ' ');
					$last_slash_position = strrpos($next_gpon_substring, '/');
					$next_gpon_slot = substr($next_gpon_substring, ($last_space_position + 1), ($last_slash_position - $last_space_position - 1));
					$next_gpon_port = trim(substr($next_gpon_substring, ($last_slash_position + 1)));
					$next_colon_position = strpos($output, ':', $next_endline_position);
					$next_space_position = strpos($output, ' ', ($next_colon_position + 4));
					$next_ont_sn = substr($output, ($next_colon_position + 2), 16);
					$next_ont_input = "<input type=\"radio\" name=\"onts\" value=\"$next_gpon_slot:$next_gpon_port:$next_ont_sn\" onchange=\"tools_ontSelect(this.value);\" />";
					$ont_array[] = array("input" => $next_ont_input, "port" => "$next_gpon_slot/$next_gpon_port", "sn" => $next_ont_sn);
				} echo json_encode($ont_array);
			break;
			case 'activate':
				if(!isset($_POST['type']) || !isset($_POST['gpon_slot']) || !isset($_POST['gpon_port']) || !isset($_POST['ont_sn'])) die();
				if(!isset($_POST['customer_id']) || !isset($_POST['customer_description'])) die();
				$gpon_slot = trim(str_replace(' ', '', $_POST['gpon_slot']));
				$gpon_port = intval($_POST['gpon_port']);
				$ont_sn = substr(trim(str_replace(' ', '', $_POST['ont_sn'])), 0, 16);
				$query = "SELECT service_port + 1 AS next FROM at_onts WHERE service_port + 1 NOT IN";
				$query.= " (SELECT service_port FROM at_onts) LIMIT 1";
				$_pgobj->query($query);
				$next_service_port = ($_pgobj->rows) ? ($_pgobj->result[0]['next']) : (0);
				$query = "SELECT ont_id + 1 AS next FROM at_onts WHERE gpon_slot = '$gpon_slot' AND gpon_port = $gpon_port AND ont_id + 1 NOT IN";
				$query.= " (SELECT ont_id FROM at_onts WHERE gpon_slot = '$gpon_slot' AND gpon_port = $gpon_port) LIMIT 1";
				$_pgobj->query($query);
				$next_ont_id = ($_pgobj->rows) ? ($_pgobj->result[0]['next']) : (0);
				require("$_path->php/str2ascii.php");
				$ont_desc = str2ascii($_POST['customer_description']);
				$ssh_conn->shell("interface gpon $gpon_slot");
				$ssh_conn->shell("ont add $gpon_port $next_ont_id sn-auth $ont_sn omci ont-lineprofile-name atto-ppp ont-srvprofile-name atto-ppp desc $ont_desc");
				if($_POST['type'] == 'Bridge') $ssh_conn->shell("ont port native-vlan $gpon_port $next_ont_id eth 1 vlan 2000 priority 4");
				$ssh_conn->shell("quit");
				$ssh_conn->shell("service-port $next_service_port vlan 2000 gpon $gpon_slot/$gpon_port ont $next_ont_id gemport 0 multi-service user-vlan 2000 tag-transform translate inbound traffic-table index 6 outbound traffic-table index 6");
				$_msg->success("ONT activated as $_POST[type]!");
				// --- Sync to Data Base
				$params = array($next_service_port, $gpon_slot, $gpon_port, $next_ont_id, $ont_sn, $_POST['type']);
				$_pgobj->query_params('INSERT INTO at_onts VALUES ($1, $2, $3, $4, $5, $6)', $params);
				if(intval($_POST['customer_id'])) {
					$ssh_conn->shell("interface gpon $gpon_slot");
					$ssh_conn->shell("display ont optical-info $gpon_port $ont_id");
					sleep(1);
					$output.= "$ssh_conn->output\n";
					while(strstr($ssh_conn->result, "---- More ( Press 'Q' to break ) ----")) {
						$ssh_conn->shell(chr(32));
						$output.= "$ssh_conn->output\n";
					} $ont_signal_label_position = strpos($output, 'OLT Rx ONT optical power(dBm)');
					$next_colon_position = strpos($output, ':', $ont_signal_label_position);
					$next_endline_position = strpos($output, "\n", $next_colon_position);
					$ont_signal = trim(substr($output, ($next_colon_position + 1), ($next_endline_position - $next_colon_position - 1)));
					$params = array(serialize(array("media" => "Fiber", "service_port" => $next_service_port, "signal" => $ont_signal)), $_POST['customer_id']);
					$_pgobj->query_params('UPDATE at_userdata SET connection = $1 WHERE id = $2', $params);
				}
			break;
		} $ssh_conn->close();
	}
?>
