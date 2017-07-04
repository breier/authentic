<?php
	if(isset($_POST['ajax']) && isset($_POST['type'])) {
		require("../config.php");

		if($_POST['type']=='ee') {
			$pta = str_replace('..', '.', P); ?>
<h3 style="color: #164D7D;">ENLACE ENTRE TORRES</h3>
<div style="display: absolute; text-align: right; height: 0;">
	<span style="color: #555; font-size: 10px; position: relative; right: 40px; top: -35px;">
		(aguarde o preenchimento)
	</span><span style="position: relative; right: 10px; top: -35px;">
		<img src="<?= $pta; ?>/expand.png" alt="&ETH;" title="Maximizar"
			style="cursor: pointer;" id="eexp" />
	</span>
</div>
<div style="height: 196px; max-height: 196px; overflow: auto;">
	<div align="center" id="ee_master" style="display: inline; border-spacing: 5px;"></div>
	<div style="position: fixed; top: 0; left: 0; z-index: 500; height: 100%; width: 100%;
			background-color: rgba(0,0,0,0.4); display: none;" id="ee_fume">
		<div style="position: relative; top: 140px; left: 2%; background-color: #FFF;
						padding: 5px; border: solid 1px #555; border-radius: 5px;
						max-width: 95%; min-height: 300px; overflow: auto;">
			<h2>ENLACE ENTRE TORRES</h2>
			<button onclick="m('ee_fume').style.display = 'none';" title="Fechar"
					style="position: absolute; right: 10px; top: 20px;">&times;</button>
			<span id="ee_span" style="border-spacing: 5px;"></span>
		</div>
	</div>
</div>
<?php	}
//////////////////////////////////////////////////////////////////////////
		if($_POST['type']=='ce') { ?>
<h3 style="color: #164D7D;">CADASTRAR EQUIPAMENTOS</h3>
<form name="hhc" action="#ce" method="post" enctype="application/x-www-form-urlencoded">
	<div style="display: absolute; text-align: right; height: 0;">
		<span style="position: relative; right: 10px; top: -35px;">
			<button type="button" name="fenv" label="null" style="width: 100px;"
					onclick="hhreg('hhc');" tabindex="13">Enviar</button>
		</span>
	</div>
	<table border="0" cellspacing="6px" width="100%">
		<tr>
			<td style="text-align: right;">Tipo:</td>
			<td style="text-align: left;">
				<select name="type" label="Tipo" id="type" tabindex="1"
						onchange="changeForm(this.value); this.blur();" >
					<option label="Torre SSH" value="repl" selected="selected">Torre SSH</option>
					<option label="Torre Telnet" value="skyp" >Torre Telnet</option>
					<option label="Torre Dual" value="dual" >Torre Dual</option>
					<option label="Ponto a Ponto" value="jump" >Ponto a Ponto</option>
					<option label="Livre Acesso" value="ownr" >Livre Acesso</option>
					<option label="Servidor" value="serv" >Servidor</option>
				</select></td>
			<td style="text-align: right;">MAC de Host:</td>
			<td style="text-align: left;">
				<input type="text" name="maca" size="12" tabindex="7"
					label="MAC de Host" style="width: 140px;" />
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">Título:</td>
			<td style="text-align: left;">
				<input id="name" name="name" size="10" style="width: 120px;" tabindex="2"
					label="Título" value="" type="text" /></td>
			<td style="text-align: right;">IP de HotSpot:</td>
			<td style="text-align: left;">
				<input type="text" name="ip_c" size="12" tabindex="8"
					label="IP de HotSpot" style="width: 140px;" /></td>
		</tr>
		<tr>
			<td style="text-align: right;">Usuário:</td>
			<td style="text-align: left;">
				<input type="text" name="user" size="16" style="width: 180px;" tabindex="3"
					label="Usuário" value="" /></td>
			<td style="text-align: right;">Número de Série:</td>
			<td style="text-align: left;">
				<input type="text" name="snum" size="12" tabindex="9"
					label="Número de Série" style="width: 140px;" /></td>
		</tr>
		<tr>
			<td style="text-align: right;">Senha:</td>
			<td style="text-align: left;">
				<input type="password" name="pas1" size="16" tabindex="4"
					label="Senha" style="width: 180px;" /></td>
			<td style="text-align: right;">Versão de Firmware:</td>
			<td style="text-align: left;">
				<input type="text" name="firm" size="12" tabindex="10"
					label="Versão de Firmware" style="width: 140px;" /></td>
		</tr>
		<tr>
			<td style="text-align: right;">Repetir Senha:</td>
			<td style="text-align: left;">
				<input type="password" name="pas2" size="16" tabindex="5"
					label="Repetir Senha" style="width: 180px;" /></td>
			<td style="text-align: right;">Localização:</td>
			<td style="text-align: left;">
				<input type="text" name="plac" size="16" tabindex="11"
					label="Localização" style="width: 180px;" /></td>
		</tr>
		<tr>
			<td style="text-align: right;">IP de Host:</td>
			<td style="text-align: left;">
				<input type="text" name="ip_h" size="12" tabindex="6"
					label="IP de Host" style="width: 140px;" />
				<button type="button" name="hhac" value="false"
					title="Auto Completar" onclick="hhcom('hhc');">&raquo;</button></td>
			<td style="text-align: right;">Detalhes:</td>
			<td style="text-align: left;">
				<input type="text" name="deta" size="16" tabindex="12"
					label="Detalhes" style="width: 140px;" /></td>
		</tr>
	</table>
</form>
<?php	}
//////////////////////////////////////////////////////////////////////////
		if($_POST['type']=='pa') {
			$pta = str_replace('..', '.', P);
			$hsh = str_replace('..', '.', H); ?>
<h3 style="color: #164D7D;">PÁGINA DE ACESSO</h3>
<iframe id="pa" name="pa" src="<?= $hsh; ?>" ></iframe>
<div style="position: absolute; display: inline;">
	<div style="position: relative; top: 0;">
		<?php include(C.'/butt.php'); ?>
	</div>
	<div><input type="hidden" id="hdntext" name="text" value="html" /></div>
</div>
<div id="imgUP" style="display: none;">
	<div class="boxed">
		<h2>Selecione uma Imagem</h2>
		<button type="button" class="close" title="Fechar"
				onclick="m('imgUP').style.display = 'none';">&times;</button>
		<hr />
		<form name="iform" action="<?= $hsh.'/'; ?>" method="post"
				target="ifupa" enctype="multipart/form-data">
			<input type="file" id="imagem" name="imagem" accept="image/*" style="width: 240px;" />
			<div id="msg"></div>
		</form>
	</div>
</div>
<?php	}
//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
	}	if(isset($_POST['ajax']) &&
			isset($_POST['list']) &&
			isset($_POST['fix']) &&
			isset($_POST['who'])) {
		require("../config.php");
		require_once(I."/php/cfgtn.php");
		include_once(I."/php/history.php");
		$errs = explode('|', $_POST['list']);

		foreach($errs as $erid) {
			$arsr = array('id' => $erid);
			if($tser = pg_select(CONN, 'radmesh', $arsr)) {
				$qrye = "SELECT dest FROM radroutes WHERE orig_type = 'mesh'";
				$qrye.= " AND dest_type = 'mesh' AND orig = ". $erid;
				$rspn = pg_query(CONN, $qrye);
				$pgnr = ($rspn)?(pg_num_rows($rspn)):(0);
				if($pgnr==1) $dest = pg_fetch_array($rspn);
				else continue;
				$qrye = "SELECT maca FROM radmesh WHERE id = ". $dest['dest'];
				$rspn = pg_query(CONN, $qrye);
				$pgnr = ($rspn)?(pg_num_rows($rspn)):(0);
				if($pgnr==1) $dest = pg_fetch_array($rspn);
				else continue;
				$outa = cfgtn($tser[0]['ip_a'], $tser[0]['pass'], 'show link', 23, $dest['maca']);
				$temp = explode("\n", $outa);
				if(sizeof($temp)==1 || $_POST['fix']) {
					$outa = cfgtn($tser[0]['ip_a'], $tser[0]['pass'], "set prov parent $dest[maca]");
					$outa.= cfgtn($tser[0]['ip_a'], $tser[0]['pass'], "set parent $dest[maca]");
					history($_POST['who'], 12, 3, "torre=". $tser[0]['name'] ."; set parent $dest[maca]");
					if(sizeof($temp)==1) {
						while(strstr($temp[0], '  ')) $temp[0] = str_replace('  ', ' ', $temp[0]);
						$outa = explode(' ', $temp[0]);
						$reboot = ($outa[2]=='standby-h') ? (TRUE) : (FALSE);
					}	else $reboot = TRUE;
					if($reboot) {
						cfgtn($tser[0]['ip_a'], $tser[0]['pass'], 'reboot');
						history($_POST['who'], 12, 3, "torre=". $tser[0]['name'] ."; reboot");
					}	echo "<p>OK</p>". $tser[0]['name'] ."\n";
				} else {
					echo "<p>ERRO</p><br />Não foi possível<br />";
					echo "corrigir o enlace<br />de ". $tser[0]['name'];
					history($_POST['who'], 12, 3, "torre=". $tser[0]['name'] ."; no parent found");
					break;
				}
			}
		}	sleep(1);
	}
