<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
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

function thold_initialize_rusage() {
	global $thold_start_rusage;

	if (function_exists("getrusage")) {
		$thold_start_rusage = getrusage();
	}

	$thold_start_rusage["microtime"] = microtime();
}

function thold_display_rusage() {
	global $colors, $thold_start_rusage;

	if (function_exists("getrusage")) {
		$dat = getrusage();

		html_start_box("", "100%", $colors["header"], "3", "left", "");
		print "<tr>";

		if (!isset($thold_start_rusage)) {
			print "<td colspan='10'>ERROR: Can not display RUSAGE please call thold_initialize_rusage first</td>";
		}else{
			$i_u_time = $thold_start_rusage["ru_utime.tv_sec"] + ($thold_start_rusage["ru_utime.tv_usec"] * 1E-6);
			$i_s_time = $thold_start_rusage["ru_stime.tv_sec"] + ($thold_start_rusage["ru_stime.tv_usec"] * 1E-6);
			$s_s      = $thold_start_rusage["ru_nswap"];
			$s_pf     = $thold_start_rusage["ru_majflt"];

			list($micro,$seconds) = split(" ", $thold_start_rusage["microtime"]);
			$start_time = $seconds + $micro;
			list($micro,$seconds) = split(" ", microtime());
			$end_time   = $seconds + $micro;

			$utime    = ($dat["ru_utime.tv_sec"] + ($dat["ru_utime.tv_usec"] * 1E-6)) - $i_u_time;
			$stime    = ($dat["ru_stime.tv_sec"] + ($dat["ru_stime.tv_usec"] * 1E-6)) - $i_s_time;
			$swaps    = $dat["ru_nswap"] - $s_s;
			$pages    = $dat["ru_majflt"] - $s_pf;

			print "<td colspan='10' width='1%' style='text-align:left;'>";
			print "<b>时间:</b>&nbsp;" . round($end_time - $start_time,2) . " 秒, ";
			print "<b>用户:</b>&nbsp;" . round($utime,2) . " 秒, ";
			print "<b>系统:</b>&nbsp;" . round($stime,2) . " 秒, ";
			print "<b>交换分区:</b>&nbsp;" . ($swaps) . " 交换,";
			print "<b>页面:</b>&nbsp;" . ($pages) . " 面";
			print "</td>";
		}

		print "</tr>";
		html_end_box(false);
	}

}

function thold_legend() {
	global $colors, $thold_bgcolors;

	html_start_box("", "100%", $colors["header"], "3", "center", "");
	print "<tr>";
	print "<td width='10%' style='text-align:center;background-color:#" . $thold_bgcolors['red'] . ";'><b>报警</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $thold_bgcolors['orange'] . ";'><b>警告</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $thold_bgcolors['yellow'] . ";'><b>通知</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $thold_bgcolors['green'] . ";'><b>正常</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $thold_bgcolors['grey'] . ";'><b>已禁用</b></td>";
	print "</tr>";
	html_end_box(false);
}

function host_legend() {
	global $colors, $host_colors, $disabled_color, $notmon_color;

	html_start_box("", "100%", $colors["header"], "3", "center", "");
	print "<tr>";
	print "<td width='10%' style='text-align:center;background-color:#" . $host_colors[HOST_DOWN] . ";'><b>宕机</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $host_colors[HOST_UP] . ";'><b>正常</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $host_colors[HOST_RECOVERING] . ";'><b>正在恢复</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $host_colors[HOST_UNKNOWN] . ";'><b>未知</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $notmon_color . ";'><b>未监视</b></td>";
	print "<td width='10%' style='text-align:center;background-color:#" . $disabled_color . ";'><b>已禁用</b></td>";
	print "</tr>";
	html_end_box(false);
}

// Update automatically 'alert_base_url' if not set and if we are called from the browser
// so that check-thold can pick it up
if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF']) && read_config_option('alert_base_url') == '') {
	$dir = dirname($_SERVER['PHP_SELF']);
	if (strpos($dir, '/plugins/') !== false)
		$dir = substr($dir, 0, strpos($dir, '/plugins/'));
	db_execute("replace into settings (name,value) values ('alert_base_url', '" . ("http://" . $_SERVER['HTTP_HOST'] . $dir . "/") . "')");

	/* reset local settings cache so the user sees the new settings */
	kill_session_var('sess_config_array');
}

function thold_calculate_percent($thold, $currentval, $rrd_update_array_reindexed) {
	$ds = $thold['percent_ds'];
	if (isset($rrd_update_array_reindexed[$thold['rra_id']][$ds])) {
		$t = $rrd_update_array_reindexed[$thold['rra_id']][$thold['percent_ds']];
		if ($t != 0) {
			$currentval = ($currentval / $t) * 100;
		} else {
			$currentval = 0;
		}
	} else {
		$currentval = '';
	}
	return $currentval;
}

function thold_user_auth_threshold ($rra) {
	$current_user = db_fetch_row("select policy_graphs,policy_hosts,policy_graph_templates from user_auth where id=" . $_SESSION["sess_user_id"]);
	$sql_where = 'WHERE ' . get_graph_permissions_sql($current_user['policy_graphs'], $current_user['policy_hosts'], $current_user['policy_graph_templates']);
	$graphs = db_fetch_assoc('SELECT DISTINCT graph_templates_graph.local_graph_id
		FROM data_template_rrd
		LEFT JOIN graph_templates_item ON graph_templates_item.task_item_id = data_template_rrd.id
		LEFT JOIN graph_local ON (graph_local.id=graph_templates_item.local_graph_id)
		LEFT JOIN host ON graph_local.host_id = host.id
		LEFT JOIN graph_templates_graph ON graph_templates_graph.local_graph_id = graph_local.id
		LEFT JOIN graph_templates ON (graph_templates.id=graph_templates_graph.graph_template_id)
		LEFT JOIN user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=' . $_SESSION['sess_user_id'] . ') OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=' . $_SESSION['sess_user_id'] . ') OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=' . $_SESSION['sess_user_id'] . "))
		$sql_where
		AND data_template_rrd.local_data_id = $rra");
	if (!empty($graphs)) {
		return true;
	}
	return false;
}

function thold_log($save){
	$save['id'] = 0;
	if (read_config_option('thold_log_cacti') == 'on') {
		$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $save['threshold_id'], FALSE);
		$dt = db_fetch_cell('SELECT data_template_id FROM data_template_data WHERE local_data_id=' . $thold['rra_id'], FALSE);
		$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $dt, FALSE);
		$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id'], FALSE);

		if ($save['status'] == 0) {
			$desc = "Threshold Restored  ID: " . $save['threshold_id'];
		} else {
			$desc = "Threshold Breached  ID: " . $save['threshold_id'];
		}
		$desc .= '  DataTemplate: ' . $tname;
		$desc .= '  DataSource: ' . $ds;

		$types = array('High/Low', 'Baseline', 'Time Based');
		$desc .= '  Type: ' . $types[$thold['thold_type']];
		$desc .= '  Enabled: ' . $thold['thold_enabled'];
		switch ($thold['thold_type']) {
			case 0:
				$desc .= '  Current: ' . $save['current'];
				$desc .= '  High: ' . $thold['thold_hi'];
				$desc .= '  Low: ' . $thold['thold_low'];
				$desc .= '  Trigger: ' . plugin_thold_duration_convert($thold['rra_id'], $thold['thold_fail_trigger'], 'alert');
				break;
			case 1:
				$desc .= '  Current: ' . $save['current'];
				break;
			case 2:
				$desc .= '  Current: ' . $save['current'];
				$desc .= '  High: ' . $thold['time_hi'];
				$desc .= '  Low: ' . $thold['time_low'];
				$desc .= '  Trigger: ' . $thold['time_fail_trigger'];
				$desc .= '  Time: ' . plugin_thold_duration_convert($thold['rra_id'], $thold['time_fail_length'], 'time');
				break;
		}
		$desc .= '  SentTo: ' . $save['emails'];
		if ($save['status'] != 1) {
			thold_cacti_log($desc);
		}
	}
	unset($save['emails']);
	$id = sql_save($save, 'plugin_thold_log');
}

