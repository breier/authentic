<?php
	/******************************************************
	* index is the main file, automatically loaded by the *
	* web server, and, except for the AJaX files, is the  *
	* responsible to include any other file.              *
	*******************************************************/

	require("./config.php");				// main config file
	include("$_path->php/record.php");	// internal log class
	$_record = new record();				// internal log object
	require("./login.php");					// login manager
	$body_style = ($_session->username) ? ('') : ('style="background: #F7F7F7;"');	// body CSS style
	// ----- Defining Page Title ----- //
	$site_title_base = "authentic";
	$site_title = $site_title_base;
	$site_version = "Delta";
	if($_session->username) {
		$page_number = (isset($_GET['p'])) ? (intval($_GET['p'])) : (0);
		$site_title = "$site_title_base &raquo; ". $_msg->lang("home");
		if($page_number>=10 && $page_number<20) $site_title = "$site_title_base &raquo; ". $_msg->lang("list");
		if($page_number>=20 && $page_number<30) $site_title = "$site_title_base &raquo; ". $_msg->lang("register");
		if($page_number>=30 && $page_number<40) $site_title = "$site_title_base &raquo; ". $_msg->lang("tools");
		if($page_number>=40 && $page_number<50) $site_title = "$site_title_base &raquo; ". $_msg->lang("settings");
	}
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $site_title; ?></title>
		<link href="<?= $_path->images; ?>/favicon.ico" rel="shortcut icon">
		<!-- Bootstrap core CSS -->
		<link href="<?= $_path->css; ?>/bootstrap.min.css" rel="stylesheet">
		<link href="<?= $_path->fonts; ?>/font-awesome.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/animate.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/pnotify.custom.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/bootstrap-select.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/bootstrap-colorpicker.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/jquery.datetimepicker.min.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/leaflet.css" rel="stylesheet">
		<link href="<?= $_path->css; ?>/custom.css?rev=<?= $_gitrev; ?>" rel="stylesheet">
		<script src="<?= $_path->js; ?>/jquery.min.js"></script>
		<!-- Bluefish default meta tags -->
		<meta name="generator" content="Atom 1.18" >
		<meta name="author" content="breier" >
		<meta name="date" content="2017-08-06T11:00:01-0300" >
		<meta name="copyright" content="GPL">
		<meta name="keywords" content="authentic,radius,administration">
		<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	</head>
<?php
	echo "\t<body $body_style>\n";
	// ----- Defining Page File to Load ----- //
	if($_session->username) {
		$current_page = "$_path->pages/home.php";
		if($page_number>=10 && $page_number<20) $current_page = "$_path->pages/list.php";
		if($page_number>=20 && $page_number<30) $current_page = "$_path->pages/register_users.php";
		if($page_number>=30 && $page_number<40) $current_page = "$_path->pages/tools.php";
		if($page_number>=40 && $page_number<50) $current_page = "$_path->pages/settings.php";
		if($page_number==20) $current_page = "$_path->pages/register_customers.php";
		if($page_number==23) $current_page = "$_path->pages/register_equipment.php";
		if($page_number==31) $current_page = "$_path->pages/financial.php";
		if($page_number==32) $current_page = "$_path->pages/manage_plans.php";
		if($page_number==33) $current_page = "$_path->pages/helpdesk.php";
		if($page_number==34) $current_page = "$_path->pages/fiber_map.php";
		include("$_path->pages/start.php");
	} else include("$_path->pages/login.php");
	$_record->close(); // close log file
	echo "\t</body>\n";
?>
</html>
