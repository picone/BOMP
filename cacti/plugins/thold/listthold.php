<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2009 The Cacti Group                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

chdir('../../');
include_once('./include/auth.php');
include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

/* global colors */
$thold_bgcolors = array('red' => 'FF6044',
	'yellow' => 'FAFD9E',
	'orange' => 'FF7D00',
	'green'  => 'CCFFCC',
	'grey'   => 'CDCFC4');

if (isset($_POST['drp_action'])) {
	do_thold();
} else {
	delete_old_thresholds();
	list_tholds();
}

function do_thold() {
	global $hostid;

	$tholds = array();
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_(.*)$", $var, $matches)) {
			$del = $matches[1];
			$rra = db_fetch_cell("SELECT rra_id FROM thold_data WHERE id=$del");

			input_validate_input_number($del);
			$tholds[$del] = $rra;
		}
	}

	switch ($_POST['drp_action']) {
		case 1:	// Delete
			foreach ($tholds as $del => $rra) {
				if (thold_user_auth_threshold ($rra)) {
					plugin_thold_log_changes($del, 'deleted', array('id' => $del));
					db_execute("DELETE FROM thold_data WHERE id=$del");
					db_execute('DELETE FROM plugin_thold_threshold_contact WHERE thold_id=' . $del);
				}
			}
			break;
		case 2:	// Disabled
			foreach ($tholds as $del => $rra) {
				if (thold_user_auth_threshold ($rra)) {
					plugin_thold_log_changes($del, 'disabled_threshold', array('id' => $del));
					db_execute("UPDATE thold_data SET thold_enabled = 'off' WHERE id = $del");
				}
			}
			break;
		case 3:	// Enabled
			foreach ($tholds as $del => $rra) {
				if (thold_user_auth_threshold ($rra)) {
					plugin_thold_log_changes($del, 'enabled_threshold', array('id' => $del));
					db_execute("UPDATE thold_data SET thold_enabled = 'on' WHERE id = $del");
				}
			}
			break;
	}

	if (isset($hostid) && $hostid != '')
		Header('Location:listthold.php?hostid=$hostid');
	else
		Header('Location:listthold.php');

	exit;
}

/** 
 *  This is a generic funtion for this page that makes sure that
 *  we have a good request.  We want to protect against people who
 *  like to create issues with Cacti.
*/
function thold_request_validation() {
	global $title, $colors, $rows_selector, $config, $reset_multi;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request('rows'));
	input_validate_input_number(get_request_var_request('page'));
	/* ==================================================== */

	/* clean up sort solumn */
	if (isset($_REQUEST['sort_column'])) {
		$_REQUEST['sort_column'] = sanitize_search_string(get_request_var('sort_column'));
	}

	/* clean up sort direction */
	if (isset($_REQUEST['sort_direction'])) {
		$_REQUEST['sort_direction'] = sanitize_search_string(get_request_var('sort_direction'));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST['button_clear_x'])) {
		kill_session_var('sess_thold_rows');
		kill_session_var('sess_thold_page');
		kill_session_var('sess_thold_sort_column');
		kill_session_var('sess_thold_sort_direction');
		kill_session_var('sess_thold_hostid');
		kill_session_var('sess_thold_state');
		kill_session_var('sess_thold_template');

		$_REQUEST['page'] = 1;
		unset($_REQUEST['rows']);
		unset($_REQUEST['page']);
		unset($_REQUEST['sort_column']);
		unset($_REQUEST['sort_direction']);
		unset($_REQUEST['hostid']);
		unset($_REQUEST['template']);
		unset($_REQUEST['state']);
		$reset_multi = true;
	}else{
		/* if any of the settings changed, reset the page number */
		$changed = 0;
		$changed += thold_request_check_changed('rows', 'sess_thold_rows');
		$changed += thold_request_check_changed('page', 'sess_thold_page');
		$changed += thold_request_check_changed('sort_column', 'sess_thold_sort_column');
		$changed += thold_request_check_changed('sort_direction', 'sess_thold_sort_direction');
		$changed += thold_request_check_changed('hostid', 'sess_thold_hostid');
		$changed += thold_request_check_changed('state', 'sess_thold_state');
		$changed += thold_request_check_changed('template', 'sess_thold_template');
		if ($changed) {
			$_REQUEST['page'] = '1';
		}

		$reset_multi = false;
	}

	/* remember search fields in session vars */
	load_current_session_value('rows', 'sess_thold_rows', read_config_option('num_rows_thold'));
	load_current_session_value('page', 'sess_thold_current_page', '1');
	load_current_session_value('sort_column', 'sess_thold_sort_column', 'thold_alert');
	load_current_session_value('sort_direction', 'sess_thold_sort_direction', 'DESC');
	load_current_session_value('state', 'sess_thold_state', 'Triggered');
	load_current_session_value('hostid', 'sess_thold_hostid', '');
	load_current_session_value('template', 'sess_thold_template', '');
}

