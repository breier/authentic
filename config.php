<?php
	/*********************************************************
	* config is required by index and any AJaX file to get   *
	* the objects: paths as $_path;                          *
	*              msgs as $_msg with browser main language; *
 	*              pgsql as $_pgobj with database.pgpass.    *
	**********************************************************/

// ----- Defining Class Paths ----- //
	class paths {
		function __construct($root) {
			$this->root  = strval($root);
		// --- Set root included folders by php
			$this->conf  = "$this->root/../attcdata";
			$this->pages = "$this->root/pages";
			$this->ajax  = "$this->root/libexec";
			$this->php   = "$this->root/lib/php";
			$this->lang  = "$this->root/lib/languages";
			$this->proto = "$this->root/lib/protocols";
		// -- Set independent browser loaded folders
			$this->js    = "./lib/js";
			$this->css   = "./lib/css";
			$this->fonts = "./lib/fonts";
			$this->images= "./lib/images";
		}
	} $_path = new paths((isset($_POST['ajax'])) ? ("../..") : ("."));
// ----- Guessing User Language ----- //
	if(!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) $temp = array('');
	else $temp = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	$browser_main_language = str_replace('-', '_', $temp[0]);
	if(!file_exists("$_path->lang/$browser_main_language.lng")) {
		$temp = glob("$_path->lang/". substr($browser_main_language, 0, 3) ."*.lng");
		if(count($temp)) $browser_main_language = substr($temp[0], (strrpos($temp[0], '/') + 1), -4);
	} unset($temp);
// ----- Defining Messages Class ----- //
	require("$_path->php/messages.php");
	$_msg = new messages($browser_main_language);
// ----- Verifying DataBase Config File ----- //
	if(!file_exists("$_path->conf/database.pgpass")) $_msg->error("Couldn't find DataBase!");
// ----- Connecting Database ----- //
	$db_dsn_array = explode(':', file_get_contents("$_path->conf/database.pgpass"));
	$db_dsn_string = 'host='.$db_dsn_array[0].' port='.$db_dsn_array[1].' dbname='.$db_dsn_array[2].' user='.$db_dsn_array[3].' password='.$db_dsn_array[4];
	require("$_path->php/pgsql.php");
	$_pgobj = new pgsql($db_dsn_string);
	if($_pgobj->error == "FATAL") $_msg->error("Couldn't connect to the DataBase!");
	unset($db_dsn_string, $db_dsn_array);
// ----- Sync PHP Time Zone with PostgreSQL ----- //
//	$_pgobj->query("SET timezone = '". date_default_timezone_get() ."'");
// ----- Get Git Head Revision Info ----- //
	$_gitrev = "none";
	if(file_exists("$_path->root/.git/ORIG_HEAD")) {
		$temp_git_hash = explode("\n", file_get_contents("$_path->root/.git/ORIG_HEAD"));
		$_gitrev = $temp_git_hash[0];
		unset($temp_git_hash);
	}
// ----- Check for DataBase essential data ----- //
	if(!$_pgobj->select("at_settings", array("category" => "system"))) include_once("$_path->root/system_initdb.php");
?>
