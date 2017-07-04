<?php
	/*******************************************************
	* record logs system activity to internal syslog file. *
	* dependencies: paths as $_path (config);              *
	*               messages as $_msg (config/login);      *
	*               session as $_sesion (login).           *
	* default log file: syslog at $_path->conf.            *
	********************************************************/

	class record {
	// Initialize log file //
		function __construct() {
			global $_msg;
			global $_path;
			if(!isset($_path->conf)) { // dependency not met
				if(!isset($_msg)) echo "Can't write to log file!"; // dependency not met
				else $_msg->warning("Can't write to log file!");
				return FALSE;
			}
			$this->handle = fopen("$_path->conf/syslog", "a");
			if(!is_resource($this->handle)) {
				if(!isset($_msg)) echo "Can't write to log file!"; // dependency not met
				else $_msg->warning("Can't write to log file!");
				return FALSE;
			}
		}
	// Write to Log Function //
		function write($type, $action, $info) {
			global $_msg;
			global $_session;
			if($_msg === NULL) return print("Can't find Class msgs!"); // dependency not met
			if(!isset($this->handle)) return $_msg->warning("Can't write to log file!");
			if(!is_resource($this->handle)) return $_msg->warning("Can't write to log file!");
			$date_now = date("Ymd His");
			$username = (isset($_session->username)) ? ($_session->username) : ($_msg->lang('unknown'));
			$string = "$date_now:$username:". $_msg->lang($type) .":". $_msg->lang($action) .":". $_msg->lang($info) ."\n";
			fwrite($this->handle, $string, strlen($string));
		}
	// Close log File Handle //
		function close() {
			if(!is_resource($this->handle)) return FALSE;
			else return fclose($this->handle);
		}
	// Close Function //
		public function __destruct() { $this->close(); }
	}
?>
