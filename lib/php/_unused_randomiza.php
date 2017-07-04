<?php
	if(!isset($pg_obj)) header("Location: /");

	function randomiza($s, $f=FALSE) {
		$h = "";
		$c = ($f) ?
				("123456789AaBbDdEeGgHhiLMmNnQqRrtWXYZ") :
				("0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz_");
		$m = strlen($c) - 1;
		mt_srand((double)microtime() * 1000000);
		for($i = 0; $i < $s; $i++)
			$h .= $c[mt_rand(0, $m)];
		return($h);
	}
?>