<?php
	if(isset($_POST['ajax']) && isset($_POST['type'])) {
		require("../config.php");
		$wto = str_replace('..', '.', A);

		if($_POST['type']=='bd') { ?>
<h3 style="color: #164D7D;">BANCO DE DADOS</h3>
<div>
	<div style="display: inline; padding: 10px; border-right: dotted 1px #333;">
		<button type="button" name="trsh" onclick="this.blur(); m('dupl').innerHTML = '';
				m('ajoc').setAttribute('onclick', 'verify(\'tt\')');
				openIn('bd', '<?= $wto.'/find.php'; ?>', 'ajax=1&tool=trsh', 1);"
				style="margin-right: 10px;">
			Resíduos<span id="trsh" style="font-size: 10px; color: #700;"></span>
		</button>
		<button type="button" name="dupl" onclick="this.blur(); m('trsh').innerHTML = '';
				m('ajoc').setAttribute('onclick', 'verify(\'tt\')');
				openIn('bd', '<?= $wto.'/find.php'; ?>', 'ajax=1&tool=dupl', 1);">
			Duplicados<span id="dupl" style="font-size: 10px; color: #700;"></span>
		</button>
	</div>
	<div style="display: inline; padding: 10px;">
		<button type="button" name="doit" style="margin-left: 2px;"
				onclick="clearDB('bd'); this.blur();">Limpar</button>
		<button type="button" name="adex" style="margin-left: 10px;"
				onclick="addEx('bd'); this.blur();">Adicionar Exceção</button>
	</div>
	<br /><hr />
	<div align="center">
		<div style="overflow: auto; height: 160px; width: 431px;">
			<div id="bd" style="display: inline;"></div>
		</div>
	</div>
</div>
<?php	} elseif($_POST['type']=='ls') { ?>
<h3 style="color: #164D7D;">LOGS AUTHENTIC</h3>
<style type="text/css">
	<!--
		table.logt td {
			text-align: left;
			white-space: nowrap;
			font: normal 10px monospace;
			padding: 0 3px 0 3px;
		}
		table.logt span {
			display: block;
			width: 120px;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		table.logt tr:nth-child(odd) {	background-color: #DDD;	}
	//-->
</style>
<div style="margin: 0; padding: 0; height: 190px; overflow: auto;">
	<table align="center" width="100%" class="logt">
		<tbody>
			<tr>
				<th>Data</th>
				<th>Hora</th>
				<th>Usuário</th>
				<th>Tipo</th>
				<th>Ação</th>
				<th>Info</th>
			</tr>
<?php		if(!file_exists(L."/syslog")) {
				echo "\t\t\t<tr><td colspan=\"6\">Arquivo de LOG não pôde ser encontrado!</td></tr>\n";
			} else {
				include(I."/php/getConfig.php");
				$rwpp = getConfig('cnfg', 'rwpp');
				$fp = fopen(L."/syslog", "r");
				fseek($fp, -($rwpp*500), SEEK_END);
				$rwlg = @fread($fp, ($rwpp*500));
				fclose($fp);
				$lnlg = explode(PHP_EOL, $rwlg);
				for($i=count($lnlg); $i>2; $i--) {
					$iflg = explode(':', $lnlg[$i-2], 5);
					$datd = date("d/m/Y", strtotime($iflg[0]));
					$tt = strlen($iflg[0]);
					$dath = $iflg[0][$tt-6].$iflg[0][$tt-5].':'.$iflg[0][$tt-4].$iflg[0][$tt-3].':'.$iflg[0][$tt-2].$iflg[0][$tt-1];
					$info = str_replace('"', "''", $iflg[4]);
					echo "\t\t\t<tr>\n\t\t\t\t<td>$datd</td>\n\t\t\t\t<td>$dath</td>\n\t\t\t\t<td>".$iflg[1]."</td>\n";
					echo "\t\t\t\t<td>".$iflg[2]."</td>\n\t\t\t\t<td><span title=\"".$iflg[3]."\" >".$iflg[3]."</span></td>\n";
					echo "\t\t\t\t<td><span title=\"$info\" >".$iflg[4]."</span></td>\n\t\t\t</tr>\n";
				}
			}
		} echo "\t\t</tbody>\n\t</table>\n</div>\n";
	} if(isset($_POST['ajax']) && isset($_POST['tool']) && isset($_POST['data'])) {
		require("../config.php");

		if($_POST['tool']=='clear') {
			// ----- Getting User Data ----- //
			$arr = explode('|', $_POST['data']);
			$wht = ($_POST['from']=='trsh') ? ('resíduo') : ('ítem');
			if(sizeof($arr)>1) {
				$ats = array();
				for($i=0; $i<sizeof($arr); $i=$i+3) {
					$j = $i + 1;
					$k = $i + 2;
					$ats[$arr[$j]][$arr[$i]] = $arr[$k];
				}
				// ## Forming query ## //
				$qry = array();
				foreach($ats as $tbl => $usr) {
					$que = "DELETE FROM $tbl WHERE ";
					foreach($usr as $unm => $cmp) {
						if(strlen($que) > (strlen($tbl) + 20)) $que.= "OR ";
						$que.= "$cmp = '$unm' ";
					}	$qry[] = $que;
				}
				// ## Deleting Trash Users ## //
				for($i=0; $i<sizeof($qry); $i++) {
					if(!$rsl = pg_query(CONN, $qry[$i])) {
						echo "ERRO $i: Não foi possível remover $wht!";
						break;
					}
				}	if($i == sizeof($qry)) echo "$i $wht(s) removido(s) com sucesso!";
			}	else echo "Nenhum $wht selecionado!";
		}
	} ?>
