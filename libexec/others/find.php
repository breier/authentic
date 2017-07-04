<?php
	if(isset($_POST['ajax']) && isset($_POST['q']) && isset($_POST['w']) && isset($_POST['t'])) {
		require("../config.php");
		require_once(I."/php/getConfig.php");

		if(strlen($_POST['q'])==0) die();
// ----- Starting Query Limiting results by the search ----- //
		$cmp = strtolower($_POST['q']);
		if(strlen($cmp)>2) $cmp = '%'.$cmp;
		$flr = "AND lower(data) SIMILAR TO '%s:4:\"name\";s:(_|__):\"$cmp"."%'";
		$ord = "data";
		$qry = "SELECT * FROM at_userdata WHERE type NOT IN ( 'full', 'admn', 'tech' )";
// ----- Query Limiting results ----- //
		$limit = ( intval(getConfig('cnfg', 'rwpp')) / 2 );
		$qry = "$qry $flr ORDER BY $ord LIMIT $limit";
// ----- Starting query loop ----- //
		if($rsl = pg_query(CONN, $qry)) {
			for($i=0; $i<pg_num_rows($rsl); $i++) {
				$arr = pg_fetch_array($rsl, $i);
				for($l=0; $l<sizeof($arr); $l++) unset($arr[$l]);
				$ats = array();
				foreach($arr as $ftc => $ftv) if($ftc!='data') $ats[$ftc] = $ftv;
				$ats = array_merge($ats, unserialize($arr['data']));
// ----- Display columns ----- //
				if(!$i) {
					$hea = "display: table-cell; background-color: #164D7D; color: #FFF; font-weight:";
					$hea.= " bolder; border-style: solid; border-color: #333; padding: 2px; font-size: 13px;";
					echo "\n\t<div style=\"width: 220px; border-width: 0 2px 1px 0; $hea\">Nome</div>";
					echo "\n\t<div style=\"width: 80px; border-width: 0 2px 1px 0; $hea\">Plano</div>";
					echo "\n\t<div style=\"width: 100px; border-width: 0 0 1px 0; $hea\">CPF</div><br />";
				}	$bdy = "display: table-cell; border-style: solid; border-color: #888; padding: 2px;";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 220px; border-width: 0 2px 1px 0; cursor: pointer; $bdy\"";
				echo " title=\"Selecionar\"\n\t\t\tonclick=\"window.location='./?p=11&id=$ats[id]";
				echo "#$_POST[t]'\" >\n\t\t" . ucwords(strtolower($ats['name'])) ."</div>";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 80px; border-width: 0 2px 1px 0; cursor: pointer; $bdy\"";
				echo " title=\"Selecionar\"\n\t\t\tonclick=\"window.location='./?p=11&id=$ats[id]";
				echo "#$_POST[t]'\" >\n\t\t$ats[type]</div>";
			/////////////////////////////////////////////////
				echo "\n\t<div style=\"width: 100px; border-width: 0 0 1px 0; cursor: pointer; $bdy\"";
				echo " title=\"Selecionar\"\n\t\t\tonclick=\"window.location='./?p=11&id=$ats[id]";
				echo "#$_POST[t]'\" >\n\t\t$ats[cpfe]</div><br />";
			}
		} else echo $qry;
	}	if(isset($_POST['ajax']) && isset($_POST['tool'])) {
		require("../config.php");

		if($_POST['tool']=='trsh') {
			$tsh = array();
			$qmt = "SELECT * FROM radusergroup WHERE groupname <> 'full' ORDER BY username";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$nna = 0;
					//////////////////////////////////
					$qst = "SELECT id FROM radreply WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
						$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $std = TRUE;
					else $std = FALSE;
					if(!($snr==1 && $std) && $snr!=0) $tsh[$mtd['username']]['radusergroup'] = ++$nna;
					//////////////////////////////////
					$qst = "SELECT id FROM radcheck WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
						$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $bnr = 2;
					else $bnr = 1;
					if($snr!=$bnr) $tsh[$mtd['username']]['radusergroup'] = ++$nna;
					//////////////////////////////////
					$qst = "SELECT id FROM at_userdata WHERE usnm = '$mtd[username]' ORDER BY usnm";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($snr!=1) $tsh[$mtd['username']]['radusergroup'] = ++$nna;
				}
			} $qmt = "SELECT DISTINCT username FROM radreply WHERE username NOT IN";
			$qmt.= " ( SELECT username FROM radusergroup WHERE groupname = 'full' ) ORDER BY username";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$nna = 0;
					//////////////////////////////////
					$qst = "SELECT * FROM radusergroup WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($snr!=1) $tsh[$mtd['username']]['radreply'] = ++$nna;
					//////////////////////////////////
					if(!$nna) {
						$tar = pg_fetch_array($rst);
						$mtd['groupname'] = $tar['groupname'];
					} else $mtd['groupname'] = 'stnd';
					//////////////////////////////////
					$qst = "SELECT id FROM radcheck WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
						$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $bnr = 2;
					else $bnr = 1;
					if($snr!=$bnr) $tsh[$mtd['username']]['radreply'] = ++$nna;
					//////////////////////////////////
					$qst = "SELECT id FROM at_userdata WHERE usnm = '$mtd[username]' ORDER BY usnm";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($snr!=1) $tsh[$mtd['username']]['radreply'] = ++$nna;
				}
			} $qmt = "SELECT DISTINCT username FROM radcheck WHERE username NOT IN";
			$qmt.= " ( SELECT username FROM radusergroup WHERE groupname = 'full' ) ORDER BY username";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$nna = 0;
					//////////////////////////////////
					$qst = "SELECT * FROM radusergroup WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($snr!=1) $tsh[$mtd['username']]['radcheck'] = ++$nna;
					//////////////////////////////////
					if(!$nna) {
						$tar = pg_fetch_array($rst);
						$mtd['groupname'] = $tar['groupname'];
					} else $mtd['groupname'] = 'stnd';
					//////////////////////////////////
					$qst = "SELECT id FROM radreply WHERE username = '$mtd[username]' ORDER BY username";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
						$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $std = TRUE;
					else $std = FALSE;
					if(!($snr==1 && $std) && $snr!=0) $tsh[$mtd['username']]['radcheck'] = ++$nna;
					//////////////////////////////////
					$qst = "SELECT id FROM at_userdata WHERE usnm = '$mtd[username]' ORDER BY usnm";
					$rst = pg_query(CONN, $qst);
					$snr = ($rst) ? (pg_num_rows($rst)) : (0);
					if($snr!=1) $tsh[$mtd['username']]['radcheck'] = ++$nna;
				}
			} $qmt = "SELECT usnm, phon FROM at_userdata ORDER BY usnm";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$nna = 0;
					if($mtd['usnm']) {
						////////////////////////////////////////////
						$qst = "SELECT * FROM radusergroup WHERE username = '$mtd[usnm]' ORDER BY username";
						$rst = pg_query(CONN, $qst);
						$snr = ($rst) ? (pg_num_rows($rst)) : (0);
						if($snr!=1) $tsh[$mtd['usnm']]['at_userdata'] = ++$nna;
						//////////////////////////////////
						if(!$nna) {
							$tar = pg_fetch_array($rst);
							$mtd['groupname'] = $tar['groupname'];
						} else $mtd['groupname'] = 'stnd';
						//////////////////////////////////
						$qst = "SELECT id FROM radreply WHERE username = '$mtd[usnm]' ORDER BY username";
						$rst = pg_query(CONN, $qst);
						$snr = ($rst) ? (pg_num_rows($rst)) : (0);
						if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
							$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $std = TRUE;
						else $std = FALSE;
						if(!($snr==1 && $std) && $snr!=0) $tsh[$mtd['usnm']]['at_userdata'] = ++$nna;
						//////////////////////////////////////////////////////////
						$qst = "SELECT id FROM radcheck WHERE username = '$mtd[usnm]' ORDER BY username";
						$rst = pg_query(CONN, $qst);
						$snr = ($rst) ? (pg_num_rows($rst)) : (0);
						if($mtd['groupname']!='free' && $mtd['groupname']!='pblc' &&
						$mtd['groupname']!='tech' && $mtd['groupname']!='admn') $bnr = 2;
						else $bnr = 1;
						if($snr!=$bnr) $tsh[$mtd['usnm']]['at_userdata'] = ++$nna;
					}
				}
			} $i = 0;
			echo "\n\t<input type=\"hidden\" name=\"tool\" id=\"tool\" value=\"trsh\">";
			foreach($tsh as $usr => $tbs) {
			// ----- Display columns ----- //
				if(!$i) {
					$hea = "display: table-cell; background-color: #164D7D; color: #FFF;";
					$hea.= " font-weight: bolder; border-style: solid; border-color: #333;";
					$hea.= " padding: 2px; font-size: 13px;";
					echo "\n\t<div style=\"width: 20px; border-width: 0 2px 1px 0; $hea\">";
					echo "\n\t\t<input type=\"checkbox\" name=\"all\" value=\"all\" style=\"width: 13px;\"";
					echo " onchange=\"mark('bd', this); this.blur();\" title=\"Marcar Todos\" />\n\t</div>";
					echo "\n\t<div style=\"width: 180px; border-width: 0 2px 1px 0; ";
					echo $hea ."\">Usuário / Ramal</div>";
					echo "\n\t<div style=\"width: 140px; border-width: 0 2px 1px 0; $hea\">Tabela</div>";
					echo "\n\t<div style=\"width: 54px; border-width: 0 0 1px 0; $hea\">Erro</div><br />";
				}	$bdy = "display: table-cell; border-style: solid; border-color: #888; padding: 2px;";
				foreach($tbs as $tbl => $vlu) {
					switch($tbl) {
						case 'telefonia': $lbl = 'name'; break;
						case 'at_userdata': $lbl = 'usnm'; break;
						default: $lbl = 'username'; break;
					}	echo "\n\t<div style=\"width: 20px; border-width: 0 2px 1px 0; $bdy\">";
					echo "\n\t\t<input type=\"checkbox\" name=\"$usr\" value=\"$tbl\" label=\"$lbl\"";
					echo " style=\"width: 13px;\" onchange=\"this.blur();\" />\n\t</div>";
					echo "\n\t<div style=\"width: 180px; border-width: 0 2px 1px 0; $bdy\" >$usr</div>";
					echo "\n\t<div style=\"width: 140px; border-width: 0 2px 1px 0; $bdy\" >$tbl</div>";
					echo "\n\t<div style=\"width: 54px; border-width: 0 0 1px 0; $bdy\" >$vlu</div><br />";
					$i++;
				}
			}	if(!$i) echo "Nenhum resíduo encontrado!";
			echo "\n\t<input type=\"hidden\" name=\"trsh\" value=\"$i\" id=\"tt\" />";
		}	if($_POST['tool']=='dupl') {
			$dbl = array();
			$qmt = "SELECT username, COUNT(username) AS hmt FROM radusergroup";
			$qmt.= " GROUP BY username HAVING ( COUNT(username) > 1 )";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$dbl[$mtd['username']]['radusergroup']['username'] = $mtd['hmt'];
				}
			}	$qmt = "SELECT username, attribute, COUNT(attribute) AS hmt FROM radreply";
			$qmt.= " GROUP BY attribute, username HAVING ( COUNT(attribute) > 1 )";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$dbl[$mtd['username']]['radreply'][$mtd['attribute']] = $mtd['hmt'];
				}
			}	$qmt = "SELECT username, attribute, COUNT(attribute) AS hmt FROM radcheck";
			$qmt.= " GROUP BY attribute, username HAVING ( COUNT(attribute) > 1 )";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$dbl[$mtd['username']]['radcheck'][$mtd['attribute']] = $mtd['hmt'];
				}
			}	$qmt = "SELECT \"value\", COUNT(value) AS hmt FROM radcheck GROUP BY";
			$qmt.= " value, attribute HAVING ( COUNT(value) > 1 ) AND attribute = 'Framed-IP-Address'";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$dbl[$mtd['value']]['radcheck']['fipa'] = $mtd['hmt'];
				}
			}	$mts = array();
			$qmt = "SELECT \"value\", COUNT(value) AS hmt FROM radcheck GROUP BY";
			$qmt.= " value, attribute HAVING ( COUNT(value) > 1 ) AND attribute = 'Calling-Station-Id'";
			if($rmt = pg_query(CONN, $qmt)) {
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$dbl[$mtd['value']]['radcheck']['csid'] = $mtd['hmt'];
					$mts[$mtd['value']] = $mtd['hmt'];
				}
			} $qmt = "SELECT usnm, phon, data FROM at_userdata ORDER BY phon";
			if($rmt = pg_query(CONN, $qmt)) {
				$rud = array(); $ruc = array();
				for($j=0; $j<pg_num_rows($rmt); $j++) {
					$mtd = pg_fetch_array($rmt, $j);
					$tmp = unserialize($mtd['data']);
					if(isset($rud[$mtd['usnm']])) {
						$rud[$mtd['usnm']] = $rud[$mtd['usnm']] + 1;
						$ruc[$mtd['usnm']] = 'user';
					}	else $rud[$mtd['usnm']] = 1;
					if(isset($rud[$mtd['phon']])) {
						$rud[$mtd['phon']] = $rud[$mtd['phon']] + 1;
						$ruc[$mtd['phon']] = 'phon';
					}	$rud[$mtd['phon']] = 1;
					foreach($tmp as $tmc => $tmv) {
						if($tmc!='rgid' && $tmc!='cpfe') continue; 
						if(isset($rud[$tmv])) {
							$rud[$tmv] = $rud[$tmv] + 1;
							$ruc[$tmv] = $tmc;
						}	else $rud[$tmv] = 1;
					}
				}	foreach($rud as $rup => $rar)
					if($rar>1) $dbl[$rup]['at_userdata'][$ruc[$rup]] = $rar;
			}	$i = 0;
			echo "\n\t<input type=\"hidden\" name=\"tool\" id=\"tool\" value=\"dupl\">";
			foreach($dbl as $usr => $tbs) {
			// ----- Display columns ----- //
				if(!$i) {
					$hea = "display: table-cell; background-color: #164D7D; color: #FFF;";
					$hea.= " font-weight: bolder; border-style: solid; border-color: #333;";
					$hea.= " padding: 2px; font-size: 13px;";
					echo "\n\t<div style=\"width: 20px; border-width: 0 2px 1px 0; $hea\">";
					echo "\n\t\t<input type=\"checkbox\" name=\"all\" value=\"all\" style=\"width: 13px;\"";
					echo " onchange=\"mark('bd', this); this.blur();\" title=\"Marcar Todos\" />\n\t</div>";
					echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; ";
					echo $hea ."\">Dado Duplicado</div>";
					echo "\n\t<div style=\"width: 100px; border-width: 0 2px 1px 0; $hea\">Tabela</div>";
					echo "\n\t<div style=\"width: 80px; border-width: 0 2px 1px 0; $hea\">Campo</div>";
					echo "\n\t<div style=\"width: 28px; border-width: 0 0 1px 0; $hea\">N</div><br />";
				}	$bdy = "display: table-cell; border-style: solid; border-color: #888; padding: 2px;";
				foreach($tbs as $tbl => $vlu) {
					foreach($vlu as $cmp => $num) {
						echo "\n\t<div style=\"width: 20px; border-width: 0 2px 1px 0; $bdy\">";
						echo "\n\t\t<input type=\"checkbox\" name=\"$usr\" value=\"$tbl\" label=\"$cmp\"";
						echo " style=\"width: 13px;\" onchange=\"this.blur();\" />\n\t</div>";
						echo "\n\t<div style=\"width: 160px; border-width: 0 2px 1px 0; $bdy\" >$usr</div>";
						echo "\n\t<div style=\"width: 100px; border-width: 0 2px 1px 0; $bdy\" >$tbl</div>";
						echo "\n\t<div style=\"width: 80px; border-width: 0 2px 1px 0; $bdy\" >$cmp</div>";
						echo "\n\t<div style=\"width: 28px; border-width: 0 0 1px 0; $bdy\" >$num</div><br />";
						$i++;
					}
				}
			}	if(!$i) echo "Nenhuma duplicata encontrada!";
			echo "\n\t<input type=\"hidden\" name=\"dupl\" value=\"$i\" id=\"tt\" />";
		}
	}
?>