//////////////////////////////////////////////////////////////////////////
	if(isset($_POST['ajax']) && isset($_POST['ip_c']) && isset($_POST['maca'])) {
		echo "<h3 style=\"color: #164D7D;\">CADASTRAR EQUIPAMENTOS</h3>\n";
		require("../config.php");

		if($tm = pg_query(CONN, "SELECT id FROM at_equip ORDER BY id DESC LIMIT 1")) {
			$tn = (pg_num_rows($tm)==1)?(pg_fetch_array($tm)):(array("id"=>0));
			$id = $tn['id']+1;
		} else $id = 1;
/*
		$qry = "INSERT INTO at_equip VALUES ( $id, CURRENT_TIMESTAMP, '$_POST[name]', ";
		$qry.= "'$_POST[user]', '$_POST[pass]', '$_POST[ip_h]', '$_POST[ip_c]', '$_POST[maca]', ";
		$qry.= "'$_POST[plac]', '$_POST[snum]', '$_POST[firm]', '$_POST[deta]', '$_POST[type]' )";

		if(!$rsl = pg_query(CONN, $qry)) {
			echo "<h2 style=\"color: red;\">ERRO: $_POST[name] não cadastrado!</h2>\n";
			echo "<a href=\"$_SERVER[REQUEST_URI]\">&laquo; voltar</a>\n";
		} else {
			echo "<h2>Equipamento $_POST[name] cadastrado com sucesso!</h2>\n";
			echo "<button name=\"more\" value=\"go\" type=\"button\" onclick=\"openIn('nett', '";
			echo A ."/nett.php', 'ajax=1&type=ce', 1);\" >Cadastrar Mais</button>\n";
		} */
		print_r($_POST);
	}
//////////////////////////////////////////////////////////////////////////
	if(isset($_POST['ajax']) && isset($_POST['text'])) {
		require("../config.php");

		$text = str_replace("><", ">\n<", $_POST['text']);
		while(strstr($text, "\n\n")) $text = str_replace("\n\n", "\n", $text);
		while(strstr($text, "\t\n")) $text = str_replace("\t\n", "", $text);
		$file = H."/content.html";
		$mode = (file_exists($file)) ? ("w") : ("a");
		if(!$flpt = fopen($file, "$mode")) die("ERRO");
		if(!fwrite($flpt, $text, strlen($text))) die("Erro");
		fclose($flpt);
		echo "Salvar";
	} ?>