function plugin_thold_duration_convert($rra, $data, $type, $field = 'local_data_id') {
	/* handle a null data value */
	if ($data == '') {
		return '';
	}

	$step = db_fetch_cell("SELECT rrd_step FROM data_template_data WHERE $field = $rra");
	if ($step == 60) {
		$repeatarray = array(0 => '永不', 1 => '每分钟', 2 => '每2分钟', 3 => '每3分钟', 4 => '每4分钟', 5 => '每5分钟', 10 => '每10分钟', 15 => '每15分钟', 20 => '每20分钟', 30 => '每30分钟', 45 => '每45分钟', 60 => '每小时', 120 => '每2小时', 180 => '每3小时', 240 => '每4小时', 360 => '每6小时', 480 => '每8小时', 720 => '每12小时', 1440 => '每天', 2880 => '每2天', 10080 => '每周', 20160 => '每2周', 43200 => '每月');
		$alertarray  = array(0 => '永不', 1 => '1分钟', 2 => '2分钟', 3 => '3分钟', 4 => '4分钟', 5 => '5分钟', 10 => '10分钟', 15 => '15分钟', 20 => '20分钟', 30 => '30分钟', 45 => '45分钟', 60 => '1小时', 120 => '2小时', 180 => '3小时', 240 => '4小时', 360 => '6小时', 480 => '8小时', 720 => '12小时', 1440 => '1天', 2880 => '2天', 10080 => '1周', 20160 => '2周', 43200 => '1月');
		$timearray   = array(1 => '1分钟', 2 => '2分钟', 3 => '3分钟', 4 => '4分钟', 5 => '5分钟', 10 => '10分钟', 15 => '15分钟', 20 => '20分钟', 30 => '30分钟', 45 => '45分钟', 60 => '1小时', 120 => '2小时', 180 => '3小时', 240 => '4小时', 360 => '6小时', 480 => '8小时', 720 => '12小时', 1440 => '1天', 2880 => '2天', 10080 => '1周', 20160 => '2周', 43200 => '1月');
	} else if ($step == 300) {
		$repeatarray = array(0 => '永不', 1 => '每5分钟', 2 => '每10分钟', 3 => '每15分钟', 4 => '每20分钟', 6 => '每30分钟', 8 => '每45分钟', 12 => '每小时', 24 => '每2小时', 36 => '每3小时', 48 => '每4小时', 72 => '每6小时', 96 => '每8小时', 144 => '每12小时', 288 => '每天', 576 => '每2天', 2016 => '每周', 4032 => '每2周', 8640 => '每月');
		$alertarray  = array(0 => '永不', 1 => '5分钟', 2 => '10分钟', 3 => '15分钟', 4 => '20分钟', 6 => '30分钟', 8 => '45分钟', 12 => '1小时', 24 => '2小时', 36 => '3小时', 48 => '4小时', 72 => '6小时', 96 => '8小时', 144 => '12小时', 288 => '1天', 576 => '2天', 2016 => '1周', 4032 => '2周', 8640 => '1月');
		$timearray   = array(1 => '5分钟', 2 => '10分钟', 3 => '15分钟', 4 => '20分钟', 6 => '30分钟', 8 => '45分钟', 12 => '1小时', 24 => '2小时', 36 => '3小时', 48 => '4小时', 72 => '6小时', 96 => '8小时', 144 => '12小时', 288 => '1天', 576 => '2天', 2016 => '1周', 4032 => '2周', 8640 => '1月');
	} else {
		$repeatarray = array(0 => '永不', 1 => '每次采集', 2 => '每2次采集', 3 => '每3次采集', 4 => '每4次采集', 6 => '每6次采集', 8 => '每8次采集', 12 => '每12次采集', 24 => '每24次采集', 36 => '每36次采集', 48 => '每48次采集', 72 => '每72次采集', 96 => '每96次采集', 144 => '每144次采集', 288 => '每288次采集', 576 => '每576次采集', 2016 => '每2016次采集');
		$alertarray  = array(0 => '永不', 1 => '1次采集', 2 => '2次采集', 3 => '3次采集', 4 => '4次采集', 6 => '6次采集', 8 => '8次采集', 12 => '12次采集', 24 => '24次采集', 36 => '36次采集', 48 => '48次采集', 72 => '72次采集', 96 => '96次采集', 144 => '144次采集', 288 => '288次采集', 576 => '576次采集', 2016 => '2016次采集');
		$timearray   = array(1 => '1次采集', 2 => '2次采集', 3 => '3次采集', 4 => '4次采集', 6 => '6次采集', 8 => '8次采集', 12 => '12次采集', 24 => '24次采集', 36 => '36次采集', 48 => '48次采集', 72 => '72次采集', 96 => '96次采集', 144 => '144次采集', 288 => '288次采集', 576 => '576次采集', 2016 => '2016次采集');
	}

	switch ($type) {
		case 'repeat':
			return (isset($repeatarray[$data]) ? $repeatarray[$data] : $data);
			break;
		case 'alert':
			return (isset($alertarray[$data]) ? $alertarray[$data] : $data);
			break;
		case 'time':
			return (isset($timearray[$data]) ? $timearray[$data] : $data);
			break;
	}
	return $data;
}

function plugin_thold_log_changes($id, $changed, $message = array()) {
	global $config;
	$desc = '';

	if (read_config_option('thold_log_changes') != 'on') {
		return;
	}

	if (isset($_SESSION['sess_user_id'])) {
		$user = db_fetch_row('SELECT username FROM user_auth WHERE id = ' . $_SESSION['sess_user_id']);
		$user = $user['username'];
	} else {
		$user = 'Unknown';
	}

	switch ($changed) {
		case 'enabled_threshold':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);
			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);
			$desc = "Enabled Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;
			break;
		case 'disabled_threshold':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);
			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);
			$desc = "Disabled Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;
			break;
		case 'enabled_host':
			$host = db_fetch_row('SELECT * FROM host WHERE id = ' . $id);
			$desc = "User: $user  Enabled Host[$id] - " . $host['description'] . ' (' . $host['hostname'] . ')';
			break;
		case 'disabled_host':
			$host = db_fetch_row('SELECT * FROM host WHERE id = ' . $id);
			$desc = "User: $user  Disabled Host[$id] - " . $host['description'] . ' (' . $host['hostname'] . ')';
			break;
		case 'auto_created':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);
			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);
			$desc = "Auto-created Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;
			break;
		case 'created':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);
			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);
			$desc = "Created Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;
			break;
		case 'deleted':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);
			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);
			$desc = "Deleted Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;
			break;
		case 'deleted_template':
			$thold = db_fetch_row('SELECT * FROM thold_template WHERE id = ' . $id, FALSE);
			$desc = "Deleted Template  User: $user  ID: $id";
			$desc .= '  DataTemplate: ' . $thold['data_template_name'];
			$desc .= '  DataSource: ' . $thold['data_source_name'];
			break;
		case 'modified':
			$thold = db_fetch_row('SELECT * FROM thold_data WHERE id = ' . $id, FALSE);

			$rows = db_fetch_assoc('SELECT plugin_thold_contacts.data FROM plugin_thold_contacts, plugin_thold_threshold_contact WHERE plugin_thold_contacts.id = plugin_thold_threshold_contact.contact_id AND plugin_thold_threshold_contact.thold_id = ' . $id);
			$alert_emails = array();
			if (count($rows)) {
				foreach ($rows as $row) {
				$alert_emails[] = $row['data'];
				}
			}
			$alert_emails = implode(',', $alert_emails);
			if ($alert_emails != '') {
				$alert_emails .= ',' . $thold['notify_extra'];
			} else {
				$alert_emails = $thold['notify_extra'];
			}

			if ($message['id'] > 0) {
				$desc = "Modified Threshold  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			} else {
				$desc = "Created Threshold  User: $user  ID:  <a href='" . $config['url_path'] . "plugins/thold/thold.php?rra=" . $thold['rra_id'] . "&view_rrd=" . $thold['data_id'] . "'>$id</a>";
			}

			$tname = db_fetch_cell('SELECT name FROM data_template WHERE id=' . $thold['data_template']);
			$ds = db_fetch_cell('SELECT data_source_name FROM data_template_rrd WHERE id=' . $thold['data_id']);

			$desc .= '  DataTemplate: ' . $tname;
			$desc .= '  DataSource: ' . $ds;

			if ($message['template_enabled'] == 'on') {
				$desc .= '  Use Template: On';
			} else {
				$types = array('High/Low', 'Baseline', 'Time Based');
				$desc .= '  Type: ' . $types[$message['thold_type']];
				$desc .= '  Enabled: ' . $message['thold_enabled'];
				switch ($message['thold_type']) {
					case 0:
						$desc .= '  High: ' . $message['thold_hi'];
						$desc .= '  Low: ' . $message['thold_low'];
						$desc .= '  Trigger: ' . plugin_thold_duration_convert($thold['rra_id'], $message['thold_fail_trigger'], 'alert');
						break;
					case 1:
						$desc .= '  Enabled: ' . $message['bl_enabled'];
						$desc .= '  Reference: ' . $message['bl_ref_time'];
						$desc .= '  Range: ' . $message['bl_ref_time_range'];
						$desc .= '  Dev Up: ' . $message['bl_pct_down'];
						$desc .= '  Dev Down: ' . $message['bl_pct_up'];
						$desc .= '  Trigger: ' . $message['bl_fail_trigger'];
						break;
					case 2:
						$desc .= '  High: ' . $message['time_hi'];
						$desc .= '  Low: ' . $message['time_low'];
						$desc .= '  Trigger: ' . $message['time_fail_trigger'];
						$desc .= '  Time: ' . plugin_thold_duration_convert($thold['rra_id'], $message['time_fail_length'], 'time');
						break;
				}
				$desc .= '  CDEF: ' . $message['cdef'];
				$desc .= '  ReAlert: ' . plugin_thold_duration_convert($thold['rra_id'], $message['repeat_alert'], 'alert');
				$desc .= '  Emails: ' . $alert_emails;
			}
			break;
		case 'modified_template':
			$thold = db_fetch_row('SELECT * FROM thold_template WHERE id = ' . $id, FALSE);

			$rows = db_fetch_assoc('SELECT plugin_thold_contacts.data FROM plugin_thold_contacts, plugin_thold_template_contact WHERE plugin_thold_contacts.id = plugin_thold_template_contact.contact_id AND plugin_thold_template_contact.template_id = ' . $id);
			$alert_emails = array();
			if (count($rows)) {
				foreach ($rows as $row) {
				$alert_emails[] = $row['data'];
				}
			}
			$alert_emails = implode(',', $alert_emails);
			if ($alert_emails != '') {
				$alert_emails .= ',' . $thold['notify_extra'];
			} else {
				$alert_emails = $thold['notify_extra'];
			}

			if ($message['id'] > 0) {
				$desc = "Modified Template  User: $user  ID: <a href='" . $config['url_path'] . "plugins/thold/thold_templates.php?action=edit&id=$id'>$id</a>";
			} else {
				$desc = "Created Template  User: $user  ID:  <a href='" . $config['url_path'] . "plugins/thold/thold_templates.php?action=edit&id=$id'>$id</a>";
			}

			$desc .= '  DataTemplate: ' . $thold['data_template_name'];
			$desc .= '  DataSource: ' . $thold['data_source_name'];

			$types = array('High/Low', 'Baseline', 'Time Based');
			$desc .= '  Type: ' . $types[$message['thold_type']];
			$desc .= '  Enabled: ' . $message['thold_enabled'];
			switch ($message['thold_type']) {
				case 0:
					$desc .= '  High: ' . (isset($message['thold_hi']) ? $message['thold_hi'] : '');
					$desc .= '  Low: ' . (isset($message['thold_low']) ? $message['thold_low'] : '');
					$desc .= '  Trigger: ' . plugin_thold_duration_convert($thold['data_template_id'], (isset($message['thold_fail_trigger']) ? $message['thold_fail_trigger'] : ''), 'alert', 'data_template_id');
					break;
				case 1:
					$desc .= '  Enabled: ' . $message['bl_enabled'];
					$desc .= '  Reference: ' . $message['bl_ref_time'];
					$desc .= '  Range: ' . $message['bl_ref_time_range'];
					$desc .= '  Dev Up: ' . $message['bl_pct_down'];
					$desc .= '  Dev Down: ' . $message['bl_pct_up'];
					$desc .= '  Trigger: ' . $message['bl_fail_trigger'];
					break;
				case 2:
					$desc .= '  High: ' . $message['time_hi'];
					$desc .= '  Low: ' . $message['time_low'];
					$desc .= '  Trigger: ' . $message['time_fail_trigger'];
					$desc .= '  Time: ' . plugin_thold_duration_convert($thold['data_template_id'], $message['time_fail_length'], 'alert', 'data_template_id');
					break;
			}
			$desc .= '  CDEF: ' . (isset($message['cdef']) ? $message['cdef']: '');
			$desc .= '  ReAlert: ' . plugin_thold_duration_convert($thold['data_template_id'], $message['repeat_alert'], 'alert', 'data_template_id');
			$desc .= '  Emails: ' . $alert_emails;
			break;
	}

	if ($desc != '') {
		thold_cacti_log($desc);
	}
}

