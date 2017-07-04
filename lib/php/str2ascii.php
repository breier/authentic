<?php
	/*********************************************************
	* str2ascii replaces any special character in the string *
	* for a likely substitute to be ASCII compliant.         *
	**********************************************************/

	function str2ascii ($string) {
	// Checking input
		if(is_array($string)) return FALSE;
	// Replacing Lower Case Special Chars
		$special_chars = array('à','è','ì','ò','ù','â','ê','î','ô','û','ä','ë','ï','ö','ü','á','é','í','ó','ú','ã','õ','ñ','ç',' ','-','ª','º','°');
		for($i=0; $i<count($special_chars); $i++) $special_chars[$i] = '/'. $special_chars[$i] .'/i';
		$replacements = array('a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','a','o','n','c','_','_','a','o','o');
		$string = preg_replace($special_chars, $replacements, mb_strtolower($string, 'UTF-8'));
	// Returning The Lower Case ASCII string
		return preg_replace('/[^a-z0-9_]/i', '', $string);
	}
?>
