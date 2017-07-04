<?php
	if(isset($_POST['ajax']) &&
		isset($_POST['user']) &&
		isset($_POST['type']) &&
		isset($_POST['actn']) &&
		isset($_POST['info'])) {
		require("../config.php");

		require(I."/php/history.php");
		history($_POST['user'], $_POST['type'], $_POST['actn'], urldecode($_POST['info']));
		echo "<p>history</p>";
	}
?>