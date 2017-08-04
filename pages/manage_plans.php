<?php
	/***************************************************************
	* plans page file, included by start if selected (also ?p=32). *
	* Responsible for configuring radius's groups (ISP plans).     *
	* dependencies: messages as $_msg (config);                    *
	*               session as $_session (login);                  *
	*               pgsql as $_pgobj (config);                     *
	*               settings as $_settings (login).                *
	****************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	// ----- Printing Plans Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Manage Plans"); ?></h2>
							<button class="btn btn-primary pull-right strong" onclick='$("#modal_plan_add").modal("show");'><?= $_msg->lang("Add"); ?></button>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<button type="button" class="table_addon" id="export_pdf" title="<?= $_msg->lang('Export PDF'); ?>">
								<i class="fa fa-file-pdf-o"></i>
							</button>
							<table id="plans_table" class="table">
								<thead>
									<tr>
										<th><?= $_msg->lang("Plan"); ?></th>
										<th><?= $_msg->lang("Price"); ?></th>
										<th><?= $_msg->lang("Media"); ?></th>
										<th><?= $_msg->lang("Download"); ?></th>
										<th><?= $_msg->lang("Upload"); ?></th>
										<th><?= $_msg->lang("Assured"); ?></th>
									</tr>
								</thead>
								<tbody>
<?php // ----- Checking Permissions ----- //
	if($_session->groupname == 'tech') $_msg->warning("You do not have permission to see this page!");
	else {
		// ----- Dealing with POST forms ----- //
		if(isset($_POST['id']) && isset($_POST['delete'])) {
			// Delete Plan
			$_pgobj->query_params('SELECT name FROM at_plans WHERE id = $1', array($_POST['id']));
			if($_pgobj->rows != 1) $_msg->warning("Plan could not be deleted!");
			else {
				$current_plan = $_pgobj->result[0]['name'];
				if($_pgobj->select("radusergroup", array("groupname" => $current_plan))) $_msg->warning("Plan could not be deleted!");
				else {
					$_pgobj->query_params('DELETE FROM radgroupreply WHERE groupname = $1', array($current_plan));
					if($_pgobj->rows) {
						$_pgobj->query_params('DELETE FROM radgroupcheck WHERE groupname = $1', array($current_plan));
						if($_pgobj->rows) $_pgobj->query_params('DELETE FROM at_plans WHERE name = $1', array($current_plan));
						else $_msg->warning("Plan could not be deleted!");
					}
				}
			} if($_pgobj->rows != 1) $_msg->warning("Plan could not be deleted!");
		} elseif(isset($_POST['plan'])) {
		// Add / Save Plan
			$assured_upload = round(($_POST['upload'] * $_POST['assured'] / 100) * 1024 * 1024);
			$assured_download = round(($_POST['download'] * $_POST['assured'] / 100) * 1024 * 1024);
			$rate_limit = round($_POST['upload'] * 1024 * 1024) .'/'. round($_POST['download'] * 1024 * 1024) .' ';
			$rate_limit.= round(($_POST['upload'] * 1024 * 1024) + $assured_upload) .'/'. round(($_POST['download'] * 1024 * 1024) + $assured_download) .' ';
			$rate_limit.= round($_POST['upload'] * 1024 * 1024) .'/'. round($_POST['download'] * 1024 * 1024) ." 2/2 4 $assured_upload/$assured_download";

			if(isset($_POST['id'])) {
			// Save Plan
				if(strlen($_POST['plan']) < 1) $_msg->warning("Name too short!");
				else {
					$_pgobj->query_params('SELECT name FROM at_plans WHERE id = $1', array($_POST['id']));
					if($_pgobj->rows != 1) $_msg->warning("Plan could not be saved!");
					else {
						$current_plan = $_pgobj->result[0]['name'];
						if($current_plan != $_POST['plan']) {
							$_pgobj->query_params('UPDATE radgroupreply SET groupname = $1 WHERE groupname = $2', array($_POST['plan'], $current_plan));
							$_pgobj->query_params('UPDATE radgroupcheck SET groupname = $1 WHERE groupname = $2', array($_POST['plan'], $current_plan));
							$_pgobj->query_params('UPDATE radusergroup SET groupname = $1 WHERE groupname = $2', array($_POST['plan'], $current_plan));
							$_pgobj->query_params('UPDATE at_plans SET name = $1 WHERE name = $2', array($_POST['plan'], $current_plan));
						} $query = 'UPDATE radgroupreply SET attribute = $1, "value" = $2 WHERE groupname = $3';
						$query.= " AND (attribute ILIKE '%rate-limit%' OR attribute ILIKE '%data-rate%') AND op = ':='";
						$_pgobj->query_params($query, array($_POST['type'], $rate_limit, $current_plan));
						if($_pgobj->rows) {
							$_pgobj->query_params('UPDATE at_plans SET price = $1, media = $2 WHERE name = $3', array($_POST['price'], $_POST['media'], $_POST['plan']));
						}
					}
				}
			} else {
			// Add Plan
				if(strlen($_POST['plan']) < 1) $_msg->warning("Name too short!");
				elseif($_pgobj->select("radgroupcheck", array("groupname" => $_POST['plan']))) $_msg->warning("Name already exists!");
				else {
					$_pgobj->query_params('INSERT INTO radgroupreply (groupname, attribute, op, "value") VALUES ($1, $2, \':=\', $3),
																						($1, \'Framed-Compression\', \':=\', \'Van-Jacobsen-TCP-IP\'),
																						($1, \'Framed-Protocol\', \':=\', \'PPP\'),
																						($1, \'Service-Type\', \':=\', \'Framed-User\'),
																						($1, \'Framed-MTU\', \':=\', \'1480\')', array($_POST['plan'], $_POST['type'], $rate_limit));
					if($_pgobj->rows) {
						$_pgobj->query_params('INSERT INTO radgroupcheck (groupname, attribute, op, "value") VALUES ($1, \'Auth-Type\', \':=\', \'MS-CHAP\')', array($_POST['plan']));
						$_pgobj->query_params('INSERT INTO at_plans (name, media, price) VALUES ($1, $2, $3)', array($_POST['plan'], $_POST['media'], floatval($_POST['price'])));
					}
				}
			} if($_pgobj->rows != 1) $_msg->warning("Plan could not be saved!");
		}
		// ----- Selecting Plans Information ----- //
		$query = 'WITH rate AS (SELECT DISTINCT ON (groupname) id, groupname, attribute, "value" FROM radgroupreply';
		$query.= " WHERE (attribute ILIKE '%rate-limit%' OR attribute ILIKE '%data-rate%') AND op = ':=')";
		$query.= " SELECT ap.*, rate.attribute, rate.value AS rate";
		$query.= " FROM at_plans ap, rate WHERE ap.name = rate.groupname ORDER BY ap.media, ap.price";
		$_pgobj->query($query);
		for($i=0; $i<$_pgobj->rows; $i++) {
			$plan_array = array(	"id" => $_pgobj->result[$i]['id'],
										"plan" => $_pgobj->result[$i]['name'],
										"type" => $_pgobj->result[$i]['attribute'],
										"media" => $_pgobj->result[$i]['media'],
										"price" => $_pgobj->result[$i]['price']);
			$main_speed_string = substr($_pgobj->result[$i]['rate'], 0, strpos($_pgobj->result[$i]['rate'], ' '));
			$plan_array['download'] = round(intval(substr($main_speed_string, strpos($main_speed_string, '/') + 1)) / (1024*1024), 1);
			$plan_array['upload'] = round(intval(substr($main_speed_string, 0, strpos($main_speed_string, '/'))) / (1024*1024), 1);
			$assured_download = (intval(substr($_pgobj->result[$i]['rate'], strrpos($_pgobj->result[$i]['rate'], '/') + 1)) / (1024*1024));
			$plan_array['assured'] = round(($assured_download * 100) / $plan_array['download']);
			// ----- Printing Plan Information Block ----- // ?>
									<tr <?= ($i>9)?('class="hide"'):(''); ?> >
										<td>
											<a class="ellipsis" href="javascript:void(0);" onclick="tools_editPlan(<?= $plan_array['id']; ?>, this.parentNode.parentNode);" title="<?= $_msg->lang('Edit Plan'); ?>">
												<span><?= $plan_array['plan']; ?></span>
											</a>
										</td>
										<td><?= $_settings->system['Currency']; ?> <span><?= $plan_array['price']; ?></span></td>
										<td><span label="<?= $plan_array['media']; ?>"><?= $_msg->lang($plan_array['media']); ?></span></td>
										<td><span><?= $plan_array['download']; ?></span> Mbps</td>
										<td><span><?= $plan_array['upload']; ?></span> Mbps</td>
										<td><span><?= $plan_array['assured']; ?></span> %</td>
									</tr>
<?php	} if(!$i) { ?>
									<tr><td colspan="6" style="padding: 8px;"><?= $_msg->lang("No data available in the table!"); ?></td></tr>
<?php	} ?>
								</tbody>
							</table>
<?php	if($i>10) { ?>
							<div class="text-center">
								<button class="btn btn-default" onclick="tools_paginate(-1);" disabled="true">&lt;</button>
								<div id="pagination" class="btn-group">
									<button class="btn btn-default active" onclick="tools_paginate(event);">1</button>
<?php	for($j=0; $j<intval($i/10); $j++) { ?>
									<button class="btn btn-default" onclick="tools_paginate(event);"><?= ($j+2); ?></button>
<?php	} ?>
								</div>
								<button class="btn btn-default" onclick="tools_paginate(+1);">&gt;</button>
							</div>
<?php	} // ----- Getting Customers Per Plan to protect from Deletion ----- //
		$_pgobj->query("SELECT id, name FROM at_plans");
		$plans_array = array();
		for($i=0; $i<$_pgobj->rows; $i++) $plans_array[$_pgobj->result[$i]['name']] = array("id" => $_pgobj->result[$i]['id'], "count" => 0);
		$query = "SELECT COUNT(groupname) AS customers_per_plan, groupname FROM radusergroup";
		$query.= " WHERE groupname NOT IN ('full', 'admn', 'tech') GROUP BY groupname";
		$_pgobj->query($query);
		for($i=0; $i<$_pgobj->rows; $i++) $plans_array[$_pgobj->result[$i]['groupname']]['count'] = $_pgobj->result[$i]['customers_per_plan'];
		// ----- Printing OnLoad Javascript and Modals ----- //
?>
							<input type="hidden" id="customers_per_plan" value='<?= json_encode($plans_array); ?>' />
							<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
							<script src="<?= $_path->js; ?>/jspdf.min.js"></script>
							<script src="<?= $_path->js; ?>/jspdf.plugin.autotable.min.js"></script>
							<script type="text/javascript">
								$(function () {
									$(".modal-body input:not([name='plan'])").on("input", function () {
										$(this).val($(this).val().replace(',', '.'));
									});
									$("#export_pdf").on("click", function () {
										var pdfTable = new jsPDF();
									   pdfTable.text('<?= $_msg->lang("Internet Plans"); ?>', 14, 16);
									   var plansTable = document.getElementById("plans_table");
										var hiddenTRs = $("#plans_table tr.hide");
										hiddenTRs.each(function (index, element) { $(element).removeClass("hide"); });
									   var jsonTable = pdfTable.autoTableHtmlToJson(plansTable);
										hiddenTRs.each(function (index, element) { $(element).addClass("hide"); });
									   pdfTable.autoTable(jsonTable.columns, jsonTable.data, {startY: 20});
									   pdfTable.save('<?= $_msg->lang("Internet Plans"); ?>.pdf');
									});
									$(".selectpicker").selectpicker();
								});
							</script>
						<!-- Form Field Add Modal /-->
							<div id="modal_plan_add" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;"><?= $_msg->lang('Add') .' '. $_msg->lang('Plan'); ?></span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="col-lg-8 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="plan"><?= $_msg->lang("Plan"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="plan" id="plan" value="" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="price"><?= $_msg->lang("Price"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="price" id="price" value="" required="" class="form-control"/>
															<span class="input-group-addon"><?= $_settings->system['Currency']; ?></span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="media"><?= $_msg->lang("Media"); ?></label>
													<input type="hidden" name="type" id="type" value="Mikrotik-Rate-Limit" />
													<!-- <input type="hidden" name="type" id="type" value="Ascend-Data-Rate" /> -->
													<div class="col-md-8 col-sm-8 col-xs-8" style="margin-bottom: 8px;">
														<select name="media" id="media" class="form-control selectpicker">
															<option value="Fiber"><?= $_msg->lang("Fiber"); ?></option>
															<option value="Radio"><?= $_msg->lang("Radio"); ?></option>
														</select>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="download"><?= $_msg->lang("Download"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="download" id="download" value="" required="" class="form-control"/>
															<span class="input-group-addon">Mbps</span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="upload"><?= $_msg->lang("Upload"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="upload" id="upload" value="" required="" class="form-control"/>
															<span class="input-group-addon">Mbps</span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="assured"><?= $_msg->lang("Assured"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="assured" id="assured" value="" required="" class="form-control"/>
															<span class="input-group-addon"> % </span>
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
						<!-- Form Field Edit Modal /-->
							<div id="modal_plan_edit" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal">&times;</button>
											<span style="font-size: 21px; padding-right: 20px;"><?= $_msg->lang('Edit') .' '. $_msg->lang('Plan'); ?></span>
										</div>
										<div class="modal-body">
											<div class="col-lg-2 col-md-2 col-sm-2 col-ms-1 col-xs-1 hide-xxs">&#160;</div>
											<div class="col-lg-8 col-md-8 col-sm-8 col-ms-10 col-xs-12">
												<form class="settings-block" action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<input type="hidden" id="plan_id" name="id" value="" />
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="plan"><?= $_msg->lang("Plan"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<input type="text" name="plan" id="plan" value="" required="" class="form-control"/>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="price"><?= $_msg->lang("Price"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="price" id="price" value="" required="" class="form-control"/>
															<span class="input-group-addon"><?= $_settings->system['Currency']; ?></span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="media"><?= $_msg->lang("Media"); ?></label>
													<input type="hidden" name="type" id="type" value="Mikrotik-Rate-Limit" />
													<!-- <input type="hidden" name="type" id="type" value="Ascend-Data-Rate" /> -->
													<div class="col-md-8 col-sm-8 col-xs-8" style="margin-bottom: 8px;">
														<select name="media" id="media" class="form-control selectpicker">
															<option value="Fiber"><?= $_msg->lang("Fiber"); ?></option>
															<option value="Radio"><?= $_msg->lang("Radio"); ?></option>
														</select>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="download"><?= $_msg->lang("Download"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="download" id="download" value="" required="" class="form-control"/>
															<span class="input-group-addon">Mbps</span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="upload"><?= $_msg->lang("Upload"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="upload" id="upload" value="" required="" class="form-control"/>
															<span class="input-group-addon">Mbps</span>
														</div>
													</div>
													<label class="control-label col-md-4 col-sm-4 col-xs-4" for="assured"><?= $_msg->lang("Assured"); ?></label>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="input-group">
															<input type="text" name="assured" id="assured" value="" required="" class="form-control"/>
															<span class="input-group-addon"> % </span>
														</div>
													</div>
													<button type="submit" class="btn btn-primary pull-right"><?= $_msg->lang("Save"); ?></button>
													<button type="button" class="btn btn-danger pull-right"><?= $_msg->lang("Delete"); ?></button>
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
											<div can-delete="">
												<p><?= $_msg->lang("Are you sure you want to delete this plan:"); ?> <span class="strong">empty</span></p>
												<br /><p><?= $_msg->lang("This action is irreversible."); ?></p>
												<form action="<?= $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
													<input type="hidden" name="id" />
													<input type="hidden" name="delete" value="true" />
												</form>
											</div><div cannot-delete="">
												<p><?= $_msg->lang("You cannot delete the plan:"); ?> <span class="strong">empty</span></p>
												<br /><p><?= $_msg->lang("The amount of customers using this plan is:"); ?> <strong>0</strong></p>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal"><?= $_msg->lang("Cancel"); ?></button>
											<button type="button" class="btn btn-danger" data-dismiss="modal" onclick='settings_delete();'><?= $_msg->lang("Delete"); ?></button>
										</div>
									</div>
								</div>
							</div>
<?php
	} // ----- Closing the Page ----- //
?>
						</div>
					</div>
