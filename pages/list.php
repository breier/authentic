<?php
	/**************************************************************
	* list page file, included by start if selected (also ?p=10). *
	* Responsoble for listing users and some extra actions.       *
	* dependencies: messages as $_msg (config);                   *
	*               session as $_session (login);                 *
	*               path as $_path (config).                      *
	***************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_path)) $_msg->error("Class Paths not set!");
	// ----- Defining List Page Variables ----- //
	$rows_per_page = (isset($_settings->system['Rows Per Page'])) ? (intval($_settings->system['Rows Per Page'])) : (10);
	switch ($page_number) {
		case 13:	$list_title = $_msg->lang("Equipments");
					$list_type = "equipment"; break;
		case 12:	$list_title = $_msg->lang("Administrators");
					$list_type = "admn"; break;
		case 11:	$list_title = $_msg->lang("Technicians");
					$list_type = "tech"; break;
		default:	$list_title = $_msg->lang("Customers");
					$list_type = "inet"; break;
	}
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("List") ." &raquo; ". $list_title; ?></h2>
<?php	if($list_type != 'equipment' || ($list_type == 'equipment' && $_session->groupname == 'full')) { ?>
							<button class="btn btn-primary pull-right strong" onclick="window.location='./?p=<?= ($page_number + 10); ?>';"><?= $_msg->lang("Add"); ?></button>
<?php	} ?>
							<div class="col-md-2 col-sm-2 col-ms-2 col-xs-2 pull-right">
								<i class="fa fa-refresh fa-spin fa-fw" style="visibility: hidden;"></i>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<input type="hidden" id="type" value="<?= $list_type; ?>" />
							<h5 class="col-xs-4"><strong><?= $_msg->lang("Total"); ?>: </strong><span id="total_result">00</span></h5>
							<div class="col-xs-8">
								<input type="search" id="search" class="form-control pull-right" placeholder="<?= $_msg->lang('Search'); ?>" />
								<span aria-hidden="true" class="form-control-feedback smart-delete">
									<img src="<?= $_path->images; ?>/glyphicons-257-delete.png" alt="X" />
								</span>
							</div>
							<table id="users_table" class="table" data-empty="<?= $_msg->lang('No data available in the table!'); ?>">
								<thead>
									<tr>
										<th onclick="list_sortTable(1);">
											<span><?= ($list_type != "equipment") ? ($_msg->lang("Full Name")) : ($_msg->lang("Name")); ?></span>
											<i class="fa fa-sort-amount-asc pull-right"></i>
										</th>
										<th onclick="list_sortTable(2);">
											<span><?= ($list_type != "equipment") ? ($_msg->lang("Register Date")) : ($_msg->lang("Location")); ?></span>
											<i class="fa fa-sort-amount-asc pull-right"></i>
										</th>
										<th onclick="list_sortTable(3);">
											<span><?= ($list_type != "equipment") ? ($_msg->lang("MAC")) : ($_msg->lang("Brand")); ?></span>
											<i class="fa fa-sort-amount-asc pull-right"></i>
										</th>
										<th onclick="list_sortTable(4);">
											<span><?= ($list_type != "equipment") ? ($_msg->lang("Plan")) : ($_msg->lang("IP Address")); ?></span>
											<i class="fa fa-sort-amount-asc pull-right"></i>
										</th>
										<th onclick="list_sortTable(5);">
											<span><?= $_msg->lang("Status"); ?></span>
											<i class="fa fa-sort-amount-asc pull-right"></i>
										</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
							<div class="text-center">
								<button class="btn btn-default" onclick="list_paginate(-1);">&lt;</button>
								<div id="pagination" class="btn-group" data-rows-per-page="<?= $rows_per_page; ?>"></div>
								<button class="btn btn-default" onclick="list_paginate(0);">&gt;</button>
							</div>
						</div>
					<!-- Show Details Modal /-->
						<div id="modal_details" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<input type="hidden" id="details_id" value="" />
										<button class="close" data-dismiss="modal">×</button>
										<strong style="line-height: 34px;"><?= $_msg->lang("Details"); ?></strong>
										<div class="btn-group pull-right">
											<button id="details_edit" class="btn btn-default" onclick="list_detailsEdit();">
												<i class="fa fa-pencil"></i> <span><?= $_msg->lang("Edit"); ?></span>
											</button>
											<button id="details_actions" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
												<i class="fa fa-bars"></i>
											</button>
											<ul class="dropdown-menu">
												<li><a id="details_history" href="javascript:void(0);">
													<i class="fa fa-book"></i> <?= $_msg->lang("History"); ?></a>
												</li>
<?php	if($list_type == 'inet') { ?>
												<li><a id="details_support" href="javascript:void(0);">
													<i class="fa fa-wrench"></i> <?= $_msg->lang("Support"); ?></a>
												</li>
<?php	} if($list_type == 'inet') { ?>
												<li role="separator" class="divider"></li>
												<li><a id="details_ticket" href="javascript:void(0);">
													<i class="fa fa-tags"></i> <?= $_msg->lang("Open Ticket"); ?></a>
												</li>
<?php	} if($_session->groupname != 'tech') {
			if($list_type != 'equipment') { ?>
	 											<li role="separator" class="divider"></li>
												<li><a id="details_disable" data-enable="<?= $_msg->lang('Enable'); ?>" data-disable="<?= $_msg->lang('Disable'); ?>" href="javascript:void(0);">
													<i class="fa fa-lock"></i> <span><?= $_msg->lang("Disable"); ?></span></a>
												</li>
<?php		} ?>
												<li><a id="details_delete" href="javascript:void(0);">
													<i class="fa fa-trash"></i> <?= $_msg->lang("Delete"); ?></a>
												</li>
<?php	} ?>
											</ul>
										</div>
									</div>
									<div class="modal-body"></div>
									<div class="modal-footer">
										<button class="btn btn-default form-edit" onclick="list_detailsEdit();"><?= $_msg->lang('Cancel'); ?></button>
										<button class="btn btn-info form-edit" onclick="list_detailsEditSend();"><?= $_msg->lang('Save'); ?></button>
									</div>
								</div>
							</div>
						</div>
					<!-- Confirm Customer / User / Equipment Deletion Modal /-->
						<div id="modal_delete" class="modal fade" role="dialog">
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<button class="close" data-dismiss="modal" style="padding: 5px;">&times;</button>
										<strong style="padding-right: 20px;"><?= $_msg->lang('Delete'); ?></strong>
									</div>
									<div class="modal-body">
										<p><?= $_msg->lang("Are you sure you want to delete:"); ?> <span class="strong">empty</span></p>
										<br /><p><?= $_msg->lang("This action is irreversible."); ?></p>
										<input type="hidden" name="delete_id" />
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal"><?= $_msg->lang("Cancel"); ?></button>
										<button type="button" class="btn btn-danger" data-dismiss="modal" onclick='list_delete();'><?= $_msg->lang("Delete"); ?></button>
									</div>
								</div>
							</div>
						</div>
						<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
						<script type="text/javascript" >
							$(function () {
							// Define hashChange listener
								window.addEventListener('hashchange', function() {
								// Get current hashes
									var searchArray = new String(window.location.href).split('#');
									var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
									var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
									var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
									$("#search").val(searchString);
									if ($("#search").val().length) $("#search + span.smart-delete").show();
									else $("#search + span.smart-delete").hide();
								// Do the AJaJSON request
									list_fillUsersTable($("#type").val(), searchString, pageNumber, sortOrder);
								});
							// Prepare document loading animation
								$(document).ajaxStart(function (){ $('.fa-spin').css("visibility", 'visible');})
												.ajaxStop(function (){ $('.fa-spin').css("visibility", 'hidden'); });
							// Do the change stuff OnLoad
								var searchArray = new String(window.location.href).split('#');
								var searchString = (searchArray[1] == undefined) ? ('') : (searchArray[1]);
								var pageNumber = (searchArray[2] == undefined) ? (1) : (searchArray[2]);
								var sortOrder = (searchArray[3] == undefined) ? ('1a') : (searchArray[3]);
								$("#search").val(searchString);
								if ($("#search").val().length) $("#search + span.smart-delete").show();
								else $("#search + span.smart-delete").hide();
								$("#search").on("input", null, "input", list_searchTable);
								$("#search + span.smart-delete").on("click", function () { window.location = '#' });
<?php	if(isset($success_message)) { ?>
								alertPNotify('alert-success', '<?= $success_message; ?>');
<?php	} ?>
								list_fillUsersTable($("#type").val(), searchString, pageNumber, sortOrder);
							});
						</script>
					</div>
