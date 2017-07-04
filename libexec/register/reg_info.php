<?php
	if(isset($_POST['ajax'])) {
		require("../config.php");
		require("../login.php");

	// --- Check REMOTE_ADDR informattion
		if(!isset($_SERVER['REMOTE_ADDR']) && $_session->groupname == 'tech')
			$_msg->err("Could not acquire remote IP!");
		elseif(!isset($_SERVER['REMOTE_ADDR'])) die("SKIP_CHECK");
		else $ipra = $_SERVER['REMOTE_ADDR'];
	// --- Check actual RADIUS connection IP
		$_pgobj->query("SELECT * FROM at_fipacct WHERE username = '$_session->username'");
		if($_pgobj->rows == 1) {
			$ipfi = $_pgobj->result[0]['framedipaddress'];
			if($ipfi == NULL && $_session->groupname == 'tech')
				$_msg->err("The Session user is not currently authenticated on the network!");
			elseif($ipfi == NULL) die("SKIP_CHECK");
		} else {
			$ipfi = NULL;
			if($_session->groupname == 'tech')
				$_msg->err("The Session user is not currently authenticated on the network!");
			else die("SKIP_CHECK");
		}
		if($ipra != $ipfi && $_session->groupname == 'tech')
			$_msg->err("The Session user is not the same as the one authenticated on the network!");
		elseif($ipra != $ipfi) die("SKIP_CHECK");
	// --- Check for user/pass/method for current equipment
		require("$_path->php/sshc.php");
		include("$_path->php/record.php");
		if(isset($_POST['usnm']) && isset($_POST['pass']) && isset($_POST['srvc']) && isset($_POST['port']))
			$drac = array('usnm'=>$_POST['usnm'],'pass'=>$_POST['pass'],'srvc'=>$_POST['srvc'],'port'=>$_POST['port']);
		else {
			$qery = "SELECT callingstationid FROM radacct WHERE username = '$_session->username'";
			$qery.= " AND acctstoptime IS NULL ORDER BY acctstarttime LIMIT 1";
			$_pgobj->query($qery);
			$maca = $_pgobj->result[0]['callingstationid'];
			if(!isset($_settings->drac[substr($maca, 0, 8)])) {
			// --- Guessing Access Concentrator. Going deeper
				$qery = "SELECT aeq.brnd, aeq.ipad, aeq.usnm, aeq.pass, aeq.srvc, aeq.port";
				$qery.= " FROM at_equip aeq, radacct rac WHERE aeq.grup = rac.calledstationid";
				$qery.= " AND rac.username = '$_session->username' AND rac.acctstoptime IS NULL";
				$_pgobj->query($qery);
				for($i=0; $i<$_pgobj->rows; $i++) {
					$temp = $_pgobj->fetch_array();
					if($temp['srvc'] != 'ssh') $_msg->err("Service not yet supported!");
					$ssho = new sshc($temp['ipad'], $temp['usnm'], $temp['pass'], $temp['port']);
					if($ssho->error) $_msg->err($ssho->error);
					$pclr = FALSE;
					if(stristr($temp['brnd'], 'ubiquiti')) {
						$ssho->exec("brmacs");
						$tpar = json_decode($ssho->output);
						for($j=0; $j<count($tpar); $j++)
							if(stristr($tpar[$j]['hwaddr'], $maca)) $pclr = $tpar[$j];
						if($pclr) {
							if(strstr($pclr['port'], 'eth')) {
								$drac = $temp;
								break;
							}
						}
					} elseif(stristr($temp['brnd'], 'mikrotik')) {
						$ssho->exec(":put [/interface bridge host print as-value]");
						$tpar = explode('.id=', $ssho->output);
						for($j=0; $j<count($tpar); $j++)
						 	if(stristr($tpar[$j], $maca)) $pclr = $tpar[$j];
						if($pclr) {
							$tpps = strpos($pclr, 'on-interface=') + 13;
							$tpin = substr($pclr, $tpps, -1);
							if(strstr($tpin, 'eth')) {
								$drac = $temp;
								break;
							}
						}
					} else {
						$ssho->exec("brctl showmacs br0");
						$pclr = $ssho->search($maca);
						if($pclr) {
							$drac = $temp;
							break;
						}
					} $ssho->close();
				} if(!isset($drac)) {
					include("$_path->ajax/reg_drac.php");
					die();
				}
			} else $drac = unserialize($_settings->drac[substr($maca, 0, 8)]);
		} if(!isset($drac['ipad'])) $drac['ipad'] = $ipfi;
	// --- Go on and try to connect to remote equipment
		if($drac['srvc'] != 'ssh') $_msg->err("Service not yet supported!");
		if(!isset($ssho)) $ssho = new sshc($drac['ipad'], $drac['usnm'], $drac['pass'], $drac['port']);
		if($ssho->error) {
			$qery = "SELECT callingstationid FROM radacct WHERE username = '$_session->username'";
			$qery.= " AND acctstoptime IS NULL ORDER BY acctstarttime LIMIT 1";
			$_pgobj->query($qery);
			$maca = $_pgobj->result[0]['callingstationid'];
			include("$_path->ajax/reg_drac.php");
			echo '<div class="col-md-12" style="margin-top: 16px;">';
			$_msg->err($ssho->error);
		} // if everything goes fine, print("PASS_CHECK");
/*
			///// Get the Client Info /////
			$outa = cfgssh($ipa, $drac[3], $drac[4], 'wstalist', $drac[2], 'TUDO');
			$info = json_decode($outa);
			///// SELECT Previous Signal Information /////
				$arcm = array('name' => $unm, 'stat' => TRUE);
				if($arot = pg_select(CONN, 'at_sess', $arcm)) {
					$ostn = intval($arot[0]['sign']);
					$otxr = intval($arot[0]['txrt']);
					$orxr = intval($arot[0]['rxrt']);
				} else {
					$ostn = 0;
					$otxr = 0;
					$orxr = 0;
				}
			///// UPDATE Client Information /////
				$qry = "UPDATE at_sess SET ip_a = '$ipa', maca = '$mac',";
				$qry.= " sign = ". ($info['signal']-$info['noisefloor']+$ostn) .",";
				$qry.= " txrt = ". ($info['tx']+$otxr) .",";
				$qry.= " rxrt = ". ($info['rx']+$orxr) ." WHERE name = '$unm' AND stat = TRUE";
				if(pg_query(CONN, $qry)) die("<p>SUCESSO</p>");
				else die("<p>ERRO</p>\n<span>Não foi possível<br />gravar os dados</span>");
			} else {
		///// Finish Client Information /////
				$qery = "SELECT sign, txrt, rxrt FROM at_sess WHERE name = '$unm' AND stat = TRUE";
				if($resl = pg_query(CONN, $qery)) {
					if(pg_num_rows($resl)==1) {
						$ardt = pg_fetch_array($resl);
						$stn = round(intval($ardt['sign']) / 15);
						$txr = round(intval($ardt['txrt']) / 15);
						$rxr = round(intval($ardt['rxrt']) / 15);
						if($stn<14 || $txr<20 || $rxr<20) die("<p>abaixo</p>");
					}	else die("<p>ERRO</p>\n<span>Não foi possível obter<br />os dados de enlace</span>");
				}	else die("<p>ERRO</p>\n<span>Não foi possível <br />conectar ao BD</span>");
				$qry = "UPDATE at_sess SET sign = $stn, txrt = $txr, rxrt = $rxr";
				$qry.= " WHERE name = '$unm' AND stat = TRUE";
				if(pg_query(CONN, $qry)) die("<p>SUCESSO</p>");
				else die("<p>ERRO</p>\n<span>Não foi possível<br />gravar os dados</span>");
			}
		}
*/
	} else header("Location: /");
?>