function thold_check_threshold ($rra_id, $data_id, $name, $currentval, $cdef) {
	global $config;

	// Maybe set an option for these?
	$debug = false;

	// Do not proceed if we have chosen to globally disable all alerts
	if (read_config_option('thold_disable_all') == 'on') {
		return;
	}

	$alert_exempt = read_config_option('alert_exempt');
	/* check for exemptions */
	$weekday = date('l');
	if (($weekday == 'Saturday' || $weekday == 'Sunday') && $alert_exempt == 'on') {
		return;
	}

	/* Get all the info about the item from the database */
	$item = db_fetch_assoc("SELECT * FROM thold_data WHERE thold_enabled = 'on' AND data_id = " . $data_id);

	/* Return if the item doesn't exist, which means its disabled */
	if (!isset($item[0]))
		return;
	$item = $item[0];

	/* Check for the weekend exemption on the threshold level */
	if (($weekday == 'Saturday' || $weekday == 'Sunday') && $item['exempt'] == 'on') {
		return;
	}

	$graph_id = $item['graph_id'];

	// Only alert if Host is in UP mode (not down, unknown, or recovering)
	$h = db_fetch_row('SELECT * FROM host WHERE id = ' . $item['host_id']);
	if ($h['status'] != 3) {
		return;
	}

	/* Pull the cached name, if not present, it means that the graph hasn't polled yet */
	$t = db_fetch_assoc('SELECT id, name, name_cache FROM data_template_data WHERE local_data_id = ' . $rra_id . ' ORDER BY id LIMIT 1');
	if (isset($t[0]['name_cache']))
		$desc = $t[0]['name_cache'];
	else
		return;
	/* Pull a few default settings */
	$global_alert_address = read_config_option('alert_email');
	$global_notify_enabled = (read_config_option('alert_notify_default') == 'on');
	$global_bl_notify_enabled = (read_config_option('alert_notify_bl') == 'on');
	$logset = (read_config_option('alert_syslog') == 'on');
	$deadnotify = (read_config_option('alert_deadnotify') == 'on');
	$realert = read_config_option('alert_repeat');
	$alert_trigger = read_config_option('alert_trigger');
	$alert_bl_trigger = read_config_option('alert_bl_trigger');
	$httpurl = read_config_option('alert_base_url');
	$thold_show_datasource = read_config_option('thold_show_datasource');
	$thold_send_text_only = read_config_option('thold_send_text_only');
	$thold_alert_text = read_config_option('thold_alert_text');
	$thold_sms_text=read_config_option('thold_alert_message');

	// Remove this after adding an option for it
	$thold_show_datasource = true;

	$trigger = ($item['thold_fail_trigger'] == '' ? $alert_trigger : $item['thold_fail_trigger']);
	$alertstat = $item['thold_alert'];

	// Make sure the alert text has been set
	if (!isset($thold_alert_text) || $thold_alert_text == '') {
		$thold_alert_text = "<html><body>请注意,已产生一个新的报警.<br><br><strong>主机</strong>: <DESCRIPTION> (<HOSTNAME>)<br><strong>URL</strong>: <URL><br><strong>消息</strong>: <SUBJECT><br><br><GRAPH></body></html>";
	}

	if(!isset($thold_sms_text)||$thold_sms_text==''){
		$thold_sms_text='主机:<DESCRIPTION>(<HOSTNAME>)产生一个消息:<SUBJECT>';
	}

	$hostname = db_fetch_row('SELECT description, hostname from host WHERE id = ' . $item['host_id']);

	$rows = db_fetch_assoc('SELECT plugin_thold_contacts.data FROM plugin_thold_contacts, plugin_thold_threshold_contact WHERE plugin_thold_contacts.id = plugin_thold_threshold_contact.contact_id AND plugin_thold_threshold_contact.thold_id = ' . $item['id']);
	$alert_emails = array();
	if (count($rows)) {
		foreach ($rows as $row) {
			$alert_emails[] = $row['data'];
		}
	}
	$alert_emails = implode(',', $alert_emails);
	if ($alert_emails != '') {
		$alert_emails .= ',' . $item['notify_extra'];
	} else {
		$alert_emails = $item['notify_extra'];
	}

	$types = array('上/下限', '基线', '基于时间');

	// Do some replacement of variables
	$thold_alert_text = str_replace('<DESCRIPTION>', $hostname['description'], $thold_alert_text);
	$thold_alert_text = str_replace('<HOSTNAME>', $hostname['hostname'], $thold_alert_text);
	$thold_alert_text = str_replace('<TIME>', time(), $thold_alert_text);
	$thold_alert_text = str_replace('<GRAPHID>', $graph_id, $thold_alert_text);
	$thold_alert_text = str_replace('<URL>', "<a href='$httpurl/graph.php?local_graph_id=$graph_id&rra_id=1'>$httpurl/graph.php?local_graph_id=$graph_id&rra_id=1</a>", $thold_alert_text);
	$thold_alert_text = str_replace('<CURRENTVALUE>', $currentval, $thold_alert_text);
	$thold_alert_text = str_replace('<THRESHOLDNAME>', $desc, $thold_alert_text);
	$thold_alert_text = str_replace('<DSNAME>', $name, $thold_alert_text);
	$thold_alert_text = str_replace('<THOLDTYPE>', $types[$item['thold_type']], $thold_alert_text);
	$thold_alert_text = str_replace('<HI>', ($item['thold_type'] == 0 ? $item['thold_hi'] : ($item['thold_type'] == 2 ? $item['time_hi'] : '')), $thold_alert_text);
	$thold_alert_text = str_replace('<LOW>', ($item['thold_type'] == 0 ? $item['thold_low'] : ($item['thold_type'] == 2 ? $item['time_low'] : '')), $thold_alert_text);
	$thold_alert_text = str_replace('<TRIGGER>', ($item['thold_type'] == 0 ? $item['thold_fail_trigger'] : ($item['thold_type'] == 2 ? $item['time_fail_trigger'] : '')), $thold_alert_text);
	$thold_alert_text = str_replace('<DURATION>', ($item['thold_type'] == 2 ? plugin_thold_duration_convert($item['rra_id'], $item['time_fail_length'], 'time') : ''), $thold_alert_text);
	$thold_alert_text = str_replace('<DATE_RFC822>', date(DATE_RFC822), $thold_alert_text);
	$thold_alert_text = str_replace('<DEVICENOTE>', $h['notes'], $thold_alert_text);

	$thold_sms_text=strtr($thold_sms_text,array(
		'<DESCRIPTION>'=>$hostname['description'],
		'<HOSTNAME>'=>$hostname['hostname']
	));

	$msg = $thold_alert_text;

	if ($thold_send_text_only == 'on') {
		$file_array = '';
	} else {
		$file_array = array(0 => array('local_graph_id' => $graph_id, 'rra_id' => 0, 'file' => "$httpurl/graph_image.php?local_graph_id=$graph_id&rra_id=0&view_type=tree",'mimetype'=>'image/png','filename'=>$graph_id));
	}

	switch ($item['thold_type']) {
		case 0:	//  HI/Low
			$breach_up = ($item['thold_hi'] != '' && $currentval > $item['thold_hi']);
			$breach_down = ($item['thold_low'] != '' && $currentval < $item['thold_low']);
			if ( $breach_up || $breach_down) {
				$item['thold_fail_count']++;
				$item['thold_alert'] = ($breach_up ? 2 : 1);

				// Re-Alert?
				$ra = ($item['thold_fail_count'] > $trigger && $item['repeat_alert'] != 0 && ($item['thold_fail_count'] % ($item['repeat_alert'] == '' ? $realert : $item['repeat_alert'])) == 0);
				$status = 1;
				if ($item['thold_fail_count'] == $trigger || $ra) {
					$status = 2;
				}
				$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . ' ' . ($ra ? '仍然' : '已经') . ' ' . ($breach_up ? '高于' : '低于') . ' 阈值: ' . ($breach_up ? $item['thold_hi'] : $item['thold_low']) . " 当前: $currentval";
				if($status == 2) {
					if ($logset == 1) {
						logger($desc, $breach_up, ($breach_up ? $item['thold_hi'] : $item['thold_low']), $currentval, $trigger, $item['thold_fail_count']);
					}
					if (trim($alert_emails) != '') {
						if($h['alert_email'])thold_mail($alert_emails, '', $subject, $msg, $file_array);
						if($h['alert_email'])thold_sms($subject,$thold_sms_text);
					}
				}

				thold_log(array(
					'type' => 0,
					'time' => time(),
					'host_id' => $item['host_id'],
					'graph_id' => $graph_id,
					'threshold_id' => $item['id'],
					'threshold_value' => ($breach_up ? $item['thold_hi'] : $item['thold_low']),
					'current' => $currentval,
					'status' => $status,
					'description' => $subject,
					'emails' => $alert_emails
					));

				$sql = "UPDATE thold_data SET thold_alert=" . $item['thold_alert'] . ", thold_fail_count=" . $item['thold_fail_count'];
				$sql .= " WHERE rra_id=$rra_id AND data_id=" . $item['data_id'];
				db_execute($sql);
			} else {
				if ($alertstat != 0) {
					if ($logset == 1)
						logger($desc, 'ok', 0, $currentval, $trigger, $item['thold_fail_count']);
					if ($item['thold_fail_count'] >= $trigger) {
						$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . " 已恢复到正常值: $currentval";
						if (trim($alert_emails) != '' && $item['restored_alert'] != 'on'&&$h['alert_email']){
							thold_mail($alert_emails,'',$subject,$msg,$file_array);
						}
						if($h['alert_sms'])thold_sms($subject,$thold_sms_text);
						thold_log(array(
							'type' => 0,
							'time' => time(),
							'host_id' => $item['host_id'],
							'graph_id' => $graph_id,
							'threshold_id' => $item['id'],
							'threshold_value' => '',
							'current' => $currentval,
							'status' => 0,
							'description' => $subject,
							'emails' => $alert_emails
							));
					}
				}
				db_execute("UPDATE thold_data SET thold_alert=0, thold_fail_count=0 WHERE rra_id=$rra_id AND data_id=" . $item['data_id']);
			}

			break;

		case 1:	//  Baseline
			$bl_alert_prev = $item['bl_alert'];
			$bl_count_prev = $item['bl_fail_count'];
			$bl_fail_trigger = ($item['bl_fail_trigger'] == '' ? $alert_bl_trigger : $item['bl_fail_trigger']);

			$item['bl_alert'] = thold_check_baseline($rra_id, $name, $item['bl_ref_time'], $item['bl_ref_time_range'], $currentval, $item['bl_pct_down'], $item['bl_pct_up']);
			switch($item['bl_alert']) {
				case -2:	// Exception is active
					// Future
					break;
				case -1:	// Reference value not available
					break;

				case 0:		// All clear
					if ($global_bl_notify_enabled && $item['bl_fail_count'] >= $bl_fail_trigger && $item['restored_alert'] != 'on') {
						$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . " 已恢复到正常值: $currentval";
						if (trim($alert_emails) != ''&&$h['alert_email']){
							thold_mail($alert_emails, '', $subject, $msg, $file_array);
						}
						if($h['alert_sms'])thold_sms($subject,$thold_sms_text);
					}
					$item['bl_fail_count'] = 0;
					break;

				case 1:		// Value is below calculated threshold
				case 2:		// Value is above calculated threshold
					$item['bl_fail_count']++;

					// Re-Alert?
					$ra = ($item['bl_fail_count'] > $bl_fail_trigger && ($item['bl_fail_count'] % ($item['repeat_alert'] == '' ? $realert : $item['repeat_alert'])) == 0);
					if($global_bl_notify_enabled && ($item['bl_fail_count'] ==  $bl_fail_trigger || $ra)) {
						if ($logset == 1) {
							logger($desc, $breach_up, ($breach_up ? $item['thold_hi'] : $item['thold_low']), $currentval, $item['thold_fail_trigger'], $item['thold_fail_count']);
						}
						$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . ' ' . ($ra ? 'is still' : 'went') . ' ' . ($item['bl_alert'] == 2 ? 'above' : 'below') . " calculated baseline threshold with $currentval";
						if (trim($alert_emails) != ''&&$h['alert_email'])
							thold_mail($alert_emails, '', $subject, $msg, $file_array);
						if($h['alert_sms'])thold_sms($subject,$thold_sms_text);
					}
					break;
			}

			$sql  = "UPDATE thold_data SET thold_alert=0, thold_fail_count=0";
			$sql .= ", bl_alert='" . $item['bl_alert'] . "'";
			$sql .= ", bl_fail_count='" . $item['bl_fail_count'] . "'";
			$sql .= " WHERE rra_id='$rra_id' AND data_id=" . $item['data_id'];
			db_execute($sql);
			break;

		case 2:	//  Time Based

			$breach_up = ($item['time_hi'] != '' && $currentval > $item['time_hi']);
			$breach_down = ($item['time_low'] != '' && $currentval < $item['time_low']);

			$item['thold_alert'] = ($breach_up ? 2 : ($breach_down ? 1 : 0));
			$trigger = $item['time_fail_trigger'];
			$step = db_fetch_cell('SELECT rrd_step FROM data_template_data WHERE local_data_id = ' . $rra_id, FALSE);
			$time = time() - ($item['time_fail_length'] * $step);
			$failures = db_fetch_cell('SELECT count(id) FROM plugin_thold_log WHERE threshold_id = ' . $item['id'] . ' AND status > 0 AND time > ' . $time);
			if ( $breach_up || $breach_down) {
				$item['thold_fail_count'] = $failures;
				// We should only re-alert X minutes after last email, not every 5 pollings, etc...
				// Re-Alert?
				$realerttime = time() - (($item['repeat_alert'] - 1) * $step);
				$lastemailtime = db_fetch_cell('SELECT time FROM plugin_thold_log WHERE threshold_id = ' . $item['id'] . ' AND status = 2 ORDER BY time DESC LIMIT 1', FALSE);
				$ra = ($failures > $trigger && $item['repeat_alert'] != 0 && $lastemailtime > 1 && ($lastemailtime < $realerttime));
				$status = 1;
				$failures++;
				if ($failures == $trigger || $ra) {
					$status = 2;
				}
				if ($item['repeat_alert'] == 0 && $failures == $trigger) {
					$lastalert = db_fetch_cell('SELECT * FROM plugin_thold_log WHERE threshold_id = ' . $item['id'] . ' ORDER BY time DESC LIMIT 1');
					if ($lastalert['status'] > 1 && $time> $lastalert['time']) {
						$status = 1;
					}
				}
				$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . ' ' . ($failures > $trigger ? '仍然' : '已经') . ' ' . ($breach_up ? '高于' : '低于') . ' 阈值: ' . ($breach_up ? $item['time_hi'] : $item['time_low']) . " 当前: $currentval";
				if ($status == 2) {
					if ($logset == 1) {
						logger($desc, $breach_up, ($breach_up ? $item['time_hi'] : $item['time_low']), $currentval, $trigger, $failures);
					}
					if (trim($alert_emails) != ''&&$h['alert_email'])
						thold_mail($alert_emails, '', $subject, $msg, $file_array);
					if($h['alert_sms'])thold_sms($subject,$thold_sms_text);
				}
				thold_log(array(
					'type' => 2,
					'time' => time(),
					'host_id' => $item['host_id'],
					'graph_id' => $graph_id,
					'threshold_id' => $item['id'],
					'threshold_value' => ($breach_up ? $item['time_hi'] : $item['time_low']),
					'current' => $currentval,
					'status' => $status,
					'description' => $subject,
					'emails' => $alert_emails
					));

				$sql  = "UPDATE thold_data SET thold_alert=" . $item['thold_alert'] . ", thold_fail_count=" . $failures;
				$sql .= " WHERE rra_id=$rra_id AND data_id=" . $item['data_id'];
				db_execute($sql);
			} else {
				if ($alertstat != 0 && $failures < $trigger) {
					if ($logset == 1)
						logger($desc, 'ok', 0, $currentval, $trigger, $item['thold_fail_count']);
					$subject = $desc . ($thold_show_datasource ? " [$name]" : '') . " 已恢复到正常值: $currentval";
					thold_log(array(
						'type' => 2,
						'time' => time(),
						'host_id' => $item['host_id'],
						'graph_id' => $graph_id,
						'threshold_id' => $item['id'],
						'threshold_value' => '',
						'current' => $currentval,
						'status' => 0,
						'description' => $subject,
						'emails' => $alert_emails
						));

					$sql  = "UPDATE thold_data SET thold_alert=0, thold_fail_count=" . $failures;
					$sql .= " WHERE rra_id=$rra_id AND data_id=" . $item['data_id'];
					db_execute($sql);
				} else {
					$sql  = "UPDATE thold_data SET thold_fail_count=" . $failures;
					$sql .= " WHERE rra_id=$rra_id AND data_id=" . $item['data_id'];
					db_execute($sql);
				}
			}
			break;
	}

	// debugging output
	if ($debug == 1) {
		$filename = $config['base_path'] . '/log/thold.log';
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, 'a')) {
				echo "无法打开文件 ($filename)";
			}
		} else {
			echo "日志文件 $filename 不可写";
		}
		$logdate = date('m-d-y.H:i:s');
		$logout = "$logdate element: $desc alertstat: $alertstat graph_id: $graph_id thold_low: " . $item['thold_low'] . ' thold_hi: ' . $item['thold_hi'] . " rra: $rra trigger: " . $trigger . ' triggerct: ' . $item['thold_fail_count'] . " current: $currentval logset: $logset";
		fwrite($handle, $logout);
		fclose($handle);
	}
}