function thold_request_check_changed($request, $session) {
	if ((isset($_REQUEST[$request])) && (isset($_SESSION[$session]))) {
		if ($_REQUEST[$request] != $_SESSION[$session]) {
			return 1;
		}
	}
}

function list_tholds() {
	global $colors, $thold_bgcolors, $config, $hostid;

	$ds_actions = array(1 => '删除', 2 => '禁用', 3 => '启用');

	thold_request_validation();

	$statefilter='';
	if (isset($_REQUEST['state'])) {
		if ($_REQUEST['state'] == 'ALL') {
			$statefilter = '';
		} else {
			if($_REQUEST['state'] == '已禁用') { $statefilter = "thold_data.thold_enabled='off'"; }
			if($_REQUEST['state'] == '已启用') { $statefilter = "thold_data.thold_enabled='on'"; }
			if($_REQUEST['state'] == '已触发') { $statefilter = 'thold_data.thold_alert!=0'; }
		}
	}

	$alert_num_rows = read_config_option('alert_num_rows');
	if ($alert_num_rows < 1 || $alert_num_rows > 999) {
		db_execute("REPLACE INTO settings VALUES ('alert_num_rows', 30)");
		/* pull it again so it updates the cache */
		$alert_num_rows = read_config_option('alert_num_rows', true);
	}

	include($config['include_path'] . '/top_header.php');

	$sql_where = '';

	$sort = $_REQUEST['sort_column'];
	$limit = ' LIMIT ' . ($alert_num_rows*($_REQUEST['page']-1)) . ",$alert_num_rows";

	if (!empty($_REQUEST['hostid']) && $_REQUEST['hostid'] != 'ALL') {
		$sql_where .= (!strlen($sql_where) ? 'WHERE ' : ' AND ') . "thold_data.host_id = " . $_REQUEST['hostid'];
	}
	if (!empty($_REQUEST['template']) && $_REQUEST['template'] != 'ALL') {
		$sql_where .= (!strlen($sql_where) ? 'WHERE ' : ' AND ') . "thold_data.data_template = " . $_REQUEST['template'];
	}
	if($statefilter != '') {
		$sql_where .= (!strlen($sql_where) ? 'WHERE ' : ' AND ') . "$statefilter";
	}

	$current_user = db_fetch_row('SELECT * FROM user_auth WHERE id=' . $_SESSION['sess_user_id']);
	$sql_where .= (!strlen($sql_where) ? 'WHERE ' : ' AND ') . get_graph_permissions_sql($current_user['policy_graphs'], $current_user['policy_hosts'], $current_user['policy_graph_templates']);

	$sql = "SELECT * FROM thold_data
		LEFT JOIN user_auth_perms on ((thold_data.graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . ") OR (thold_data.host_id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . ") OR (thold_data.graph_template=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . "))
		$sql_where
		ORDER BY $sort " . $_REQUEST['sort_direction'] .
		$limit;
	$result = db_fetch_assoc($sql);

	$sql_where_hid    = 'WHERE ' . get_graph_permissions_sql($current_user['policy_graphs'], $current_user['policy_hosts'], $current_user['policy_graph_templates']);
	$hostresult = db_fetch_assoc("SELECT DISTINCT host.id, host.description, host.hostname
		FROM host
		INNER JOIN thold_data ON (host.id = thold_data.host_id)
		LEFT JOIN user_auth_perms on (thold_data.host_id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . ")
		$sql_where_hid
		ORDER BY hostname");

	$data_templates = db_fetch_assoc("SELECT DISTINCT data_template.id, data_template.name
		FROM data_template
		INNER JOIN thold_data ON (thold_data.data_template = data_template.id)
		ORDER BY data_template.name");

	?>
	<script type="text/javascript">
	<!--

	function applyTHoldFilterChange(objForm) {
		strURL = '?hostid=' + objForm.hostid.value;
		strURL = strURL + '&state=' + objForm.state.value;
		strURL = strURL + '&template=' + objForm.template.value;
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box('<strong>阈值管理</strong>' ,'100%', $colors['header'],'3','center','');
	?>
	<tr bgcolor="<?php print $colors["panel"];?>" class="noprint">
		<form name="listthold" action=listthold.php method=post>
		<input type=hidden name=search value=search>
		<td class="noprint">
			<table cellpadding="0" cellspacing="0">
				<tr class="noprint">
					<td nowrap style='white-space: nowrap;' width="50">
						主机:&nbsp;
					</td>
					<td width='1'>
						<select name='hostid' onChange='applyTHoldFilterChange(document.listthold)'>
							<option value=ALL>全部</option>
							<?php
							foreach ($hostresult as $row) {
								echo "<option value='" . $row['id'] . "'" . (isset($_REQUEST['hostid']) && $row['id'] == $_REQUEST['hostid'] ? ' selected' : '') . '>' . $row['description'] . ' - (' . $row['hostname'] . ')' . '</option>';
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;模板:&nbsp;
					</td>
					<td width='1'>
						<select name=template onChange='applyTHoldFilterChange(document.listthold)'>
							<option value=ALL>全部</option>
							<?php
							foreach ($data_templates as $row) {
								echo "<option value='" . $row['id'] . "'" . (isset($_REQUEST['template']) && $row['id'] == $_REQUEST['template'] ? ' selected' : '') . '>' . $row['name'] . '</option>';
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;状态:&nbsp;
					</td>
					<td width='1'>
						<select name=state onChange='applyTHoldFilterChange(document.listthold)'>
							<option value=ALL>全部</option>
							<?php
							foreach (array('已禁用','已启用','已触发') as $row) {
								echo "<option value='" . $row . "'" . (isset($_REQUEST['state']) && $row == $_REQUEST['state'] ? ' selected' : '') . '>' . $row . '</option>';
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type='image' name='button_clear' src='../../images/button_clear.gif' alt='返回默认时间段' border='0' align='absmiddle' action='submit'>
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>
	<?php

	html_end_box();
	echo '<div><a href="thold_add.php"><button type="button">添加</button></a></div>';echo '<br />';
	define('MAX_DISPLAY_PAGES', 21);

	$total_rows = count(db_fetch_assoc("SELECT thold_data.id FROM thold_data
		LEFT JOIN user_auth_perms on ((thold_data.graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . ") OR (thold_data.host_id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . ") OR (thold_data.graph_template=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION['sess_user_id'] . "))
		$sql_where"));

	$url_page_select = get_page_list($_REQUEST['page'], MAX_DISPLAY_PAGES, $alert_num_rows, $total_rows, 'listthold.php?');

	html_start_box('', '100%', $colors['header'], '4', 'center', '');
	$nav = "<tr bgcolor='#" . $colors['header'] . "'>
			<td colspan='12'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='listthold.php?page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "上一页"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
						</td>\n
						<td align='center' class='textHeaderDark'>
							" . (($alert_num_rows*($_REQUEST["page"]-1))+1) . " 到 " . ((($total_rows < $alert_num_rows) || ($total_rows < ($alert_num_rows*$_REQUEST["page"]))) ? $total_rows : ($alert_num_rows*$_REQUEST["page"])) . " 行,共 $total_rows 行 [ 第 $url_page_select 页 ]
						</td>\n
						<td align='right' class='textHeaderDark'>
							<strong>"; if (($_REQUEST["page"] * $alert_num_rows) < $total_rows) { $nav .= "<a class='linkOverDark' href='listthold.php?page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "下一页"; if (($_REQUEST["page"] * $alert_num_rows) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	//print $nav;

	$display_text = array(
		'name' => array('名称', 'ASC'),
		'thold_type' => array('类型', 'ASC'),
		'thold_hi' => array('上限', 'ASC'),
		'thold_low' => array('下限', 'ASC'),
		'thold_fail_trigger' => array('容忍时长', 'ASC'),
		'time_fail_length' => array('持续时间', 'ASC'),
		'repeat_alert' => array('再次报警', 'ASC'),
		'lastread' => array('当前', 'ASC'),
		'thold_alert' => array('已触发', 'ASC'),
		'thold_enabled' => array('已启用', 'ASC'));

	html_header_sort_checkbox($display_text, $_REQUEST['sort_column'], $_REQUEST['sort_direction']);

	$c=0;
	$i=0;
	$types = array('上/下限', '基线', '基于时间');
	if (count($result)) {
		foreach ($result as $row) {
			$c++;

			$grapharr = db_fetch_row('SELECT DISTINCT graph_templates_item.local_graph_id
						FROM graph_templates_item, data_template_rrd
						where (data_template_rrd.local_data_id=' . $row['rra_id'] . ' AND data_template_rrd.id=graph_templates_item.task_item_id)');
			$graph_id = $grapharr['local_graph_id'];

			if ($row['thold_alert'] != 0) {
				$alertstat='是';
				$bgcolor=($row['thold_fail_count'] >= $row['thold_fail_trigger'] ? 'red' : 'yellow');
			} else {
				$alertstat='否';
				$bgcolor='green';
				if($row['bl_enabled'] == 'on') {
					if($row['bl_alert'] == 1) {
						$alertstat='baseline-LOW';
						$bgcolor=($row['bl_fail_count'] >= $row['bl_fail_trigger'] ? 'orange' : 'yellow');
					} elseif ($row['bl_alert'] == 2)  {
						$alertstat='baseline-HIGH';
						$bgcolor=($row['bl_fail_count'] >= $row['bl_fail_trigger'] ? 'orange' : 'yellow');
					}
				}
			};

			if ($row['thold_enabled'] == 'off') {
				form_alternate_row_color($thold_bgcolors['grey'], $thold_bgcolors['grey'], $i, 'line' . $row["id"]); $i++;
			}else{
				form_alternate_row_color($thold_bgcolors[$bgcolor], $thold_bgcolors[$bgcolor], $i, 'line' . $row["id"]); $i++;
			}
			form_selectable_cell("<a class='linkEditMain' href='thold.php?rra=" . $row['rra_id'] . "&view_rrd=" . $row['data_id'] . "'><b>" . ($row['name'] != '' ? $row['name'] : $row['name_cache'] . " [" . $row['data_source_name'] . ']') . '</b></a>', $row['id']);
			form_selectable_cell($types[$row['thold_type']], $row["id"]);
			form_selectable_cell(($row['thold_type'] == 0 ? $row['thold_hi'] : ($row['thold_type'] == 2 ? $row['time_hi'] : '')), $row["id"]);
			form_selectable_cell(($row['thold_type'] == 0 ? $row['thold_low'] : ($row['thold_type'] == 2 ? $row['time_low'] : '')), $row["id"]);
			form_selectable_cell(($row['thold_type'] == 0 ? ("<i>" . plugin_thold_duration_convert($row['rra_id'], $row['thold_fail_trigger'], 'alert') . "</i>") : ($row['thold_type'] == 2 ? ("<i>" . $row['time_fail_trigger'] . "</i>") : '')), $row["id"]);
			form_selectable_cell(($row['thold_type'] == 2 ? plugin_thold_duration_convert($row['rra_id'], $row['time_fail_length'], 'time') : ''), $row["id"]);
			form_selectable_cell(($row['repeat_alert'] == '' ? '' : plugin_thold_duration_convert($row['rra_id'], $row['repeat_alert'], 'repeat')), $row["id"]);
			form_selectable_cell($row['lastread'], $row["id"]);
			form_selectable_cell($alertstat, $row["id"]);
			form_selectable_cell((($row['thold_enabled'] == 'off') ? "已禁用": "已启用"), $row["id"]);
			form_checkbox_cell($row['name'], $row['id']);
			form_end_row();
		}
	} else {
		form_alternate_row_color($colors['alternate'],$colors['light'],0);
		print '<td colspan=12><center>无阈值</center></td></tr>';
	}
	print $nav;

	html_end_box(false);

	thold_legend();

	draw_actions_dropdown($ds_actions,'添加','thold_add.php');

	if (isset($hostid) && $hostid != '')
		print "<input type=hidden name=hostid value=$hostid>";
	print "</form>\n";

	include_once($config['include_path'] . '/bottom_footer.php');
}
