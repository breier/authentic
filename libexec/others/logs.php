<?php
	if(!isset($_pgobj)) header("Location: /");
	if($logn['groupname'] != 'admn' && $logn['groupname'] != 'full') {
		echo "\t\t\t\t<h2 style=\"color: red;\">Você não deveria estar aqui!</h2>\n";
		echo "\t\t\t\t<a href=\"./\">&laquo; voltar</a>\n";
	} else { ?>
		<h2 class="topt"><a href="<?= $_SERVER['REQUEST_URI']; ?>" >VISUALIZAR LOGS</a></h2>
<?php	} ?>
