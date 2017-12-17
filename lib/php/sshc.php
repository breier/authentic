<?php
	/***********************************************************
	* sshc creates a SSHv2 connection using PHP SSH2 extension *
	* and stores the resulting socket to $this->conn           *
	* this extension depends on "php-pecl-ssh2" package!       *
	************************************************************/

	class sshc {
		// Initialize persistent connection //
		function __construct($host, $username, $password, $port=22) {
			// Check SSH2 Extension...
			if(!function_exists('ssh2_connect')) return $this->err("SSH2 Extension not found!");
			// Check IP Address...
			if(!filter_var($host, FILTER_VALIDATE_IP) === false) $ip_address = $host;
			else {
				$host_resolv = gethostbyname($host);
				if(!filter_var($host_resolv, FILTER_VALIDATE_IP) === false) $ip_address = $host_resolv;
				else return $this->err("Invalid Host Name!");
			}
			// Set Connection...
			function falloff($reason, $message, $long) {
				$this->error = "Server disconnected! $reason ($message)";
				return TRUE;
			}
			$callback = array( "disconnect" => "falloff");
			$this->method = array( "kex" => "diffie-hellman-group1-sha1" );
			set_time_limit(4);

			$this->conn = ssh2_connect($ip_address, $port, $this->method, $callback);
			if(!is_resource($this->conn)) return $this->err("Connection Failed!");
			if(!ssh2_fingerprint($this->conn)) return $this->err("Fingerprint Failed!");
			if(file_exists($password)) {
				if(!ssh2_auth_pubkey_file($this->conn, $username, $password, “$password.key”)) return $this->err("Authentication Failed!");
			} else {
				if(!ssh2_auth_password($this->conn, $username, $password)) return $this->err("Authentication Failed!");
			}
		}
		// Initialize variables //
		public $session = NULL;
		public $result = FALSE;
		public $output = "";
		public $error = "";
		public $rows = 0;
		// Exec Function //
		function exec($command) {
			if($resource = ssh2_exec($this->conn, $command)) {
				stream_set_blocking($resource, 1);
				$this->result = fgets($resource);
				$this->output = "";
				$this->rows = 0;
				while($this->result) {
					$this->output.= $this->result . PHP_EOL;
					$this->result = fgets($resource);
					$this->rows++;
				} fclose($resource);
			} else return $this->err("Command Execution Failed!");
			return $this->result;
		}
		// Shell Function //
		function shell($command) {
			if(!is_resource($this->session)) {
				$this->session = ssh2_shell($this->conn);
				sleep(1); // regular time for shell to answer
				if(!is_resource($this->session)) return $this->err("Command Execution Failed!");
			} fwrite($this->session, $command . chr(13));
			sleep(1); // regular time for shell to answer
			$this->output = stream_get_contents($this->session);
			$output_array = explode(PHP_EOL, $this->output);
			$this->rows = count($output_array);
			$this->result = $output_array[(count($output_array) - 1)];
			return $this->result;
		}
		// Search Function //
		function search($string) {
			$output_array = explode(PHP_EOL, $this->output);
			for($i=0; $i<count($output_array); $i++) if(stristr($output_array[$i], $string)) break;
			return $output_array[$i];
		}
		// Error Function //
		function err($string) {
			$this->error = $string;
			return FALSE;
		}
		// Close Function //
		function close() {
			if($this->session !== NULL) {
				if(is_resource($this->session)) fclose($this->session);
			} elseif(is_resource($this->conn)) {
				if($this->exec("exit")) $this->exec("quit");
			}
		}
		public function __destruct() { $this->close(); }
	}
?>
