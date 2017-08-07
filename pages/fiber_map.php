<?php
	/*******************************************************************
	* fiber_map page file, included by start if selected (also ?p=34). *
	* Responsible for leaflet map navigation with polylines and boxes. *
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
	// ----- Getting Geometric Data ----- //
	$fiber_array = array();
	$_pgobj->query("SELECT id, array_to_json(color) AS color, location, path FROM at_fiberdata WHERE type = 'fiber'");
	for($i=0; $i<$_pgobj->rows; $i++) {
		$fiber_array[$_pgobj->result[$i]['id']] = array(
			"color" => json_decode($_pgobj->result[$i]['color']),
			"location" => str_replace(array('(', ')'), array('[', ']'), $_pgobj->result[$i]['location']),
			"path" => str_replace(array('(', ')'), array('[', ']'), $_pgobj->result[$i]['path'])
		); // nedd to adjust +0.00003 and -0.00011 from https://itouchmap.com/latlong.html
	} $initial_location = (count($fiber_array)) ? ($fiber_array[key($fiber_array)]['location']) : ("[-27.4220552, -51.7784841]");
	$initial_zoom = (count($fiber_array)) ? (16) : (6);
	// ----- Printing Plans Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Tools") ." &raquo; ". $_msg->lang("Fiber Map"); ?></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<div id="fiber_map" style="height: 540px;"></div>
							<script src="<?= $_path->js; ?>/leaflet.min.js"></script>
							<script type="text/javascript">
								$(function () {
									var fiberMap = L.map('fiber_map').setView(<?= $initial_location; ?>, <?= $initial_zoom; ?>);
									L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
   									maxZoom: 19
									}).addTo(fiberMap);
<?php	foreach($fiber_array as $fiber_id => $fiber_data) { ?>
									L.polyline(<?= $fiber_data["path"]; ?>, {color: '<?= $fiber_data["color"][0]; ?>'}).addTo(fiberMap);
<?php	} ?>
								});
							</script>
						</div>
					</div>
