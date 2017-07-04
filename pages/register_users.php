<?php
	/************************************************************************
	* register_users page file, included by start if selected (also ?p=21). *
	* Responsible for the registering Administrators and Technicians.       *
	* dependencies: message as $_msg (config);                              *
	*               session as $_session (login);                           *
	*               pgsql as $_pgobj (config);                              *
	*               settings as $_settings (login);                         *
	*               path as $_path (config).                                *
	*************************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	// ----- Defining Register Page Variables ----- //
	switch ($page_number) {
		default:
		case 21:	$register_title = $_msg->lang("Technician");
					$register_type = "tech"; break;
		case 22:	$register_title = $_msg->lang("Administrator");
					$register_type = "admn"; break;
	} $user_added_success = FALSE;
	// ----- Printing Register Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Register") ." &raquo; $register_title"; ?></h2>
							<div class="col-md-2 col-sm-2 col-ms-2 col-xs-2 pull-right">
								<i class="fa fa-refresh fa-spin fa-fw" style="visibility: hidden;"></i>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
<?php // ----- Checking Permissions ----- //
	if($_session->groupname == 'tech') $_msg->warning("You do not have permission to see this page!");
	elseif($_session->groupname == 'admn' && $register_type == 'admn') $_msg->warning("You do not have permission to see this page!");
	else {
		// ----- Dealing with POST forms ----- //
		if(isset($_POST['username']) && isset($_POST['confirm'])) {
			if($_pgobj->select("radusergroup", array("username" => pg_escape_string($_POST["username"])))) $_msg->warning("Username already exists!");
			else {
				$_pgobj->query_params('INSERT INTO radusergroup (username, groupname) VALUES ($1, $2)', array($_POST['username'], $register_type));
				if(!$_pgobj->rows) $_msg->warning("$register_title couldn't be added!");
				else {
					$params = array($_POST['username'], 'Cleartext-Password', ':=', $_POST['password']);
					$_pgobj->query_params('INSERT INTO radcheck (username, attribute, op, "value") VALUES ($1, $2, $3, $4)', $params);
					if(!$_pgobj->rows) {
						$_msg->warning("$register_title couldn't be added!");
						$_pgobj->query_params('DELETE FROM radusergroup WHERE username = $1', array($_POST['username']));
					} else {
						$user_data = serialize(array_diff_key($_POST, array("username" => '', "password" => '', "picture" => '', "confirm" => '')));
						$params = array($_POST['username'], (($_session->id) ? ($_session->id) : (0)), $user_data, $_POST['picture'][1]);
						$_pgobj->query_params('INSERT INTO at_userdata (username, higher_id, data, picture) VALUES ($1, $2, $3, $4)', $params);
						if(!$_pgobj->rows) {
							$_msg->warning("$register_title couldn't be added!");
							$_pgobj->query_params('DELETE FROM radusergroup WHERE username = $1', array($_POST['username']));
							$_pgobj->query_params('DELETE FROM radcheck WHERE username = $1', array($_POST['username']));
						} else { ?>
							<div class="alert alert-success">
								<strong><?= $_msg->lang("Success:"); ?></strong>
								<?= "$register_title ". $_msg->lang("added with success!") ."\n"; ?>
							</div>
							<button class="btn btn-primary" onclick="window.location = '<?= $_SERVER['REQUEST_URI']; ?>';">
								<?= $_msg->lang("Add another") ."\n"; ?>
							</button>
							<button class="btn btn-success" onclick="window.location = './?p=<?= ($page_number - 10); ?>';">
								<?= $_msg->lang("List") ." $register_title\n"; ?>
							</button>
<?php						$user_added_success = TRUE;
						}
					}
				}
			}
		} if(!$user_added_success) {
?>
							<form class="form-horizontal" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return register_checkSendUser();" enctype="application/x-www-form-urlencoded">
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
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="name">
												<?= $_msg->lang("Full Name") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="name" id="name" autocomplete="off" data-error="<?= $_msg->lang('Invalid Name!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="username">
												<?= $_msg->lang("Username") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="username" id="username" autocomplete="off" data-error="<?= $_msg->lang('Invalid Username!'); ?>" data-taken="<?= $_msg->lang('Username Already Taken!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="password">
												<?= $_msg->lang("Password") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<div class="input-group">
													<input type="password" name="password" id="password" data-error="<?= $_msg->lang('Invalid Password!'); ?>" class="form-control col-md-7 col-xs-12"/>
													<span class="input-group-addon" style="cursor: pointer;" title="<?= $_msg->lang('show / hide'); ?>" onclick="register_togglePassword(this);"><i class="fa fa-eye"></i></span>
												</div>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="picture">
												<?= $_msg->lang("Picture") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="file" name="picture[]" id="picture" accept="image/*" tabindex="-1"
														onchange="register_fileSelect(event, this);"
														data-error-html5="<?= $_msg->lang('Error: HTML5 is not supported!'); ?>"
														data-error-format="<?= $_msg->lang('Error: File is not an image!'); ?>" />
												<input type="hidden" name="picture[]" />
												<button type="button" class="btn btn-default col-xs-12" onclick="$('#picture').click();">
													<i class="fa fa-upload"></i><span><?= $_msg->lang("Send File"); ?></span>
												</button>
											</div>
										</div><div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-primary pull-right"><?= $_msg->lang("Next"); ?></button>
										</div>
									</div>
							<!-- Step Two -->
									<div id="2" style="display: none;">
<?php		$required_form_fields = array();
			foreach($_settings->form_field as $form_field_label => $form_field_data) {
				$mask = ($form_field_data['mask']) ? ("data-inputmask=\"'mask': '". $form_field_data['mask'] ."', 'greedy': false\"") : ("");
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
<?php		} if(!isset($form_field_data)) { ?>
										<div class="alert alert-info">
											<strong>Info:</strong>
											<?= $_msg->lang("No custom form fields found! You should really add them before registering someone!") ."\n"; ?>
											<div class="clearfix"></div>
											<button type="button" class="btn btn-default pull-right" onclick="window.location = './?p=41';"><?= $_msg->lang("Add"); ?></button>
											<div class="clearfix"></div>
										</div>
<?php		} ?>
										<div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-primary pull-right"><?= $_msg->lang("Next"); ?></button>
											<div class="col-md-1 pull-right">&#160;</div>
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-info pull-right"><?= $_msg->lang("Previous"); ?></button>
										</div>
									</div>
							<!-- Step Three -->
									<div id="3" style="display: none;">
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="check_username">
												<?= $_msg->lang("Username") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="check_username" id="check_username" disabled="disabled" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
<?php			foreach($required_form_fields as $form_field_label => $form_field_data) { ?>
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="check_<?= $form_field_label; ?>">
												<?= $_msg->lang($form_field_data['title']) ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="check_<?= $form_field_label; ?>" id="check_<?= $form_field_label; ?>" disabled="disabled" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
<?php			} ?>
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
							<script src="<?= $_path->js; ?>/jquery.inputmask.bundle.min.js"></script>
							<script type="text/javascript">
								$(function () {
								// Prepare document loading animation
									$(document).ajaxStart(function (){ $('.fa-spin').css("visibility", 'visible'); })
													.ajaxStop(function (){ $('.fa-spin').css("visibility", 'hidden'); });
									$("#name").on("input", function () {
										var usernameTip = register_usernameTip($(this).val(), false);
										if (usernameTip) $("#username").val(usernameTip);
										setTimeout('register_checkUsername('+ usernameTip.length +');', 300);
									});
									$("#username").on("input", function () {
										$(this).val(register_str2ascii($(this).val()));
										setTimeout('register_checkUsername('+ $(this).val().length +');', 300);
									});
									$("#2 input[data-inputmask]").inputmask();
									list_handleDuplicates($("#2"));
								});
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
<?php	}
	} // ----- Closing the Page ----- //
?>
						</div>
					</div>
