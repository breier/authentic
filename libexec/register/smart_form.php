<?php
	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
	// --- Get Connection Information
		$ip_address = (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : (NULL);
		$connection = array('signal' => 0, 'tx_rate' => 0, 'rx_rate' => 0);
		$mac_address = NULL;
		if($ip_address) {
			if($_pgobj->select("at_framedipaddress_accounts", array("username" => $_session->username, "framedipaddress" => $ip_address))) {
				$query = "SELECT callingstationid, nasipaddress FROM radacct WHERE username = '$_session->username'";
				$query.= " AND framedipaddress = '$ip_address' ORDER BY acctstarttime DESC LIMIT 1";
				$_pgobj->query($query);
				if($_pgobj->rows) {
					$mac_address = strtoupper($_pgobj->result[0]['callingstationid']);
					$nas_ip_address = $_pgobj->result[0]['nasipaddress'];
				}
			} elseif($_pgobj->select("at_framedipaddress_accounts", array("username" => $_session->username))) {
				//$_msg->error("The Session user is not the same as the one authenticated on the network!");
			} //else $_msg->error("The Session user is not currently authenticated on the network!");
		} //else $_msg->error("Could not acquire remote IP!");
		$connection_string = $_msg->lang('Service not yet supported!');
		$server_request_uri = './?p=20'; // $_SERVER['REQUEST_URI'] doesn't work within AJaX request
?>
							<form class="form-horizontal" action="<?= $server_request_uri; ?>" method="post" onsubmit="return register_checkSendUser();" enctype="application/x-www-form-urlencoded">
								<div class="wizard_horizontal">
									<ul class="wizard_steps">
										<li>
											<a href="#1" onclick="return register_pageNavigate(this);" class="selected">
												<span class="step_number">1</span>
												<span class="step_description"><?= $_msg->lang("Main"); ?></span>
											</a>
										</li><li>
											<a href="#2" onclick="return register_pageNavigate(this);" class="disabled">
												<span class="step_number">2</span>
												<span class="step_description"><?= $_msg->lang("Details"); ?></span>
											</a>
										</li><li>
											<a href="#3" onclick="return register_pageNavigate(this);" class="disabled">
												<span class="step_number">3</span>
												<span class="step_description"><?= $_msg->lang("Confirmation"); ?></span>
											</a>
										</li>
									</ul>
								<!-- Step One -->
									<div id="1" style="display: block;">
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="plan">
												<?= $_msg->lang("Plan"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<select name="plan" id="plan" class="form-control selectpicker" onchange="$('#check_plan').val(this.value);">
<?php	$_pgobj->query("SELECT name, price FROM at_plans ORDER BY media, price");
		for($i=0; $i<$_pgobj->rows; $i++) {
			$plan_string = $_pgobj->result[$i]['name'] ." - ". $_settings->system['Currency'] ." ". $_pgobj->result[$i]['price']; ?>
													<option value="<?= $_pgobj->result[$i]['name']; ?>"><?= $plan_string; ?></option>
<?php	} if(!$i) echo str_repeat("\t", 13) ."<option value=\"0\">". $_msg->lang("No Plan") ."</option>\n"; ?>
												</select>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="mac_address">
												<?= $_msg->lang("MAC Address"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="mac_address" id="mac_address" data-error="<?= $_msg->lang('Invalid MAC Address!'); ?>" value="<?= $mac_address; ?>" mac-mask="true" onblur='$("#check_mac_address").val(this.value);' class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="connection">
												<?= $_msg->lang("Connection"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="connection" id="connection" value='<?= $connection_string; ?>' disabled="disabled" class="form-control col-md-7 col-xs-12" >
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="name">
												<?= $_msg->lang("Full Name"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="name" id="name" autocomplete="off" autocorrect="off" autocapitalize="words" data-error="<?= $_msg->lang('Invalid Name!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="password">
												<?= $_msg->lang("Password"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<div class="input-group">
													<input type="password" name="password" id="password" data-error="<?= $_msg->lang('Invalid Password!'); ?>" class="form-control col-md-7 col-xs-12"/>
													<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
												</div>
											</div>
										</div><div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-primary pull-right"><?= $_msg->lang("Next"); ?></button>
										</div>
									</div>
							<!-- Step Two -->
									<div id="2" style="display: none;">
<?php	$required_form_fields = array();
		foreach($_settings->form_field as $form_field_label => $form_field_data) {
			$mask = ($form_field_data['mask']) ? ("data-mask=\"$form_field_data[mask]\"") : ("");
			if($form_field_data['validation']) {
				$require = 'data-validate="'. $form_field_data['validation'] .'" ';
				$require.= 'data-error="'. $_msg->lang("Invalid $form_field_data[title]!");
				$require.= "\" onkeyup=\"\$('#check_$form_field_label').val(this.value);\"";
				$required_form_fields[$form_field_label] = $form_field_data;
			} else $require = "";
			if(isset($previous_sequence)) {
				if($previous_sequence == $_settings->full['form_field'][$form_field_label]['sequence']) $duplicated = TRUE;
				else $duplicated = FALSE;
			} else $duplicated = FALSE;
			$previous_sequence = $_settings->full['form_field'][$form_field_label]['sequence']; ?>
										<div class="form-group <?= ($duplicated) ? ('duplicated') : (''); ?>">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="<?= $form_field_label; ?>">
												<?= $_msg->lang($form_field_data['title']) ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="<?= $form_field_label; ?>" id="<?= $form_field_label; ?>" <?= "$require $mask"; ?> class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
<?php	} if(!isset($form_field_data)) { ?>
										<div class="alert alert-info">
											<strong>Info:</strong>
											<?= $_msg->lang("No custom form fields found! You should really add them before registering someone!") ."\n"; ?>
											<div class="clearfix"></div>
											<button type="button" class="btn btn-default pull-right" onclick="window.location = './?p=41';"><?= $_msg->lang("Add"); ?></button>
											<div class="clearfix"></div>
										</div>
<?php	} ?>
										<div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-primary pull-right"><?= $_msg->lang("Next"); ?></button>
											<div class="col-md-1 pull-right">&#160;</div>
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-info pull-right"><?= $_msg->lang("Previous"); ?></button>
										</div>
									</div>
							<!-- Step Three -->
									<div id="3" style="display: none;">
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="check_plan">
												<?= $_msg->lang("Plan"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="check_plan" id="check_plan" disabled="disabled" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="username">
												<?= $_msg->lang("Username"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="username" id="username" autocomplete="off" data-error="<?= $_msg->lang('Invalid Username!'); ?>" data-taken="<?= $_msg->lang('Username Already Taken!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="check_mac_address">
												<?= $_msg->lang("MAC Address"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="check_mac_address" id="check_mac_address" disabled="disabled" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
<?php	foreach($required_form_fields as $form_field_label => $form_field_data) { ?>
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="check_<?= $form_field_label; ?>">
												<?= $_msg->lang($form_field_data['title']) ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="check_<?= $form_field_label; ?>" id="check_<?= $form_field_label; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
<?php	} ?>
										<div class="form-group">
											<div class="col-md-3 col-sm-3 col-ms-3 col-xs-2 text-right">
												<input  type="checkbox" name="confirm" id="confirm" data-error="<?= $_msg->lang('Check the confirmation box!'); ?>" class="checkbox pull-right"/>
												<span class="custom-checkbox"></span>
											</div>
											<label class="control-label col-md-6 col-sm-6 col-ms-6 col-xs-10 label-checkbox" for="confirm">
												<?= $_msg->lang("Confirm the above data!") ."\n"; ?>
											</label>
										</div>
										<div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<input type="submit" class="btn btn-success pull-right" value="<?= $_msg->lang('Finish'); ?>"/>
											<div class="col-md-1 pull-right">&#160;</div>
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-info pull-right"><?= $_msg->lang("Previous"); ?></button>
										</div>
									</div>
								</div>
							</form>
							<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
							<script src="<?= $_path->js; ?>/jquery.mask.min.js"></script>
							<script type="text/javascript">
<?php		if($_session->groupname=='tech') { ?>
							//	$("#mac_address").attr('disabled', 'disabled');
								$("#username").attr('disabled', 'disabled');
<?php		} ?>
								$("#name").on("input", function () {
									var usernameTip = register_usernameTip($(this).val());
									if (usernameTip) $("#username").val(usernameTip);
									setTimeout('register_checkUsername('+ usernameTip.length +');', 300);
								});
								$("#username").on("input", function () {
									setTimeout('register_checkUsername('+ $(this).val().length +');', 300);
								});
								$("#check_plan").val($('#plan').val());
								$("#check_mac_address").val($('#mac_address').val());
								$('input[mac-mask]').mask('HH:HH:HH:HH:HH:HH', {translation:  {'H': {pattern: /[a-fA-F0-9]/}}});
								$(".selectpicker").selectpicker();
								list_handleDuplicates($("#2"));
							</script>
							<div id="modal_confirm" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal" style="padding: 5px;">&times;</button>
											<strong style="padding-right: 20px;"><?= $_msg->lang('Details'); ?></strong>
										</div>
										<div class="modal-body">
											<p class="fa"><?= $_msg->lang("There are empty inputs in the form."); ?></p>
											<p class="fa"><?= $_msg->lang("These inputs are optional."); ?></p>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal"><?= $_msg->lang("Fill them"); ?></button>
											<button type="button" class="btn btn-default" data-dismiss="modal" onclick="register_formNavigate(2, 3);"><?= $_msg->lang("Leave them empty"); ?></button>
										</div>
									</div>
								</div>
							</div>
<?php	} else header("Location: /"); ?>
