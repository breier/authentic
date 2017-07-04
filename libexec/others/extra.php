<?php
	if(isset($_POST['ajax']) && isset($_POST['t']) && isset($_POST['g'])) {
		require("../config.php");

// ------ Adding phone input predefined ------- //
		if($_POST['t']=='phon') {
			require_once(I."/php/getConfig.php");
			$pnfl = ($_POST['g']=='pblc') ? ('pnop') : ('pnpb');
			$pnfl = unserialize(getConfig('cnfg', $pnfl));
			$npq = "SELECT name::integer + 1 AS phon FROM astuserpeer WHERE";
			$npq.= " name::integer > $pnfl[frst] AND name::integer < $pnfl[last] AND";
			$npq.= " name::integer + 1 NOT IN ( SELECT name::integer FROM astuserpeer )";
			$npq.= " ORDER BY name::integer LIMIT 1";
			if($npr = pg_query(CONN, $npq)) {
				$npn = (pg_num_rows($npr)==1)?(pg_fetch_array($npr)):(array("phon" => ($pnfl['frst'] + 1)));
				$npo = $npn['phon'];
			}	else $npo = ($pnfl['frst'] + 1);
			if($npo > $pnfl['last']) $npo = "N/A";
			$num = (isset($_POST['c'])) ? (intval($_POST['c']) + 1) : (1);
			$npq = "SELECT id + 1 AS id FROM astuserpeer ORDER BY id DESC LIMIT 1";
			if($npr = pg_query(CONN, $npq)) {
				$npn = (pg_num_rows($npr)==1)?(pg_fetch_array($npr)):(array("id" => 1));
				$nid = $npn['id'];
			}	else $nid = 1; ?>
	<td style="text-align: right; font-weight: bold;">Ramal <?= $num; ?>:</td>
	<td style="text-align: left;">
		<input type="hidden" name="phon_<?= $num; ?>[rpid]" value="<?= $nid; ?>" label="" />
		<input type="text" name="phon_<?= $num; ?>[phon]" value="<?= $npo; ?>"
			label="" style="width: 130px;" />
	</td>
<?php	}
// ------ Adding user and pass inputs ------- //
		if($_POST['t']=='intr') { ?>
	<td style="text-align: right; font-weight: bold;">Usuário:</td>
	<td style="text-align: left;">
		<input type="text" name="user" value="<?= $_POST['g']; ?>" label="" style="width: 130px;" />
	</td>
	<td style="width: 15px;"> </td>
	<td style="text-align: right; font-weight: bold;">Senha Internet:</td>
	<td style="text-align: left;">
		<input type="password" name="Cleartext-Password" value="" label="" style="width: 130px;" />
	</td>
<?php }
// ------ Adding user available style ------- //
		if($_POST['t']=='available') {
			$cfr = array('username' => $_POST['g']);
			if(pg_select(CONN, 'radusergroup', $cfr)) echo "FALSE";
			else echo "TRUE";
		}
	} ?>