function logger($desc, $breach_up, $threshld, $currentval, $trigger, $triggerct) {
	define_syslog_variables();

	$syslog_level = read_config_option('thold_syslog_level');
	$syslog_facility = read_config_option('thold_syslog_facility');
	if (!isset($syslog_level)) {
		$syslog_level = LOG_WARNING;
	} else if (isset($syslog_level) && ($syslog_level > 7 || $syslog_level < 0)) {
		$syslog_level = LOG_WARNING;
	}
	if (!isset($syslog_facility)) {
		$syslog_facility = LOG_DAEMON;
	}

	openlog('CactiTholdLog', LOG_PID | LOG_PERROR, $syslog_facility);

	if(strval($breach_up) == 'ok') {
		syslog($syslog_level, $desc . ' restored to normal with ' . $currentval . ' at trigger ' . $trigger . ' out of ' . $triggerct);
	} else {
		syslog($syslog_level, $desc . ' went ' . ($breach_up ? 'above' : 'below') . ' threshold of ' . $threshld . ' with ' . $currentval . ' at trigger ' . $trigger . ' out of ' . $triggerct);
	}
}

function thold_cdef_get_usable () {
	$cdef_items = db_fetch_assoc("select * from cdef_items where value = 'CURRENT_DATA_SOURCE' order by cdef_id");
	$cdef_usable = array();
	if (sizeof($cdef_items) > 0) {
		foreach ($cdef_items as $cdef_item) {
			  	$cdef_usable[] =  $cdef_item['cdef_id'];
		}
	}

	return $cdef_usable;
}

