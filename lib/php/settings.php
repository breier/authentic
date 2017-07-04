<?php
	/***************************************************************
	* settings arrange system variables from DataBase into arrays. *
	* dependencies: class pgsql in global $_pgobj (config).        *
	****************************************************************/

	class settings {
	// Load system settings from DataBase //
		function __construct() {
			global $_pgobj;
			if(!isset($_pgobj->error)) return FALSE; // dependency not met
			$_pgobj->query("SELECT * FROM at_settings ORDER BY category, sequence");
			for ($i=0; $i<$_pgobj->rows; $i++) {
				$settings_array = $_pgobj->fetch_array();
				$data = (preg_match("/^a\:\d+\:\{/", $settings_array['data'])) ? (unserialize($settings_array['data'])) : ($settings_array['data']);
				eval("\$settings_array_target = &\$this->". $settings_array['category'] .";");
				$settings_array_target[$settings_array['label']] = $data;
				$this->full[$settings_array['category']][$settings_array['label']] = array('id' => $settings_array['id']);
				$this->full[$settings_array['category']][$settings_array['label']]['sequence'] = $settings_array['sequence'];
				$this->full[$settings_array['category']][$settings_array['label']]['data'] = $data;
			}
		}
	// Declaring Varialbles //
		public $system = array();
		public $form_field = array();
		public $provisioning = array();
		public $ticket_category = array();
		public $ticket_priority = array();
		public $full = array('system' => array(),
									'form_field' => array(),
									'provisioning' => array(),
									'ticket_category' => array(),
									'ticket_priority' => array());
		// This variables are the types of settings on the database,
		// if any type ever be added to the database, it shall be added here too.
	}
?>
