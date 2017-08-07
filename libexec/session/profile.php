<?php
	/*********************************************************************
	* profile edit, requested by custom AJaX from any page action bar    *
	* at the user-profile dropdown-menu, in order to update own profile. *
	*********************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");
		// Cheking for POST actions
		if(isset($_POST['action'])) {
			switch(pg_escape_string($_POST['action'])) {
				case 'password':
					$_pgobj->query_params('SELECT username FROM at_userauth WHERE password = $1', array($_POST['current_password']));
					if($_pgobj->rows == 0) echo json_encode(array("alert-danger", $_msg->lang("Invalid Password!")));
					elseif($_pgobj->result[0]['username'] != $_session->username) echo json_encode(array("alert-danger", $_msg->lang("Invalid Password!")));
					else {
						$params = array($_POST['new_password'], $_session->username, 'Cleartext-Password');
						$_pgobj->query_params('UPDATE radcheck SET "value" = $1 WHERE username = $2 AND attribute = $3', $params);
						if($_pgobj->rows == 1) echo json_encode(array("alert-success", $_msg->lang("Password successfully updated!")));
						else echo json_encode(array("alert-danger", $_msg->lang("Failed to update password!")));
					}
				break;
				default: echo json_encode(array("alert-info", $_msg->lang("Service not yet supported!"))); break;
			}
		} else {
?>
<!-- Profile Edit Modal /-->
	<div id="profile" class="modal fade" role="dialog">
		<div class='modal-dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					<button class="close" data-dismiss="modal">&times;</button>
					<span style="font-size: 21px;"><?= $_msg->lang('Profile'); ?></span>
				</div>
				<div class="modal-body">
					<h4 class="strong text-center"><?= $_msg->lang("Change Password"); ?></h4>
					<div class="col-md-6 col-xs-12">
						<label class="control-label" for="current_password"><?= $_msg->lang("Current Password"); ?></label>
						<div class="input-group">
							<input class="form-control" type="password" id="current_password" data-error="<?= $_msg->lang('Invalid Password!'); ?>" />
							<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
						</div>
					</div><div class="col-md-6 col-xs-12">
						<label class="control-label" for="new_password"><?= $_msg->lang("New Password"); ?></label>
						<div class="input-group">
							<input class="form-control" type="password" id="new_password" data-error="<?= $_msg->lang('Invalid Password!'); ?>" />
							<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
						</div>
					</div><div class="col-md-6 col-xs-12">
					</div><div class="col-md-6 col-xs-12">
						<label class="control-label" for="confirm_password"><?= $_msg->lang("Confirm Password"); ?></label>
						<div class="input-group">
							<input class="form-control" type="password" id="confirm_password" data-error="<?= $_msg->lang('Invalid Password!'); ?>" />
							<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal"><?= $_msg->lang("Save"); ?></button>
				</div>
				<script type="text/javascript">
					$("#profile .modal-footer button").on("click", function () {
						if ($("#profile #current_password").val().length < 6) return register_alert('#current_password');
						if ($("#profile #new_password").val().length < 6) return register_alert('#new_password');
						if ($("#profile #confirm_password").val() != $("#profile #new_password").val()) return register_alert('#confirm_password');
						editProfile({
							'ajax': 1,
							'action': "password",
							'current_password': $("#profile #current_password").val(),
							'new_password': $("#profile #new_password").val()
						});
					});
				</script>
			</div>
		</div>
	</div>
<?php	}
	}
?>