function thold_cdef_select_usable_names () {
	$ids = thold_cdef_get_usable();
	$cdefs = db_fetch_assoc('select id, name from cdef');
	$cdef_names[0] = '';
	if (sizeof($cdefs) > 0) {
		foreach ($cdefs as $cdef) {
			if (in_array($cdef['id'], $ids)) {

			  	$cdef_names[$cdef['id']] =  $cdef['name'];
			}
		}
	}
	return $cdef_names;
}

function thold_build_cdef ($id, $value, $rra, $ds) {
	$oldvalue = $value;

	$cdefs = db_fetch_assoc("select * from cdef_items where cdef_id = $id order by sequence");
	if (sizeof($cdefs) > 0) {
		foreach ($cdefs as $cdef) {
		     	if ($cdef['type'] == 4) {
				$cdef['type'] = 6;
				switch ($cdef['value']) {
				case 'CURRENT_DATA_SOURCE':
					$cdef['value'] = $oldvalue; // get_current_value($rra, $ds, 0);
					break;
				case 'CURRENT_GRAPH_MAXIMUM_VALUE':
					$cdef['value'] = get_current_value($rra, 'upper_limit', 0);
					break;
				case 'CURRENT_GRAPH_MINIMUM_VALUE':
					$cdef['value'] = get_current_value($rra, 'lower_limit', 0);
					break;
				case 'CURRENT_DS_MINIMUM_VALUE':
					$cdef['value'] = get_current_value($rra, 'rrd_minimum', 0);
					break;
				case 'CURRENT_DS_MAXIMUM_VALUE':
					$cdef['value'] = get_current_value($rra, 'rrd_maximum', 0);
					break;
				case 'VALUE_OF_HDD_TOTAL':
					$cdef['value'] = get_current_value($rra, 'hdd_total', 0);
					break;
				case 'ALL_DATA_SOURCES_NODUPS': // you can't have DUPs in a single data source, really...
				case 'ALL_DATA_SOURCES_DUPS':
					$cdef['value'] = 0;
					$all_dsns = array();
					$all_dsns = db_fetch_assoc("SELECT data_source_name FROM data_template_rrd WHERE local_data_id = $rra");
					if(is_array($all_dsns)) {
						foreach ($all_dsns as $dsn) {
							$cdef['value'] += get_current_value($rra, $dsn['data_source_name'], 0);
						}
					}
					break;
				default:
					print 'CDEF property not implemented yet: ' . $cdef['value'];
					return $oldvalue;
					break;
				}
			}
			$cdef_array[] = $cdef;
		}
	}
	$x = count($cdef_array);

	if ($x == 0) return $oldvalue;

	$stack = array(); // operation stack for RPN
	array_push($stack, $cdef_array[0]); // first one always goes on
	$cursor = 1; // current pointer through RPN operations list

	while($cursor < $x) {
		$type = $cdef_array[$cursor]['type'];
		switch($type) {
			case 6:
				array_push($stack, $cdef_array[$cursor]);
				break;
			case 2:
				// this is a binary operation. pop two values, and then use them.
				$v1 = array_pop($stack);
				$v2 = array_pop($stack);
				$result = thold_rpn($v2['value'], $v1['value'], $cdef_array[$cursor]['value']);
				// put the result back on the stack.
				array_push($stack, array('type'=>6,'value'=>$result));
				break;
			default:
				print 'Unknown RPN type: ';
				print $cdef_array[$cursor]['type'];
				return($oldvalue);
				break;
		}
		$cursor++;
	}

	return $stack[0]['value'];
}

function thold_rpn ($x, $y, $z) {
	switch ($z) {
		case 1:
			return $x + $y;
			break;
		case 2:
			return $x - $y;
			break;
		case 3:
			return $x * $y;
			break;
		case 4:
			return $x / $y;
			break;
		case 5:
			return $x % $y;
			break;
	}
	return '';
}

function delete_old_thresholds () {
	$result = db_fetch_assoc('SELECT id, data_id, rra_id FROM thold_data');
	foreach ($result as $row) {
		$ds_item_desc = db_fetch_assoc('select id, data_source_name from data_template_rrd where id = ' . $row['data_id']);
		if (!isset($ds_item_desc[0]['data_source_name'])) {
			db_execute('DELETE FROM thold_data WHERE id=' . $row['id']);
			db_execute('DELETE FROM plugin_thold_threshold_contact WHERE thold_id=' . $row['id']);
		}
	}
}

function thold_rrd_last($rra, $cf) {
	global $config;
	$last_time_entry = rrdtool_execute('last ' . trim(get_data_source_path($rra, true)) . ' ' . trim($cf), false, RRDTOOL_OUTPUT_STDOUT, $rrdtool_pipe);
	return trim($last_time_entry);
}

function get_current_value($rra, $ds, $cdef = 0) {
	global $config;
	$last_time_entry = thold_rrd_last($rra, 'AVERAGE');

	// This should fix and 'did you really mean month 899 errors', this is because your RRD has not polled yet
	if ($last_time_entry == -1)
		$last_time_entry = time();

	$data_template_data = db_fetch_row("SELECT * FROM data_template_data WHERE local_data_id = $rra");

	$step = $data_template_data['rrd_step'];

	// Round down to the nearest 100
	$last_time_entry = (intval($last_time_entry /100) * 100) - $step;
	$last_needed = $last_time_entry + $step;

	$result = rrdtool_function_fetch($rra, trim($last_time_entry), trim($last_needed));

	// Return Blank if the data source is not found (Newly created?)
	if (!isset( $result['data_source_names'])) return '';

	$idx = array_search($ds, $result['data_source_names']);

	// Return Blank if the value was not found (Cache Cleared?)
	if (!isset($result['values'][$idx][0]))
			return '';

	$value = $result['values'][$idx][0];
	if ($cdef != 0)
		$value = thold_build_cdef($cdef, $value, $rra, $ds);
	return round($value, 4);
}

function thold_get_ref_value($rra_id, $ds, $ref_time, $time_range) {
	global $config;

	$real_ref_time = time() - $ref_time;

	$result = rrdtool_function_fetch($rra_id, $real_ref_time - ($time_range / 2), $real_ref_time + ($time_range / 2));

	//print_r($result);
	//echo "\n";
	$idx = array_search($ds, $result['data_source_names']);
	if(count($result['values'][$idx]) == 0) {
		return false;
	}

	return $result['values'][$idx];
}

/* thold_check_exception_periods
 @to-do: This function should check 'globally' declared exceptions, like
 holidays etc., as well as exceptions bound to the speciffic $rra_id. $rra_id
 should inherit exceptions that are assigned on the higher level (i.e. device).

*/
function thold_check_exception_periods($rra_id, $ref_time, $ref_range) {
	// TO-DO
	// Check if the reference time falls into global exceptions
	// Check if the current time falls into global exceptions
	// Check if $rra_id + $ds have an exception (again both reference time and current time)
	// Check if there are inheritances

	// More on the exception concept:
	// -Exceptions can be one time and recurring
	// -Exceptions can be global and assigned to:
	// 	-templates
	//	-devices
	//	-data sources
	//

	return false;
}

/* thold_check_baseline -
 Should be called after hard limits have been checked and only when they are OK

 The function "goes back in time" $ref_time seconds and retrieves the data
 for $ref_range seconds. Then it finds minimum and maximum values and calculates
 allowed deviations from those values.

 @arg $rra_id - the data source to check the data
 @arg $ds - Index of the data_source in the RRD
 @arg $ref_time - Integer value representing reference offset in seconds
 @arg $ref_range - Integer value indicating reference time range in seconds
 @arg $current_value - Current "value" of the data source
 @arg $pct_down - Allowed baseline deviation in % - if set to false will not be considered
 @arg $pct_up - Allowed baseline deviation in % - if set to false will not be considered

 @returns (integer) - integer value that indicates status
   -2 if the exception is active
   -1 if the reference value is not available
   0 if the current value is within the boundaries
   1 if the current value is below the calculated threshold
   2 if the current value is above the calculated threshold
 */
