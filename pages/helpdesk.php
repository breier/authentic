	<?php
	/******************************************************************
	* helpdesk page file, included by start if selected (also ?p=33). *
	* dependencies: msgs as $_msg (config);                           *
	*               pgsql as $_pgobj (config);                        *
	*               session as $_session (login);                     *
	*               settings as $_settings (login);                   *
	*               path as $_path (config).                          *
	*******************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	// ----- Setting deafult variables ----- //
	$success = FALSE;
	$date_format = (isset($_settings->system["Date Format"])) ? ($_settings->system["Date Format"]) : ("m/d/Y");
	// ----- Formating Date ----- //
	if(isset($_GET['cid']) || isset($_GET['tid'])) {
		$days_week = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
		foreach($days_week as &$name) $name = $_msg->lang($name);
		unset($name);
		$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		foreach($months as &$name) $name = $_msg->lang($name);
		unset($name);
		$hours = array('07:30');
		while($hours[(count($hours)-1)] != '19:30') $hours[] = date("H:i", strtotime($hours[(count($hours)-1)] ." +30 minutes"));
	}
	// ----- Formating Deadline Date ----- //
	if(isset($_POST['deadline'])) {
		$full_time_now = time();
		$final_time_diff = strtotime(date("Y-m-d") ." 19:30:00") - $full_time_now; // Make it configurable... later//
		$date_format = (isset($_settings->system["Date Format"])) ? ($_settings->system["Date Format"]) : ("m/d/Y");
		if(strpos($date_format, 'm') > strpos($date_format, 'd')) $deadline_string = str_replace(array('/', '.'), array('-', '-'), $_POST['deadline']);
		else $deadline_string = str_replace(array('-', '.'), array('/', '/'), $_POST['deadline']);
		$deadline = strtotime(pg_escape_string($deadline_string));
		if($deadline > (strtotime("+2 weeks") + $final_time_diff)) $_msg->error("Invalid Deadline!");
		elseif($deadline < strtotime("-1 month")) $_msg->error("Invalid Deadline!");  // Make it configurable... later//
		$deadline_string = date("Y-m-d H:i:s.0000", $deadline);
	}
?>
					<div class="x_panel" style="min-height: 200px;">
						<div class="x_title">
							<h2><?= $_msg->lang("Tools") ." &raquo; ". $_msg->lang("Help Desk"); ?></h2>
<?php if(isset($_GET['cid']) || isset($_GET['tid'])) { ?>
							<button class="btn btn-default pull-right strong" onclick="window.location = '<?= $_SERVER["HTTP_REFERER"]; ?>';"><?= $_msg->lang("Return"); ?></button>
<?php } else { ?>
							<button class="btn btn-primary pull-right strong" onclick="window.location = '<?= "./?p=$page_number&cid=0"; ?>';"><?= $_msg->lang("Add"); ?></button>
<?php } ?>
							<div class="clearfix"></div>
						</div>
						<div class="x_content text-center">
<?php
	// ----- Deal with newlly posted ticket ----- //
	if(isset($_POST['customer_id'])) {
		$query = 'INSERT INTO at_tickets (customer_id, category, subject, deadline) VALUES ($1, $2, $3, $4) RETURNING id';
		$customer_id = ($_POST['customer_id']) ? (intval($_POST['customer_id'])) : (NULL);
		// --- Make sure the subject is not uppercase
		$subject = pg_escape_string($_POST['subject']);
		$upper_subject = mb_strtoupper($subject, 'UTF-8');
		similar_text($upper_subject, $subject, $percent);
		if($percent > 20) $subject = ucfirst(mb_strtolower($subject, 'UTF-8'));
		// --- Insert the Ticket
		$_pgobj->query_params($query, array($customer_id, intval($_POST['category']), $subject, $deadline_string));
		if($_pgobj->rows) $ticket_id = $_pgobj->result[0]['id'];
		else $_msg->error("Could not open ticket!");
		$query = 'INSERT INTO at_ticket_messages (ticket_id, user_id, priority, message, target_id) VALUES ($1, $2, $3, $4, $5)';
		// --- Make sure the message is not uppercase
		$message = pg_escape_string($_POST['message']);
		$upper_message = mb_strtoupper($message, 'UTF-8');
		similar_text($upper_message, $message, $percent);
		if($percent > 20) $message = ucfirst(mb_strtolower($message, 'UTF-8'));
		// --- Insert the Ticket Message
		$target_id = ($_POST['target']) ? (intval($_POST['target'])) : (NULL);
		$_pgobj->query_params($query, array($ticket_id, intval($_session->id), intval($_POST['priority']), $message, $target_id));
		if($_pgobj->rows) $success = $_msg->lang("Ticket opened with success!");
		else $_msg->error("Could not open ticket!");
	}
	// ----- Deal with updates posted in a ticket ----- //
	if(isset($_POST['ticket_id'])) {
		$query = 'INSERT INTO at_ticket_messages (ticket_id, user_id, priority, message, status, target_id) VALUES ($1, $2, $3, $4, $5, $6)';
		$ticket_status = (isset($_POST['close'])) ? ("false") : ("true");
		// --- Make sure the message is not uppercase
		$message = pg_escape_string($_POST['message']);
		$upper_message = mb_strtoupper($message, 'UTF-8');
		similar_text($upper_message, $message, $percent);
		if($percent > 20) $message = ucfirst(mb_strtolower($message, 'UTF-8'));
		// --- Insert the Ticket Message
		$target_id = ($_POST['target']) ? (intval($_POST['target'])) : (NULL);
		$_pgobj->query_params($query, array($_POST['ticket_id'], intval($_session->id), intval($_POST['priority']), $message, $ticket_status, $target_id));
		if($_pgobj->rows == 0) $_msg->error("Could not add message to the ticket!");
		$query = 'UPDATE at_tickets SET category = $1, deadline = $2 WHERE id = $3';
		// --- Update Ticket anyway
		$_pgobj->query_params($query, array($_POST['category'], $deadline_string, $_POST['ticket_id']));
		if($_pgobj->rows) $success = $_msg->lang("Ticket updated with success!");
		else $_msg->error("Could not update ticket!");
	}
	// ----- Deal with Deleting Customers ----- //
	if(isset($_POST['id']) && isset($_POST['delete'])) {
		// Checking Permissions
		if($_session->groupname == 'tech') $_msg->warning("You do not have permission to delete!");
		else {
			$_pgobj->query_params('SELECT username FROM at_userdata WHERE id = $1', array(intval($_POST['id'])));
			if($_pgobj->rows != 1) $_msg->warning("Couldn't locate username!");
			else {
				$delete_user = $_pgobj->result[0]['username'];
				$delete_count = 0;
				$_pgobj->query("DELETE FROM radusergroup WHERE username = '$delete_user'");
				$delete_count += $_pgobj->rows;
				$_pgobj->query("DELETE FROM radreply WHERE username = '$delete_user'");
				$_pgobj->query("DELETE FROM radcheck WHERE username = '$delete_user'");
				$delete_count += $_pgobj->rows;
				$_pgobj->query("DELETE FROM at_userdata WHERE username = '$delete_user'");
				$delete_count += $_pgobj->rows;
				if($delete_count >= 3) $success = $_msg->lang("Customer successfully deleted!");
			}
		}
	}
	// ----- Display Tickets List (Open/Closed) ----- //
	if(!(isset($_GET['cid']) || isset($_GET['tid']))) {
		if(isset($_GET['closed'])) {
			$open_button_class = "btn btn-default";
			$closed_button_class = "btn btn-primary active";
			$list_status = "NOT";
			$rows_amount = 10; // Manually defines the amount of rows to display
			$page_offset = (isset($_GET['n'])) ? (intval($_GET['n'])) : (0);
			$pagination = "LIMIT $rows_amount OFFSET ". ($rows_amount * $page_offset);
			$order_by = "alm.date";
		} else {
			$open_button_class = "btn btn-primary active";
			$closed_button_class = "btn btn-default";
			$list_status = "";
			$pagination = "";
			$order_by = "at.deadline";
		}
		// --- Display Tickets List Header Buttons
?>
							<div class="text-center" style="height: 40px;">
								<div class="btn-group" style="margin: auto;">
									<button class="<?= $open_button_class; ?>" onclick="window.location = '<?= "./?p=$page_number"; ?>'"><?= $_msg->lang("Open Tickets"); ?></button>
									<button class="<?= $closed_button_class; ?>" onclick="window.location = '<?= "./?p=$page_number&closed"; ?>'"><?= $_msg->lang("Closed Tickets"); ?></button>
								</div>
							</div>
<?php	// --- Tickets List Main Query
		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, date, message, priority, status, user_id FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= ' SELECT at.id, at.category, at.subject, at.deadline, alm.message, alm.priority, alm.date,';
		$query.= ' substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS user_name, at.customer_id';
		$query.= " FROM alm, at_tickets at LEFT OUTER JOIN at_userdata aud ON aud.id = at.customer_id";
		$query.= " WHERE at.id = alm.ticket_id AND $list_status alm.status ORDER BY $order_by DESC $pagination" ;
		$_pgobj->query($query);
		for($i=0; $i<$_pgobj->rows; $i++) {
			$customer_id = ($_pgobj->result[$i]['customer_id']) ? (intval($_pgobj->result[$i]['customer_id'])) : (FALSE);
			$ticket_deadline = (isset($_GET['closed'])) ? (strtotime($_pgobj->result[$i]['date'])) : (strtotime($_pgobj->result[$i]['deadline']));
			$priority_data = $_settings->ticket_priority[$_pgobj->result[$i]['priority']];
			$display_ticket_header = TRUE;
			if ($customer_id) {
				$customer_name = ($_pgobj->rows) ? ($_pgobj->result[$i]['user_name']) : ($_msg->lang('No Customer'));
			}	else {
				$customer_name = $_msg->lang('No Customer');
			}
			if(isset($previous_ticket_date)) { // --- Aggregate same day tickets
				if($previous_ticket_date == date('Y-m-d', $ticket_deadline)) $display_ticket_header = FALSE;
			} $previous_ticket_date = date('Y-m-d', $ticket_deadline);
			if($display_ticket_header) {
				if($i) { ?>
							</nav>
<?php			} ?>
							<nav class="tickets-header">
								<div><?= $_msg->lang(date("F", $ticket_deadline)) . date(", d", $ticket_deadline); ?></div>
								<div><?= $_msg->lang(date("l", $ticket_deadline)); ?></div>
							</nav>
							<nav class="tickets-list">
<?php		} ?>
								<div onclick="window.location = '<?= "./?p=$page_number&tid=". $_pgobj->result[$i]['id']; ?>'">
									<div><?= date("H:i", $ticket_deadline); ?></div>
									<div><i class="fa fa-circle" style="color: <?= $priority_data['color']; ?>;" title="<?= $_msg->lang($priority_data['title']); ?>"></i></div>
									<div class="ellipsis"><?= $_msg->lang($_settings->ticket_category[$_pgobj->result[$i]['category']]); ?></div>
									<div class="ellipsis"><?= $_msg->lang($customer_name); ?></div>
									<div class="ellipsis"><?= $_pgobj->result[$i]['subject']; ?></div>
									<div class="ellipsis"><?= $_pgobj->result[$i]['message']; ?></div>
								</div>
<?php	} if($i) { ?>
							</nav>
<?php	} else { ?>
							<div class="alert alert-info" style="max-width: 360px; margin: auto;">
								<strong><?= $_msg->lang("Info:"); ?></strong> <?= $_msg->lang("No items so far! Use the Add button.") ."\n"; ?>
							</div>
<?php	} // ----- Display Simple Pagination Control for Closed Tickets ----- //
		if(isset($_GET['closed'])) {
			$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
			$query.= " SELECT COUNT(at.id) AS total_closed FROM at_tickets at, alm WHERE at.id = alm.ticket_id AND NOT alm.status";
			$_pgobj->query($query); ?>
							<div class="clearfix" style="height: 20px;"></div>
							<div class="btn-group" style="margin: auto;">
<?php		if($page_offset) { ?>
								<button class="btn btn-default" onclick="history.back();">
									<i class="fa fa-share-square-o fa-flip-horizontal"></i> <?= $_msg->lang("Return") ."\n"; ?>
								</button>
<?php		} if(intval($_pgobj->result[0]['total_closed']) > ($rows_amount * ($page_offset + 1))) { ?>
								<button class="btn btn-primary" onclick="window.location = '<?= "./?p=$page_number&closed&n=". ($page_offset + 1); ?>';">
									<i class="fa fa-share-square-o"></i> <?= $_msg->lang("Load More") ."\n"; ?>
								</button>
<?php		} ?>
							</div>
<?php	}
	} else {
		// ----- Display Insert OR Update Tickets Form ----- //
		if(isset($_GET['tid'])) {
			$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, priority, status, target_id FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
			$query.= " SELECT at.*, alm.priority, alm.status, alm.target_id FROM at_tickets at, alm WHERE at.id = alm.ticket_id AND at.id = \$1";
			$_pgobj->query_params($query, array($_GET['tid']));
			if($_pgobj->rows == 0) $_msg->error("Could not open ticket!");
			$ticket_array = $_pgobj->result[0];
			$ticket_title = $_msg->lang("Update Ticket");
			$input_hidden = array("ticket_id", $ticket_array['id']);
			$subject_disable = 'disabled="true"';
			$deadline_string = date("$date_format H:i", strtotime($ticket_array['deadline']));
			$status_check = ($ticket_array['status'] == 't') ? ('') : ('checked="true"');
			$customer_id = ($ticket_array['customer_id']) ? (intval($ticket_array['customer_id'])) : (FALSE);
		} else {
			$ticket_title = $_msg->lang("Open Ticket");
			$input_hidden = array("customer_id", $_GET['cid']);
			$ticket_array = array('category' => NULL, 'priority' => round(count($_settings->ticket_priority)/2), 'subject' => '', 'target_id' => NULL);
			$deadline_string = date("$date_format H:i", (round(strtotime('+1 day')/1800) * 1800));
			$subject_disable = '';
			$customer_id = ($_GET['cid']) ? (intval($_GET['cid'])) : (FALSE);
		} if($customer_id) {
			$query = 'SELECT substring(data from \':"name";s:[0-9]+:"([^"]+)";\') AS customer_name FROM at_userdata WHERE id = $1';
			$_pgobj->query_params($query, array($customer_id));
			$customer_name = ($_pgobj->rows) ? ($_pgobj->result[0]['customer_name']) : ($_msg->lang('No Customer'));
			$button_function = "onclick=\"list_details($customer_id);\"";
		} else {
			$customer_name = $_msg->lang('No Customer');
			$button_function = "";
		}
		// ----- Fill Ticket Calendar Array ----- //
		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, priority, status FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= " SELECT at.deadline, at.subject, at.category, alm.priority FROM at_tickets at, alm WHERE at.id = alm.ticket_id AND alm.status";
		$_pgobj->query($query);
		$ticket_calendar = array();
		for($i=0; $i<$_pgobj->rows; $i++) {
			$calendar_date = date("Y-n-j", strtotime($_pgobj->result[$i]['deadline']));
			$calendar_hour = date("H-i", strtotime($_pgobj->result[$i]['deadline']));
			$tooltip_string = $_msg->lang($_settings->ticket_category[$_pgobj->result[$i]['category']]) ." / ";
			$tooltip_string.= $_msg->lang($_settings->ticket_priority[$_pgobj->result[$i]['priority']]['title']) ."<br />";
			$ticket_calendar[$calendar_date][$calendar_hour] = $tooltip_string . $_pgobj->result[$i]['subject'];
		}
		// ----- Insert input type and div modal to enable customer_name button ----- //
		if($customer_id) { ?>
							<input type="hidden" id="type" name="type" value="inet" />
							<div id="modal_details" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content text-left">
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
													<li><a id="details_support" href="javascript:void(0);">
														<i class="fa fa-wrench"></i> <?= $_msg->lang("Support"); ?></a>
													</li>
<?php		if($_session->groupname != 'tech') { ?>
													<li role="separator" class="divider"></li>
													<li><a id="details_disable" data-enable="<?= $_msg->lang('Enable'); ?>" data-disable="<?= $_msg->lang('Disable'); ?>" href="javascript:void(0);">
														<i class="fa fa-lock"></i> <span><?= $_msg->lang("Disable"); ?></span></a>
													</li>
													<li><a id="details_delete" href="javascript:void(0);">
														<i class="fa fa-trash"></i> <?= $_msg->lang("Delete"); ?></a>
													</li>
<?php		} ?>
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
<?php	} ?>
							<form class="form-ticket" action="<?= "./?p=$page_number"; ?>" method="post" enctype="application/x-www-form-urlencoded">
								<div class="text-center">
									<div class="btn-group" style="margin: auto;">
										<button type="submit" tabindex="-1" class="btn btn-primary active"><?= $ticket_title; ?></button>
										<button type="button" tabindex="-1" class="btn btn-default" <?= $button_function; ?> ><?= $customer_name; ?></button>
									</div>
								</div>
								<div class="clearfix" style="height: 20px;"></div>
								<input type="hidden" tabindex="-1" name="<?= $input_hidden[0]; ?>" value="<?= $input_hidden[1]; ?>" />
								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12" style="margin-bottom: 10px;">
										<label class="col-xs-12" for="category"><?= $_msg->lang("Category"); ?></label>
										<select id="category" name="category" tabindex="1" class="form-control selectpicker">
<?php	// --- Display Database Settings Ticket Categories
		foreach($_settings->ticket_category as $category_value => $category_name) {
			$is_selected = ($ticket_array['category'] == $category_value) ? ('selected="true"') : (''); ?>
											<option value="<?= $category_value; ?>" <?= $is_selected; ?> ><?= $_msg->lang($category_name); ?></option>
<?php	} ?>
										</select>
									</div><div class="col-md-6 col-sm-6 col-ms-6 col-xs-12" style="margin-bottom: 10px;">
										<label class="col-xs-12" for="priority"><?= $_msg->lang("Priority"); ?></label>
										<select id="priority" name="priority" tabindex="2" class="form-control selectpicker">
<?php	// --- Display Database Settings Ticket Priorities
		foreach($_settings->ticket_priority as $priority_value => $priority_data) {
			$is_selected = ($ticket_array['priority'] == $priority_value) ? ('selected=true') : ('');
			$priority_content = "<i class=\"fa fa-circle\" style=\"color: $priority_data[color]; margin-right: 6px;\"></i> ". $_msg->lang($priority_data['title']); ?>
											<option value="<?= $priority_value; ?>" <?= $is_selected; ?> data-content='<?= $priority_content; ?>' ><?= $_msg->lang($priority_data['title']); ?></option>
<?php	} ?>
										</select>
									</div>
								</div><div class="clearfix"></div>
								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
										<label class="col-xs-12" for="subject"><?= $_msg->lang("Subject"); ?></label>
										<input id="subject" name="subject" tabindex="3" style="margin-bottom: 10px;" class="form-control" type="text" value="<?= $ticket_array['subject']; ?>" required="" <?= $subject_disable; ?>>
									</div><div class="col-md-6 col-sm-6 col-ms-6 col-xs-12" style="margin-bottom: 10px;">
										<label class="col-xs-12" for="target"><?= $_msg->lang("Target"); ?></label>
										<select id="target" name="target" tabindex="4" class="form-control selectpicker">
											<option value=""><?= $_msg->lang("Nobody"); ?></option>
<?php	// --- Display Admins and Technicians to be targeted
		$_pgobj->query("SELECT * FROM at_technicians WHERE id > 0 ORDER BY id");
		for($i=0; $i<$_pgobj->rows; $i++) {
			$is_selected = ($ticket_array['target_id'] == $_pgobj->result[$i]['id']) ? ('selected="true"') : (''); ?>
											<option value="<?= $_pgobj->result[$i]['id']; ?>" <?= $is_selected; ?> ><?= $_pgobj->result[$i]['name']; ?></option>
<?php	} ?>
										</select>
									</div>
								</div><div class="clearfix"></div>
								<div class="form-group">
									<div class="col-md-7 col-sm-7 col-ms-9 col-xs-12" style="min-width: 330px; margin-bottom: 10px;">
										<label class="col-xs-12" for="deadline"><?= $_msg->lang("Deadline"); ?></label>
										<input id="deadline" name="deadline" type="text" tabindex="-1" value="<?= $deadline_string; ?>" class="form-control" data-date-format="<?= $date_format; ?>">
									</div><div class="col-md-5 col-sm-5 col-ms-9 col-xs-12" style="margin-bottom: 10px;">
										<label class="col-xs-12" for="message"><?= $_msg->lang("Message"); ?></label>
										<textarea id="message" name="message" tabindex="5" class="form-control" required="" style="resize: none;" rows="6"></textarea>
									</div>
								</div><div class="clearfix"></div>
								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
<?php	if(isset($_GET['tid'])) { ?>
										<input type="checkbox" name="close" id="close" tabindex="6" class="checkbox ticket" <?= $status_check; ?> />
										<span class="custom-checkbox"></span>
										<label class="label-checkbox ticket" for="close"><?= $_msg->lang("Close Ticket!"); ?></label>
<?php	} ?>
									</div><div class="col-md-6 col-sm-6 col-ms-6 col-xs-12">
										<button type="submit" tabindex="6" class="btn btn-primary pull-right"><?= $_msg->lang("Save"); ?></button>
									</div>
								</div>
<?php	if(isset($_GET['tid'])) { ?>
								<div class="form-group">
									<div class="history col-xs-12 " style="margin-bottom: 10px;"><?= $_msg->lang("History"); ?></div>
								</div>
<?php	} ?>
							</form>
<?php	if(isset($_GET['tid'])) { ?>
							<div class="clearfix"></div>
						<!-- Confirm Configuration Deletion Modal /-->
							<div id="modal_ticket_message_delete" class="modal fade" role="dialog">
								<div class='modal-dialog'>
									<div class='modal-content'>
										<div class='modal-header'>
											<button class="close" data-dismiss="modal" style="padding: 5px;">&times;</button>
											<strong style="padding-right: 20px;"><?= $_msg->lang('Delete'); ?></strong>
										</div>
										<div class="modal-body">
											<p><?= $_msg->lang("Are you sure you want to delete this message?"); ?><br />"<span class="strong">empty</span>"</p>
											<br /><p><?= $_msg->lang("This action is irreversible."); ?></p>
											<input type="hidden" name="id" value="0" />
										</div>
										<div class="modal-footer">
											<button class="btn btn-default" data-dismiss="modal"><?= $_msg->lang("Cancel"); ?></button>
											<button class="btn btn-danger" data-dismiss="modal" onclick='tools_ticketDelete($("#modal_ticket_message_delete input").val(), true);'><?= $_msg->lang("Delete"); ?></button>
										</div>
									</div>
								</div>
							</div>
							<div class="list-group col-md-6 col-sm-6 col-ms-10 col-xs-12" style="float: none; margin: auto; text-align: left;">
<?php		// --- Display Ticket History of Messages in case of Update
			$query = 'SELECT atm.id, atm.date, atm.status, atm.message, substring(aud.data from \':"name";s:[0-9]+:"([^"]+)";\') AS user_name';
			$query.= ' FROM at_ticket_messages atm LEFT OUTER JOIN at_userdata aud ON atm.user_id = aud.id WHERE atm.ticket_id = $1 ORDER BY atm.date DESC';
			$_pgobj->query_params($query, array($_GET['tid']));
			for($i=0; $i<$_pgobj->rows; $i++) {
				$status_string = ($_pgobj->result[$i]['status'] == 't') ? ("Open") : ("Closed");
				$date_string = date("$date_format H:i", strtotime($_pgobj->result[$i]['date']));
				$user_name = ($_pgobj->result[$i]['user_name']) ? ($_pgobj->result[$i]['user_name']) : (" # ". $_msg->lang("System"));
?>
								<div class="list-group-item" data-id="<?= $_pgobj->result[$i]['id']; ?>">
									<a class="ticket-menu" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
									<ul class="dropdown-menu ticket-menu">
										<li><a href="javascript:void(0);" onclick="tools_ticketDelete(<?= $_pgobj->result[$i]['id']; ?>);">
											<span class="strong red"><?= $_msg->lang("Delete"); ?></span><i class="fa fa-trash red"></i>
										</a></li>
										<li><a href="javascript:void(0);" onclick="tools_ticketEdit(<?= $_pgobj->result[$i]['id']; ?>);">
											<span class="strong"><?= $_msg->lang("Edit"); ?></span><i class="fa fa-pencil"></i>
										</a></li>
									</ul>
									<h4 class="list-group-item-heading"><?= $date_string; ?> (<?= $_msg->lang($status_string); ?>)</h4>
									<p class="list-group-item-text">
										<span><?= $user_name; ?> - </span><span data-message=""><?= $_pgobj->result[$i]['message']; ?></span>
									</p>
								</div>
<?php		} ?>
							</div>
<?php	}
	} ?>
							<script src="<?= $_path->js; ?>/bootstrap-select.min.js"></script>
							<script src="<?= $_path->js; ?>/jquery.datetimepicker.full.min.js"></script>
							<script src="<?= $_path->js; ?>/pnotify.custom.min.js"></script>
							<script type="text/javascript">
								$(function () {
								// --- Fancify Select Elements as Buttons
									$(".selectpicker").selectpicker({ noneSelectedText: '<?= $_msg->lang("Nothing Selected"); ?>' });
<?php if(isset($_GET['cid']) || isset($_GET['tid'])) { ?>
									$("#deadline").datetimepicker({ // --- Initialize TimeDatePicker Calendar
										i18n: { en: { months: <?= json_encode($months); ?>, dayOfWeekShort: <?= json_encode($days_week); ?>}},
										minDate: 0, // today
										maxDate: '<?= date("Y-m-d", strtotime("+2 weeks")); ?>',
										format: '<?= "$date_format H:i"; ?>',
										allowTimes: <?= json_encode($hours); ?>,
										yearStart: '<?= date("Y"); ?>',
										yearEnd: '<?= date("Y", strtotime("+1 year")); ?>',
										inline: true
									});
									$(".xdsoft_datepicker").off('mousewheel wheel'); // --- Disable Calendar Months Whelling
									ticketCalendar = JSON.parse('<?= json_encode($ticket_calendar); ?>'); // --- Load Open Tickets Array
									$(".xdsoft_datetimepicker").on('generate.xdsoft', null, "input", tools_ticketCalendar);
<?php	} else { ?>
									$(".tickets-list i[title]").tooltip(); // --- Initialize Priority Blotches Tooltips
<?php	} if($success) echo str_repeat("\t", 10) ."alertPNotify('alert-success', '$success');\n"; ?>
								});
							</script>
						</div>
					</div>
