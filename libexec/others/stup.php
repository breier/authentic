<?php
	if(isset($_POST['ajax']) && isset($_POST['type']) && isset($_POST['w'])) {
		require("../config.php");
		include_once(I."/php/history.php");
		$typ = $_POST['type'];
		$who = $_POST['w'];

		switch($typ) {
			case 'tv':
			// ##### Tempo de Validade ##### //
				echo "<p>Defina por quantos dias será válido o cadastro de um usuário.</p>\n";
				$wstc = pg_select(CONN, "at_settings", array('type' => "cnfg", 'conf' => "wstt"));
				$wsto = (isset($wstc[0]['data'])) ? ($wstc[0]['data']) : (2592000);
				$anos = ($wsto/31557600);
				$leng = (strpos(strval($anos), '.')) ? (strpos(strval($anos), '.')) : (strlen(strval($anos)));
				$anos = ($anos<1) ? (0) : (substr(strval($anos), 0, $leng));
				$mess = ($wsto/2629800)-($anos*12);
				$leng = (strpos(strval($mess), '.')) ? (strpos(strval($mess), '.')) : (strlen(strval($mess)));
				$mess = ($mess<1) ? (0) : (substr(strval($mess), 0, $leng));
				$dias = ($wsto/86400)-($anos*365.25)-($mess*30.4375);
				$leng = (strpos(strval($dias), '.')) ? (strpos(strval($dias), '.')) : (strlen(strval($dias)));
				$dias = ($dias<1) ? (0) : (substr(strval($dias), 0, $leng));
				$hors = ($wsto/3600)-($anos*8766)-($mess*730.5)-($dias*24);
				$leng = (strpos(strval($hors), '.')) ? (strpos(strval($hors), '.')) : (strlen(strval($hors)));
				$hors = ($hors<1) ? (0) : (substr(strval($hors), 0, $leng));
				$mins = ($wsto/60)-($anos*525960)-($mess*43830)-($dias*1440)-($hors*60);
				$leng = (strpos(strval($mins), '.')) ? (strpos(strval($mins), '.')) : (strlen(strval($mins)));
				$mins = ($mins<1) ? (0) : (substr(strval($mins), 0, $leng));
				$secs = ($wsto)-($anos*31557600)-($mess*2629800)-($dias*86400)-($hors*3600)-($mins*60);
				$leng = (strpos(strval($secs), '.')) ? (strpos(strval($secs), '.')) : (strlen(strval($secs)));
				$secs = ($secs<1) ? (0) : (substr(strval($secs), 0, $leng));
?>
<hr />
<table align="center">
	<tr align="center">
		<td>Anos</td>
		<td>Meses</td>
		<td>Dias</td>
		<td>|</td>
		<td>Horas</td>
		<td>Minutos</td>
		<td>Segundos</td>
	</tr>
	<tr align="center" id="tv">
		<td><input type="number" name="anos" style="width: 50px;" value="<?= $anos; ?>" /></td>
		<td><input type="number" name="mess" style="width: 50px;" value="<?= $mess; ?>" /></td>
		<td><input type="number" name="dias" style="width: 50px;" value="<?= $dias; ?>" /></td>
		<td>:</td>
		<td><input type="number" name="hors" style="width: 50px;" value="<?= $hors; ?>" /></td>
		<td><input type="number" name="mins" style="width: 50px;" value="<?= $mins; ?>" /></td>
		<td><input type="number" name="secs" style="width: 50px;" value="<?= $secs; ?>" /></td>
	</tr>
</table>
<br />
<button type="button" name="save" onclick="envia('tv'); this.blur();">Salvar Alterações</button>
<?php		break;
			// ##### Limite de Sessão ##### //
			case 'ls':
				echo "Ainda não Implementado.";
			break;
			// ##### Pagina Inicial ##### //
			case 'pi':
				echo "<p>Defina a Página Inicial à qual os usuários serão redirecionados após login.</p>";
				echo "\n<hr />\n<table align=\"center\">\n";
				$arra = array(	'pblc' => 'Órgão Públicos: ',
									'stnd' => 'Clientes Internet: ',
									'free' => 'Áreas de Livre Acesso: '	);
				foreach($arra as $grup => $name) {
					$paga = array('groupname' => "$grup", 'attribute' => "WISPr-Redirection-URL");
					$pagc = pg_select(CONN, "radgroupreply", $paga);
					$page = (isset($pagc[0]['value'])) ? ($pagc[0]['value']) : ('');
					echo "\t<tr align=\"center\">\n";
					echo "\t\t<td style=\"text-align: right;\">$name</td>\n";
					echo "\t\t<td style=\"text-align: left;\">\n";
					echo "\t\t\t<input type=\"text\" name=\"$grup\" id=\"$grup\"";
					echo " style=\"width: 220px;\" value=\"$page\" />\n";
					echo "\t\t\t<button type=\"button\" name=\"pagb\"";
					echo " onclick=\"this.blur();\">Salvar</button>\n";
					echo "\t\t</td>\n\t</tr>\n";
				}	echo "</table>\n";
			break;
			// ##### Linhas de Resultado ##### //
			case 'lr':
				echo "<p>Defina quantas linhas aparecerão no resultado de pesquisa por usuários.</p>";
			break;
			// ##### Sessão Authentic ##### //
			case 'sa':
				echo "<p>Defina por quanto tempo um usuário permanecerá logado no authentic.</p>";
			break;
			// ##### Colunas de Resultado ##### //
			case 'cr':
				echo "Ainda não Implementado.";
			break;
		}
//////////////////////////////////////////////////////////////////////////////
	}	elseif(isset($_POST['ajax']) && isset($_POST['mess']) && isset($_POST['mins'])) {
		require("../config.php");
		include_once(I."/php/history.php");
		echo "\t\t<td colspan=\"7\">";

		$anos = intval($_POST['anos']);
		$mess = intval($_POST['mess']);
		$dias = intval($_POST['dias']);
		$hors = intval($_POST['hors']);
		$mins = intval($_POST['mins']);
		$secs = intval($_POST['secs']);
		if($anos<0 || $anos>99 ||
			$mess<0 || $mess>11 ||
			$dias<0 || $dias>31 ||
			$hors<0 || $hors>23 ||
			$mins<0 || $mins>59 ||
			$secs<0 || $secs>59)
			echo '<span style="color: #F00;">Erro: Valores Inválidos!</span>';
		else {
			$crtt = 'CURRENT_TIMESTAMP';
			$wsto = ($anos*31557600)+($mess*2629800)+($dias*86400)+($hors*3600)+($mins*60)+$secs;
			$rslt = pg_query(CONN, "SELECT id + 1 AS id FROM at_settings ORDER BY id DESC");
			$nxid = (pg_num_rows($rslt)) ? (pg_fetch_array($rslt)) : (array('id' => 1));
			if($rslt = pg_select(CONN, 'at_settings', array('type' => 'cnfg', 'conf' => 'wstt')))
				$qery = "UPDATE at_settings SET date = $crtt, data = '$wsto' WHERE id = ". $rslt[0]['id'];
			else $qery = "INSERT INTO at_settings VALUES ( $nxid[id], $crtt, 'cnfg', '$wsto', 'wstt', 7 )";
			if(!pg_query(CONN, $qery)) echo '<span style="color: #F00;">Erro: Banco de Dados!</span>';
			else echo '<span style="color: #080;">Valores salvos com sucesso!</span>';
		}	echo "</td>\n";
	}
?>