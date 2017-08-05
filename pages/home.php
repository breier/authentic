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
	// ----- Defining Links to other pages ----- //
	$link_list_customers = './?p=10';
	$link_helpdesk = './?p=33';
	$link_add_customers = './?p=20';
	$link_form_fields = './?p=41';
	$link_add_admin = './?p=22';
	$link_manage_plans = './?p=32';
?>
					<div class="row tile_count">
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-users"></i> <?= $_msg->lang("Registrations"); ?></span>
							<div class="count"><a href="<?= $link_list_customers; ?>">0</a></div>
							<span class="count_bottom">
								<a href="<?= $link_list_customers .'#'. date('m/Y') .'#1#2d'; ?>">
									<strong class="green">0</strong> <span class="green"><?= $_msg->lang("This Month"); ?></span>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-check-circle-o"></i> <?= $_msg->lang("On Line"); ?></span>
							<div class="count green">0</div>
							<span class="count_bottom red">
								<a href="<?= $link_list_customers .'#'. $_msg->lang('disconnected'); ?>">
									<strong class="red">0</strong> <span class="red"><?= $_msg->lang("Off Line"); ?></span>
								</a>
							</span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-times-circle-o"></i> <?= $_msg->lang("Disabled"); ?></span>
							<div class="count"><a href="<?= $link_list_customers .'#'. $_msg->lang('disabled'); ?>">0</a></div>
							<span class="count_bottom"><strong>0</strong> <?= $_msg->lang("This Month"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-dashboard"></i> <?= $_msg->lang("Mbits Sold"); ?></span>
							<div class="count">0</div>
							<span class="count_bottom"><strong>0</strong> <?= $_msg->lang("Upload"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-tags"></i> <?= $_msg->lang("Open Tickets"); ?></span>
							<div class="count"><a href="<?= $link_helpdesk; ?>">0</a></div>
							<span class="count_bottom"><strong>0</strong> <?= $_msg->lang("This Month"); ?></span>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-ms-4 col-xs-6 tile_stats_count ellipsis">
							<span class="count_top"><i class="fa fa-thumbs-o-up"></i> <?= $_msg->lang("Solved Tickets"); ?></span>
							<div class="count"><a href="<?= $link_helpdesk .'&closed'; ?>">0</a></div>
							<span class="count_bottom">
								<strong class="red">0</strong> <span class="red"><?= $_msg->lang("Late Tickets"); ?></span>
							</span>
						</div>
					</div>
				</div>
				<!-- Close and re-open parent outside <div class="row"> to fix double layout after tiles /-->
				<script src="<?= $_path->js; ?>/Chart.bundle.min.js"></script>
				<div class="row">
					<div id="main_chart" class="col-xl-9 col-md-12"><i class="fa fa-refresh fa-spin fa-fw"></i></div>
					<script type="text/javascript">
					/* ----- Define AJaX request for Main Chart at Home Page ----- */
						function home_fillMainChart () {
							$.ajax({
								url: '<?= $_path->ajax; ?>/charts/<?= $_settings->system["Main Chart"]; ?>.php',
								type: 'POST',
								data: 'ajax=1',
								success: function (response) {
									$("#main_chart").html(response);
								//	setTimeout('home_fillMainChart()', 30*1000); // only when it updates data only
								}
							});
						}; home_fillMainChart();
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
<?php	/* ----- Display first use messages at Home Page ----- */
		if(!count($_settings->form_field)) { ?>
					<div class="clearfix"></div>
					<div class="alert alert-warning col-lg-4 col-md-6 col-sm-6 col-cs-12 text-justify">
						<i class="fa fa-quote-right fa-3x pull-right"></i>
						<h3 style="margin: 0;"><?= $_msg->lang("Looks like you're new here!"); ?></h3><br />
						<?= $_msg->lang("I suggest you to add some custom form fields before anything else.") ."\n"; ?>
						<?= $_msg->lang("We already have the required form fields, though.") ."\n"; ?>
						<?= $_msg->lang("Like Internet Plan, MAC Address, Full Name and Password.") ."\n"; ?>
						<?= $_msg->lang("You can have a look") ." <a class=\"strong\" href=\"$link_add_customers\">". $_msg->lang("here") ."</a>.\n"; ?>
						<?= $_msg->lang("Whenever you're ready, click") ." <a class=\"strong\" href=\"$link_form_fields\">". $_msg->lang("here") .'</a> '; ?>
						<?= $_msg->lang("to add form fields.") ."\n"; ?>
					</div>
<?php	} elseif(!$_pgobj->select("radusergroup", array("groupname" => "admn"))) { ?>
					<div class="clearfix"></div>
					<div class="alert alert-warning col-lg-4 col-md-6 col-sm-6 col-cs-12 text-justify">
						<i class="fa fa-quote-right fa-3x pull-right"></i>
						<h3 style="margin: 0;"><?= $_msg->lang("Add an Administrator!"); ?></h3><br />
						<?= $_msg->lang("I suggest you to add administrator user.") ."\n"; ?>
						<?= $_msg->lang("It's unwise to fool around with so much power.") ."\n"; ?>
						<?= $_msg->lang("To do the right thing, click") ." <a class=\"strong\" href=\"$link_add_admin\">". $_msg->lang("here") ."</a>.\n"; ?>
					</div>
<?php	} elseif(!$_pgobj->select("at_plans", array("id" => ""))) { ?>
					<div class="clearfix"></div>
					<div class="alert alert-warning col-lg-4 col-md-6 col-sm-6 col-cs-12 text-justify">
						<i class="fa fa-quote-right fa-3x pull-right"></i>
						<h3 style="margin: 0;"><?= $_msg->lang("One last thing!"); ?></h3><br />
						<?= $_msg->lang("I suggest you to add internet plans.") ."\n"; ?>
						<?= $_msg->lang("To get <strong>authentic</strong> ready to manage customers you got to have a plan.") ."\n"; ?>
						<?= $_msg->lang("Add your first plan clicking") ." <a class=\"strong\" href=\"$link_manage_plans\">". $_msg->lang("here") ."</a>.\n"; ?>
					</div>
<?php	} ?>
					<script type="text/javascript">
					/* ----- Define AJaX request for Tiles Info at Home Page ----- */
						function home_fillTilesInfo () {
							$.ajax({
								url: '<?= $_path->ajax; ?>/charts/tiles_info.php',
								type: 'POST',
								data: 'ajax=1',
								success: function (response) {
									if (response[0]!='[') alertPNotify ('alert-danger', response, 5000);
									else {
										var tilesInfo = JSON.parse(response);
										numberAnimate($(".row.tile_count div.count:nth(0) a"), $(".row.tile_count div.count:nth(0) a").html(), tilesInfo[0]);
										numberAnimate($(".row.tile_count strong:nth(0)"), $(".row.tile_count strong:nth(0)").html(), tilesInfo[1]);
										numberAnimate($(".row.tile_count div.count:nth(1)"), $(".row.tile_count div.count:nth(1)").html(), tilesInfo[2]);
										numberAnimate($(".row.tile_count strong:nth(1)"), $(".row.tile_count strong:nth(1)").html(), tilesInfo[3]);
										numberAnimate($(".row.tile_count div.count:nth(2) a"), $(".row.tile_count div.count:nth(2) a").html(), tilesInfo[4]);
										numberAnimate($(".row.tile_count strong:nth(2)"), $(".row.tile_count strong:nth(2)").html(), tilesInfo[5]);
										numberAnimate($(".row.tile_count div.count:nth(3)"), $(".row.tile_count div.count:nth(3)").html(), tilesInfo[6]);
										numberAnimate($(".row.tile_count strong:nth(3)"), $(".row.tile_count strong:nth(3)").html(), tilesInfo[7]);
										numberAnimate($(".row.tile_count div.count:nth(4) a"), $(".row.tile_count div.count:nth(4) a").html(), tilesInfo[8]);
										numberAnimate($(".row.tile_count strong:nth(4)"), $(".row.tile_count strong:nth(4)").html(), tilesInfo[9]);
										numberAnimate($(".row.tile_count div.count:nth(5) a"), $(".row.tile_count div.count:nth(5) a").html(), tilesInfo[10]);
										numberAnimate($(".row.tile_count strong:nth(5)"), $(".row.tile_count strong:nth(5)").html(), tilesInfo[11]);
									} setTimeout('home_fillTilesInfo()', 30*1000);
								}
							});
						}; home_fillTilesInfo();
					</script>
