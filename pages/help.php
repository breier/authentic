<?php
	/***************************************************************
	* help page file, included by start if selected (also ?p=32).  *
	* dependencies: msgs as $_msg (config); settings as $_settings *
	* (login); path as $_path (config).                            *
	****************************************************************/

	// ----- Checking Dependecies ----- //
	if(!isset($_msg)) die("Error: Messages Class not Initialized!");
	if(!isset($_settings)) $_msg->error("Class Settings not set!");
	if(!isset($_path)) $_msg->error("Class Path not set!");
?>
						<div class="x_panel">
							<div class="x_title">
								<h2><?= $_msg->lang("Help"); ?></h2>
								<div class="clearfix"></div>
							</div>
							<div class="x_content text-center">
								<div class="col-xl-3 col-lg-4 col-md-5 col-sm-6 col-ms-9 col-xs-12" style="margin: auto; float: none;">
									<?= $_msg->info("Under Construction!"); ?>
								</div>
							</div>
						</div>
