<?php
	/******************************************************************
	* settings page file, included by start if selected (also ?p=40). *
	* Responsible for auto-configuring authentic's parameters.        *
	* dependencies: messages as $_msg (config);                       *
	*               session as $_session (login);                     *
	*               pgsql as $_pgobj (config);                        *
	*               settings as $_settings (login);                   *
	*               path as $_path (config).                          *
	*******************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	// ----- Defining Settings Page Variables ----- //
	switch ($page_number) {
		default:
		case 40:	$settings_title = $_msg->lang("System");
					$settings_type = "system"; break;
		case 41:	$settings_title = $_msg->lang("Form Fields");
					$settings_type = "form_field"; break;
		case 42:	$settings_title = $_msg->lang("Provisioning");
					$settings_type = "provisioning"; break;
		case 43:	$settings_title = $_msg->lang("Help Desk");
					$settings_type = "helpdesk"; break;
	}
	// ----- Printing Settings Page Main Header ----- //
?>
					<div class="x_panel" style="min-height: 200px;">
						<div class="x_title">
							<h2><?= $_msg->lang("Settings"); ?>&nbsp;&raquo;&nbsp;</h2><h2><?= $settings_title; ?></h2>
<?php	if($_session->groupname != 'tech' && $settings_type == 'form_field') { ?>
							<button class="btn btn-primary pull-right strong" onclick='$("#modal_form_field_add").modal("show");'><?= $_msg->lang("Add Form Field"); ?></button>
<?php	} elseif($_session->groupname != 'tech' && $settings_type == 'provisioning') { ?>
							<button class="btn btn-primary pull-right strong" onclick='$("#modal_provisioning_add").modal("show");'><?= $_msg->lang("Add Provisioning"); ?></button>
<?php	} ?>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
<?php // ----- Checking Permissions ----- //
	if($_session->groupname == 'tech') $_msg->warning("You do not have permission to see this page!");
	else {
		$reserved_keys = array(	'id', 'date', 'username', 'password', 'groupname', 'name', 'higher_name', 'higher_id', 'data',
										'picture', 'comments', 'mac_address', 'framedipaddress', 'category', 'sequence', 'label');
		// ----- Dealing with POST forms ----- //
		if(isset($_POST['label']) && isset($_POST['delete'])) {
			// Delete - Form Field / Provisioning / Ticket Priority / Ticket Category - Settings
			$settings_category = ($settings_type == 'helpdesk') ? ($_POST['settings_category']) : ($settings_type);
			$_pgobj->query_params('DELETE FROM at_settings WHERE label = $1 AND category = $2', array($_POST['label'], $settings_category));
			if($_pgobj->rows != 1) $_msg->warning("The setting could not be not deleted!");
			else $_settings = new settings();
		} elseif($settings_type == 'helpdesk' && isset($_POST['set_name'])) {
			// Add Ticket Priority / Ticket Category - Settings
			require_once("$_path->php/str2ascii.php");
			$setting_label = str2ascii($_POST['set_name']);
			$settings_category = $_POST['settings_category'];
			foreach($reserved_keys as $key_name) if($setting_label == $key_name) $reserved_key_error = TRUE;
			if(strlen($setting_label) < 1) $_msg->warning("Name too short!");
			elseif(isset($reserved_key_error)) $_msg->warning("Name already exists!");
			elseif(isset($_settings->full[$settings_category][$setting_label])) $_msg->warning("Name already exists!");
			else {
				if($_POST['settings_category'] == 'ticket_priority')
					$setting_data = serialize(array("color" => pg_escape_string($_POST['set_color']), "title" => pg_escape_string($_POST['set_name'])));
				else $setting_data = pg_escape_string($_POST['set_name']);
				$_pgobj->query_params('SELECT sequence FROM at_settings WHERE category = $1 ORDER BY sequence DESC LIMIT 1', array($_POST['settings_category']));
				$setting_sequence = ($_pgobj->rows) ? ($_pgobj->result[0]['sequence'] + 1) : (1);
				$params = array($_POST['settings_category'], $setting_label, $setting_data, $setting_sequence);
				$_pgobj->query_params('INSERT INTO at_settings (category, label, data, sequence) VALUES ($1, $2, $3, $4)', $params);
				if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
				else $_settings = new settings();
			}
		} elseif(count($_POST) && ($settings_type == 'system' || $settings_type == 'helpdesk')) {
			// Save - System / Ticket Priority / Ticket Category - Settings
			$settings_category = ($settings_type == 'helpdesk') ? ($_POST['settings_category']) : ($settings_type);
			foreach ($_POST as $data_set) {
				if(!is_array($data_set)) continue;
				if(isset($_settings->full[$settings_category][$data_set[0]])) {
					if(strlen($data_set[1]) < 1) $_msg->warning("Configuration Value too short!");
					else {
						$data_value = ($settings_category == 'ticket_priority') ? (serialize(array('color' => $data_set[1], 'title' => $data_set[2]))) : ($data_set[1]);
						$_pgobj->query_params('UPDATE at_settings SET data = $1 WHERE id = $2', array($data_value, $_settings->full[$settings_category][$data_set[0]]['id']));
						if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
						else $_settings = new settings();
					}
				}
			}
		} elseif(!isset($_POST['label']) && isset($_POST['sequence'])) {
			// Add Form Field
			require_once("$_path->php/str2ascii.php");
			$form_field_label = str2ascii($_POST['title']);
			if(strlen($form_field_label) < 1) $_msg->warning("Name too short!");
			elseif(isset($_settings->full[$settings_type][$form_field_label])) $_msg->warning("Name already exists!");
			elseif(array_search($form_field_label, $reserved_keys) !== FALSE) $_msg->warning("Name already exists!");
			else {
				if($_POST['sequence']) $next_sequence = intval($_POST['sequence']);
				else {
					$_pgobj->query("SELECT sequence + 1 AS next FROM at_settings WHERE category = '$settings_type' ORDER BY sequence DESC LIMIT 1");
					$next_sequence = ($_pgobj->rows == 1) ? ($_pgobj->result[0]['next']) : (1);
				} $serialized_data = serialize(array('title' => $_POST['title'], 'mask' => $_POST['mask'], 'validation' => $_POST['validation']));
				$params = array($settings_type, $form_field_label, $serialized_data, $next_sequence);
				$_pgobj->query_params('INSERT INTO at_settings (category, label, data, sequence) VALUES ($1, $2, $3, $4)', $params);
				if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
				else $_settings = new settings();
			}
		} elseif(isset($_POST['label']) && isset($_POST['sequence'])) {
			// Save Form Field
			if(strlen($_POST['title']) < 1) $_msg->warning("Name too short!");
			else {
				$serialized_data = serialize(array('title' => $_POST['title'], 'mask' => $_POST['mask'], 'validation' => $_POST['validation']));
				$params = array($serialized_data, $_POST['sequence'], $settings_type, $_POST['label']);
				$_pgobj->query_params('UPDATE at_settings SET data = $1, sequence = $2 WHERE category = $3 AND label = $4', $params);
				if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
				else $_settings = new settings();
			}
		} elseif(!isset($_POST['label']) && isset($_POST['brand'])) {
			// Add Provisioning
			require_once("$_path->php/str2ascii.php");
			$provisioning_label = str2ascii($_POST['brand']);
			if(strlen($provisioning_label) < 1) $_msg->warning("Name too short!");
			elseif(isset($_settings->full[$settings_type][$provisioning_label])) $_msg->warning("Name already exists!");
			elseif(array_search($provisioning_label, $reserved_keys) !== FALSE) $_msg->warning("Name already exists!");
			else {
				$_pgobj->query("SELECT sequence + 1 AS next FROM at_settings WHERE category = '$settings_type' ORDER BY sequence DESC LIMIT 1");
				$next_sequence = ($_pgobj->rows == 1) ? ($_pgobj->result[0]['next']) : (1);
				$serialized_data = serialize( array('brand' => $_POST['brand'],
																'ssh_port' => $_POST['ssh_port'],
																'username' => $_POST['username'],
																'password' => $_POST['password'],
																'mac_prefix' => $_POST['mac_prefix']) );
				$params = array($settings_type, $provisioning_label, $serialized_data, $next_sequence);
				$_pgobj->query_params('INSERT INTO at_settings (category, label, data, sequence) VALUES ($1, $2, $3, $4)', $params);
				if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
				else $_settings = new settings();
			}
		} elseif(isset($_POST['label']) && isset($_POST['brand'])) {
			// Save Provisioning
			if(strlen($_POST['brand']) < 1) $_msg->warning("Name too short!");
			else {
				$serialized_data = serialize( array('brand' => $_POST['brand'],
																'ssh_port' => $_POST['ssh_port'],
																'username' => $_POST['username'],
																'password' => $_POST['password'],
																'mac_prefix' => $_POST['mac_prefix']) );
				$params = array($serialized_data, $settings_type, $_POST['label']);
				$_pgobj->query_params('UPDATE at_settings SET data = $1 WHERE category = $2 AND label = $3', $params);
				if($_pgobj->rows != 1) $_msg->warning("The setting could not be saved!");
				else $_settings = new settings();
			}
		} //elseif(count($_POST)) $_msg->info(print_r($_POST, 1));
		// ----- Printing System Settings Page ----- //
		if($settings_type == 'system') { ?>
							<form action="<?= $_SERVER['REQUEST_URI']; ?>" onsubmit="return settings_saveSingle();" method="post" enctype="application/x-www-form-urlencoded">
<?php		foreach($_settings->system as $label => $data) { // System Settings ?>
								<div class="col-lg-3 col-md-3 col-sm-4 col-ms-6 col-xs-12">
									<label class="control-label"><?= $_msg->lang("$label"); ?></label>
<?php			if($label == 'Date Format') { ?>
									<select name="<?= $label; ?>[]" class="form-control selectpicker">
										<option value="m/d/Y" <?= ($data == 'm/d/Y') ? ('selected="true"') : (''); ?> >m/d/Y</option>
										<option value="d/m/Y" <?= ($data == 'd/m/Y') ? ('selected="true"') : (''); ?> >d/m/Y</option>
									</select>
<?php			} elseif($label == 'Main Chart') { ?>
									<select name="<?= $label; ?>[]" class="form-control selectpicker">
										<option value="main_throughput" <?= ($data == 'main_throughput') ? ('selected="true"') : (''); ?> >main_throughput</option>
									</select>
<?php			} else { ?>
									<input type="hidden" name="<?= $label; ?>[]" value="<?= $label; ?>" />
									<input type="text" name="<?= $label; ?>[]" value="<?= $data; ?>" required="" class="form-control" data-error="<?= $_msg->lang('Invalid Value!'); ?>" />
<?php			} ?>
								</div>
<?php		} ?>
								<div class="clearfix"></div>
								<div class="col-xs-12"><button type="submit" class="btn btn-info"><?= $_msg->lang("Save"); ?></button></div>
							</form>
<?php } // ----- Printing Form Fields' Settings Page ----- //
		if($settings_type == 'form_field') {
			$form_fields = array_merge(array("name" => array("id" => 0, "sequence" => 0, "data" => array("title" => "Name", "mask" => NULL, "validation" => NULL))), $_settings->full['form_field']);
			foreach($form_fields as $form_field_label => $form_field_data) { ?>
							<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12">
								<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" data-label="<?= $form_field_label; ?>" enctype="application/x-www-form-urlencoded">
									<div class="header col-md-12 text-right">
<?php			if($form_field_label == 'name') { $form_field_disabled = 'disabled="true"'; ?>
										<a class="disabled"><span class="strong"><?= $_msg->lang("Save"); ?></span><i class="fa fa-save"></i></a>
										<a class="disabled"><span class="strong"><?= $_msg->lang("Delete"); ?></span><i class="fa fa-trash"></i></a>
<?php			} else { $form_field_disabled = ''; ?>
										<input type="hidden" name="label" value="<?= $form_field_label; ?>" />
										<input type="hidden" name="<?= $form_field_label; ?>[]" value="<?= $form_field_data['data']['title']; ?>" />
										<a href="javascript:void(0);" onclick="settings_saveFormField('<?= $form_field_label; ?>');"><span class="strong"><?= $_msg->lang("Save"); ?></span><i class="fa fa-save"></i></a>
										<a href="javascript:void(0);" onclick="settings_delete('<?= $form_field_label; ?>');"><span class="strong red"><?= $_msg->lang("Delete"); ?></span><i class="fa fa-trash red"></i></a>
<?php			} ?>
									</div>
									<div class="clearfix"></div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="<?= 'title_'.$form_field_label; ?>"><?= $_msg->lang("Title"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="text" name="title" id="<?= 'title_'.$form_field_label; ?>" value="<?= $form_field_data['data']['title']; ?>" required="" class="form-control" <?= $form_field_disabled; ?>/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="<?= 'sequence_'.$form_field_label; ?>"><?= $_msg->lang("Sequence"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="number" name="sequence" id="<?= 'sequence_'.$form_field_label; ?>" value="<?= $form_field_data['sequence']; ?>" class="form-control" <?= $form_field_disabled; ?>/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="<?= 'mask_'.$form_field_label; ?>"><?= $_msg->lang("Mask"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="text" name="mask" id="<?= 'mask_'.$form_field_label; ?>" value="<?= $form_field_data['data']['mask']; ?>" class="form-control" <?= $form_field_disabled; ?>/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="<?= 'validation_'.$form_field_label; ?>"><?= $_msg->lang("Validation"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="text" name="validation" id="<?= 'validation_'.$form_field_label; ?>" value="<?= $form_field_data['data']['validation']; ?>" class="form-control" <?= $form_field_disabled; ?>/>
									</div>
									<div class="clearfix"></div>
								</form>
							</div>
<?php		}
		} // ----- Printing Provisioning Settings Page ----- //
		if($settings_type == 'provisioning') {
			foreach($_settings->provisioning as $provisioning_label => $provisioning_data) { ?>
							<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12">
								<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" data-label="<?= $provisioning_label; ?>" enctype="application/x-www-form-urlencoded">
									<div class="header col-md-12 text-right">
										<input type="hidden" name="label" value="<?= $provisioning_label; ?>" />
										<input type="hidden" name="<?= $provisioning_label; ?>[]" value="<?= $provisioning_data['brand']; ?>" />
										<a href="javascript:void(0);" onclick="settings_saveFormField('<?= $provisioning_label; ?>');"><span class="strong"><?= $_msg->lang("Save"); ?></span><i class="fa fa-save"></i></a>
										<a href="javascript:void(0);" onclick="settings_delete('<?= $provisioning_label; ?>');"><span class="strong red"><?= $_msg->lang("Delete"); ?></span><i class="fa fa-trash red"></i></a>
									</div>
									<div class="clearfix"></div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="brand_<?= $provisioning_label; ?>"><?= $_msg->lang("Brand"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="text" name="brand" id="brand_<?= $provisioning_label; ?>" value="<?= $provisioning_data['brand']; ?>" required="" class="form-control"/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="ssh_port_<?= $provisioning_label; ?>"><?= $_msg->lang("SSH Port"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="number" name="ssh_port" id="ssh_port_<?= $provisioning_label; ?>" value="<?= $provisioning_data['ssh_port']; ?>" required="" class="form-control"/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="username_<?= $provisioning_label; ?>"><?= $_msg->lang("Username"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<input type="text" name="username" id="username_<?= $provisioning_label; ?>" value="<?= $provisioning_data['username']; ?>" autocorrect="off" autocapitalize="none" required="" class="form-control"/>
									</div>
									<label class="control-label col-md-4 col-sm-4 col-xs-4" for="password_<?= $provisioning_label; ?>"><?= $_msg->lang("Password"); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<div class="input-group">
											<input type="password" name="password" id="password_<?= $provisioning_label; ?>" value="<?= $provisioning_data['password']; ?>" required="" class="form-control"/>
											<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
										</div>
									</div>
<?php			for($i=0; $i<count($provisioning_data['mac_prefix']); $i++) { ?>
									<label class="control-label col-md-4 col-sm-4 col-xs-4"><?= (!$i) ? ($_msg->lang("MAC Prefix")) : ('&#160;'); ?></label>
									<div class="col-md-8 col-sm-8 col-xs-8">
										<div class="input-group">
											<input type="text" name="mac_prefix[]" value="<?= $provisioning_data['mac_prefix'][$i]; ?>" required="" class="form-control"/>
<?php				if($i == (count($provisioning_data['mac_prefix']) - 1)) { ?>
											<span class="input-group-addon" onclick="settings_addMACPrefix(this);"><i class="fa fa-plus"></i></span>
<?php				} else { ?>
											<span class="input-group-addon" onclick="settings_deleteMACPrefix(this);"><i class="fa fa-minus"></i></span>
<?php				} ?>
										</div>
									</div>
<?php			} ?>
									<div class="clearfix"></div>
								</form>
							</div>
<?php		} if(!isset($provisioning_data)) $_msg->info("No items so far! Use the Add button.");
		} // ----- Printing Helpdesk Settings Page ----- //
		if($settings_type == 'helpdesk') { ?>
							<!-- Ticket Categories Block /-->
							<div class="col-md-6 col-sm-6 col-ms-12 col-xs-12">
								<form action="<?= $_SERVER['REQUEST_URI']; ?>" onsubmit="return settings_saveSingle(this);" method="post" enctype="application/x-www-form-urlencoded">
									<input type="hidden" name="settings_category" value="ticket_category" data-required="true" />
									<h4 class="text-center"><?= $_msg->lang("Ticket Categories"); ?></h4>
<?php		foreach($_settings->ticket_category as $label => $data) { ?>
									<div class="col-md-6 col-sm-6 col-ms-12 col-xs-12">
										<input type="hidden" name="<?= $label; ?>[]" value="<?= $label; ?>" />
										<div class="input-group" style="margin-bottom: 16px;">
											<input type="text" name="<?= $label; ?>[]" value="<?= $data; ?>" required="" class="form-control col-md-7 col-xs-12"/>
											<span class="input-group-addon red" style="cursor: pointer;" title="<?= $_msg->lang('Delete'); ?>" onclick="settings_delete('<?= $label; ?>');"><i class="fa fa-trash"></i></span>
										</div>
									</div>
<?php		} if(count($_settings->ticket_category) > 0) { ?>
									<div class="clearfix"></div>
									<div class="col-xs-12">
										<button type="button" class="btn btn-primary strong" onclick="settings_modalAdd('ticket_category');"><?= $_msg->lang("Add"); ?></button>
										<button type="submit" class="btn btn-info"><?= $_msg->lang("Save"); ?></button>
									</div>
<?php		} else {
				$_msg->info("No items so far! Use the Add button."); ?>
									<div class="col-xs-12">
										<button type="button" class="btn btn-primary strong" onclick="settings_modalAdd('ticket_category');"><?= $_msg->lang("Add"); ?></button>
									</div>
<?php		} ?>
								</form>
							</div>
							<!-- Ticket Priorities Block /-->
							<div class="col-md-6 col-sm-6 col-ms-12 col-xs-12">
								<form action="<?= $_SERVER['REQUEST_URI']; ?>" onsubmit="return settings_saveSingle(this);" method="post" enctype="application/x-www-form-urlencoded">
									<input type="hidden" name="settings_category" value="ticket_priority" data-required="true" />
									<h4 class="text-center"><?= $_msg->lang("Ticket Priorities"); ?></h4>
<?php		foreach($_settings->ticket_priority as $label => $data) { ?>
									<div class="col-md-6 col-sm-6 col-ms-12 col-xs-12" data-colorpicker="" data-color="<?= $data['color']; ?>">
										<input type="hidden" name="<?= $label; ?>[]" value="<?= $label; ?>" />
										<input type="hidden" name="<?= $label; ?>[]" data-colorpicker-store="" value="<?= $data['color']; ?>" />
										<div class="input-group" style="margin-bottom: 16px;">
											<span class="input-group-addon" data-colorpicker-show style="cursor: pointer;" title="<?= $_msg->lang('Change Color'); ?>">
												<i class="fa fa-circle" style="color: <?= $data['color']; ?>;"></i>
											</span>
											<input type="text" name="<?= $label; ?>[]" value="<?= $data['title']; ?>" required="" class="form-control col-md-7 col-xs-12" />
											<span class="input-group-addon red" style="cursor: pointer;" title="<?= $_msg->lang('Delete'); ?>" onclick="settings_delete('<?= $label; ?>');"><i class="fa fa-trash"></i></span>
										</div>
									</div>
<?php		} if(count($_settings->ticket_priority) > 0) { ?>
									<div class="clearfix"></div>
									<div class="col-xs-12">
										<button type="button" class="btn btn-primary strong" onclick="settings_modalAdd('ticket_priority');"><?= $_msg->lang("Add"); ?></button>
										<button type="submit" class="btn btn-info"><?= $_msg->lang("Save"); ?></button>
									</div>
<?php		} else {
				$_msg->info("No items so far! Use the Add button."); ?>
									<div class="col-xs-12">
										<button type="button" class="btn btn-primary strong" onclick="settings_modalAdd('ticket_priority');"><?= $_msg->lang("Add"); ?></button>
									</div>
<?php		} ?>
								</form>
							</div>
<?php	} // ----- Printing OnLoad Javascript and Modals ----- // ?>
							<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
							<script src="<?= $_path->js; ?>/bootstrap-colorpicker.min.js"></script>
							<script type="text/javascript">
								$(function () {
									$(".selectpicker").selectpicker();
									$("div[data-colorpicker]").colorpicker({component: "span[data-colorpicker-show]", input: "input[data-colorpicker-store]"});
								});
							</script>
						<!-- Nothing to do! Modal /-->
							<div id="modal_nochange" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;"><?= $_msg->lang('Nothing to do!'); ?></span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="settings-block col-lg-6 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<p><?= $_msg->lang('Nothing has changed!'); ?></p>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
							</div>
						<!-- Form Field Add Modal /-->
							<div id="modal_form_field_add" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;"><?= $_msg->lang('Add Form Field'); ?></span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="col-lg-8 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="title"><?= $_msg->lang("Title"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="title" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="sequence"><?= $_msg->lang("Sequence"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="number" name="sequence" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="mask"><?= $_msg->lang("Mask"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="mask" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="vrfy"><?= $_msg->lang("Validation"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="validation" class="form-control"/>
													</div>
													<button type="submit" class="btn btn-primary pull-right"><?= $_msg->lang("Add"); ?></button>
													<div class="clearfix"></div>
												</form>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
							</div>
						<!-- Confirm Configuration Deletion Modal /-->
							<div id="modal_delete" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal" style="padding: 5px;">&times;</button>
											<strong style="padding-right: 20px;"><?= $_msg->lang('Delete'); ?></strong>
										</div>
										<div class="modal-body">
											<p><?= $_msg->lang("Are you sure you want to delete this field:"); ?> <span class="strong">empty</span></p>
											<br /><p><?= $_msg->lang("This action is irreversible."); ?></p>
											<form action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
												<input type="hidden" name="label" />
												<input type="hidden" name="settings_category" />
												<input type="hidden" name="delete" value="true" />
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal"><?= $_msg->lang("Cancel"); ?></button>
											<button type="button" class="btn btn-danger" data-dismiss="modal" onclick='settings_delete();'><?= $_msg->lang("Delete"); ?></button>
										</div>
									</div>
								</div>
							</div>
						<!-- Provisioning Add Modal /-->
							<div id="modal_provisioning_add" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;"><?= $_msg->lang('Add Provisioning'); ?></span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="col-lg-8 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="brand"><?= $_msg->lang("Brand"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="brand" id="brand" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="ssh_port"><?= $_msg->lang("SSH Port"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="number" name="ssh_port" id="ssh_port" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="username"><?= $_msg->lang("Username"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="username" id="username" autocorrect="off" autocapitalize="none" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="password"><?= $_msg->lang("Password"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="password" name="password" id="password" required="" class="form-control"/>
															<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4"><?= $_msg->lang("MAC Prefix"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="mac_prefix[]" required="" class="form-control"/>
															<span class="input-group-addon" onclick="settings_addMACPrefix(this);"><i class="fa fa-plus"></i></span>
														</div>
													</div>
													<button type="submit" class="btn btn-primary pull-right"><?= $_msg->lang("Add"); ?></button>
													<div class="clearfix"></div>
												</form>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
							</div>
						<!-- Single Add Modal /-->
							<div id="modal_single_add" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;">replace add title</span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="settings-block col-lg-6 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<form action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<input type="hidden" name="settings_category" value="" />
													<label class="control-label col-xs-4" for="set_color"><?= $_msg->lang("Color"); ?></label>
													<div class="col-xs-8">
														<div class="input-group" data-colorpicker="" data-color="#08C">
															<input type="text" name="set_color" value="#08C" class="form-control" data-colorpicker-store="" />
															<span class="input-group-addon" data-colorpicker-show style="cursor: pointer;" title="<?= $_msg->lang('Change Color'); ?>">
																<i class="fa fa-circle" style="color: #08C;"></i>
															</span>
														</div>
													</div>
													<label class="control-label col-xs-4" for="set_name"><?= $_msg->lang("Name"); ?></label>
													<div class="col-xs-8">
														<input type="text" name="set_name" required="" class="form-control strong"/>
													</div>
													<button type="submit" class="btn btn-primary pull-right"><?= $_msg->lang("Add"); ?></button>
												</form>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
							</div>
<?php
	} // ----- Closing the Page ----- //
?>
						</div>
					</div>
