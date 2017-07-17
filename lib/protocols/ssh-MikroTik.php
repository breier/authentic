<?php
	/***********************************************************
	* device_proto defines propriatary commands bindings into  *
	* standard outputs! Executing with an external socket.     *
	************************************************************/

	class device_proto {
		// Initialize persistent connection //
		function __construct($device_conn) {
			// Check Connection...
			if(!isset($device_conn->error)) return FALSE;
         if($device_conn->error) return $device_conn->error;
         $this->conn = $device_conn;
		}
		// PPP Get User Function //
		function ppp_get_user($username) {
			$this->conn->exec("/ppp active print value-list where name=\"$username\"");
         if(strlen($this->conn->output) < 100) return $this->conn->error;
         $result_array = array();
         $output_array = explode("\n", $this->conn->output);
         for($i=0; $i<count($output_array); $i++) {
            $key_value = explode(":", $output_array[$i]);
            $result_array[trim($key_value[0])] = trim($key_value[1]);
         } return $result_array;
		}
      // PPP Drop User Function //
		function ppp_drop_user($username) {
			$this->conn->exec("/ppp active remove [find name=\"$username\"]");
         if($this->result) return $this->conn->error;
         return TRUE;
		}
	}
?>
