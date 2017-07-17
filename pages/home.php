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
?>
					<div class="row tile_count">
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-users"></i> <?= $_msg->lang("Registrations"); ?></span>
							<div class="count">00</div>
							<span class="count_bottom">
								<a href="./?p=10#<?= date('m/Y'); ?>#1#2d">
									<strong class="green">00</strong> <span class="green"><?= $_msg->lang("This Month"); ?></span>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-check-circle-o"></i> <?= $_msg->lang("On Line"); ?></span>
							<div class="count green">00</div>
							<span class="count_bottom red">
								<a href="./?p=10#<?= $_msg->lang('disconnected'); ?>">
									<strong class="red">00</strong> <span class="red"><?= $_msg->lang("Off Line"); ?></span>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-times-circle-o"></i> <?= $_msg->lang("Disabled"); ?></span>
							<div class="count">00</div>
							<span class="count_bottom"><strong>00</strong> <?= $_msg->lang("This Month"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-dashboard"></i> <?= $_msg->lang("Mbits Sold"); ?></span>
							<div class="count">00</div>
							<span class="count_bottom"><strong>00</strong> <?= $_msg->lang("Upload"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-tags"></i> <?= $_msg->lang("Open Tickets"); ?></span>
							<div class="count">00</div>
							<span class="count_bottom"><strong>00</strong> <?= $_msg->lang("This Month"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-thumbs-o-up"></i> <?= $_msg->lang("Solved Tickets"); ?></span>
							<div class="count">00</div>
							<span class="count_bottom">
								<strong class="red">00</strong> <span class="red"><?= $_msg->lang("Late Tickets"); ?></span>
							</span>
						</div>
						<script type="text/javascript">home_fillTilesInfo();</script>
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
