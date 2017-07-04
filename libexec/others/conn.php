<?php	/// ----- Show connection info (Signal/TX/RX) ----- ///
	if(isset($_POST['ajax']) && isset($_POST['mac']) && isset($_POST['ipa'])) {
		require("../config.php");
		require(I."/php/cfgssh.php");
		include(I."/php/history.php");

		$mac = $_POST['mac'];
		$ipa = $_POST['ipa'];
	///// Get the Client Info /////
		include_once(I."/php/getConfig.php");
		$drac = getConfig('drac');
		if($drac[1]=='ssh')
			$outa = cfgssh($ipa, $drac[3], $drac[4], 'wstalist', $drac[2], 'TUDO');
		else $outa = 'empty';
		if(strlen($outa)>20) {
			$outa = substr($outa, 2, (strlen($outa)-5));
			$outa = str_replace('{', 'array(', $outa);
			$outa = str_replace('}', ')', $outa);
			$outa = str_replace('":', '" =>', $outa);
			@eval("\$info = $outa;");
		} if(!isset($info)) $info = array('hostname'=>'?', 'signal'=>0, 'noisefloor'=>0, 'tx'=>0, 'rx'=>0);
	///// Display Client Information /////
		include(I."/php/makeBar.php"); ?>
		<tr>
			<td style="text-align: right;">Sinal:</td>
			<td style="text-align: left;">
				<?= makeBar(14, 26, ($info['signal']-$info['noisefloor']), 80); ?>
			</td>
			<td style="text-align: left;"><?= ($info['signal']-$info['noisefloor']); ?></td>
		</tr><tr>
			<td style="text-align: right;">TX Mod:</td>
			<td style="text-align: left;">
				<?= makeBar(20, 64, $info['tx'], 80); ?>
			</td>
			<td style="text-align: left;"><?= intval($info['tx']); ?></td>
		</tr><tr>
			<td style="text-align: right;">RX Mod:</td>
			<td style="text-align: left;">
				<?= makeBar(20, 64, $info['rx'], 80); ?>
			</td>
			<td style="text-align: left;"><?= intval($info['rx']); ?></td>
		</tr><tr>
			<td><strong>Interface:</strong></td>
			<td colspan="2"><?= (isset($info['remote']))?($info['remote']['hostname']):('?'); ?></td>
		</tr>
<?php	/// ----- Show connection history ----- ///
	}	if(isset($_POST['ajax']) && isset($_POST['id'])) {
		require("../config.php");
?>		<div>
			<table border="0" width="100%">
				<tbody>
					<tr>
						<td style="font: normal 18px default;">Detalhes</td>
						<td style="text-align: right;">
							<button name="card" value="t" type="button"
								onclick="reset_showD(<?=$_POST['id'];?>)">Dados</button>
							&nbsp;&nbsp;
							<button name="supp" value="t" type="button"
								onclick="window.open('./?p=11&amp;id='+ m('edid').value, '_newtab');">Suporte</button>
							&nbsp;&nbsp;
							<button name="edit" value="t" type="button"
								onclick="window.location='./?p=6&amp;id='+ m('edid').value +'&amp;t=1';">Editar</button>
							&nbsp;&nbsp;
							<button name="x" value="x" type="button"
								onclick="m('deta').parentNode.style.display='none';">×</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="edid" id="edid" value="<?=$_POST['id'];?>" />
		<hr>
<?php
		$res = pg_query(CONN, "SELECT usnm FROM at_userdata WHERE id = $_POST[id]");
		if(is_resource($res)) if(pg_num_rows($res)==1) $atud = pg_fetch_array($res);
		if(!isset($atud)) die("Não foi possível encontrar o usuário!");
		$qry = "SELECT acctstarttime, acctstoptime, acctinputoctets, acctoutputoctets, framedipaddress";
		$qry.= " FROM radacct WHERE username = '$atud[usnm]' ORDER BY acctstarttime";
		if(!$res = pg_query(CONN, $qry)) die("Não foi possível encontrar o histórico!");
		echo "\t\t<div align=\"center\"><strong>$atud[usnm]</strong></div>\n";
		echo "\t\t<div class=\"dubm\">\n";
		for($i=0; $i<pg_num_rows($res); $i++) {
			$hda = pg_fetch_array($res, $i);
			$idc = strtotime($hda['acctstarttime']);
			if(isset($fdc)) {
				$odi = intval(($idc-$fdc)/3600);
				if($odi) {
					$ttl = "OFF-LINE / $odi".'hs';
					echo "\t\t\t<div title=\"$ttl\" class=\"dub\" style=\"height: 16px;\">\n\t\t\t\t";
					for($j=0; $j<=$odi; $j++) echo "&#160;";
					echo "\n\t\t\t\t<div style=\"border-color: black; height: 6px;\"> </div>\n\t\t\t</div>\n";
				}
			} $fdc = (strlen($hda['acctstoptime'])<10) ? (time()) : (strtotime($hda['acctstoptime']));
			$edi = intval(($fdc-$idc)/3600);
			$uso = ($hda['acctinputoctets'] && $edi) ? (intval($hda['acctinputoctets']/(1048576*$edi))) : (1);
			$dso = ($hda['acctoutputoctets'] && $edi) ? (intval($hda['acctoutputoctets']/(1048576*$edi))) : (6);
			if($uso>300) $uso = 300;
			if($dso>310) $dso = 310;
			$ttl = "IP $hda[framedipaddress] / $edi".'hs';
			echo "\t\t\t<div title=\"$ttl\" class=\"dub\" style=\"height: ".($dso+10)."px;\">\n\t\t\t\t";
			for($j=0; $j<=$edi; $j++) echo "&#160;";
			echo "\n\t\t\t\t<div style=\"border-color: red; height: ".$uso."px;\"> </div>";
			echo "\n\t\t\t\t<div style=\"border-color: blue; height: ".$dso."px;\"> </div>";
			echo "\n\t\t\t</div>\n";
		} echo "\t\t</div>\n";
		/// ----- Show connection attempts ----- ///
	}	if(isset($_POST['ajax']) && isset($_POST['logc'])) {
		require("../config.php");

		$tmin = 30; // minutos de últimas tentativas de login rejeitadas !
		$qry = "SELECT username, \"pass\", authdate FROM radpostauth WHERE";
		$qry.= " CURRENT_TIMESTAMP < ( authdate + interval '$tmin minutes' ) AND";
		$qry.= " \"reply\" = 'Access-Reject'";
		$qry.= " ORDER BY authdate DESC LIMIT 3";
// ----- Starting query loop ----- //
		if($rsl = pg_query(CONN, $qry)) {
			for($i=0; $i<pg_num_rows($rsl); $i++) {
				$arr = pg_fetch_array($rsl, $i);
// ----- Display columns ----- //
				if(!$i) {
					$hea = "display: table-cell; background-color: #164D7D; color: #FFF; font-weight: ";
					$hea.= "bolder; border-style: solid; border-color: #333; padding: 2px; font-size: 13px;";
					echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; $hea\">Usuário</div>";
					echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; $hea\">Senha</div>";
					echo "\n\t<div style=\"width: 80px; border-width: 0 0 1px 0; $hea\">Hora</div><br />";
				}	$bdy = "display: table-cell; border-style: solid; border-color: #888; padding: 2px;";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; $bdy\">\n";
				echo "\t\t". $arr['username']."</div>";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; $bdy\">\n";
				echo "\t\t". $arr['pass']."</div>";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 80px; border-width: 0 0 1px 0; $bdy\">\n";
				$authdate = explode(' ', $arr['authdate']);
				$hour = explode('.', $authdate[1]);
				echo "\t\t". $hour[0]."</div><br />";
			}	if(!$i) echo "Nenhuma tentativa de login<br />registrada nos últimos $tmin minutos";
		}
		/// ----- Show connection actual MAC address ----- ///
	}	if(isset($_POST['ajax']) && isset($_POST['name'])) {
		require("../config.php");
	///// Get the Client MAC-Address /////
		$qry = "SELECT callingstationid FROM radacct WHERE username = '$_POST[name]' AND acctstoptime IS NULL";
		$res = pg_query(CONN, $qry);
		if(pg_num_rows($res)==1) {
			$tmp = pg_fetch_array($res);
			$mac = $tmp['callingstationid'];
		} else die("ERRO");
		print($mac);
	} ?>
