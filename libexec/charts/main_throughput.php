<?php
	/****************************************************************
	* main_throughput file, requested by custom AJaX from home page *
	* when set to display HTML5 canvas interactive Chart.js chart.  *
	* dependencies: messages as $_msg (config);                     *
	*               settings as $_settings (login);                 *
	*               pgsql as $_pgobj (config).                      *
	*****************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
	// Get Main Chart info form at_monitor
		$equipment_id = 1;
		$data_points = intval($_settings->system['Data Points']) + 1;
		$moment = array();
		$mbps_upload = array();
		$mbps_download = array();
		if($_pgobj->query("SELECT equipment_name, date, data FROM at_monitor WHERE equipment_id = $equipment_id ORDER BY date DESC LIMIT $data_points")) {
			for($i=($_pgobj->rows - 1); $i>=0; $i--) {
				$temp = $_pgobj->fetch_array($i);
				if(!@unserialize($temp['data'])) continue;
				$data = unserialize($temp['data']);
				if(!isset($previous_moment)) {
					if(!isset($data['throughput'])) break;
					if(!isset($data['throughput'][0][0])) break;
					$previous_moment = strtotime($temp['date']);
					$previous_download = $data['throughput'][0][0];
					$previous_upload = $data['throughput'][0][1];
					continue;
				} $actual_moment = strtotime($temp['date']);
				$actual_download = $data['throughput'][0][0];
				$actual_upload = $data['throughput'][0][1];
				if(!isset($diff_moment)) $diff_moment = ($actual_moment-$previous_moment);
				$moment[] = date("Y-m-d H:i", ($actual_moment-($diff_moment/2)));
			// --- Make it smart for many charts
			// $kpps_download[] = round(($actual_download-$previous_download)/1000/($actual_moment-$previous_moment), 2); // kpps
			// $kpps_upload[] = round(($actual_upload-$previous_upload)/1000/($actual_moment-$previous_moment), 2); // kpps
				$mbps_download[] = round((($actual_download-$previous_download)/(1024*1024)/($actual_moment-$previous_moment))*8); // Mbps
				$mbps_upload[] = round((($actual_upload-$previous_upload)/(1024*1024)/($actual_moment-$previous_moment))*8); // Mbps
				$previous_moment = $actual_moment;
				$previous_download = $actual_download;
				$previous_upload = $actual_upload;
			}
		}

		if(count($moment)>1) {
			$moment_str = "[ '".implode("', '", $moment)."' ]";
			$mbps_download_str = "[ ".implode(", ", $mbps_download)." ]";
			$mbps_upload_str = "[ ".implode(", ", $mbps_upload)." ]";
?>
							<div class="x_panel" style="min-height: 200px;">
								<div class="x_title">
									<h2><?= $_msg->lang("Main Throughput"); ?></h2>
									<div class="clearfix"></div>
								</div>
								<div class="x_content"><canvas id="chart_main_throughput" height="180"></canvas></div>
								<script>
									$(function () {
										var confMainThroughput = {
											type: 'line',
											data: {
												labels: <?= $moment_str; ?>,
												datasets: [{
													label: '<?= $_msg->lang("Upload"); ?>',
													data: <?= $mbps_upload_str; ?>,
													borderColor: 'rgba(224,32,0,0.6)',
													backgroundColor: 'rgba(224,32,0,0.1)'
												},{
													label: '<?= $_msg->lang("Download"); ?>',
													data: <?= $mbps_download_str; ?>,
													borderColor: 'rgba(0,128,224,0.6)',
													backgroundColor: 'rgba(0,128,224,0.1)'
												}]
											},
											options: {
												responsive: true,
												maintainAspectRatio: false,
												scales: {
													xAxes: [{
														type: 'time',
														time: {
															displayFormats: {
																minute: 'HH:mm',
																hour: 'HH:mm',
																day: 'HH:mm'
															},
															unitStepSize: <?= round($diff_moment/60); ?>,
															min: '<?= $moment[0]; ?>',
															max: '<?= $moment[(count($moment)-1)]; ?>'
														}
													}],
													yAxes: [{ stacked: true }]
												},
												tooltips: {
													callbacks: {
														beforeTitle: (function(tootlipObject){
															for (var k=0; k<tootlipObject.length; k++) {
																var tootlipString = tootlipObject[k]['xLabel'].toString();
																tootlipObject[k]['xLabel'] = tootlipString.replace(/(\d+)-(\d+)-(\d+)/, '$3/$2/$1');
																tootlipObject[k]['yLabel'] += ' Mbps';
															}
														})
													}
												}
											}
										};
										$.each(confMainThroughput.data.datasets, function(i, dataset) {
											dataset.lineTension = 0.3;
											dataset.borderWidth = 1.5;
											dataset.pointBorderWidth = 0.5;
											dataset.pointRadius = 2.5;
											dataset.pointHitRadius = 10;
										});
										var ctxMainThroughput = document.getElementById("chart_main_throughput").getContext("2d");
										window.chartMainThroughput = new Chart(ctxMainThroughput, confMainThroughput);
									});
								</script>
							</div>
<?php	} else $_msg->info("No throughput data at monitor table!");
	};
?>