function &thold_check_baseline($rra_id, $ds, $ref_time, $ref_range, $current_value, $pct_down, $pct_up) {
	global $debug;

	// First let's check if either current time or reference time falls within either
	// globally set exceptions or rra itself has some exceptios

	if(thold_check_exception_periods($rra_id, $ref_time, $ref_range)) {
		return -2;	// An exception period is blocking us out...
	}
	$ref_values = thold_get_ref_value($rra_id, $ds, $ref_time, $ref_range);

	if(!$ref_values) {
		// if($debug) echo "Baseline reference value not yet established!\n";
		return -1; // Baseline reference value not yet established
	}
	$current_value = get_current_value($rra_id,$ds);
	$ref_value_max = round(max($ref_values));
	$ref_value_min = round(min($ref_values));

	$blt_low = false;
	$blt_high = false;

	if($pct_down != '') {
		$blt_low = round($ref_value_min - ($ref_value_min * $pct_down / 100));
	}

	if($pct_up != '') {
		$blt_high = round($ref_value_max + ($ref_value_max * $pct_up / 100));
	}

	$failed = 0;

	// Check low boundary
	if($blt_low && $current_value < $blt_low) {
		$failed = 1;
	}

	// Check up boundary
	if($failed == 0 && $blt_high && $current_value > $blt_high) {
		$failed = 2;
	}

	if($debug) {
		echo "RRA: $rra_id : $ds\n";
		echo 'Ref. values count: '. count($ref_values) . "\n";
		echo "Ref. value (min): $ref_value_min\n";
		echo "Ref. value (max): $ref_value_max\n";
		echo "Cur. value: $current_value\n";
		echo "Low bl thresh: $blt_low\n";
		echo "High bl thresh: $blt_high\n";
		echo 'Check against baseline: ';
		switch($failed) {
			case 0:
			echo 'OK';
			break;

			case 1:
			echo 'FAIL: Below baseline threshold!';
			break;

			case 2:
			echo 'FAIL: Above baseline threshold!';
			break;
		}
		echo "\n";
		echo "------------------\n";
	}

	return $failed;
}

function save_thold() {
	global $rra, $banner, $hostid;

	$template_enabled = isset($_POST['template_enabled']) && $_POST['template_enabled'] == 'on' ? $_POST['template_enabled'] : 'off';
	if ($template_enabled == 'on') {
		input_validate_input_number($_POST['rra']);
		input_validate_input_number($_POST['data_template_rrd_id']);

		$rra_id = $_POST['rra'];
		if (!thold_user_auth_threshold ($rra_id)) {
			$banner = '<font color=red><strong>Permission Denied</strong></font>';
			return;
		}
		$data_id = $_POST['data_template_rrd_id'];
		$data = db_fetch_row("SELECT id, template FROM thold_data WHERE rra_id = $rra_id AND data_id = $data_id");
		thold_template_update_threshold ($data['id'], $data['template']);
		$banner = '<font color=green><strong>Record Updated</strong></font>';
		plugin_thold_log_changes($data['id'], 'modified', array('id' => $data['id'], 'template_enabled' => 'on'));
		return true;
	}

	// Make sure this is defined
	$_POST['bl_enabled'] = isset($_POST['bl_enabled']) ? 'on' : 'off';
	$_POST['thold_enabled'] = isset($_POST['thold_enabled']) ? 'on' : 'off';
	$_POST['template_enabled'] = isset($_POST['template_enabled']) ? 'on' : 'off';


	$banner = '<font color=red><strong>';
//	if (($_POST['thold_type'] == 0 && !isset($_POST['thold_hi']) || trim($_POST['thold_hi']) == '') && ($_POST['thold_type'] == 0 && !isset($_POST['thold_low']) || trim($_POST['thold_low']) == '') && (!isset($_POST['bl_ref_time']) || trim($_POST['bl_ref_time'])  == '')) {
//		$banner .= 'You must specify either &quot;High Threshold&quot; or &quot;Low Threshold&quot; or both!<br>RECORD NOT UPDATED!</strong></font>';
//		return;
//	}

	if ($_POST['thold_type'] == 0 && isset($_POST['thold_hi']) && isset($_POST['thold_low']) && trim($_POST['thold_hi']) != '' && trim($_POST['thold_low']) != '' && round($_POST['thold_low'],4) >= round($_POST['thold_hi'],4)) {
		$banner .= '不可能的阈值: &quot;阈值上限&quot; 小于或等于 &quot;阈值下限&quot;<br>设置未更新!</strong></font>';
		return;
	}

	if($_POST['thold_type'] == 1 && $_POST['bl_enabled'] == 'on') {
		$banner .= 'With baseline thresholds enabled ';
		if(!thold_mandatory_field_ok('bl_ref_time', '参考过去时间')) {
			return;
		}
		if((!isset($_POST['bl_pct_down']) || trim($_POST['bl_pct_down']) == '') && (!isset($_POST['bl_pct_up']) || trim($_POST['bl_pct_up']) == '')) {
			$banner .= '您必需指定 &quot;基线上偏差&quot; 或 &quot;基线下偏差&quot; 或同时指定!<br>设置未更新!</strong></font>';
			return;
		}
	}

	$existing = db_fetch_assoc('SELECT id FROM thold_data WHERE rra_id = ' . $rra . ' AND data_id = ' . $_POST['data_template_rrd_id']);
	$save = array();
	if (count($existing)) {
		$save['id'] = $existing[0]['id'];
	} else {
		$save['id'] = 0;
		$save['template'] = '';
	}

	input_validate_input_number(get_request_var('thold_hi'));
	input_validate_input_number(get_request_var('thold_low'));
	input_validate_input_number(get_request_var('thold_fail_trigger'));
	input_validate_input_number(get_request_var('repeat_alert'));
	input_validate_input_number(get_request_var('cdef'));
	input_validate_input_number($_POST['rra']);
	input_validate_input_number($_POST['data_template_rrd_id']);
	input_validate_input_number(get_request_var('thold_type'));
	input_validate_input_number(get_request_var('time_hi'));
	input_validate_input_number(get_request_var('time_low'));
	input_validate_input_number(get_request_var('time_fail_trigger'));
	input_validate_input_number(get_request_var('time_fail_length'));
	input_validate_input_number(get_request_var('data_type'));

	$_POST['name'] = str_replace(array("\\", '"', "'"), '', $_POST['name']);
	$save['name'] = (trim($_POST['name'])) == '' ? '' : $_POST['name'];
	$save['host_id'] = $hostid;
	$save['data_id'] = $_POST['data_template_rrd_id'];
	$save['rra_id'] = $_POST['rra'];
	$save['thold_enabled'] = isset($_POST['thold_enabled']) ? $_POST['thold_enabled'] : '';
	$save['exempt'] = isset($_POST['exempt']) ? $_POST['exempt'] : 'off';
	$save['restored_alert'] = isset($_POST['restored_alert']) ? $_POST['restored_alert'] : 'off';
	$save['thold_type'] = $_POST['thold_type'];
	// High / Low
	$save['thold_hi'] = (trim($_POST['thold_hi'])) == '' ? '' : round($_POST['thold_hi'],4);
	$save['thold_low'] = (trim($_POST['thold_low'])) == '' ? '' : round($_POST['thold_low'],4);
	$save['thold_fail_trigger'] = (trim($_POST['thold_fail_trigger'])) == '' ? '' : $_POST['thold_fail_trigger'];
	// Time Based
	$save['time_hi'] = (trim($_POST['time_hi'])) == '' ? '' : round($_POST['time_hi'],4);
	$save['time_low'] = (trim($_POST['time_low'])) == '' ? '' : round($_POST['time_low'],4);
	$save['time_fail_trigger'] = (trim($_POST['time_fail_trigger'])) == '' ? '' : $_POST['time_fail_trigger'];
	$save['time_fail_length'] = (trim($_POST['time_fail_length'])) == '' ? '' : $_POST['time_fail_length'];
	// Baseline
	$save['bl_enabled'] = isset($_POST['bl_enabled']) ? $_POST['bl_enabled'] : '';
	$save['repeat_alert'] = (trim($_POST['repeat_alert'])) == '' ? '' : $_POST['repeat_alert'];
	$save['notify_extra'] = (trim($_POST['notify_extra'])) == '' ? '' : $_POST['notify_extra'];
	$save['cdef'] = (trim($_POST['cdef'])) == '' ? '' : $_POST['cdef'];
	$save['template_enabled'] = $_POST['template_enabled'];

	$save['data_type'] = $_POST['data_type'];
	if (isset($_POST['percent_ds'])) {
		$save['percent_ds'] = $_POST['percent_ds'];
	} else {
		$save['percent_ds'] = '';
	}

	/* Get the Data Template, Graph Template, and Graph */
	$rrdsql = db_fetch_row('SELECT id, data_template_id FROM data_template_rrd WHERE local_data_id=' . $save['rra_id'] . ' ORDER BY id');
	$rrdlookup = $rrdsql['id'];
	$grapharr = db_fetch_row("SELECT local_graph_id, graph_template_id FROM graph_templates_item WHERE task_item_id=$rrdlookup and local_graph_id <> '' LIMIT 1");

	$save['graph_id'] = $grapharr['local_graph_id'];
	$save['graph_template'] = $grapharr['graph_template_id'];
	$save['data_template'] = $rrdsql['data_template_id'];

	if (!thold_user_auth_threshold ($save['rra_id'])) {
		$banner = '<font color=red><strong>无权限</strong></font>';
		return;
	}

	if($_POST['bl_enabled'] == 'on') {
		input_validate_input_number(get_request_var('bl_ref_time'));
		input_validate_input_number(get_request_var('bl_ref_time_range'));
		input_validate_input_number(get_request_var('bl_pct_down'));
		input_validate_input_number(get_request_var('bl_pct_up'));
		input_validate_input_number(get_request_var('bl_fail_trigger'));
		$save['bl_ref_time'] = (trim($_POST['bl_ref_time'])) == '' ? '' : $_POST['bl_ref_time'];
		$save['bl_ref_time_range'] = (trim($_POST['bl_ref_time_range'])) == '' ? '' : $_POST['bl_ref_time_range'];
		$save['bl_pct_down'] = (trim($_POST['bl_pct_down'])) == '' ? '' : $_POST['bl_pct_down'];
		$save['bl_pct_up'] = (trim($_POST['bl_pct_up'])) == '' ? '' : $_POST['bl_pct_up'];
		$save['bl_fail_trigger'] = (trim($_POST['bl_fail_trigger'])) == '' ? '' : $_POST['bl_fail_trigger'];
	}

	$id = sql_save($save , 'thold_data');

	if (isset($_POST['notify_accounts'])) {
		thold_save_threshold_contacts ($id, $_POST['notify_accounts']);
	}

	if ($id) {
		plugin_thold_log_changes($id, 'modified', $save);
	}

	$banner = '<font color=green><strong>设置未更新</strong></font>';
}

