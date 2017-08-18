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
	// ----- Get Google Maps API Key ----- //
	$google_api_key_file = "$_path->conf/googleAPI.key";
	if(file_exists($google_api_key_file)) {
		$google_api_key = trim(file_get_contents($google_api_key_file));
	} else $google_api_key = '';
	// ----- Getting Geometric Data ----- //
	$fiber_array = array();
	$_pgobj->query("SELECT id, array_to_json(color) AS color, location, path FROM at_fiberdata WHERE type = 'fiber'");
	for($i=0; $i<$_pgobj->rows; $i++) {
		$path_array = json_decode(str_replace(array('(', ')'), array('[', ']'), $_pgobj->result[$i]['path']));
		$path_object = "[";
		for($j=0; $j<count($path_array); $j++) {
			if($j) $path_object.= ",";
			$path_object.= "{lat:". $path_array[$j][0] .",lng:". $path_array[$j][1] ."}";
		} $path_object.= "]";
		$fiber_array[$_pgobj->result[$i]['id']] = array(
			"color" => json_decode($_pgobj->result[$i]['color']),
			"location" => str_replace(array('(', ')'), array('[', ']'), $_pgobj->result[$i]['location']),
			"path" => $path_object
		);
	} $initial_location = (count($fiber_array)) ? ($fiber_array[key($fiber_array)]['location']) : ("[-27.4220552,-51.7784841]");
	$initial_latitude = substr($initial_location, 1, strpos($initial_location, ',')-1);
	$initial_longitude = substr($initial_location, strpos($initial_location, ',')+1, -1);
	$initial_zoom = (count($fiber_array)) ? (16) : (6);
	// ----- Printing Plans Page Main Header ----- //
?>
					<div class="x_panel">
						<div class="x_title">
							<h2><?= $_msg->lang("Tools") ." &raquo; ". $_msg->lang("Fiber Map"); ?></h2>
							<div class="btn-group pull-right">
								<button id="map_actions" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><?= $_msg->lang("Add"); ?></button>
								<button id="map_actions" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bars"></i></button>
								<ul class="dropdown-menu">
									<li><a id="add_fiber_path" href="javascript:void(0);">
										<i class="fa fa-share-alt"></i> <?= $_msg->lang("Fiber Path"); ?></a>
									</li>
									<li><a id="add_termination_box" href="javascript:void(0);">
										<i class="fa fa-share-alt-square"></i> <?= $_msg->lang("Termination Box"); ?></a>
									</li>
								</ul>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<div id="fiber_map" style="height: 540px;"></div>
							<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key; ?>&callback=initMap&libraries=drawing" type="text/javascript"></script>
							<script src="<?= $_path->js; ?>/gmaps.min.js"></script>
							<script type="text/javascript">
								function initMap () {
									var fiberMap = new google.maps.Map(
										document.getElementById('fiber_map'),
										{ center: {lat: <?= $initial_latitude; ?>, lng: <?= $initial_longitude; ?>}, zoom: <?= $initial_zoom; ?> }
									);
<?php	foreach($fiber_array as $fiber_id => $fiber_data) { ?>
									var lineBorder<?= $fiber_id; ?> = new google.maps.Polyline({ path: <?= $fiber_data["path"]; ?>, strokeColor: 'black', strokeOpacity: 0.5, strokeWeight: 4 });
									lineBorder<?= $fiber_id; ?>.setMap(fiberMap);
									var polyLine<?= $fiber_id; ?> = new google.maps.Polyline({ path: <?= $fiber_data["path"]; ?>, strokeColor: '<?= $fiber_data["color"][0]; ?>', strokeOpacity: 1, strokeWeight: 2 });
									polyLine<?= $fiber_id; ?>.setMap(fiberMap);
<?php	} /* --- Initilize Add Buttons --- */ ?>
									terminationBoxIcon = {
										url: '<?= $_path->images; ?>/share-alt-square.png',
										size: new google.maps.Size(32, 32),
										scaledSize: new google.maps.Size(20, 20),
										anchor: new google.maps.Point(10, 10)
									};
									$("#add_fiber_path").on("click", function () {
										drawingManager = new google.maps.drawing.DrawingManager({
											drawingControl: false,
											drawingMode: 'polyline',
											polylineOptions: { strokeColor: 'black', strokeOpacity: 0.75, strokeWeight: 4 }
										});
										drawingManager.setMap(fiberMap);
										google.maps.event.addListener(drawingManager, 'polylinecomplete', function(polyline) {
											var newLinePath = polyline.getPath();
											console.log(JSON.stringify(newLinePath.b));
											drawingManager.setMap(null);
											delete(drawingManager);
										});
									});
									$("#add_termination_box").on("click", function () {
										drawingManager = new google.maps.drawing.DrawingManager({
											drawingControl: false,
											drawingMode: 'marker',
											markerOptions: { icon: terminationBoxIcon, title: 'new TB' }
										});
										drawingManager.setMap(fiberMap);
										google.maps.event.addListener(drawingManager, 'markercomplete', function(marker) {
											console.log(JSON.stringify(marker.position));
											drawingManager.setMap(null);
											delete(drawingManager);
										});
									});
								}
							</script>
						</div>
					</div>
