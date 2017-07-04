<?php
	/***************************************************************
	* users_per_plan file, requested by custom AJaX from home page *
	* when set to display HTML5 canvas interactive Chart.js chart. *
	* dependencies: messages as $_msg (config);                    *
	*               path as $_path (config);                       *
	*               pgsql as $_pgobj (config).                     *
	****************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
	// Get chart info users per plan
		include("$_path->php/get_color.php"); // Function get_color($string, $alpha = 1);
		$plans = array();
		$temp_plans = array();
		$customers_per_plan = array();
		$plan_custom_color = array();
		// Get temporary plans array ordered by price
		$_pgobj->query("SELECT name FROM at_plans ORDER BY media, price");
		for($i=0; $i<$_pgobj->rows; $i++) $temp_plans[$_pgobj->result[$i]['name']] = array();
		$query = "SELECT COUNT(groupname) AS customers_per_plan, groupname FROM radusergroup";
		$query.= " WHERE groupname NOT IN ('full', 'admn', 'tech') GROUP BY groupname";
		if($_pgobj->query($query)) {
			for($i=0; $i<$_pgobj->rows; $i++) {
				$temp = $_pgobj->fetch_array($i);
				$temp_plans[$temp['groupname']]['customers_per_plan'] = $temp['customers_per_plan'];
				$temp_plans[$temp['groupname']]['plan_custom_color'] = get_color($temp['groupname'], 0.8);
			}
		}
		// Put each data on its array
		foreach($temp_plans as $key => $value) {
			$plans[] = $key;
			$customers_per_plan[] = (isset($value['customers_per_plan'])) ? ($value['customers_per_plan']) : (0);
			$plan_custom_color[] = (isset($value['plan_custom_color'])) ? ($value['plan_custom_color']) : (0);
		}
		// Prepare for chart
		if(count($plans)>1) {
			$plans_string = "[ '".implode("', '", $plans)."' ]";
			$plan_custom_color_string = "[ '".implode("', '", $plan_custom_color)."' ]";
			$customers_per_plan_string = "[ ".implode(", ", $customers_per_plan)." ]";
?>

								<div class="x_panel" style="min-height: 200px;">
									<div class="x_title">
										<h2><?= $_msg->lang("Users per Plan"); ?></h2>
										<div class="clearfix"></div>
									</div>
									<div class="x_content"><canvas id="chart_users_per_plan" height="160"></canvas></div>
									<script>
										$(function () {
											var ctxUsersPerPlan = document.getElementById("chart_users_per_plan").getContext("2d");
											window.chartUsersPerPlan = new Chart(ctxUsersPerPlan, {
												type: 'doughnut',
												data: {
													labels: <?= $plans_string; ?>,
													datasets: [{
														label: '<?= $_msg->lang("Total"); ?>',
														data: <?= $customers_per_plan_string; ?>,
														backgroundColor: <?= $plan_custom_color_string; ?>
													}]
												},
												options: {
													responsive: true,
													maintainAspectRatio: false,
													legend: { position: 'right' }
												}
											});
										});
									</script>
								</div>
<?php	} else $_msg->info("No plans registered!");
	};
?>