function thold_save_template_contacts ($id, $contacts) {
	db_execute('DELETE FROM plugin_thold_template_contact WHERE template_id = ' . $id);
	// ADD SOME SECURITY!!
	if (!empty($contacts)) {
		foreach ($contacts as $contact) {
			db_execute("INSERT INTO plugin_thold_template_contact (template_id, contact_id) VALUES ($id, $contact)");
		}
	}
}

function thold_save_threshold_contacts ($id, $contacts) {
	db_execute('DELETE FROM plugin_thold_threshold_contact WHERE thold_id = ' . $id);
	// ADD SOME SECURITY!!
	foreach ($contacts as $contact) {
		db_execute("INSERT INTO plugin_thold_threshold_contact (thold_id, contact_id) VALUES ($id, $contact)");
	}
}

function thold_mandatory_field_ok($name, $friendly_name) {
	global $banner;
	if(!isset($_POST[$name]) || (isset($_POST[$name]) && (trim($_POST[$name]) == '' || $_POST[$name] <= 0))) {
		$banner .= '&quot;' . $friendly_name . '&quot; 必需设置为正整数!<br>设置未更新!</strong></font>';
		return false;
	}
	return true;
}

// Create tholds for all possible data elements for a host
function autocreate($hostid) {
	$c = 0;
	$message = '';

	$rralist = db_fetch_assoc("SELECT id, data_template_id FROM data_local where host_id='$hostid'");

	if (!count($rralist)) {
		$_SESSION['thold_message'] = '<font size=-2>未添加阈值.</font>';
		return 0;
	}

	foreach ($rralist as $row) {
		$local_data_id = $row['id'];
		$data_template_id = $row['data_template_id'];
		$existing = db_fetch_assoc('SELECT id FROM thold_data WHERE rra_id = ' . $local_data_id . ' AND data_id = ' . $data_template_id);
		$template = db_fetch_assoc('SELECT * FROM thold_template WHERE data_template_id = ' . $data_template_id);
		if (count($existing) == 0 && count($template)) {
			$rrdlookup = db_fetch_cell("SELECT id FROM data_template_rrd WHERE local_data_id=$local_data_id order by id LIMIT 1");

			$grapharr = db_fetch_row("SELECT local_graph_id, graph_template_id FROM graph_templates_item WHERE task_item_id=$rrdlookup and local_graph_id <> '' LIMIT 1");
			$graph = (isset($grapharr['local_graph_id']) ? $grapharr['local_graph_id'] : '');

			if ($graph) {
				for ($y = 0; $y < count($template); $y++) {
					$data_source_name = $template[$y]['data_source_name'];
					$insert = array();

					$desc = db_fetch_cell('SELECT name_cache FROM data_template_data WHERE local_data_id=' . $local_data_id . ' LIMIT 1');

					$insert['name'] = $desc . ' [' . $data_source_name . ']';
					$insert['host_id'] = $hostid;
					$insert['rra_id'] = $local_data_id;
					$insert['graph_id'] = $graph;
					$insert['data_template'] = $data_template_id;
					$insert['graph_template'] = $grapharr['graph_template_id'];

					$insert['thold_hi'] = $template[$y]['thold_hi'];
					$insert['thold_low'] = $template[$y]['thold_low'];
					$insert['thold_fail_trigger'] = $template[$y]['thold_fail_trigger'];
					$insert['thold_enabled'] = $template[$y]['thold_enabled'];
					$insert['bl_enabled'] = $template[$y]['bl_enabled'];
					$insert['bl_ref_time'] = $template[$y]['bl_ref_time'];
					$insert['bl_ref_time_range'] = $template[$y]['bl_ref_time_range'];
					$insert['bl_pct_down'] = $template[$y]['bl_pct_down'];
					$insert['bl_pct_up'] = $template[$y]['bl_pct_up'];
					$insert['bl_fail_trigger'] = $template[$y]['bl_fail_trigger'];
					$insert['bl_alert'] = $template[$y]['bl_alert'];
					$insert['repeat_alert'] = $template[$y]['repeat_alert'];
					$insert['notify_extra'] = $template[$y]['notify_extra'];
					$insert['cdef'] = $template[$y]['cdef'];
					$insert['template'] = $template[$y]['id'];
					$insert['template_enabled'] = 'on';

					$rrdlist = db_fetch_assoc("SELECT id, data_input_field_id FROM data_template_rrd where local_data_id='$local_data_id' and data_source_name = '$data_source_name'");

					$int = array('id', 'data_template_id', 'data_source_id', 'thold_fail_trigger', 'bl_ref_time', 'bl_ref_time_range', 'bl_pct_down', 'bl_pct_up', 'bl_fail_trigger', 'bl_alert', 'repeat_alert', 'cdef');
					foreach ($rrdlist as $rrdrow) {
						$data_rrd_id=$rrdrow['id'];
						$insert['data_id'] = $data_rrd_id;
						$existing = db_fetch_assoc("SELECT id FROM thold_data WHERE rra_id='$local_data_id' AND data_id='$data_rrd_id'");
						if (count($existing) == 0) {
							$insert['id'] = 0;
							$id = sql_save($insert, 'thold_data');
							if ($id) {
								thold_template_update_threshold ($id, $insert['template']);

								$l = db_fetch_assoc("SELECT name FROM data_template where id=$data_template_id");
								$tname = $l[0]['name'];

								$name = $data_source_name;
								if ($rrdrow['data_input_field_id'] != 0) {
									$l = db_fetch_assoc('SELECT name FROM data_input_fields where id=' . $rrdrow['data_input_field_id']);
									$name = $l[0]['name'];
								}
								plugin_thold_log_changes($id, 'auto_created', " $tname [$name]");
								$message .= "为图形: '<i>$tname</i>' 添加阈值,使用数据源: '<i>$name</i>'<br>";
								$c++;
							}
						}
					}
				}
			}
		}
	}
	$_SESSION['thold_message'] = "<font size=-2>$message</font>";
	return $c;
}

