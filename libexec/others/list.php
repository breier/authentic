<?php
	if(isset($_POST['ajax']) && isset($_POST['mode']) && isset($_POST['id'])) {
		require("../config.php");
		include_once(I."/php/history.php");

		$mode = $_POST['mode'];
		$arts = explode('_', $_POST['id']);
		$tabl = 'at_equip';
		$arsr = array("id" => $arts[1]);

		if($mode=='macro') {
			if($resr = pg_select(CONN, $tabl, $arsr)) {
				$wtoa = str_replace('..', '.', A);
				require_once(I."/php/cfgssh.php");
				$cmnd = '/interface wireless registration-table print terse stats';
				$outa = cfgssh($resr[0]['ipad'], $resr[0]['usnm'], $resr[0]['pass'], $cmnd, $resr[0]['port'], 'TUDO');
				$mtrx_outa = explode("\n", $outa);
				$scnd_outa = "";
				foreach($mtrx_outa as $lines) {
					if(strstr($lines, 'interface')) $scnd_outa.= "\n$lines";
					else $scnd_outa.= $lines;
				}	$outa = $scnd_outa;
				$name = $resr[0]['deta'];
				echo "<h3>Torre: <a href=\"javascript:void(null);\" title=\"Atualizar\" ";
				echo "onclick=\"openIn(this.parentNode.parentNode.id, '$wtoa/list.php', ";
				echo "'ajax=1&mode=macro&id=$_POST[id]', 1); m('eexp').parentNode.previousSibling";
				echo ".innerHTML='(aguarde o preenchimento)';\">$name</a></h3>\n";
				$mtrx = explode("\n", $outa);
				$outa = array();
				$ttls = array();
				for($i=0; $i<sizeof($mtrx); $i++) {
					$item = explode(' ', $mtrx[$i]);
					$test = (isset($item[1]))?(strtolower($item[1])):('router');
					if($test=='associated') {
						$outa['wlan0'][] = strtoupper($item[0]);
						$temp_towr = floatval($item[3]) .' - '. floatval($item[4]) .'/';
						$ttls['wlan0'][] = $temp_towr . floatval($item[5]);
					} else {
						if(sizeof($item) < 4) continue;
						$pos1 = strpos($mtrx[$i], 'interface=') + 10;
						$pos2 = strpos($mtrx[$i], 'mac-address=', $pos1) - 1;
						$ifce = substr($mtrx[$i], $pos1, ($pos2 - $pos1));
						if(stristr($mtrx[$i], 'radio-name=')) { 
							$posT = strpos($mtrx[$i], 'radio-name=', $pos1) - 1;
							if($posT < $pos2) $ifce = substr($mtrx[$i], $pos1, ($posT - $pos1));
						}	$pos3 = strpos($mtrx[$i], '=', $pos2) + 1;
						$pos4 = strpos($mtrx[$i], ' ', $pos3);
						$maca = substr($mtrx[$i], $pos3, ($pos4 - $pos3));
						$outa[$ifce][] = strtoupper($maca);
						$pos1 = strpos($mtrx[$i], 'rx-rate="') + 9;
						$pos2 = strpos($mtrx[$i], '"', $pos1);
						$txrt = substr($mtrx[$i], $pos1, ($pos2 - $pos1));
						$pos3 = strpos($mtrx[$i], 'tx-rate="') + 9;
						$pos4 = strpos($mtrx[$i], '"', $pos3);
						$rxrt = substr($mtrx[$i], $pos3, ($pos4 - $pos3));
						$pos1 = strpos($mtrx[$i], 'signal-to-noise=') + 16;
						$pos2 = strpos($mtrx[$i], ' ', $pos1);
						$sntn = substr($mtrx[$i], $pos1, ($pos2 - $pos1));
						$ttls[$ifce][] = floatval($sntn) .' - '. floatval($txrt) .'/'. floatval($rxrt);
						unset($pos1, $pos2, $pos3, $pos4, $ifce, $maca);
					}	unset($item, $test);
				}	foreach($outa as $wlan => $clnt) {
					echo "<div style=\"display: table-cell; border: solid 1px #555; padding: 5px;\">\n";
					echo "\t<strong>$wlan</strong>\n\t<hr />\n";
					echo "\t<table border=\"0\" cellspacing=\"0\" style=\"text-align: left;\">\n";
					echo "\t\t<tr>\n\t\t\t<th style=\"border-right: dotted 1px #555; border-bottom:";
					echo " dotted 1px #555;\">Nome</th>\n\t\t\t<th style=\"border-bottom: dotted 1px";
					echo " #555; font-size: 9px;\">DBm - RX/TX</th>\n\t\t</tr>\n";
					for($i=0; $i<sizeof($clnt); $i++) {
						$quer = "SELECT a.data FROM at_userdata a, radcheck b WHERE a.usnm = b.username";
						$quer.= " AND b.attribute = 'Calling-Station-Id' AND b.value = '$clnt[$i]'";
						if($rslt = pg_query(CONN, $quer)) {
							if(pg_num_rows($rslt)>0) {
								$ctdt = pg_fetch_array($rslt);
								$data = unserialize($ctdt['data']);
								$extr = (pg_num_rows($rslt)>1) ? ('(+'. (pg_num_rows($rslt)-1) .')') : (FALSE);
								$extr = ($extr) ? ("<strong>$extr</strong>") : ('');
								$name_show = "<a href=\"./?p=1&f=$clnt[$i]#0\" title=\"Visualizar";
								$name_show.= ' Cadastro">'. ucfirst(strtolower($data['name'])) ." $extr</a>";
							}	else $name_show = "$clnt[$i] <strong>(*)</strong>";
							echo "\t\t<tr>\n\t\t\t<td style=\"border-right: dotted 1px #555;\">";
							echo "$name_show</td>\n\t\t\t<td style=\"padding-left: 4px;\">";
							echo $ttls[$wlan][$i] ."</td>\n\t\t</tr>\n";
						}
					}	echo "\t</table>\n</div>\n";
				}	history((isset($_POST['whos']))?($_POST['whos']):("DESCONHECIDO"), 10, 6, "torre=$name");
			}
		}	elseif($mode=='list') {
			include(I."/php/ping.php");
			if($resr = pg_select(CONN, $tabl, $arsr))
				echo ($rslt = ping($resr[0]['ipad'])) ? ($rslt) : ("err");
		}	else echo "err";
	}
?>