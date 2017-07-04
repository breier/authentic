<?php
	/***************************************************************
	* start page file, included by index if the user is signed in. *
	* Responsible for the main layout and menus.                   *
	* dependencies: session as $_session (login);                  *
	*               pgsql as $_pgobj (config);                     *
	*               messages as $_msg (config);                    *
	*               paths as $_path (config).                      *
	****************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_path)) $_msg->error("Class Paths not set!");
	// ----- Setting User's Full Name ----- //
	$fancy_name = ucfirst($_session->username);
	$_pgobj->query("SELECT substring(data from ':\"name\";s:[0-9]+:\"([^\"]+)\";') AS name FROM at_userdata WHERE username = '$_session->username'");
	if($_pgobj->rows == 1) if(strlen($_pgobj->result[0]['name']) > 2) $fancy_name = $_pgobj->result[0]['name'];
	// ----- Setting User's Profile Picture ----- //
	$user_pic_path = "$_path->images/user.jpg";
	$_pgobj->query("SELECT picture FROM at_userdata WHERE username = '$_session->username' AND picture IS NOT NULL");
	if($_pgobj->rows == 1) if(file_exists("$_path->images/uploaded/". $_pgobj->result[0]['picture'])) $user_pic_path = "$_path->images/uploaded/". $_pgobj->result[0]['picture'];
	// ----- Setting Session Timeout Counter ----- //
	$timeout_counter = ($_session->timeout) ? (date("i:s", $_session->timeout)) : (FALSE);
?>
		<div class="container body">
	<!-- Left Menu Context //-->
			<div class="left_col">
				<div class="nav_title">
					<a href="./" class="site_title">
						<img src="<?= $_path->images; ?>/favicon.ico" alt="a"/>
						<span><?= $site_title_base ;?></span><span style="font-size: 17px;"> - <?= $site_version; ?></span>
					</a>
				</div>
				<div class="clearfix"></div>
				<div id="sidebar-menu">
					<ul class="nav side-menu">
						<li><a href="./"><i class="fa fa-home"></i> <?= $_msg->lang("Home"); ?> </a></li>
<?php	if($_session->groupname=='tech') { ?>
						<li><a href="./?p=10"><i class="fa fa-book"></i> <?= $_msg->lang("List") ." ". $_msg->lang("Customers"); ?> </a></li>
						<li><a href="./?p=20"><i class="fa fa-edit"></i> <?= $_msg->lang("Register") ." ". $_msg->lang("Customer"); ?> </a></li>
						<li><a href="./?p=33"><i class="fa fa-tags"></i> <?= $_msg->lang("Help Desk"); ?></a></li>
						<li><a href="./?p=30"><i class="fa fa-wrench"></i> <?= $_msg->lang("Tools"); ?> </a></li>
<?php	} else { ?>
						<li class="parent"><a><i class="fa fa-book"></i> <?= $_msg->lang("List"); ?> <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display: none">
								<li><a href="./?p=10"><?= $_msg->lang("Customers"); ?></a></li>
								<li><a href="./?p=11"><?= $_msg->lang("Technicians"); ?></a></li>
<?php		if($_session->groupname == 'full') { ?>
								<li><a href="./?p=12"><?= $_msg->lang("Administrators"); ?></a></li>
<?php		} if($_session->groupname != 'tech') { ?>
								<li><a href="./?p=13"><?= $_msg->lang("Equipments"); ?></a></li>
<?php		} ?>
							</ul>
						</li>
						<li class="parent"><a><i class="fa fa-edit"></i> <?= $_msg->lang("Register"); ?> <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display: none">
								<li><a href="./?p=20"><?= $_msg->lang("Customer"); ?></a></li>
								<li><a href="./?p=21"><?= $_msg->lang("Technician"); ?></a></li>
<?php		if($_session->groupname=='full') { ?>
								<li><a href="./?p=22"><?= $_msg->lang("Administrator"); ?></a></li>
								<li><a href="./?p=23"><?= $_msg->lang("Equipment"); ?></a></li>
<?php		} ?>
							</ul>
						</li>
						<li class="parent"><a><i class="fa fa-wrench"></i> <?= $_msg->lang("Tools"); ?> <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display: none">
								<li><a href="./?p=30"><?= $_msg->lang("Customers"); ?></a></li>
								<li><a href="./?p=32"><?= $_msg->lang("Manage Plans"); ?></a></li>
								<li><a href="./?p=33"><?= $_msg->lang("Help Desk"); ?></a></li>
							</ul>
						</li>
						<li class="parent"><a><i class="fa fa-gear"></i> <?= $_msg->lang("Settings"); ?> <span class="fa fa-chevron-down"></span></a>
							<ul class="nav child_menu" style="display: none">
								<li><a href="./?p=40"><?= $_msg->lang("System"); ?></a></li>
								<li><a href="./?p=41"><?= $_msg->lang("Form Fields"); ?></a></li>
								<li><a href="./?p=42"><?= $_msg->lang("Provisioning"); ?></a></li>
								<li><a href="./?p=43"><?= $_msg->lang("Help Desk"); ?></a></li>
							</ul>
						</li>
<?php	} ?>
						<li><a href="./?p=off"><i class="fa fa-sign-out"></i> <?= $_msg->lang("Sign Out"); ?> </a></li>
					</ul>
				</div>
			</div>
		<!-- Top Menu Context //-->
			<div class="right_col" role="main">
				<div class="top_nav">
					<div class="nav_menu">
						<div class="nav toggle"><a id="menu_toggle"><i class="fa fa-bars"></i></a></div>
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a class="user-profile ellipsis dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<span class="profile-pic" style="background: url('<?= $user_pic_path; ?>');">&nbsp;</span>
									<span> <?= $fancy_name; ?> </span>
									<span class="fa fa-angle-down pull-right"></span>
<?php if($timeout_counter) { ?>
									<span id="timeout_counter" value="<?= $_session->timeout; ?>"><?= $timeout_counter; ?></span>
								<!-- Setting Session Timeout Counter //-->
									<script type="text/javascript">
										$(function () {
											setInterval(function () {
												var currentTime = (isNaN(parseInt($("#timeout_counter").attr("value")))) ? (0) : (parseInt($("#timeout_counter").attr("value")));
												var tempo = new Date(currentTime * 1000);
												tempo.setTime(tempo.getTime() - 1000);
												if (tempo.getTime() == 0) checkTimeout();
												else {
													var minutes = (tempo.getMinutes() < 10) ? ("0"+ tempo.getMinutes()) : (tempo.getMinutes());
													var seconds = (tempo.getSeconds() < 10) ? ("0"+ tempo.getSeconds()) : (tempo.getSeconds());
													$("#timeout_counter").html(minutes +":"+ seconds);
													$("#timeout_counter").attr("value", (tempo.getTime() / 1000));
												}
											}, 1000);
										});
									</script>
<?php } ?>
								</a>
								<ul class="dropdown-menu pull-right">
									<li><a href="javascript:editProfile(<?= $_session->id; ?>);"><i class="fa fa-user"></i> <?= $_msg->lang("Profile"); ?></a></li>
									<li><a href="javascript:smartHelp();"><i class="fa fa-question-circle"></i> <?= $_msg->lang("Help"); ?></a></li>
									<li><a href="./?p=off"><i class="fa fa-sign-out"></i> <?= $_msg->lang("Sign Out"); ?></a></li>
								</ul>
							</li>
							<li class="dropdown">
								<a class="user-session ellipsis dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-globe"></i>
									<span class="hide-xxs"> <?= $_msg->lang("Language"); ?></span>
									<span class="fa fa-angle-down pull-right"></span>
								</a>
								<ul class="dropdown-menu pull-right" id="langset">
									<li><a href="javascript:void(0);" label="en_US">English</a></li>
<?php
	// ----- Scan Folder for more Languages ----- //
	$lang_files = glob("$_path->lang/*.lng");
	for($i=0; $i<count($lang_files); $i++) {
		$file_handle = fopen($lang_files[$i], "r");
		$first_line = fgets($file_handle);
		$lang_name = substr($first_line, strpos($first_line, ':=') + 2, -1);
		$lang_code = substr($lang_files[$i], (strrpos($lang_files[$i], '/') + 1), -4);
		echo str_repeat("\t", 9) ."<li><a href=\"javascript:void(0);\" label=\"$lang_code\">$lang_name</a></li>\n";
	} unset($lang_files, $file_handle, $first_line, $lang_name, $lang_code);
?>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			<!-- Main Context //-->
				<script src="<?= $_path->js; ?>/custom.js?rev=<?= $_gitrev; ?>" data-ajax-folder="<?= $_path->ajax; ?>" data-error="<?= $_msg->lang('The asynchronous request has failed!'); ?>"></script>
				<div class="row">
<?php include($current_page); ?>
				</div>
				<footer>
					<div>
						<div class="footertitle"><?= $_msg->lang('Contacts'); ?></div>
						<div>contato@attoweb.com.br</div>
						<div>suporte@attoweb.com.br</div>
						<div>vendas@attoweb.com.br</div>
					</div><div>
						<div class="footertitle"><?= $_msg->lang('Version'); ?></div>
						<div><?= $site_title_base; ?> - <?= $site_version; ?></div>
					</div><div>
						<div class="footertitle"><?= $_msg->lang('Developers'); ?></div>
						<div><span> Andre Breier </span></div>
						<div><span> Guilherme Caon Z. </span></div>
					</div>
				</footer>
			</div>
		</div>
		<script src="<?= $_path->js; ?>/bootstrap.min.js"></script>
		<script src="<?= $_path->js; ?>/pace.min.js"></script>
		<script src="<?= $_path->js; ?>/pnotify.custom.min.js"></script>
