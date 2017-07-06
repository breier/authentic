<?php
	/***************************************************************
	* tools page file, included by start if selected (also ?p=30). *
	* dependencies: msgs as $_msg (config);                        *
	*               session as $_session (login);                  *
	*               pgsql as $_pgobj (config).                     *
	****************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
?>
						<div class="x_panel">
							<div class="x_title">
								<h2><?= $_msg->lang("Tools") ." &raquo; ". $_msg->lang("Home"); ?></h2>
								<div class="col-md-2 col-sm-2 col-ms-2 col-xs-2 pull-right">
									<i class="fa fa-refresh fa-spin fa-fw" style="visibility: hidden;"></i>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="x_content">
								<div class="col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12 text-center">
									<button type="button" class="btn btn-default" onclick="tools_ontAutofind();"><?= $_msg->lang("ONT Autofind"); ?></button>
									<table id="onts" class="table">
										<thead>
											<tr>
												<th style="text-align: center;"><?= $_msg->lang("ONT"); ?></th>
												<th style="text-align: center;"><?= $_msg->lang("Port"); ?></th>
												<th style="text-align: center;"><?= $_msg->lang("Serial Number"); ?></th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div><div id="name_select" class="col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12 form-horizontal text-center">
									<div class="form-group ">
										<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="customer">
											<?= $_msg->lang("Customer") ."\n"; ?>
										</label>
										<div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<input type="hidden" name="customer" />
											<select id="customer" data-live-search="true" class="selectpicker form-control col-xs-12" onchange="tools_ontCustomerSelect(this.value);" multiple data-max-options=1 disabled>
<?php // List Customers
	$query = 'SELECT aud.id, substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS name, rug.username';
	$query.= ' FROM at_userdata aud, radusergroup rug WHERE aud.username = rug.username';
	$query.= ' AND rug.groupname NOT IN (\'full\', \'admn\', \'tech\') ORDER BY name';
	$_pgobj->query($query);
	for($i=0; $i<$_pgobj->rows; $i++) {
		echo str_repeat("\t", 12) .'<option value="'. $_pgobj->result[$i]['id'] .':';
		echo substr($_pgobj->result[$i]['username'], 0, strpos($_pgobj->result[$i]['username'], '@'));
		echo '">'. $_pgobj->result[$i]['name'] ."</option>\n";
	}
?>
											</select>
										</div>
									</div><div class="form-group duplicated">
										<label class="control-label col-md-3 col-sm-3 col-ms-3 col-xs-12" for="building">
											<?= $_msg->lang("Building") ."\n"; ?>
										</label>
										<div class="col-md-9 col-sm-9 col-ms-9 col-xs-12">
											<input type="text" id="building" placeholder="<?= $_msg->lang('Name'); ?>" oninput="tools_ontBuildingSelect(this.value);" data-error="<?= $_msg->lang('Building Name too short!'); ?>" class="form-control col-xs-12" disabled />
										</div>
									</div>
								</div><div class="col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12 text-center">
									<h4><?= $_msg->lang("ONT Activate"); ?></h4>
									<input id="customer_description" type="hidden" />
									<input id="customer_id" type="hidden" />
									<input id="gpon_slot" type="hidden" />
									<input id="gpon_port" type="hidden" />
									<input id="ont_sn" type="hidden" />
									<button id="activate_pppoe" type="button" class="btn btn-primary" onclick="tools_ontActivate('PPPoE');" disabled><?= $_msg->lang("ONT Activate PPPoE"); ?></button>
									<button id="activate_bridge" type="button" class="btn btn-primary" onclick="tools_ontActivate('Bridge');" disabled><?= $_msg->lang("ONT Activate Bridge"); ?></button>
								</div><div class="col-lg-4 col-md-6 col-sm-6 col-ms-9 col-xs-12 text-center" style="display: none;">
									<h4><?= $_msg->lang("Result"); ?></h4>
									<div id="result"></div>
								</div>
								<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
								<script type="text/javascript" >
									$(function () {
									// Prepare document loading animation
										$(document).ajaxStart(function (){ $('.fa-spin').css("visibility", 'visible');})
														.ajaxStop(function (){ $('.fa-spin').css("visibility", 'hidden'); });
										$(".selectpicker").selectpicker({ noneSelectedText: '<?= $_msg->lang("Nothing Selected"); ?>' });
									// Name Select Switch
										list_handleDuplicates("#name_select");
										$("#name_select .control").find("div input").off("change").on("change", function () {
											var firstElement = $(this).parent("div").parent("div").next("div");
											var secondElement = $(this).parent("div").parent("div").next("div").next("div");
											if($(this).val() == $(firstElement).find("input").attr("name")) {
												$(firstElement).removeClass("duplicated").addClass("flipInX animated").find("input").focus();
												$(secondElement).addClass("duplicated").removeClass("flipInX animated").find("input");
											} else {
												$(firstElement).addClass("duplicated").removeClass("flipInX animated").find("input");
												$(secondElement).removeClass("duplicated").addClass("flipInX animated").find("input").focus();
											}
										});
									});
								</script>
							</div>
						</div>
