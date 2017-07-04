<?php
	/******************************************************
	* register file_upload, requested by "register_users" *
	* file in order to save the profile picture safely.   *
	* dependencies: all the classes at config file; plus  *
	* str2ascii as a safe renameing function.             *
	*******************************************************/

	if(isset($_POST['ajax']) && isset($_POST['name']) && isset($_POST['image'])) {
		require("../../config.php");
		require("$_path->php/str2ascii.php");
		if(!strstr($_POST['image'], 'data:image/jpeg;base64,')) die($_msg->lang("Error: Invalid image format!"));
		// ----- Set Destination Folder for uploaded images ----- //
		$image_path = "$_path->root/$_path->images/uploaded";
		if(!is_dir($image_path)) {
			if(!mkdir($image_path, 0755)) die($_msg->lang("Error: Upload path not found!"));
		} if(!is_writable($image_path)) die($_msg->lang("Error: Upload path not writeable!"));
		// ----- Clean Unused uploaded images ----- //
		$uploaded_files = glob("$image_path/*.jpg");
		for($i=0; $i<count($uploaded_files); $i++)
			if(!$_pgobj->select("at_userdata", array("picture" => str_replace("$image_path/", '', $uploaded_files[$i])))) unlink($uploaded_files[$i]);
		// ----- Get uploaded image data ----- //
		$image_data_string = $_POST['image'];
		$image_name = date("YmdHis_") . substr(str2ascii($_POST['name']), 0, -3) .".jpg";
		$image_data_string = str_replace(array('data:image/jpeg;base64,', ' '), array('', '+'), $image_data_string);
		$image_data = base64_decode($image_data_string);
		// ----- Write file and print its resulting name ----- //
		$result = file_put_contents("$image_path/$image_name", $image_data);
		echo ($result) ? ($image_name) : ($_msg->lang("Error: Picture couldn't be saved!"));
	} else header("Location: /");
?>
