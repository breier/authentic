<?php
	/*************************************************************
	* register equipment_info, requested by "register_equipment" *
	* file in order to get monitoring info for new equipments.   *
	* dependencies: all the classes at config file; plus  *
	* str2ascii as a safe renameing function.             *
	*******************************************************/

	if(isset($_POST['ajax']) && isset($_POST['ip_address']) && isset($_POST['username']) && isset($_POST['password'])) {
		require("../../config.php");
		require("../../login.php");
		print_r($_POST);
	} else header("Location: /");
?>
