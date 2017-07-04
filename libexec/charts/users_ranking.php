<?php
	/***************************************************************
	* users_ranking file, requested by custom AJaX from home page  *
	* when set to display HTML5 canvas interactive Chart.js chart. *
	* dependencies: messages as $_msg (config);                    *
	*               pgsql as $_pgobj (config).                     *
	****************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
	// Get chart info ranking
		$customers_download_utilisation = array();
		$customers_upload_utilisation = array();
		for($i=1; $i<15; $i++) { // Try last 14 months for movement
			$query = "SELECT rac.username,";
			$query.= " SUBSTRING(rgr.value FROM '^[0-9]+/([^ ]+)') AS download_plan,";
			$query.= " SUBSTRING(rgr.value FROM '^([^/]+)') AS upload_plan,";
			$query.= " EXTRACT(epoch FROM SUM(rac.acctstoptime - rac.acctstarttime)) AS session_time,";
			$query.= " SUM(rac.acctinputoctets) AS download_bits, SUM(rac.acctoutputoctets) AS upload_bits";
			$query.= " FROM radacct rac, radusergroup rug, radgroupreply rgr";
			$query.= " WHERE rac.acctstoptime IS NOT NULL AND rac.username = rug.username";
			$query.= " AND rac.acctstarttime > (now() - INTERVAL '$i MONTH')";
			$query.= " AND rug.groupname NOT IN ('full', 'admn', 'tech')";
			$query.= " AND rug.groupname = rgr.groupname";
			$query.= " AND (rgr.attribute ILIKE '%rate-limit%' OR rgr.attribute ILIKE '%data-rate%')";
			$query.= " GROUP BY rac.username, rgr.value";

			$_pgobj->query($query);
			if($_pgobj->rows < 1) continue; // Skip month with no movement
			for($j=0; $j<$_pgobj->rows; $j++) {
				$temp = $_pgobj->fetch_array($j);
				$customers_download_utilisation[$temp['username']] = round((($temp['download_bits'] * 8) / $temp['session_time']) * 100 / $temp['download_plan'], 2);
				$customers_upload_utilisation[$temp['username']] = round((($temp['upload_bits'] * 8) / $temp['session_time']) * 100 / $temp['upload_plan'], 2);
			} break;
		}
		// Function to reverse sort keeping keys
		function cmp($a, $b) {
			if ($a == $b) return 0;
			return ($a > $b) ? (-1) : (1);
		}

		uasort($customers_download_utilisation, 'cmp');
		uasort($customers_upload_utilisation, 'cmp');
		$customers_download_utilisation = array_slice($customers_download_utilisation, 0, 4);
		$customers_upload_utilisation = array_slice($customers_upload_utilisation, 0, 4);

		if(count($customers_download_utilisation)>1) {
			$user_ranking_labels = "[ '". $_msg->lang("1st") ."', '". $_msg->lang("2nd") ."', '". $_msg->lang("3rd");
			for($i=4; $i<=count($customers_download_utilisation); $i++) $user_ranking_labels.= "', '". $_msg->lang("$i"."th");
			$user_ranking_labels.= "' ]";
			$customers_upload_utilisation_string = "[ ".implode(", ", $customers_upload_utilisation)." ]";
			$customers_download_utilisation_string = "[ ".implode(", ", $customers_download_utilisation)." ]";

			$upload_usersnames = array(); $download_usersnames = array();
			foreach($customers_upload_utilisation as $temp => $vlup)
				$upload_usersnames[] = ucwords(str_replace('.', ' ', substr($temp, 0, strpos($temp, '@'))));
			foreach($customers_download_utilisation as $temp => $vlup)
				$download_usersnames[] = ucwords(str_replace('.', ' ', substr($temp, 0, strpos($temp, '@'))));
?>

								<div class="x_panel" style="min-height: 200px;">
									<div class="x_title">
										<h2><?= $_msg->lang("Users Ranking"); ?></h2>
										<div class="clearfix"></div>
									</div>
									<div class="x_content">
										<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12"><canvas id="chart_ranking_upload" height="160"></canvas></div>
										<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12"><canvas id="chart_ranking_download" height="160"></canvas></div>
									</div>
									<script>
										$(function () {
											var labelRankingUpload = JSON.parse('<?= json_encode($upload_usersnames); ?>');
											var ctxRankingUpload = document.getElementById("chart_ranking_upload").getContext("2d");
											window.chartRankingUpload = new Chart(ctxRankingUpload, {
												type: 'horizontalBar',
												data: {
													labels: <?= $user_ranking_labels; ?>,
													datasets: [{
														label: '<?= $_msg->lang("Upload"); ?>',
														data: <?= $customers_upload_utilisation_string; ?>,
														borderColor: 'rgba(224,32,0,0.6)',
														backgroundColor: 'rgba(224,32,0,0.4)',
														borderWidth: 0.5
													}]
												},
												options: {
													responsive: true,
													maintainAspectRatio: false,
													tooltips: {
														callbacks: {
															beforeTitle: (function (tooltipObject) {
																for (var k=0; k<tooltipObject.length; k++) {
																	tooltipObject[k]['xLabel'] += ' %';
																	var tooltipString = parseInt(tooltipObject[k]['yLabel']) - 1;
																	tooltipObject[k]['yLabel'] = labelRankingUpload[tooltipString];
																}
															})
														}
													}
												}
											});
											var labelRankingDownload = JSON.parse('<?= json_encode($download_usersnames); ?>');
											var ctxRankingDownload = document.getElementById("chart_ranking_download").getContext("2d");
											window.chartRankingDownload = new Chart(ctxRankingDownload, {
												type: 'horizontalBar',
												data: {
													labels: <?= $user_ranking_labels; ?>,
													datasets: [{
														label: '<?= $_msg->lang("Download"); ?>',
														data: <?= $customers_download_utilisation_string; ?>,
														borderColor: 'rgba(0,128,224,0.6)',
														backgroundColor: 'rgba(0,128,224,0.4)',
														borderWidth: 0.5
													}]
												},
												options: {
													responsive: true,
													maintainAspectRatio: false,
													tooltips: {
														callbacks: {
															beforeTitle: (function(tooltipObject){
																for (var k=0; k<tooltipObject.length; k++) {
																	tooltipObject[k]['xLabel'] += ' %';
																	var tooltipString = parseInt(tooltipObject[k]['yLabel']) - 1;
																	tooltipObject[k]['yLabel'] = labelRankingDownload[tooltipString];
																}
															})
														}
													}
												}
											});
										});
									</script>
								</div>
<?php	} else $_msg->info("No accounting info found!");
	};
?>
