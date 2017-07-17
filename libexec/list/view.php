<?php
	/**********************************************************************
	* view file, requested by search AJaX file from list page in order to *
	* return JSON information for users_table (also equipment_table).     *
	* dependencies: messages as $_msg (config);                           *
	*               pgsql as $_pgobj (config).                            *
	***********************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['type'])) {
	//$_msg->wrn($query);
// ---- Loop Fill Table Rows ----- //
		$all_rows = array(array('total' => $_pgobj->rows));
		$current_page = (isset($_POST['page'])) ? (intval($_POST['page'])-1) : (0);
		$offset = (10 * $current_page);
		for($i=$offset; $i<$_pgobj->rows; $i++) {
			if($i == ($offset + 10)) break; // 10 rows per page
			$info_array = $_pgobj->fetch_array($i);
			if($list_type != 'equipment') {
				$groupname_array = json_decode($info_array['groupname']);
				$disabled = (array_search('disabled', $groupname_array) !== FALSE) ? (TRUE) : (FALSE);
				$ip_address = $info_array['framedipaddress'];
			} else {
				if($info_array['equipment_name'] == NULL) $info_array['equipment_name'] = $_msg->lang("unknown");
				if($info_array['date'] == NULL) $disabled = TRUE;
				else $disabled = ((time() - strtotime($info_array['date']))>1000) ? (TRUE) : (FALSE);
				$ip_address = $info_array['ip_address'];
			}
// ----- Display columns ----- //
			$td_first = '<a href="javascript:void(0);" onclick="list_details('. $info_array['id'];
			$td_first.= ');" title="'. $_msg->lang('Show Details') .'" ';
			$td_first.= ($disabled) ? ('class="ellipsis disabled">') : ('class="ellipsis">');
			$td_first.= ($list_type == 'equipment') ? ($info_array['equipment_name']) : ($info_array['full_name']);
			$td_first.= '</a>';

			$date_format = (isset($_settings->system['Date Format'])) ? ($_settings->system['Date Format']) : ('m/d/Y');
			$td_second = ($list_type == 'equipment') ? ($info_array['location']) : (date($date_format, strtotime($info_array['date'])));
			$td_third = ($list_type == 'equipment') ? ($info_array['brand_name']) : (strtoupper($info_array['mac_address'])); // iedea for technicians - last ticket info 2 cols

			if($list_type == 'equipment') $td_fourth = $info_array['ip_address'];
			else {
				if($list_type=='inet') {
					$priority_zero_index = array_search(0, json_decode($info_array['priority']));
					if(!$priority_zero_index) $priority_zero_index = 0;
					$td_fourth = $groupname_array[$priority_zero_index];
				} else $td_fourth = $info_array['username'];
			}
			if(!filter_var($ip_address, FILTER_VALIDATE_IP) === false) {
				$td_status = "<a href=\"http://$ip_address/\" title=\"". $_msg->lang("Access Device");
				$td_status.= "\" target=\"_blank\">". $_msg->lang("connected") ."</a>";
			} else $td_status = '<i class="red">'. $_msg->lang("disconnected") .'</i>';
			$all_rows[] = array($td_first, $td_second, $td_third, $td_fourth, $td_status);
		} print(json_encode($all_rows));
	}
?>
