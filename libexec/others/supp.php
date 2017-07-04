<?php
	if(isset($_POST['ajax']) && isset($_POST['type'])) {
		require("../config.php");
		include_once(I."/php/history.php");
		$wto = str_replace('..', '.', A);

		if(isset($_POST['id']) && isset($_POST['w'])) {
			if($_POST['id']==0) {
// ----- Looking for User ----- //
				$wtp = 'ajax=1&q=\' + this.value + \'&w=' . $_POST['w'] . '&t=' . $_POST['type'];
				echo "<br />\nLocalizar Usuário: <input type=\"text\" name=\"lu\" size=\"20\"";
				echo " onkeyup=\"openIn('lu', '$wto/find.php', '$wtp', 1);\" />";
				echo "<br />\n<br />\n<div style=\"display: inline;\" id=\"lu\"></div>";
			} else {
				$t = $_POST['type'];
				if($t=='ec' || $t=='lc' || $t=='am' || $t=='um') {
					$pta = str_replace('..', '.', P);
				// ----- Getting Tech Data ----- //
					if($toa = pg_select(CONN, 'radusergroup', array("username" => $_POST['w']))) {
						$grou = $toa[0]['groupname'];
					}	else $grou = 'tech';
					if($tid = pg_select(CONN, 'at_userdata', array("usnm" => $_POST['w']))) {
						$whos = $tid[0]['id'];
					}	else $whos = 0;
					unset($toa, $tid);
				// ----- Getting User Data ----- //
					$ats = array("id" => FALSE);
					$qry = "SELECT id, usnm, phon, data, conn FROM at_userdata WHERE id = $_POST[id]";
					if($rsl = pg_query(CONN, $qry)) {
						$arr = (pg_num_rows($rsl)==1) ? (pg_fetch_array($rsl)) : (array("id" => FALSE));
						if($arr['id']) {
							for($l=0; $l<sizeof($arr); $l++) unset($arr[$l]);
							foreach($arr as $ftc => $ftv) if($ftc!='data' && $ftc!='conn') $ats[$ftc] = $ftv;
							$ats = array_merge($ats, unserialize($arr['conn']), unserialize($arr['data']));
						}
					}	if($ats['id'] && $t!='lc') {
					// ## Getting MAC Address ## //
						$mac = '';
						if(strlen($mac)<8 && $ats['usnm']) {
							$qtu = "SELECT \"value\" FROM radcheck WHERE username = '$ats[usnm]'";
							$qtu.= " AND attribute = 'Calling-Station-Id'";
							if($qtr = pg_query(CONN, $qtu)) {
								$tmp = (pg_num_rows($qtr)==1) ? (pg_fetch_array($qtr)) : (array('value' => ''));
								$mac = strtoupper($tmp['value']);
							}
						} $ats['maca'] = (strlen($mac)<8) ? (FALSE) : ($mac);
///////////////////////////////////////////////////////////////////////////////////////
					}	if($ats['id'] && $t=='ec') {
					// ## Getting IP Address ## //
						$ipa = '';
						if($ats['usnm']) {
							$qer = "SELECT framedipaddress FROM radacct WHERE callingstationid = '$ats[maca]' AND acctstoptime IS NULL";
							$acr = pg_query(CONN, $qer);
							if(pg_num_rows($acr)==1) {
								$acc = pg_fetch_array($acr);
								$ipa = $acc['framedipaddress'];
							}
						} $ats['ip_a'] = (strlen($ipa)<6) ? (FALSE) : ($ipa);
						history($_POST['w'], 10, 1, "client_id=$ats[id]; mac=$ats[maca];");
						include(I."/php/makeBar.php"); ?>
<h3 style="color: #164D7D;">ENLACE CLIENTE</h3>
<div style="display: inline; border-spacing: 15px; position: relative; top: -20px;">
	<div style="display: table-cell; border: solid 1px #888; border-radius: 5px; padding: 5px;">
		<span style="font-weight: bold;">Enlace de Cadastro</span>
		<br /><div style="display: block; border: none; width: 170px; height: 16px;"> </div>
		<table border="0" cellspacing="5px">
			<tr>
				<td style="text-align: right;">Sinal:</td>
				<td style="text-align: left;">
					<?= makeBar(14, 26, $ats['sign'], 80); ?>
				</td>
				<td style="text-align: left;"><?= $ats['sign']; ?></td>
			</tr><tr>
				<td style="text-align: right;">TX Mod:</td>
				<td style="text-align: left;">
					<?= makeBar(11, 54, $ats['txrt'], 80); ?>
				</td>
				<td style="text-align: left;"><?= $ats['txrt']; ?></td>
			</tr><tr>
				<td style="text-align: right;">RX Mod:</td>
				<td style="text-align: left;">
					<?= makeBar(11, 54, $ats['rxrt'], 80); ?>
				</td>
				<td style="text-align: left;"><?= $ats['rxrt']; ?></td>
			</tr>
		</table>
	</div>
	<div style="display: table-cell; border: solid 1px #888; border-radius: 5px; padding: 5px;">
		<span style="font-weight: bold;">Enlace Atual</span>
		<br /><div style="display: block; border: none; width: 170px; height: 16px;"> </div>
		<input type="hidden" id="ec_mac" name="ec_mac" value="<?= $ats['maca']; ?>" />
		<input type="hidden" id="ec_ipa" name="ec_ipa" value="<?= $ats['ip_a']; ?>" />
		<table id="ec" border="0" cellspacing="5px" width="100%">
			<tr>
				<td>
					<img src="<?= $pta.'/ajax-loader.gif'; ?>" border="0" alt="..."
						style="background-color: #164E7E; padding: 3px; border-radius: 5px;" />
				</td>
			</tr>
		</table>
	</div>
</div><?php
///////////////////////////////////////////////////////////////////////////////////////
					}	if($ats['id'] && $t=='lc') {
						echo "<h3 style=\"color: #164D7D;\">LOGIN CLIENTE</h3>\n";
						history($_POST['w'], 10, 2, "id=$ats[id]; user=$ats[usnm];");
						// ## Getting Cleartext-Password ## //
						if($ats['usnm']) {
							$qtu = "SELECT \"value\" FROM radcheck WHERE username = '$ats[usnm]'";
							$qtu.= " AND attribute = 'Cleartext-Password'";
							if($qtr = pg_query(CONN, $qtu)) {
								$tmp = (pg_num_rows($qtr)==1) ? (pg_fetch_array($qtr)) : (array('value' => ''));
								$upa = $tmp['value'];
							} else $upa = '';
						// ## Displaying Scan ## //
							echo "<br />\n<div>Usuário: <strong>$ats[usnm]</strong>&#160;&#160;&#160;&#160;";
							echo "&#160;&#160;&#160;&#160;&#160;Senha: <strong>$upa</strong></div>";
							echo "<br />\n<div style=\"display: inline;\" id=\"lc\"><img src=\"$pta";
							echo "/ajax-loader.gif\" border=\"0\" alt=\"...\" style=\"background-color:";
							echo " #164E7E; padding: 3px; border-radius: 5px;\" /></div>";
						}	else echo "<br />\n<span>Cliente Somente Telefone</span>";
///////////////////////////////////////////////////////////////////////////////////////
					}	if($ats['id'] && $t=='am') {
						$full = ($grou=='full') ? (TRUE) : (FALSE);
						history($_POST['w'], 10, 3, "client_id=$ats[id]; mac=$ats[maca];");
						$macsty = "padding: 5px; margin-top: 5px; border: solid 1px #333;";
						$macsty.= " width: 200px; background-color: #EAEAEA; font: bolder";
						$macsty.= " 18px arial,helvetica,'sans-serif';"; ?>
<h3 style="color: #164D7D;">ATUALIZAR MAC</h3>
<input type="hidden" id="am_name" name="am_name" value="<?= $_POST['w']; ?>" />
<div style="display: inline; border-spacing: 15px;">
	<div style="display: table-cell; border: solid 1px #888; border-radius: 5px; padding: 5px;">
		<span style="font-weight: bold;">MAC de Cadastro</span>
		<br />
		<div style="<?= $macsty; ?> color: #023;"><?= $ats['maca']; ?></div>
	</div>
	<div style="display: table-cell; border: solid 1px #888; border-radius: 5px; padding: 5px;">
		<span style="font-weight: bold;">MAC desta Conexão</span>
		<br />
		<div id="am" style="<?= $macsty; ?> color: #042;">...</div>
<?php	if($full) { ?>
		<div style="position: absolute; width: 200px; text-align: left;">
			<span style="position: relative; bottom: 56px; cursor: pointer;" title="Inserir Manualmente">
				<span style="color: #060; font: bolder 16px default;"
						onclick="m('manu').style.display = 'inline';">+</span>
				<span id="manu" style="display: none; background-color: #FFF; padding-left: 20px;">
					<input type="text" name="mc" style="width: 140px;"
							onkeyup="m('am').innerHTML = this.value.toUpperCase();" />
					<span onclick="m('manu').style.display = 'none';" title="Fechar"
							style="color: #000; font: bolder 16px default;">&times;</span>
				</span>
			</span>
		</div>
<?php	} ?>
	</div>
</div>
<br />
<button type="button" name="apply" value="apply" style="padding: 10px; font-size: 18px;"
		onclick="newMac('<?= $ats['id']; ?>', m('am').innerHTML); this.blur();">
	Aplicar Novo MAC
</button><?php
///////////////////////////////////////////////////////////////////////////////////////
					}	if($ats['id'] && $t=='um') {
						echo "<h3 style=\"color: #164D7D;\">USUÁRIOS POR MAC</h3>";
						$ers = FALSE;
						if(!$ers && $ats['usnm']) {
							$qtu = array("attribute" => 'Calling-Station-Id', "value" => "$ats[maca]");
							$qtr = pg_select(CONN, 'radcheck', $qtu);
							if(sizeof($qtr)>1) {
								$tmp = ($qtr[0]['username']==$ats['usnm']) ?
									($qtr[1]['username']) : ($qtr[0]['username']);
								$ers =  "Cliente $ats[usnm] já compartilhado com $tmp.";
							}
						}	if(!$ers) {
							$qtu = array("data" => "$ats[maca]");
							if($qtr = pg_select(CONN, 'at_settings', $qtu))
								if(isset($qtr[0]['seqn']))
									$ers =  "MAC do cliente $ats[name] já está liberado.";
						}	if(!$ers) {
							$wtpi = "ajax=1&mc=$ats[maca]&id=$_POST[w]&wi="; ?>
<div style="position: relative; top: -10px;"><?= $ats['maca']; ?></div>
<div align="center" style="display: inline; border-spacing: 10px;">
	<div style="display: table-cell; vertical-align: top;">
		<div style="text-align: center; width: 160px; line-height: 20px;">
			Técnico Responsável
			<select name="tech" id="tech" size="0" style="width: 140px;" onchange="this.blur();">
				<option label="DESCONHECIDO" value="0">DESCONHECIDO</option>
<?php				$qtu = "SELECT id, data FROM at_userdata WHERE type = 'tech'";
					if($qtr = pg_query(CONN, $qtu)) {
						for($i=0; $i<pg_num_rows($qtr); $i++) {
							$arr = pg_fetch_array($qtr, $i);
							$tmp = unserialize($arr['data']);
							echo "\t\t\t\t\t<option label=\"$tmp[name]\" value=\"$arr[id]\">";
							echo $tmp['name'] ."</option>\n";
						}
					}
?>			</select>
		</div>
	</div><div style="display: table-cell;">
		<button type="button" name="apply" value="apply" style="padding: 10px; font-size: 18px;"
				onclick="freeMac('<?= $wtpi; ?>', this);">Liberar MAC</button>
	</div>
	<br />
	<div id="msg"></div>
</div>
<?php					}	else {
							echo "<br />\n<span style=\"color: #F00;\">$ers</span>";
							history($_POST['w'], 10, 4, $ers);
						}
					}
				}
			}
///////////////////////////////////////////////////////////////////////////////////////
		}	if($_POST['type']=='et') {
			$pta = str_replace('..', '.', P); ?>
<h3 style="color: #164D7D;">ENLACES POR TORRE</h3>
<div style="display: absolute; text-align: right; height: 0;">
	<span style="color: #555; font-size: 10px; position: relative; right: 40px; top: -35px;">
	</span><span style="position: relative; right: 10px; top: -35px;">
		<img src="<?= $pta; ?>/expand.png" alt="&ETH;" title="Maximizar"
			style="cursor: pointer;" id="eexp" />
	</span>
</div>
<div style="max-height: 156px; overflow: auto;">
	<div id="et" align="center" style="display: inline; border-spacing: 5px;">
<?php		include_once(I."/php/getConfig.php");
			$qry = " SELECT id, deta, usnm, pass, ipad, maca FROM at_equip";
			$qry.= " WHERE plac <> 'data-center' ORDER BY id";
			if($rpl = pg_query(CONN, $qry)) {
				for($i=0, $j=1; $i<pg_num_rows($rpl); $i++, $j++) {
					$arr = pg_fetch_array($rpl, $i);
					if($j>8) {
						echo "\t\t<br />\n";
						$j = 1;
					} $sty = "display: table-cell; border: solid 1px #555; background-color: #DDD;";
					$sty.= " padding: 5px; font-family: monospace; color: #777; cursor: default;";
					echo "\t\t<div id=\"$arr[id]\" style=\"$sty\">$arr[deta]</div>\n";
				}
			}	echo "\t</div>\n"; ?>
	<div style="position: fixed; top: 0; left: 0; z-index: 500; height: 100%; width: 100%;
			background-color: rgba(0,0,0,0.4); display: none;" id="ee_fume">
		<div style="position: relative; top: 40px; left: 2%; background-color: #FFF;
						padding: 5px; border: solid 1px #555; border-radius: 5px;
						max-width: 95%; min-height: 300px; overflow: auto;">
			<h2>ENLACES POR TORRE</h2>
			<button onclick="m('ee_fume').style.display = 'none';" title="Fechar"
					style="position: absolute; right: 10px; top: 20px;">&times;</button>
			<div style="overflow: auto; display: inline; border-spacing: 5px;" id="ee_span"></div>
		</div>
	</div>
</div><?php
///////////////////////////////////////////////////////////////////////////////////////
		}	if($_POST['type']=='ss' && isset($_POST['w'])) {
			$arg = array('username' => $_POST['w']);
			if($rsg = pg_select(CONN, 'radusergroup', $arg)) $aot = $rsg[0]['groupname'];
			else $aot = 'tech';
?>
<h3 style="color: #164D7D;">STATUS DOS SERVIÇOS</h3>
<div align="center">
	<div id="ss" style="display: inline; border-spacing: 5px;">
		<div style="display: table-cell; padding-right: 20px; text-align: left;">
<?php		if($aot=='admn' || $aot=='full') { ?>
		<a href="javascript:void(0);" title="Vizualisar Detalhes"
			onclick="soh('dt_net', this);" style="color: #555;" >
<?php		} ?>
			Serviços Internet <span id="ss_net">...</span><br />
<?php		if($aot=='admn' || $aot=='full') { ?>
		</a>
			<div id="dt_net" style="display: none;">
				<li label="www" style="color: #555;">Link Internet</li>
				<li label="gtw" style="color: #555;">Gateway Principal</li>
				<li label="prx" style="color: #555;">Serviço Proxy</li>
				<li label="dns" style="color: #555;">Serviço DNS</li>
				<li label="rad" style="color: #555;">Serviço RADIUS</li>
				<li label="sql" style="color: #555;">Banco de Dados</li>
			</div>
<?php		} ?>
		</div>
		<div style="display: table-cell; padding-left: 20px; text-align: left;">
<?php		if($aot=='admn' || $aot=='full') { ?>
		<a href="javascript:void(0);" title="Vizualisar Detalhes"
			onclick="soh('dt_tel', this);" style="color: #555;" >
<?php		} ?>
			Serviços Telefonia <span id="ss_tel">...</span><br />
<?php		if($aot=='admn' || $aot=='full') { ?>
		</a>
			<div id="dt_tel" style="display: none;">
				<li label="ddr" style="color: #555;">DDR Operadora</li>
				<li label="com" style="color: #555;">Serviço Comutadora</li>
				<li label="pbx" style="color: #555;">Serviço PABX</li>
				<li label="sql" style="color: #555;">Banco de Dados</li>
			</div>
<?php		} ?>
		</div>
	</div>
</div>
<?php	}
///////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////
	}	if(isset($_POST['ajax']) && isset($_POST['maca']) && isset($_POST['usid'])) {
		require("../config.php");
		include_once(I."/php/history.php");

		// ----- Getting User Data ----- //
		$ats = array("id" => FALSE);
		$qry = "SELECT id, usnm, phon FROM at_userdata WHERE id = $_POST[usid]";
		if($rsl = pg_query(CONN, $qry)) {
			if(pg_num_rows($rsl)==1) $ats = pg_fetch_array($rsl);
		}	if($ats['id']) {
		// ## Updating Calling-Station-Id ## //
			if($ats['usnm']) {
				$qtu = "UPDATE radcheck SET \"value\" = '". strtoupper($_POST['maca']);
				$qtu.= "' WHERE username = '$ats[usnm]' AND attribute = 'Calling-Station-Id'";
				if($qtr = pg_query(CONN, $qtu)) {
					if(pg_affected_rows($qtr)!=1) die("ERRO");
				}	else die("ERRO");
			}
		// ## Updating formmac ## //
			history("ACIMA", 10, 3, "client_id=$ats[id]; mac_atual=$_POST[maca];");
			die("SUCESSO");
		}	else die("ERRO");
///////////////////////////////////////////////////////////////////////////////////////
	}	if(isset($_POST['ajax']) && isset($_POST['mc']) && isset($_POST['id']) && isset($_POST['wi'])) {
		require("../config.php");
		include_once(I."/php/history.php");

		// ----- Getting Next settings ID ----- //
		$ers = "Erro no Banco de Dados ao liberar MAC do Cliente.";
		$qtu = "SELECT id + 1 AS id FROM at_settings ORDER BY id DESC LIMIT 1";
		if($qtr = pg_query(CONN, $qtu)) {
			$tmp = (pg_num_rows($qtr)==1) ? (pg_fetch_array($qtr)) : (array('id' => 1));
			$ida = $tmp['id'];
		}	else $ida = 1;
		// ----- Getting Admin ID ----- //
		$qtu = "SELECT id FROM at_userdata WHERE usnm = '$_POST[id]'";
		if($qtr = pg_query(CONN, $qtu)) {
			$tmp = (pg_num_rows($qtr)==1) ? (pg_fetch_array($qtr)) : (array('id' => 0));
			$aid = $tmp['id'];
		}	else $aid = 0;
		// ## Insertig MAC to at_settings ## //
		$qtu = "INSERT INTO at_settings VALUES ( $ida, CURRENT_TIMESTAMP,";
		$qtu.= " 'maca', '". strtoupper($_POST['mc']) ."', '$_POST[wi]', $aid )";
		if($qtr = pg_query(CONN, $qtu)) {
			if(pg_affected_rows($qtr)==1) {
				echo "<br />\n<span style=\"font-size: 16px; color: #023;\">";
				echo "MAC do Cliente liberado com sucesso!</span>";
				$ers = FALSE;
			}
		}	if($ers) echo "<br />\n<span style=\"color: #F00;\">$ers</span>";
		history($_POST['id'], 10, 5, "maca=$_POST[mc]; msg=$ers");
///////////////////////////////////////////////////////////////////////////////////////
	} ?>
