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
					$next_ont_substring = substr($output, $next_ont_position, ($next_endline_position - $next_ont_position));
					$last_slash_position = strrpos($next_ont_substring, '/');
					$next_ont_port = trim(substr($next_ont_substring, ($last_slash_position + 1)));
					$next_colon_position = strpos($output, ':', $next_endline_position);
					$next_space_position = strpos($output, ' ', ($next_colon_position + 4));
					$next_ont_sn = substr($output, ($next_colon_position + 2), 16);
					$next_ont_input = "<input type=\"radio\" name=\"onts\" value=\"$next_ont_port:$next_ont_sn\" onchange=\"tools_ontSelect(this.value);\" />";
					$ont_array[] = array("input" => $next_ont_input, "port" => $next_ont_port, "sn" => $next_ont_sn);
				} echo json_encode($ont_array);
			break;
			case 'activate':
				if(!isset($_POST['type']) || !isset($_POST['ont_port']) || !isset($_POST['ont_sn'])) die();
				if(!isset($_POST['customer_id']) || !isset($_POST['customer_description'])) die();
				$ont_port = intval($_POST['ont_port']);
				$ont_sn = substr(trim(str_replace(' ', '', $_POST['ont_sn'])), 0, 16);
				$ont_id = 1;//intval($_POST['customer_id']);
				$ont_desc = $_POST['customer_description'];
				$ssh_conn->shell("interface gpon 0/1");
				$ssh_conn->shell("ont add $ont_port $ont_id sn-auth $ont_sn omci ont-lineprofile-name atto-ppp ont-srvprofile-name atto-ppp desc $ont_desc");
				if($_POST['type'] == 'Bridge') $ssh_conn->shell("ont port native-vlan $ont_port $ont_id eth 1 vlan 2000 priority 4");
				$ssh_conn->shell("quit");
				$ssh_conn->shell("service-port $ont_id vlan 2000 gpon 0/1/$ont_port ont $ont_id gemport 0 multi-service user-vlan 2000 tag-transform translate inbound traffic-table index 6 outbound traffic-table index 6");
				$_msg->success("ONT activated as $_POST[type]!");
			break;
		} $ssh_conn->close();
	}
?>
