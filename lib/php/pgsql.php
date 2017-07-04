<?php
	/**********************************************************
	* pgsql creates a postgresql connection using pg_pconnect *
	* and stores the resulting resource id to $this->conn     *
	* using $db_dsn_str. It can be used with internal and     *
	* external variables to store the results.                *
	***********************************************************/

	class pgsql {
	// Initialize persistent connection //
		function __construct($db_dsn_str) {
			if(function_exists('pg_pconnect')) {
				$this->conn = pg_pconnect($db_dsn_str);
				if(is_resource($this->conn)) return TRUE;
			} $this->error = "FATAL";
		}
	// Initialize variables //
		public $error = "";
		public $result = FALSE;
		public $rows = 0;
		public $rown = 0;
	// Select Funtion //
		function select($table, $filter, $opts=NULL) {
			if($this->error=="FATAL") return FALSE;
			$this->result = pg_select($this->conn, $table, $filter, $opts);
			$this->rows = (is_array($this->result)) ? (count($this->result)) : (0);
			$this->rown = 0;
			$this->error = (is_array($this->result)) ? (FALSE) : (pg_last_error($this->conn));
			return $this->result;
		}
	// Query Function //
		function query($query_string) {
			if($this->error == "FATAL") return FALSE;
			$resource = pg_query($this->conn, $query_string);
			if(!is_resource($resource)) return $this->pg_err();
			$this->result = pg_fetch_all($resource);
			$this->rows = (pg_num_rows($resource)) ? (pg_num_rows($resource)) : (pg_affected_rows($resource));
			$this->rown = 0;
			return TRUE;
		}
	// Query with Parameters Function //
		function query_params($query_string, $parameters_array) {
			if($this->error=="FATAL") return FALSE;
			$resource = pg_query_params($this->conn, $query_string, $parameters_array);
			if(!is_resource($resource)) return $this->pg_err();
			$this->result = pg_fetch_all($resource);
			$this->rows = (pg_num_rows($resource)) ? (pg_num_rows($resource)) : (pg_affected_rows($resource));
			$this->rown = 0;
			return TRUE;
		}
	// Fetch All Function //
		function fetch_all($result=FALSE) { return ($result) ? (pg_fetch_all($result)) : ($this->result); }
	// Fetch Result Function //
		function fetch_result($result_or_row, $row_or_field, $field=FALSE) {
			if($field) return pg_fetch_result($result_or_row, $row_or_field, $field);
			else {
				if(!isset($this->result[$result_or_row][$row_or_field])) return FALSE;
				else return $this->result[$result_or_row][$row_or_field];
			}
		}
	// Fetch Array Function //
		function fetch_array($result_or_row=FALSE, $row=FALSE) {
			if($row) return pg_fetch_array($result_or_row, $row);
			if(is_int($result_or_row)) return $this->result[$result_or_row];
			if(!isset($this->result[$this->rown])) return FALSE;
			else return $this->result[$this->rown++];
		}
	// Error Handling Function //
		private function pg_err () {
			$this->error = pg_last_error($this->conn);
			$this->result = FALSE;
			$this->rows = 0;
			$this->rown = 0;
			return FALSE;
		}
	// Close Function //
		public function __destruct() { pg_close($this->conn); }
	}
?>
