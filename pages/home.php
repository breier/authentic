<?php
	/**************************************************************
	* home page file, included by start if no page is selected.   *
	* Responsible for general charts, tiles and welcome messages. *
	* dependencies: all classes in the config file;               *
	*               all classes in the login file.                *
	***************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");

	// Get tile info total customers
	$_pgobj->query("SELECT COUNT(id) AS total_customers FROM radusergroup WHERE groupname NOT IN ('full', 'admn', 'tech')");
	$total_customers = $_pgobj->result[0]['total_customers'];
	$_pgobj->query("SELECT COUNT(id) AS month_customers FROM at_userdata WHERE date > DATE_TRUNC('month', now())");
	$month_customers = $_pgobj->result[0]['month_customers'];

	// Get tile info onLine / offLine
	$query = "SELECT COUNT(username) AS online_customers FROM at_framedipaddress_accounts";
	$query.= " WHERE username NOT IN (SELECT username FROM radusergroup WHERE groupname IN ('full', 'admn', 'tech'))";
	$query.= " AND framedipaddress IS NOT NULL";
	$_pgobj->query($query);
	$online_customers = $_pgobj->result[0]['online_customers'];
	$offline_customers = $total_customers - $online_customers;

	// Get tile info Mbits Sold
	$plans = array();
	$customers_per_plan = array();
	$query = "SELECT COUNT(groupname) AS customers_per_plan, groupname FROM radusergroup";
	$query.= " WHERE groupname NOT IN ('full', 'admn', 'tech') GROUP BY groupname ORDER BY to_number(groupname, '99S')";
	if($_pgobj->query($query)) {
		for($i=0; $i<$_pgobj->rows; $i++) {
			$plans[] = $_pgobj->result[$i]['groupname'];
			$customers_per_plan[] = $_pgobj->result[$i]['customers_per_plan'];
		}
	}
	if(count($plans)) {
		$sold_bits_download = array();
		$sold_bits_upload = array();
		for($i=0; $i<count($plans); $i++) {
			$query = "SELECT \"value\" FROM radgroupreply WHERE (attribute ILIKE '%rate-limit%' OR attribute ILIKE '%data-rate%')";
			$query.= " AND groupname = '". $plans[$i] ."' ORDER BY attribute LIMIT 1";
			if($_pgobj->query($query)) {
				$temp = substr($_pgobj->result[0]['value'], strpos($_pgobj->result[0]['value'], '/'));
				$sold_bits_download[] = intval(substr($temp, 1, strpos($temp, ' '))) * $customers_per_plan[$i];
				$sold_bits_upload[] = intval($_pgobj->result[0]['value']) * $customers_per_plan[$i];
			} else {
				$sold_bits_download[] = intval($plans[$i]) * $customers_per_plan[$i];
				$sold_bits_upload[] = intval($plans[$i]) * $customers_per_plan[$i] * 0.4;
			}
		}
	} else {
		$sold_bits_download = array(0);
		$sold_bits_upload = array(0);
	}
	if($sold_bits_download[0]<500000) $total_sold_mbits_download = round(array_sum($sold_bits_download));
	else $total_sold_mbits_download = round(array_sum($sold_bits_download) / (1024 * 1024));
	if($sold_bits_upload[0]<100000) $total_sold_mbits_upload = round(array_sum($sold_bits_upload));
	else $total_sold_mbits_upload = round(array_sum($sold_bits_upload) / (1024 * 1024));

	// Get tile info total of open Tickets
	$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
	$query.= " SELECT COUNT (id) AS total_open_tickets FROM at_tickets at, alm";
	$query.= " WHERE at.id = alm.ticket_id AND alm.status";
	$_pgobj->query($query);
	$total_open_tickets = $_pgobj->result[0]['total_open_tickets'];
		// open tickets on the month
	$query = "WITH afm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status, date FROM at_ticket_messages ORDER BY ticket_id, date ASC)";
	$query.= " SELECT COUNT (id) AS month_open_tickets FROM at_tickets at, afm";
	$query.= " WHERE at.id = afm.ticket_id AND afm.status AND afm.date > DATE_TRUNC('month', now())";
	$_pgobj->query($query);
	$month_open_tickets = $_pgobj->result[0]['month_open_tickets'];
?>
					<div class="row tile_count">
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-users"></i> <?= $_msg->lang("Registrations"); ?></span>
							<div class="count"><a href="./?p=10"><?= $total_customers; ?></a></div>
							<span class="count_bottom">
								<a href="./?p=10#<?= date('m/Y'); ?>#1#2d">
									<strong class="green"><?= $month_customers; ?></strong> <?= $_msg->lang("This Month") ."\n"; ?>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-check-circle-o"></i> <?= $_msg->lang("On Line"); ?></span>
							<div class="count green"><?= $online_customers; ?></div>
							<span class="count_bottom">
								<a href="./?p=10#<?= $_msg->lang('disconnected'); ?>">
									<strong class="red"><?= $offline_customers; ?></strong> <?= $_msg->lang("Off Line") ."\n"; ?>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-dashboard"></i> <?= $_msg->lang("Mbits Sold"); ?></span>
							<div class="count"><?= $total_sold_mbits_download; ?></div>
							<span class="count_bottom"><strong><?= $total_sold_mbits_upload; ?></strong> <?= $_msg->lang("Upload"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-tags"></i> <?= $_msg->lang("Open Tickets"); ?></span>
							<div class="count"><a href="./?p=33"><?= $total_open_tickets; ?></a></div>
							<span class="count_bottom"><strong><?= $month_open_tickets; ?></strong> <?= $_msg->lang("This Month"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-user"></i> template</span>
							<div class="count">100</div>
							<span class="count_bottom"><i class="red"><i class="fa fa-sort-desc"></i>10% </i> From last Week</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-user"></i> Template</span>
							<div class="count">100</div>
							<span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>10% </i> From last Week</span>
						</div>
					</div>
				</div>
				<!-- Close and re-open parent outside <div class="row"> to fix double layout after tiles /-->
				<script src="<?= $_path->js; ?>/Chart.bundle.min.js"></script>
				<div class="row">
					<div id="main_chart" class="col-xl-9 col-md-12"><i class="fa fa-refresh fa-spin fa-fw"></i></div>
					<script type="text/javascript">
						$.ajax({
							url: '<?= $_path->ajax; ?>/charts/<?= $_settings->system["Main Chart"]; ?>.php',
							type: 'POST',
							data: 'ajax=1',
							success: function (response) { $("#main_chart").html(response); }
						});
					</script>
					<div id="users_per_plan_chart" class="col-xl-3 col-md-5 col-sm-5 col-ms-8 col-xs-12"><i class="fa fa-refresh fa-spin fa-fw"></i></div>
					<script type="text/javascript">
						$.ajax({
							url: '<?= $_path->ajax; ?>/charts/users_per_plan.php',
							type: 'POST',
							data: 'ajax=1',
							success: function (response) { $("#users_per_plan_chart").html(response); }
						});
					</script>
					<div id="users_ranking_chart" class="col-xl-4 col-md-7 col-sm-7 col-xs-12"><i class="fa fa-refresh fa-spin fa-fw"></i></div>
					<script type="text/javascript">
						$.ajax({
							url: '<?= $_path->ajax; ?>/charts/users_ranking.php',
							type: 'POST',
							data: 'ajax=1',
							success: function (response) { $("#users_ranking_chart").html(response); }
						});
					</script>