/* Sends a group of graphs to a user */
function thold_mail($to, $from, $subject, $message, $filename, $headers = '') {
	global $config;
	include_once($config['base_path'] . '/plugins/settings/include/mailer.php');
	include_once($config['base_path'] . '/plugins/thold/setup.php');
	
	$subject = iconv("UTF-8", "GB2312//IGNORE", $subject);
	$subject = trim($subject);
	$message = iconv("UTF-8", "GB2312//IGNORE", $message);
	$message = str_replace('<SUBJECT>', $subject, $message);

	$how = read_config_option('settings_how');
	if ($how < 0 && $how > 2)
		$how = 0;
	if ($how == 0) {
		$Mailer = new Mailer(array(
			'Type' => 'PHP'));
	} else if ($how == 1) {
		$sendmail = read_config_option('settings_sendmail_path');
		$Mailer = new Mailer(array(
			'Type' => 'DirectInject',
			'DirectInject_Path' => $sendmail));
	} else if ($how == 2) {
		$smtp_host = read_config_option('settings_smtp_host');
		$smtp_port = read_config_option('settings_smtp_port');
		$smtp_username = read_config_option('settings_smtp_username');
		$smtp_password = read_config_option('settings_smtp_password');

		$Mailer = new Mailer(array(
			'Type' => 'SMTP',
			'SMTP_Host' => $smtp_host,
			'SMTP_Port' => $smtp_port,
			'SMTP_Username' => $smtp_username,
			'SMTP_Password' => $smtp_password));
	}

	if ($from == '') {
		$from = read_config_option('thold_from_email');
		$fromname = read_config_option('thold_from_name');
		$fromname = iconv("UTF-8", "GB2312//IGNORE", $fromname);
		if ($from == '') {
			if (isset($_SERVER['HOSTNAME'])) {
				$from = 'Cacti@' . $_SERVER['HOSTNAME'];
			} else {
				$from = 'Cacti@localhost';
			}
		}
		if ($fromname == '')
//			$fromname = 'Cacti';
			$fromname = iconv("UTF-8", "GB2312//IGNORE", "BOMP");
		$from = $Mailer->email_format($fromname, $from);
		if ($Mailer->header_set('From', $from) === false) {
			print 'ERROR: ' . $Mailer->error() . "\n";
			return $Mailer->error();
		}
	} else {
		$from = $Mailer->email_format('Cacti', $from);
		if ($Mailer->header_set('From', $from) === false) {
			print 'ERROR: ' . $Mailer->error() . "\n";
			return $Mailer->error();
		}
	}

	if ($to == '')
		return 'Mailer Error: No <b>TO</b> address set!!<br>If using the <i>Test Mail</i> link, please set the <b>Alert e-mail</b> setting.';
	$to = explode(',', $to);

	foreach($to as $t) {
		if (trim($t) != '' && !$Mailer->header_set('To', $t)) {
			print 'ERROR: ' . $Mailer->error() . "\n";
			return $Mailer->error();
		}
	}

	$wordwrap = read_config_option('settings_wordwrap');
	if ($wordwrap == '')
		$wordwrap = 76;
	if ($wordwrap > 9999)
		$wordwrap = 9999;
	if ($wordwrap < 0)
		$wordwrap = 76;

	$Mailer->Config['Mail']['WordWrap'] = $wordwrap;

	if (! $Mailer->header_set('Subject', $subject)) {
		print 'ERROR: ' . $Mailer->error() . "\n";
		return $Mailer->error();
	}

	if (is_array($filename) && !empty($filename) && strstr($message, '<GRAPH>') !==0) {
		foreach($filename as $val) {
			$graph_data_array = array('output_flag'=> RRDTOOL_OUTPUT_STDOUT);
  			$data = rrdtool_function_graph($val['local_graph_id'], $val['rra_id'], $graph_data_array);
			if ($data != '') {
				$cid = $Mailer->content_id();
				if ($Mailer->attach($data, $val['filename'].'.png', 'image/png', 'inline', $cid) == false) {
					print 'ERROR: ' . $Mailer->error() . "\n";
					return $Mailer->error();
				}
				$message = str_replace('<GRAPH>', "<br><br><img src='cid:$cid'>", $message);
			} else {
				$message = str_replace('<GRAPH>', "<br><img src='" . $val['file'] . "'><br>无法打开!<br>" . $val['file'], $message);
			}
		}
	}
	$text = array('text' => '', 'html' => '');
	if ($filename == '') {
		$message = str_replace('<br>',  "\n", $message);
		$message = str_replace('<BR>',  "\n", $message);
		$message = str_replace('</BR>', "\n", $message);
		$text['text'] = strip_tags($message);
	} else {
		$text['html'] = $message . '<br>';
		$text['text'] = strip_tags(str_replace('<br>', "\n", $message));
	}

	$v = thold_version();
	$Mailer->header_set('X-Mailer', 'Cacti');
	$Mailer->header_set('User-Agent', 'Cacti');

	if ($Mailer->send($text) == false) {
		print 'ERROR: ' . $Mailer->error() . "\n";
		return $Mailer->error();
	}

	return '';
}

function thold_sms($subject,$message){
	$phones=read_config_option('alert_phone');
	if($phones!=''){
		$phones=explode(',',$phones);
		$path=read_config_option('thold_phone_path');
		$message=trim(str_replace('<SUBJECT>',$subject,$message));
		foreach($phones as $phone){
			if(strlen($phone)==11){
				exec($path.' '.$phone.' \''.$message.'\'');
			}
		}
	}
}

function thold_template_update_threshold ($id, $template) {
	db_execute("UPDATE thold_data, thold_template
		SET thold_data.thold_hi = thold_template.thold_hi,
		thold_data.template_enabled = 'on',
		thold_data.thold_low = thold_template.thold_low,
		thold_data.thold_fail_trigger = thold_template.thold_fail_trigger,
		thold_data.time_hi = thold_template.time_hi,
		thold_data.time_low = thold_template.time_low,
		thold_data.time_fail_trigger = thold_template.time_fail_trigger,
		thold_data.time_fail_length = thold_template.time_fail_length,
		thold_data.thold_enabled = thold_template.thold_enabled,
		thold_data.thold_type = thold_template.thold_type,
		thold_data.bl_enabled = thold_template.bl_enabled,
		thold_data.bl_ref_time = thold_template.bl_ref_time,
		thold_data.bl_ref_time_range = thold_template.bl_ref_time_range,
		thold_data.bl_pct_down = thold_template.bl_pct_down,
		thold_data.bl_fail_trigger = thold_template.bl_fail_trigger,
		thold_data.bl_alert = thold_template.bl_alert,
		thold_data.repeat_alert = thold_template.repeat_alert,
		thold_data.notify_extra = thold_template.notify_extra,
		thold_data.data_type = thold_template.data_type,
		thold_data.cdef = thold_template.cdef,
		thold_data.percent_ds = thold_template.percent_ds,
		thold_data.exempt = thold_template.exempt,
		thold_data.data_template = thold_template.data_template_id,
		thold_data.restored_alert = thold_template.restored_alert
		WHERE thold_data.id=$id AND thold_template.id=$template");
	db_execute('DELETE FROM plugin_thold_threshold_contact where thold_id = ' . $id);
	db_execute("INSERT INTO plugin_thold_threshold_contact (thold_id, contact_id) SELECT $id, contact_id FROM plugin_thold_template_contact WHERE template_id = $template");
}

function thold_template_update_thresholds ($id) {
	db_execute("UPDATE thold_data, thold_template
		SET thold_data.thold_hi = thold_template.thold_hi,
		thold_data.thold_low = thold_template.thold_low,
		thold_data.thold_fail_trigger = thold_template.thold_fail_trigger,
		thold_data.time_hi = thold_template.time_hi,
		thold_data.time_low = thold_template.time_low,
		thold_data.time_fail_trigger = thold_template.time_fail_trigger,
		thold_data.time_fail_length = thold_template.time_fail_length,
		thold_data.thold_enabled = thold_template.thold_enabled,
		thold_data.thold_type = thold_template.thold_type,
		thold_data.bl_enabled = thold_template.bl_enabled,
		thold_data.bl_ref_time = thold_template.bl_ref_time,
		thold_data.bl_ref_time_range = thold_template.bl_ref_time_range,
		thold_data.bl_pct_down = thold_template.bl_pct_down,
		thold_data.bl_fail_trigger = thold_template.bl_fail_trigger,
		thold_data.bl_alert = thold_template.bl_alert,
		thold_data.repeat_alert = thold_template.repeat_alert,
		thold_data.notify_extra = thold_template.notify_extra,
		thold_data.data_type = thold_template.data_type,
		thold_data.cdef = thold_template.cdef,
		thold_data.percent_ds = thold_template.percent_ds,
		thold_data.exempt = thold_template.exempt,
		thold_data.data_template = thold_template.data_template_id,
		thold_data.restored_alert = thold_template.restored_alert
		WHERE thold_data.template=$id AND thold_data.template_enabled='on' AND thold_template.id=$id");
	$rows = db_fetch_assoc("SELECT id, template FROM thold_data WHERE thold_data.template=$id AND thold_data.template_enabled='on'");

	foreach ($rows as $row) {
		db_execute('DELETE FROM plugin_thold_threshold_contact where thold_id = ' . $row['id']);
		db_execute('INSERT INTO plugin_thold_threshold_contact (thold_id, contact_id) SELECT ' . $row['id'] . ', contact_id FROM plugin_thold_template_contact WHERE template_id = ' . $row['template']);
	}
}

function thold_cacti_log($string) {
	global $config;
	$environ = 'THOLD';
	/* fill in the current date for printing in the log */
	$date = date("m/d/Y h:i:s A");

	/* determine how to log data */
	$logdestination = read_config_option("log_destination");
	$logfile        = read_config_option("path_cactilog");

	/* format the message */
	$message = "$date - " . $environ . ": " . $string . "\n";

	/* Log to Logfile */
	if ((($logdestination == 1) || ($logdestination == 2)) && (read_config_option("log_verbosity") != POLLER_VERBOSITY_NONE)) {
		if ($logfile == "") {
			$logfile = $config["base_path"] . "/log/cacti.log";
		}

		/* echo the data to the log (append) */
		$fp = @fopen($logfile, "a");

		if ($fp) {
			@fwrite($fp, $message);
			fclose($fp);
		}
	}

	/* Log to Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if (($logdestination == 2) || ($logdestination == 3)) {
		$string = strip_tags($string);
		$log_type = "";
		if (substr_count($string,"ERROR:"))
			$log_type = "err";
		else if (substr_count($string,"WARNING:"))
			$log_type = "warn";
		else if (substr_count($string,"STATS:"))
			$log_type = "stat";
		else if (substr_count($string,"NOTICE:"))
			$log_type = "note";

		if (strlen($log_type)) {
			define_syslog_variables();

			if ($config["cacti_server_os"] == "win32")
				openlog("Cacti", LOG_NDELAY | LOG_PID, LOG_USER);
			else
				openlog("Cacti", LOG_NDELAY | LOG_PID, LOG_SYSLOG);

			if (($log_type == "err") && (read_config_option("log_perror"))) {
				syslog(LOG_CRIT, $environ . ": " . $string);
			}

			if (($log_type == "warn") && (read_config_option("log_pwarn"))) {
				syslog(LOG_WARNING, $environ . ": " . $string);
			}

			if ((($log_type == "stat") || ($log_type == "note")) && (read_config_option("log_pstats"))) {
				syslog(LOG_INFO, $environ . ": " . $string);
			}

			closelog();
		}
	}
}

function thold_threshold_enable($id) {
	db_execute("UPDATE thold_data SET thold_enabled='on' WHERE id=$id");
}

function thold_threshold_disable($id) {
	db_execute("UPDATE thold_data SET thold_enabled='off' WHERE id=$id");
}
