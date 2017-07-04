<?php
	if(isset($_POST['ajax']) && isset($_POST['pl_nome']) && isset($_POST['pl_prdd'])) {
		require("../config.php");
		include_once(I."/php/history.php");

		$plgn = $_POST['pl_nome'];
		$txup = round(floatval($_POST['pl_txup']) * 1048576);
		$txdw = round(floatval($_POST['pl_txdw']) * 1048576);
		$brup = round(floatval($_POST['pl_brup']) * 1048576);
		$brdw = round(floatval($_POST['pl_brdw']) * 1048576);
		$mdup = round(floatval($_POST['pl_mdup']) * 1048576);
		$mddw = round(floatval($_POST['pl_mddw']) * 1048576);
		$tbup = intval($_POST['pl_tbup']);
		$tbdw = intval($_POST['pl_tbdw']);
		$mnup = round(floatval($_POST['pl_mnup']) * 1048576);
		$mndw = round(floatval($_POST['pl_mndw']) * 1048576);
		$plvl = "$txup/$txdw $brup/$brdw $mdup/$mddw $tbup/$tbdw $_POST[pl_prdd] $mnup/$mndw";

		$res = pg_query(CONN, "SELECT id FROM radgroupreply WHERE groupname = '$plgn' OR \"value\" = '$plvl'");
		if(pg_num_rows($res)>0) {
			if($_POST['ajax']=='admn') die("<div>Erro! Este Plano de Internet já existe!</div>");
			else {
				if($_POST['ajax']!=$plgn) {
					$qry = "UPDATE radgroupreply SET groupname = '$plgn' WHERE groupname = '$_POST[ajax]'";
					$res = pg_query(CONN, $qry);
					if(pg_affected_rows($res)!=1) die("<div>Erro! Problema ao atualizar este Plano de Internet!</div>");
				} $qry = "UPDATE radgroupreply SET \"value\" = '$plvl' WHERE groupname = '$plgn' ";
				$res = pg_query(CONN, $qry."AND attribute = 'Mikrotik-Rate-Limit'");
				if(pg_affected_rows($res)!=1) die("<div>Erro! Problema ao atualizar este Plano de Internet!</div>");
			}
		} else {
			$qry = "INSERT INTO radgroupreply (groupname, attribute, op, \"value\") VALUES";
			$qry.= " ('$plgn', 'Mikrotik-Rate-Limit', ':=', '$plvl'),";
			$qry.= " ('$plgn', 'Framed-Compression', ':=', 'Van-Jacobsen-TCP-IP'),";
			$qry.= " ('$plgn', 'Framed-Protocol', ':=', 'PPP'),";
			$qry.= " ('$plgn', 'Service-Type', ':=', 'Framed-User'),";
			$qry.= " ('$plgn', 'Framed-MTU', ':=', '1480')";
			$res = pg_query(CONN, $qry);
			if(pg_affected_rows($res)!=5) die("<div>Erro! Problema ao inserir este Plano de Internet!</div>");
			$qry = "INSERT INTO radgroupcheck (groupname, attribute, op, \"value\") VALUES";
			$qry.= " ('$plgn', 'Auth-Type', ':=', 'MS-CHAP')";
			$res = pg_query(CONN, $qry);
			if(pg_affected_rows($res)!=1) die("<div>Erro! Problema ao inserir este Plano de Internet!</div>");
		}
		$dts = explode(' ', $plvl);
		$txup = round(intval(substr($dts[0], 0, strpos($dts[0], '/'))) / 1048576, 1);
		$txdw = round(intval(substr($dts[0], strpos($dts[0], '/')+1)) / 1048576, 1);
		$brup = round(intval(substr($dts[1], 0, strpos($dts[1], '/'))) / 1048576, 1);
		$brdw = round(intval(substr($dts[1], strpos($dts[1], '/')+1)) / 1048576, 1);
		$mdup = round(intval(substr($dts[2], 0, strpos($dts[2], '/'))) / 1048576, 1);
		$mddw = round(intval(substr($dts[2], strpos($dts[2], '/')+1)) / 1048576, 1);
		$tbup = intval(substr($dts[3], 0, strpos($dts[3], '/')));
		$tbdw = intval(substr($dts[3], strpos($dts[3], '/')+1));
		$mnup = round(intval(substr($dts[5], 0, strpos($dts[5], '/'))) / 1048576, 1);
		$mndw = round(intval(substr($dts[5], strpos($dts[5], '/')+1)) / 1048576, 1);
		echo "\t\t\t\t\t\t\t\t<td><span class=\"ipt\" style=\"font-weight: bolder;\">$plgn</span></td>\n";
		echo "\t\t\t\t\t\t\t\t<td><span class=\"ipt\">$txup M</span><br style=\"line-height: 30px;\" />\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$txdw M</span></td>\n";
		echo "\t\t\t\t\t\t\t\t<td><span class=\"ipt\">$brup M</span><br style=\"line-height: 30px;\" />\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$brdw M</span></td>\n";
		echo "\t\t\t\t\t\t\t\t<td><span class=\"ipt\">$mdup M</span><br style=\"line-height: 30px;\" />\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$mddw M</span></td>\n";
		echo "\t\t\t\t\t\t\t\t<td><span class=\"ipt\">$tbup s</span><br style=\"line-height: 30px;\" />\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$tbdw s</span></td>\n";
		echo "\t\t\t\t\t\t\t\t<td style=\"position: relative;\">\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$mnup M</span><br style=\"line-height: 30px;\" />\n";
		echo "\t\t\t\t\t\t\t\t\t<span class=\"ipt\">$mndw M</span>\n";
		echo "\t\t\t\t\t\t\t\t\t<button style=\"position: absolute; left: 90px; top: 15px;\"";
		echo " onclick=\"location.reload();\">Recarregar</button></td>\n";
	}
?>
