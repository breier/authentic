<?php
	/************************************************************
	* tiles_info file, requested by custom AJaX from home page  *
	* in order to gather general system metrics.                *
	* dependencies: messages as $_msg (config);                 *
	*               pgsql as $_pgobj (config).                  *
	*************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
		// Total Customers
		$_pgobj->query("SELECT COUNT(username) AS total_customers FROM at_userauth WHERE NOT groupname && ARRAY['full', 'admn', 'tech']");
		$total_customers = $_pgobj->result[0]['total_customers'];
		$_pgobj->query("SELECT COUNT(username) AS month_customers FROM at_userdata WHERE date > DATE_TRUNC('month', now())");
		$month_customers = $_pgobj->result[0]['month_customers'];
		// OnLine / OffLine
		$query = "SELECT COUNT(username) AS online_customers FROM at_framedipaddress_accounts";
		$query.= " WHERE username NOT IN (SELECT username FROM at_userauth WHERE groupname && ARRAY['full', 'admn', 'tech'])";
		$query.= " AND framedipaddress IS NOT NULL";
		$_pgobj->query($query);
		$online_customers = $_pgobj->result[0]['online_customers'];
		$offline_customers = $total_customers - $online_customers;
		// Disabled
		$_pgobj->query("SELECT username FROM at_userauth WHERE NOT groupname && ARRAY['full', 'admn', 'tech'] AND groupname && ARRAY['disabled']");
		$disabled_customers = $_pgobj->rows;
		$disabled_month = 0;
		$disabled_customers_array = $_pgobj->result;
		for($i=0; $i<count($disabled_customers_array); $i++) {
			$query = "SELECT username FROM radacct WHERE username = '". $disabled_customers_array[$i]['username'];
			$query.= "' AND acctstoptime > DATE_TRUNC('month', now()) AND groupname <> 'disabled'";
			$_pgobj->query($query);
			if($_pgobj->rows) $disabled_month++;
		}
		// Mbits Sold
		$plans = array();
		$customers_per_plan = array();
		$query = "SELECT COUNT(groupname) AS customers_per_plan, array_to_json(groupname) AS groupname, array_to_json(priority) AS priority";
		$query.= " FROM at_userauth WHERE NOT groupname && ARRAY['full', 'admn', 'tech', 'disabled'] GROUP BY groupname, priority";
		$_pgobj->query($query);
		for($i=0; $i<$_pgobj->rows; $i++) {
			$groupname_array = json_decode($_pgobj->result[$i]['groupname']);
			$priority_zero_index = array_search(0, json_decode($_pgobj->result[$i]['priority']));
			if(!$priority_zero_index) $priority_zero_index = 0;
			$plans[] = $groupname_array[$priority_zero_index];
			$customers_per_plan[] = $_pgobj->result[$i]['customers_per_plan'];
		}
		if(count($plans)) {
			$sold_bits_download = array(0);
			$sold_bits_upload = array(0);
			for($i=0; $i<count($plans); $i++) {
				$query = "SELECT \"value\" FROM radgroupreply WHERE (attribute ILIKE '%rate-limit%' OR attribute ILIKE '%data-rate%')";
				$query.= " AND groupname = '". $plans[$i] ."' ORDER BY attribute LIMIT 1";
				$_pgobj->query($query);
				$first_slash_position = substr($_pgobj->result[0]['value'], strpos($_pgobj->result[0]['value'], '/'));
				$sold_bits_download[] = intval(substr($first_slash_position, 1, strpos($first_slash_position, ' '))) * $customers_per_plan[$i];
				$sold_bits_upload[] = intval($_pgobj->result[0]['value']) * $customers_per_plan[$i];
			}
		} $total_sold_mbits_download = round(array_sum($sold_bits_download) / (1024 * 1024));
		$total_sold_mbits_upload = round(array_sum($sold_bits_upload) / (1024 * 1024));
		// Total Open Tickets
		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= " SELECT COUNT (id) AS total_open_tickets FROM at_tickets at, alm";
		$query.= " WHERE at.id = alm.ticket_id AND alm.status";
		$_pgobj->query($query);
		$total_open_tickets = $_pgobj->result[0]['total_open_tickets'];
		$query = "WITH afm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status, date FROM at_ticket_messages ORDER BY ticket_id, date ASC)";
		$query.= " SELECT COUNT (id) AS month_open_tickets FROM at_tickets at, afm";
		$query.= " WHERE at.id = afm.ticket_id AND afm.status AND afm.date > DATE_TRUNC('month', now())";
		$_pgobj->query($query);
		$month_open_tickets = $_pgobj->result[0]['month_open_tickets'];
		// Solved / Late Tickets
		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, date, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= " SELECT COUNT (id) AS solved_tickets FROM at_tickets at, alm";
		$query.= " WHERE at.id = alm.ticket_id AND alm.date > DATE_TRUNC('month', now()) AND NOT alm.status";
		$_pgobj->query($query);
		$solved_tickets = $_pgobj->result[0]['solved_tickets'];
		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= " SELECT COUNT (id) AS late_tickets FROM at_tickets at, alm";
		$query.= " WHERE at.id = alm.ticket_id AND at.deadline < now() AND alm.status";
		$_pgobj->query($query);
		$late_tickets = $_pgobj->result[0]['late_tickets'];
		// Reply JSON with ordered data
		$output_array = array(	"<a href=\"./?p=10\">$total_customers</a>", $month_customers,
										$online_customers, $offline_customers,
										$disabled_customers, $disabled_month,
										$total_sold_mbits_download, $total_sold_mbits_upload,
										"<a href=\"./?p=33\">$total_open_tickets</a>", $month_open_tickets,
										"<a href=\"./?p=33&closed\">$solved_tickets</a>", $late_tickets );
		echo json_encode($output_array);
	}
?>
