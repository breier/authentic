<?php
	/**********************************************************************
	* details file, requested by search AJaX file from list page in order *
	* to return HTML to modal_details with users or equipment info.       *
	* dependencies: messages as $_msg (config);                           *
	*               pgsql as $_pgobj (config).                            *
	***********************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['type'])) {
// ---- Loop Fill Table Rows ----- //
//$_msg->wrn($query);
		$details_result = $_pgobj->result;
		// --- Filling plans array
		$plans_array = array();
		$_pgobj->query("SELECT name, price FROM at_plans ORDER BY media, price");
		for($i=0; $i<$_pgobj->rows; $i++) {
			$plan_string = $_pgobj->result[$i]['name'] ." - ". $_settings->system['Currency'] ." ". $_pgobj->result[$i]['price'];
			$plans_array[$_pgobj->result[$i]['name']] = $plan_string;
		}
		// --- Filling Technician Array
		$_pgobj->query("SELECT * FROM at_technicians ORDER BY id");
		$techs_array = array();
		for ($i=0; $i<$_pgobj->rows; $i++)
			$techs_array[$_pgobj->result[$i]['id']] = ($_pgobj->result[$i]['name'] == 'unknown') ? (ucfirst($_msg->lang('unknown'))) : ($_pgobj->result[$i]['name']);
		// --- Display user Details
		for($i=0; $i<count($details_result); $i++) {
			$details_array = array();
			foreach($details_result[$i] as $field => $value) if($field!='data') $details_array[$field] = $value;
			if($list_type != 'equipment') {
				$details_array = array_merge($details_array, unserialize($details_result[$i]['data']));
				if($list_type=='inet') {
					if($details_array['higher_name'] == 'unknown') $details_array['higher_name'] = $_msg->lang("unknown");
				} $groupname_array = json_decode($details_array['groupname']);
				if(array_search('disabled', $groupname_array) !== FALSE) echo '<input type="hidden" id="user_disabled" value="true" />';
				$priority_zero_index = array_search(0, json_decode($details_array['priority']));
				if(!$priority_zero_index) $priority_zero_index = 0;
				$details_array['groupname'] = $groupname_array[$priority_zero_index];
			} elseif($details_array['equipment_name'] == NULL) $details_array['equipment_name'] = $_msg->lang("unknown");
// ----- Prepare to fill the modal ----- //
			if($list_type == 'equipment') {
				$fancy_array = array("equipment_name"	=> array($_msg->lang("Name"), $details_array['equipment_name'], FALSE),
											"brand_name"		=> array($_msg->lang("Brand"), $details_array['brand_name'], FALSE),
											"date"				=> array($_msg->lang("Register Date"), date("d/m/Y", strtotime($details_array['date'])), FALSE),
											"comments"			=> array($_msg->lang("Details"), $details_array['comments'], FALSE),
											"groupname"			=> array($_msg->lang("Group"), $details_array['groupname'], FALSE),
											"mac_address"		=> array($_msg->lang("MAC"), strtoupper($details_array['mac_address']), FALSE),
											"ip_address"		=> array($_msg->lang("IP Address"), $details_array['ip_address'], FALSE),
											"location"			=> array($_msg->lang("Location"), $details_array['location'], FALSE),
											"service_type"		=> array($_msg->lang("Service"), $details_array['service_type'], FALSE),
											"service_port"		=> array($_msg->lang("Port"), $details_array['service_port'], FALSE),
											"username"			=> array($_msg->lang("Username"), $details_array['username'], FALSE),
											"password"			=> array($_msg->lang("Password"), $details_array['password'], FALSE));
			} elseif($list_type == 'inet') {
				$fancy_array = array("name"				=> array($_msg->lang("Full Name"), $details_array['name'], FALSE),
											"groupname"			=> array($_msg->lang("Plan"), $details_array['groupname'], FALSE),
											"date"				=> array($_msg->lang("Register Date"), date("d/m/Y", strtotime($details_array['date'])), FALSE),
											"higher_name"		=> array($_msg->lang("Technician"), $details_array['higher_name'], FALSE),
											"mac_address"		=> array($_msg->lang("MAC"), strtoupper($details_array['mac_address']), FALSE),
											"framedipaddress"	=> array($_msg->lang("IP Address"), $details_array['framedipaddress'], FALSE),
											"username"			=> array($_msg->lang("Username"), $details_array['username'], FALSE),
											"password"			=> array($_msg->lang("Password"), $details_array['password'], FALSE));
			} else {
				$fancy_array = array("name"				=> array($_msg->lang("Full Name"), $details_array['name'], FALSE),
											"framedipaddress"	=> array($_msg->lang("IP Address"), $details_array['framedipaddress'], FALSE),
											"date"				=> array($_msg->lang("Register Date"), date("d/m/Y", strtotime($details_array['date'])), FALSE),
											"username"			=> array($_msg->lang("Username"), $details_array['username'], FALSE),
											"password"			=> array($_msg->lang("Password"), $details_array['password'], FALSE));
			}
// ----- Fill the remaining fields with dynamic data from DB ----- //
			if($list_type != 'equipment') {
				foreach($_settings->form_field as $field => $form_field_data) {
					if(isset($last_sequence)) {
						if($last_sequence == $_settings->full['form_field'][$field]['sequence']) $sequence_duplicated = TRUE;
						else $sequence_duplicated = FALSE;
					} else $sequence_duplicated = FALSE;
					$last_sequence = $_settings->full['form_field'][$field]['sequence'];
					$fancy_array[$field] = array($_msg->lang($form_field_data['title']), (isset($details_array[$field]))?($details_array[$field]):(NULL), $sequence_duplicated);
				}
			} foreach($fancy_array as $field => $details) {
?>
				<div class="col-md-6 col-sm-6 col-xs-12 form-group <?= ($details[2]) ? ('duplicated') : (''); ?>">
					<div class="col-md-12 col-sm-12 col-xs-12 form-group"><strong><?= $details[0]; ?></strong></div>
					<div class="col-md-12 col-sm-12 col-xs-12 form-group">
<?php			if($field == 'password') {
					$hidden_password = str_repeat("●", strlen($details[1])); ?>
						<div class="input-group form-edit">
							<input type="password" name="<?= $field; ?>" value="<?= $details[1]; ?>" class="form-control" />
							<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
						</div>
						<span class="ellipsis fadeIn animated"><?= (strlen($hidden_password)) ? ($hidden_password) : ('●●'); ?></span>
<?php			} elseif($field == 'date') { ?>
						<input type="text" name="date" value="<?= $details[1]; ?>" class="form-control form-edit" disabled="true" />
						<span class="ellipsis fadeIn animated"><?= (strlen($details[1])) ? ($details[1]) : ('--'); ?></span>
<?php			} elseif($field == 'higher_name') { ?>
						<select name="higher_id" class="form-control selectpicker form-edit">
<?php				foreach($techs_array as $tech_id => $tech_name) {
						$is_selected = ($tech_name == $details[1]) ? ('selected="true"') : (''); ?>
							<option value="<?= $tech_id; ?>" <?= $is_selected; ?> ><?= $tech_name; ?></option>
<?php				} ?>
						</select>
						<span class="ellipsis fadeIn animated"><?= (strlen($details[1])) ? ($details[1]) : ('--'); ?></span>
<?php			} elseif($field == 'groupname') { ?>
						<select name="groupname" class="form-control selectpicker form-edit">
<?php				foreach($plans_array as $plan_name => $plan_title) {
						$is_selected = ($plan_name == $details[1]) ? ('selected="true"') : (''); ?>
							<option value="<?= $plan_name; ?>"<?= $is_selected; ?> ><?= $plan_title; ?></option>
<?php				} ?>
						</select>
						<span class="ellipsis fadeIn animated"><?= (strlen($details[1])) ? ($details[1]) : ('--'); ?></span>
<?php			} else { ?>
						<input type="text" name="<?= $field; ?>" value="<?= $details[1]; ?>" class="form-control form-edit" />
						<span class="ellipsis fadeIn animated"><?= (strlen($details[1])) ? ($details[1]) : ('--'); ?></span>
<?php			} ?>
					</div>
				</div>
<?php		} ?>
				<div class="clearfix"></div>
<?php	}
	} ?>
