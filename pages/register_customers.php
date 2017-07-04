<?php
	/*****************************************************************************
	* register_customers page file, included by start if selected (also ?p=20).  *
	* Responsible for the registering Customers and provisioning its equipments. *
	* dependencies: message as $_msg (config);                                   *
	*               pgsql as $_pgobj (config);                                   *
	*               path as $_path (config).                                     *
	******************************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
	// ----- Printing Register Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Register") ." &raquo; ". $_msg->lang("Customer"); ?></h2>
							<div class="col-md-2 col-sm-2 col-ms-2 col-xs-2 pull-right">
								<i class="fa fa-refresh fa-spin fa-fw" style="visibility: hidden;"></i>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
<?php	// ----- Dealing with POST forms ----- //
	$customer_added_success = FALSE;
	if(isset($_POST['username']) && isset($_POST['confirm'])) {
		if(!$_pgobj->select("radgroupcheck", array("groupname" => pg_escape_string($_POST['plan'])))) $_msg->warning("Invalid Plan!");
		else {
			if($_pgobj->select("radusergroup", array("username" => pg_escape_string($_POST["username"])))) $_msg->warning("Username already exists!");
			else {
				$_pgobj->query_params('INSERT INTO radusergroup (username, groupname) VALUES ($1, $2)', array($_POST['username'], $_POST['plan']));
				if(!$_pgobj->rows) $_msg->warning("Customer couldn't be added!");
				else {
					$params = array($_POST['username'], $_POST['password'], $_POST['mac_address']);
					$query = 'INSERT INTO radcheck (username, attribute, op, "value") VALUES ($1, \'Cleartext-Password\', \':=\', $2), ($1, \'Calling-Station-Id\', \'==\', $3)';
					$_pgobj->query_params($query, $params);
					if(!$_pgobj->rows) {
						$_msg->warning("Customer couldn't be added!");
						$_pgobj->query_params('DELETE FROM radusergroup WHERE username = $1', array($_POST['username']));
					} else {
						$customer_data = serialize(array_diff_key($_POST, array("username" => '', "password" => '', "mac_address" => '', "connection" => '', "confirm" => '')));
						$params = array($_POST['username'], (($_session->id) ? ($_session->id) : (0)), $customer_data, $_POST['connection']);
						$_pgobj->query_params('INSERT INTO at_userdata (username, higher_id, data, connection) VALUES ($1, $2, $3, $4)', $params);
						if(!$_pgobj->rows) {
							$_msg->warning("Customer couldn't be added!");
							$_pgobj->query_params('DELETE FROM radusergroup WHERE username = $1', array($_POST['username']));
							$_pgobj->query_params('DELETE FROM radcheck WHERE username = $1', array($_POST['username']));
						} else { ?>
							<div class="alert alert-success">
								<strong><?= $_msg->lang("Success:"); ?></strong>
								<?= $_msg->lang("Customer") ." ". $_msg->lang("added with success!") ."\n"; ?>
							</div>
							<button class="btn btn-primary" onclick="window.location = '<?= $_SERVER['REQUEST_URI']; ?>';">
								<?= $_msg->lang("Add another") ."\n"; ?>
							</button>
							<button class="btn btn-success" onclick="window.location = './?p=<?= ($page_number - 10) ."#". $_POST["name"]; ?>';">
								<?= $_msg->lang("List") ." ". $_msg->lang("Customers") ."\n"; ?>
							</button>
<?php						$customer_added_success = TRUE;
							//try activate any ONUs just to be shure
						}
					}
				}
			}
		}
	} if(!$customer_added_success) { ?>
							<div style="text-align: center;"><?= $_msg->lang("Please Wait ..."); ?></div>
						</div>
						<script type="text/javascript" >
							$(function () {
							// Prepare document loading animation
								$(document).ajaxStart(function (){ $('.fa-spin').css("visibility", 'visible'); })
												.ajaxStop(function (){ $('.fa-spin').css("visibility", 'hidden'); });
							// Load Registration Form
								$.ajax({ url: '<?= $_path->ajax; ?>/register/smart_form.php', type: 'POST', data: 'ajax=1', success: function (response) { $('.x_content').html(response); } });
							});
						</script>
<?php	} ?>
					</div>
