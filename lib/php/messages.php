<?php
	/*************************************************************
	* messages displays html messages according to the level and *
	* translates the messages if declared $language_file exists. *
	* dependencies: paths as $_path (config).                    *
	**************************************************************/

	class messages {
	// Initialize language if set //
		function __construct($language_file=FALSE) {
			if($language_file != FALSE) {
				global $_path;
				if(!isset($_path->lang)) return FALSE; // dependency not met
				if(file_exists("$_path->lang/$language_file.lng"))
					$this->translation = file_get_contents("$_path->lang/$language_file.lng");
			}
		}
	// Declare Language Set Function //
		function lang_set($language_file) {
			global $_path;
			if(!isset($_path->lang)) return FALSE; // dependency not met
			if(file_exists("$_path->lang/$language_file.lng")) {
				$this->translation = file_get_contents("$_path->lang/$language_file.lng");
				return TRUE;
			} else {
				unset($this->translation);
				return FALSE;
			}
		}
	// Declare Translation Function //
		function lang($string) {
			if(isset($this->translation)) {
				$position_exists = strpos($this->translation, "\n$string =/=");
				if($position_exists === FALSE) return $string;
				$position_start = strpos($this->translation, '=/=', $position_exists) + 4;
				$position_end = strpos($this->translation, "\n", $position_start);
				return substr($this->translation, $position_start, ($position_end - $position_start));
			} else return $string;
		}
	// Internal Display Function //
		private function msg($string, $class="default", $strong="->") {
			echo "<div class=\"alert alert-$class\">";
			echo "<strong>". $this->lang($strong) ."</strong> ";
			echo $this->lang($string) ."</div>";
			if($class=="danger") die();
		}
	// External Display Functions //
		function error($string) { $this->msg($string, "danger", "Error:"); }		// Error Display Function
		function warning($string) { $this->msg($string, "warning", "Warning:"); }	// Warning Display Function
		function info($string) { $this->msg($string, "info", "Info:"); }			// Info Display Function
		function success($string) { $this->msg($string, "success", "Success:"); }	// Success Display Function
	}
?>
