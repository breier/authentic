<?php
	/*******************************************************************
	* financial page file, included by start if selected (also ?p=31). *
	* Responsible for listing and configuring financial data.          *
	* dependencies: messages as $_msg (config);                        *
	*               session as $_session (login);                      *
	*               pgsql as $_pgobj (config);                         *
	*               settings as $_settings (login).                    *
	*******************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_session)) $_msg->error("Class Session not set!");
	if(!isset($_pgobj)) $_msg->error("Class PgSQL not set!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	// ----- Checking Permissions ----- //
	if($_session->groupname == 'tech') $_msg->warning("You do not have permission to see this page!");
	else {
	// Total Active Customers
		$_pgobj->query("SELECT COUNT(username) AS total_customers FROM at_userauth WHERE NOT groupname && ARRAY['full', 'admn', 'tech', 'disabled']");
		$total_customers = $_pgobj->result[0]['total_customers'];
	// Amount Sold
		$plans = array();
		$customers_per_plan = array();
		$total_sold = 0;
		$query = "SELECT COUNT(groupname) AS customers_per_plan, array_to_json(groupname) AS groupname, array_to_json(priority) AS priority";
		$query.= " FROM at_userauth WHERE NOT groupname && ARRAY['full', 'admn', 'tech', 'disabled'] GROUP BY groupname, priority";
		$_pgobj->query($query);
		for($i=0; $i<$_pgobj->rows; $i++) {
			$groupname_array = json_decode($_pgobj->result[$i]['groupname']);
			$priority_zero_index = array_search(0, json_decode($_pgobj->result[$i]['priority']));
			if(!$priority_zero_index) $priority_zero_index = 0;
			$plans[] = $groupname_array[$priority_zero_index];
			$customers_per_plan[] = $_pgobj->result[$i]['customers_per_plan'];
		}
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Tools") ." &raquo; ". $_msg->lang("Financial"); ?></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<table class="table">
								<thead>
									<tr>
										<th><?= $_msg->lang("Media"); ?></th>
										<th><?= $_msg->lang("Plan"); ?></th>
										<th><?= $_msg->lang("Price"); ?></th>
										<th><?= $_msg->lang("Customers"); ?></th>
										<th><?= $_msg->lang("Total"); ?></th>
									</tr>
								</thead>
								<tbody>
<?php	if(count($plans)) {
			$plan_sold = array(0);
			for($i=0; $i<count($plans); $i++) {
				$_pgobj->query_params('SELECT media, price FROM at_plans WHERE name = $1', array($plans[$i]));
				$plan_price = ($_pgobj->rows) ? ($_pgobj->result[0]['price']) : (0);
				$plan_media = ($_pgobj->rows) ? ($_pgobj->result[0]['media']) : (0);
				$plan_sold[] = $plan_price * $customers_per_plan[$i];
?>
									<tr>
										<td><?= $_msg->lang($plan_media); ?></td>
										<td><?= $plans[$i]; ?></td>
										<td><?= $_settings->system["Currency"] .' '. number_format($plan_price, 2); ?></td>
										<td><?= $customers_per_plan[$i]; ?></td>
										<td><?= $_settings->system["Currency"] .' '. number_format($plan_price * $customers_per_plan[$i], 2); ?></td>
									</tr>
<?php		} ?>
									<tr>
										<td colspan="3" class="strong text-right"><?= mb_strtoupper($_msg->lang("Total Customers"), "UTF-8"); ?></td>
										<td colspan="2" class="strong"><?= $total_customers; ?></td>
									</tr>
									<tr>
										<td colspan="3" class="strong text-right"><?= mb_strtoupper($_msg->lang("Amount"), "UTF-8"); ?></td>
										<td></td>
										<td class="strong"><?= $_settings->system["Currency"] .' '. number_format(array_sum($plan_sold), 2); ?></td>
									</tr>
									<tr>
										<td colspan="3" class="strong text-right"><?= mb_strtoupper($_msg->lang("Average"), "UTF-8"); ?></td>
										<td></td>
										<td class="strong"><?= $_settings->system["Currency"] .' '. number_format(array_sum($plan_sold) / $total_customers, 2); ?></td>
									</tr>
<?php	} else { ?>
									<tr>
										<td colspan="5"><?= $_msg->lang("No data available in the table!"); ?></td>
									</tr>
<?php	} ?>
								</tbody>
							</table>
						</div>
					</div>
<?php
	}
?>
