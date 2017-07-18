<?php
	/*****************************************************************
	* get_color creates a rgba color string based on an input string *
	* the 'b' search is intended to separate colors from each other  *
	* this function is not random! same input means same color.      *
	******************************************************************/

	function get_color ($string, $alpha = 1, $md5_search_char = 'b') {
	// Checking input
		if(is_array($string)) return FALSE;
		if(!is_numeric($alpha)) return FALSE;
	// Defining Variables
		$md5 = md5(strval($string));
		$colors = array();
	// Getting a good position
		$start_position = (strpos($md5, $md5_search_char) < 26) ? (strpos($md5, $md5_search_char)) : (18);
		$end_position = ($start_position + 6);
	// Looping for Red, Green and Blue
		for($i = $start_position; $i < $end_position; $i += 2)
			$colors[] = round(hexdec(substr($md5, $i, 2)) * 240 / 255);
		$colors[] = floatval($alpha);
	// Returning The Color
		return vsprintf("rgba(%d,%d,%d,%0.2f)", $colors);
	}
?>
