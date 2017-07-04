<?php
	/****************************************************************************
	* register_equipment page file, included by start if selected (also ?p=23). *
	* Responsible for the registering Infra-structure equipments.               *
	* dependencies: message as $_msg (config);                                  *
	*               session as $_session (login);                               *
	*               pgsql as $_pgobj (config);                                  *
	*               settings as $_settings (login);                             *
	*               path as $_path (config).                                    *
	*****************************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	// ----- Defining Register Page Variables ----- //
	$equipment_added_success = FALSE;
	// ----- Printing Register Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Register") ." &raquo; ". $_msg->lang("Equipment"); ?></h2>
							<div class="col-md-2 col-sm-2 col-ms-2 col-xs-2 pull-right">
								<i class="fa fa-refresh fa-spin fa-fw" style="visibility: hidden;"></i>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
<?php // ----- Checking Permissions ----- //
	if($_session->groupname != 'full') $_msg->warning("You do not have permission to see this page!");
	else {
		// ----- Dealing with POST forms ----- //
		if(isset($_POST['username']) && isset($_POST['confirm'])) {
			if(1) { //do all registering and check for success ?>
							<div class="alert alert-success">
								<strong><?= $_msg->lang("Success:"); ?></strong>
								<?= $_msg->lang("Equipment") ." ". $_msg->lang("added with success!") ."\n"; ?>
							</div>
							<button class="btn btn-primary" onclick="window.location = '<?= $_SERVER['REQUEST_URI']; ?>';">
								<?= $_msg->lang("Add another") ."\n"; ?>
							</button>
							<button class="btn btn-success" onclick="window.location = './?p=<?= ($page_number - 10); ?>';">
								<?= $_msg->lang("List") ." ". $_msg->lang("Equipments") ."\n"; ?>
							</button>
<?php			$equipment_added_success = TRUE;
			}
		} if(!$equipment_added_success) {
?>
							<form class="form-horizontal" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return register_checkSendEquipment();" enctype="application/x-www-form-urlencoded">
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
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="brand">
												<?= $_msg->lang("Brand") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="brand" id="brand" data-error="<?= $_msg->lang('Invalid Brand!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="ip_address">
												<?= $_msg->lang("IP Address") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="ip_address" id="ip_address" data-error="<?= $_msg->lang('Invalid IP Address!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="ssh_port">
												<?= $_msg->lang("SSH Port") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="number" name="ssh_port" id="ssh_port" value="22" data-error="<?= $_msg->lang('Invalid SSH Port!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="username">
												<?= $_msg->lang("Username") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="username" id="username" data-error="<?= $_msg->lang('Invalid Username!'); ?>" class="form-control col-md-7 col-xs-12"/>
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
										</div><div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<button type="button" onclick="register_pageNavigate(this);" class="btn btn-primary pull-right"><?= $_msg->lang("Next"); ?></button>
										</div>
									</div>
							<!-- Step Two -->
									<div id="2" style="display: none;">
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="category">
												<?= $_msg->lang("Category") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<select name="category" id="category" class="form-control selectpicker" onchange="$('#check_category').val($(this).val());">
													<option value="server"><?= $_msg->lang("Server"); ?></option>
													<option value="pop"><?= $_msg->lang("P.O.P."); ?></option>
												</select>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="groupname">
												<?= $_msg->lang("Group Name") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="groupname" id="groupname" data-error="<?= $_msg->lang('Invalid Group Name!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="mac_address">
												<?= $_msg->lang("MAC Address"); ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="mac_address" id="mac_address" data-error="<?= $_msg->lang('Invalid MAC Address!'); ?>" data-inputmask="'mask': '**:**:**:**:**:**'" onblur='$("#check_mac_address").val(this.value);' class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="location">
												<?= $_msg->lang("Location") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="location" id="location" data-error="<?= $_msg->lang('Invalid Location!'); ?>" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div><div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="comments">
												<?= $_msg->lang("Comments") ."\n"; ?>
											</label>
											<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
												<input type="text" name="comments" id="comments" class="form-control col-md-7 col-xs-12"/>
											</div>
										</div>
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
							<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
							<script type="text/javascript">
								$(function () {
								// Prepare document loading animation
									$(document).ajaxStart(function (){ $('.fa-spin').css("visibility", 'visible'); })
													.ajaxStop(function (){ $('.fa-spin').css("visibility", 'hidden'); });
									$("#2 input[data-inputmask]").inputmask();
									$(".selectpicker").selectpicker();
								});
							</script>
<?php	}
	} // ----- Closing the Page ----- //
?>
						</div>
					</div>
