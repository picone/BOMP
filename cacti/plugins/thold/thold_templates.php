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

chdir('../../');

include_once('./include/auth.php');
include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

$ds_actions = array(
	1 => 'Delete'
	);

$action = '';
if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

if (isset($_POST['drp_action']) && $_POST['drp_action'] == 1) {
	$action = 'delete';
}

switch ($action) {
	case 'add':
		template_add();
		break;
	case 'edit':
		include_once('./include/top_header.php');
		template_edit();
		include_once('./include/bottom_footer.php');
		break;
	case 'save':
		if (isset($_POST['save']) && $_POST['save'] == 'edit') {
			template_save_edit();

			if (isset($_SESSION["graph_return"])) {
				$return_to = $_SESSION["graph_return"];
				unset($_SESSION["graph_return"]);
				kill_session_var("graph_return");
				header('Location: ' . $return_to);
			}
		} else if (isset($_POST['save']) && $_POST['save'] == 'add') {

		}
		break;
	case 'delete':
		template_delete();
		break;
	default:
		include_once('./include/top_header.php');
		templates();
		include_once('./include/bottom_footer.php');
		break;
}

function template_delete() {
	foreach($_POST as $t=>$v) {
		if (substr($t, 0,4) == 'chk_') {
			$id = substr($t, 4);
			input_validate_input_number($id);
			plugin_thold_log_changes($id, 'deleted_template', array('id' => $id));
			db_fetch_assoc("delete from thold_template where id = $id LIMIT 1");
			db_execute('DELETE FROM plugin_thold_template_contact WHERE template_id=' . $id);
			db_execute("UPDATE thold_data SET template = '', template_enabled = 'off' WHERE template = $id");
		}
	}

	Header('Location: thold_templates.php');
	exit;
}

function template_add() {
	global $colors;

	if ((!isset($_REQUEST['save'])) || ($_REQUEST['save'] == '')) {
		$data_templates = array_rekey(db_fetch_assoc('select id, name from data_template order by name'), "id", "name");

		include_once('./include/top_header.php');

		html_start_box('<strong>阈值模板添加向导</strong>', '50%', $colors['header'], '3', 'center', '');

		print "<tr><td><form action=thold_templates.php method='post' name='tholdform'>";

		if (!isset($_REQUEST["data_template_id"])) $_REQUEST["data_template_id"] = '';
		if (!isset($_REQUEST["data_source_id"])) $_REQUEST["data_source_id"] = '';

		if ($_REQUEST["data_template_id"] == '') {
			print '<center><h3>请选择一个数据模板</h3></center>';
		} else if ($_REQUEST["data_source_id"] == '') {
			print '<center><h3>请选择一个数据源</h3></center>';
		} else {
			print '<center><h3>请点击 "添加" 按钮添加您的新阈值模板</h3></center>';
		}

		/* display the data template dropdown */
		?>
		<center><table>
			<tr>
				<td width='70' style='white-space:nowrap;'>
					&nbsp;<b>数据模板:</b>
				</td>
				<td style='width:1;'>
					<select name=data_template_id onChange="applyTholdFilterChange(document.tholdform, 'dt')">
						<option value=""></option><?php
						foreach ($data_templates as $id => $name) {
							echo "<option value='" . $id . "'" . ($id == $_REQUEST['data_template_id'] ? ' selected' : '') . '>' . $name . '</option>';
						}?>
					</select>
				</td>
			</tr><?php

		if ($_REQUEST['data_template_id'] != '') {
			$data_template_id = $_REQUEST['data_template_id'];
			$data_fields      = array();
			$temp             = db_fetch_assoc('select id, local_data_template_rrd_id, data_source_name, data_input_field_id from data_template_rrd where local_data_template_rrd_id = 0 and data_template_id = ' . $data_template_id);

			foreach ($temp as $d) {
				if ($d['data_input_field_id'] != 0) {
					$temp2 = db_fetch_assoc('select name, data_name from data_input_fields where id = ' . $d['data_input_field_id']);
					$data_fields[$d['id']] = $temp2[0]['data_name'] . ' (' . $temp2[0]['name'] . ')';
				} else {
					$temp2[0]['name'] = $d['data_source_name'];
					$data_fields[$d['id']] = $temp2[0]['name'];
				}
			}

			/* display the data source dropdown */
			?>
			<tr>
				<td width='70' style='white-space:nowrap;'>
					&nbsp;<b>数据源:</b>
				</td>
				<td>
					<select id='data_source_id' name='data_source_id' onChange="applyTholdFilterChange(document.tholdform, 'ds')">
						<option value=""></option><?php
						foreach ($data_fields as $id => $name) {
							echo "<option value='" . $id . "'" . ($id == $_REQUEST['data_source_id'] ? ' selected' : '') . '>' . $name . '</option>';
						}?>
					</select>
				</td>
			</tr>
			<?php
		}

		if ($_REQUEST["data_source_id"] != '') {
			echo '<tr><td colspan=2><input type=hidden name=action value="add"><input id="save" type=hidden name="save" value="save"><br><center><input type=image src="../../images/button_create.gif" alt="Create"></center></td></tr>';
		} else {
			echo '<tr><td colspan=2><input type=hidden name=action value="add"><br><br><br></td></tr>';
		}
		echo '</table></form></td></tr>';
		html_end_box();
		include_once('./include/bottom_footer.php');
	}else{
		$data_template_id = $_REQUEST['data_template_id'];
		$data_source_id = $_REQUEST['data_source_id'];

		$save['id'] = '';
		$save['data_template_id'] = $data_template_id;

		$temp = db_fetch_assoc('select id, name from data_template where id=' . $data_template_id);
		$save['name'] = $temp[0]['name'];
		$save['data_template_name'] = $temp[0]['name'];
		$save['data_source_id'] = $data_source_id;

		$temp = db_fetch_assoc('select id, local_data_template_rrd_id, data_source_name, data_input_field_id from data_template_rrd where id = ' . $data_source_id);

		$save['data_source_name'] = $temp[0]['data_source_name'];
		$save['name'] .= ' [' . $temp[0]['data_source_name'] . ']';

		if ($temp[0]['data_input_field_id'] != 0)
			$temp2 = db_fetch_assoc('select name from data_input_fields where id = ' . $temp[0]['data_input_field_id']);
		else
			$temp2[0]['name'] = $temp[0]['data_source_name'];

		$save['data_source_friendly'] = $temp2[0]['name'];
		$save['thold_enabled'] = 'on';
		$save['thold_type'] = 0;
		$save['bl_enabled'] = 'off';
		$save['repeat_alert'] = read_config_option('alert_repeat');
		$id = sql_save($save, 'thold_template');

		if ($id) {
			plugin_thold_log_changes($id, 'modified_template', $save);
			Header("Location: thold_templates.php?action=edit&id=$id");
			exit;
		} else {
			raise_message('thold_save');
			Header('Location: thold_templates.php?action=add');
			exit;
		}
	}
}

function template_save_edit() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_post('id'));
	input_validate_input_number(get_request_var_post('thold_type'));
	input_validate_input_number(get_request_var_post('thold_hi'));
	input_validate_input_number(get_request_var_post('thold_low'));
	input_validate_input_number(get_request_var_post('thold_fail_trigger'));
	input_validate_input_number(get_request_var_post('time_hi'));
	input_validate_input_number(get_request_var_post('time_low'));
	input_validate_input_number(get_request_var_post('time_fail_trigger'));
	input_validate_input_number(get_request_var_post('time_fail_length'));
	input_validate_input_number(get_request_var_post('bl_ref_time'));
	input_validate_input_number(get_request_var_post('bl_ref_time_range'));
	input_validate_input_number(get_request_var_post('bl_pct_down'));
	input_validate_input_number(get_request_var_post('bl_pct_up'));
	input_validate_input_number(get_request_var_post('bl_fail_trigger'));
	input_validate_input_number(get_request_var_post('repeat_alert'));
	input_validate_input_number(get_request_var_post('data_type'));
	input_validate_input_number(get_request_var_post('cdef'));
	/* ==================================================== */

	/* clean up date1 string */
	if (isset($_POST['name'])) {
		$_POST['name'] = trim(str_replace(array("\\", "'", '"'), '', get_request_var_post('name')));
	}

	/* save: data_template */
	$save['id'] = $_POST['id'];
	$save['name'] = $_POST['name'];
	$save['thold_type'] = $_POST['thold_type'];

	// High / Low
	$save['thold_hi'] = $_POST['thold_hi'];
	$save['thold_low'] = $_POST['thold_low'];
	$save['thold_fail_trigger'] = $_POST['thold_fail_trigger'];
	// Time Based
	$save['time_hi'] = $_POST['time_hi'];
	$save['time_low'] = $_POST['time_low'];

	$save['time_fail_trigger'] = $_POST['time_fail_trigger'];
	$save['time_fail_length'] = $_POST['time_fail_length'];

	if (isset($_POST['thold_fail_trigger']) && $_POST['thold_fail_trigger'] != '')
		$save['thold_fail_trigger'] = $_POST['thold_fail_trigger'];
	else {
		$alert_trigger = read_config_option('alert_trigger');
		if ($alert_trigger != '' && is_numeric($alert_trigger))
			$save['thold_fail_trigger'] = $alert_trigger;
		else
			$save['thold_fail_trigger'] = 5;
	}

	if (isset($_POST['thold_enabled']))
		$save['thold_enabled'] = 'on';
	else
		$save['thold_enabled'] = 'off';
	if (isset($_POST['exempt']))
		$save['exempt'] = 'on';
	else
		$save['exempt'] = 'off';
	if (isset($_POST['restored_alert']))
		$save['restored_alert'] = 'on';
	else
		$save['restored_alert'] = 'off';
	if (isset($_POST['bl_enabled']))
		$save['bl_enabled'] = 'on';
	else
		$save['bl_enabled'] = 'off';
	if (isset($_POST['bl_ref_time'])  && $_POST['bl_ref_time'] != '')
		$save['bl_ref_time'] = $_POST['bl_ref_time'];
	else {
		$alert_bl_past_default = read_config_option('alert_bl_past_default');
		if ($alert_bl_past_default != '' && is_numeric($alert_bl_past_default))
			$save['bl_ref_time'] = $alert_bl_past_default;
		else
			$save['bl_ref_time'] = 86400;
	}
	if (isset($_POST['bl_ref_time_range']) && $_POST['bl_ref_time_range'] != '')
		$save['bl_ref_time_range'] = $_POST['bl_ref_time_range'];
	else {
		$alert_bl_timerange_def = read_config_option('alert_bl_timerange_def');
		if ($alert_bl_timerange_def != '' && is_numeric($alert_bl_timerange_def))
			$save['bl_ref_time_range'] = $alert_bl_timerange_def;
		else
			$save['bl_ref_time_range'] = 10800;
	}
	if (isset($_POST['bl_pct_down']) && $_POST['bl_pct_down'] != '')
		$save['bl_pct_down'] = $_POST['bl_pct_down'];
	if (isset($_POST['bl_pct_up']) && $_POST['bl_pct_up'] != '')
		$save['bl_pct_up'] = $_POST['bl_pct_up'];
	if (isset($_POST['bl_fail_trigger']) && $_POST['bl_fail_trigger'] != '')
		$save['bl_fail_trigger'] = $_POST['bl_fail_trigger'];
	else {
		$alert_bl_trigger = read_config_option('alert_bl_trigger');
		if ($alert_bl_trigger != '' && is_numeric($alert_bl_trigger))
			$save['bl_fail_trigger'] = $alert_bl_trigger;
		else
			$save['bl_fail_trigger'] = 3;
	}

	if (isset($_POST['repeat_alert']) && $_POST['repeat_alert'] != '')
		$save['repeat_alert'] = $_POST['repeat_alert'];
	else {
		$alert_repeat = read_config_option('alert_repeat');
		if ($alert_repeat != '' && is_numeric($alert_repeat))
			$save['repeat_alert'] = $alert_repeat;
		else
			$save['repeat_alert'] = 12;
	}

	$save['notify_extra'] = $_POST['notify_extra'];
	$save['cdef'] = $_POST['cdef'];


	$save['data_type'] = $_POST['data_type'];
	$save['percent_ds'] = $_POST['percent_ds'];

	if (!is_error_message()) {
		$id = sql_save($save, 'thold_template');
		if ($id) {
			raise_message(1);
			if (isset($_POST['notify_accounts'])) {
				thold_save_template_contacts ($id, $_POST['notify_accounts']);
			} else {
				thold_save_template_contacts ($id, array());
			}
			thold_template_update_thresholds ($id);

			plugin_thold_log_changes($id, 'modified_template', $save);
		}else{
			raise_message(2);
		}
	}

	if ((is_error_message()) || (empty($_POST['id']))) {
		header('Location: thold_templates.php?action=edit&id=' . (empty($id) ? $_POST['id'] : $id));
	}else{
		header('Location: thold_templates.php');
	}
}

function template_edit() {
	global $colors;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var('id'));
	/* ==================================================== */
//	if (isset($_GET['id']))
		$id = $_GET['id'];
	$thold_item_data = db_fetch_assoc('select * from thold_template where id = ' . $id);

	$thold_item_data = count($thold_item_data) > 0 ? $thold_item_data[0] : $thold_item_data;


	$temp = db_fetch_assoc('select id, name from data_template where id = ' . $thold_item_data['data_template_id']);

	foreach ($temp as $d) {
		$data_templates[$d['id']] = $d['name'];
	}

	$temp = db_fetch_assoc('select id, data_source_name, data_input_field_id from data_template_rrd where id = ' . $thold_item_data['data_source_id']);
	$source_id = $temp[0]['data_input_field_id'];

	if ($source_id != 0) {
		$temp2 = db_fetch_assoc('select id, name from data_input_fields where id = ' . $source_id);
		foreach ($temp2 as $d) {
			$data_fields[$d['id']] = $d['name'];
		}
	} else {
		$data_fields[$temp[0]['id']]= $temp[0]['data_source_name'];
	}

	$send_notification_array = array();

	$users = db_fetch_assoc("SELECT plugin_thold_contacts.id, plugin_thold_contacts.data, plugin_thold_contacts.type, user_auth.full_name FROM plugin_thold_contacts, user_auth WHERE user_auth.id = plugin_thold_contacts.user_id AND plugin_thold_contacts.data != '' ORDER BY user_auth.full_name ASC, plugin_thold_contacts.type ASC");
	if (!empty($users)) {
		foreach ($users as $user) {
			$send_notification_array[$user['id']] = $user['full_name'] . ' - ' . ucfirst($user['type']);
		}
	}
	if (isset($thold_item_data['id'])) {
		$sql = 'SELECT contact_id as id FROM plugin_thold_template_contact WHERE template_id=' . $thold_item_data['id'];
	} else {
		$sql = 'SELECT contact_id as id FROM plugin_thold_template_contact WHERE template_id=0';
	}

	$step = db_fetch_cell('SELECT rrd_step FROM data_template_data WHERE data_template_id = ' . $thold_item_data['data_template_id'], FALSE);
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

	$thold_types = array (
		0 => '上/下限设置',
		1 => '基线',
		2 => '基于次数',
	);

	$data_types = array (
		0 => '绝对值',
		1 => 'CDEF',
		2 => '百分比',
	);

	$data_fields2 = array();
	$temp = db_fetch_assoc('select id, local_data_template_rrd_id, data_source_name, data_input_field_id from data_template_rrd where local_data_template_rrd_id = 0 and data_template_id = ' . $thold_item_data['data_template_id']);
	foreach ($temp as $d) {
		if ($d['data_input_field_id'] != 0) {
			$temp2 = db_fetch_assoc('select id, name, data_name from data_input_fields where id = ' . $d['data_input_field_id'] . ' order by data_name');
			$data_fields2[$d['data_source_name']] = $temp2[0]['data_name'] . ' (' . $temp2[0]['name'] . ')';
		} else {
			$temp2[0]['name'] = $d['data_source_name'];
			$data_fields2[$d['data_source_name']] = $temp2[0]['name'];
		}
	}

	html_start_box('', '100%', $colors['header'], '3', 'center', '');
	print "<form name='THold' action=thold_templates.php method=post><input type='hidden' name='save' value='edit'><input type='hidden' name='id' value='$id'>";
	$form_array = array(
		'general_header' => array(
			'friendly_name' => '独立设置',
			'method' => 'spacer',
		),
		'name' => array(
			'friendly_name' => '模板名称',
			'method' => 'textbox',
			'max_length' => 100,
			'default' => $thold_item_data['data_template_name'] . ' [' . $thold_item_data['data_source_name'] . ']',
			//'description' => '给阈值模板设置一个名称.',
			'value' => isset($thold_item_data['name']) ? $thold_item_data['name'] : ''
		),
		'data_template_name' => array(
			'friendly_name' => '数据模板',
			'method' => 'drop_array',
			'default' => 'NULL',
			//'description' => '您使用的数据模板. (该值不可更改)',
			'value' => $thold_item_data['data_template_id'],
			'array' => $data_templates,
		),
		'data_field_name' => array(
			'friendly_name' => '数据字段',
			'method' => 'drop_array',
			'default' => 'NULL',
			//'description' => '您使用的数据字段. (该值不可更改)',
			'value' => $thold_item_data['id'],
			'array' => $data_fields,
		),
		'thold_enabled' => array(
			'friendly_name' => '启用',
			'method' => 'checkbox',
			'default' => 'on',
			//'description' => '选择是否启用该阈值.',
			'value' => isset($thold_item_data['thold_enabled']) ? $thold_item_data['thold_enabled'] : ''
		),
		'exempt' => array(
			'friendly_name' => '周末休息',
			'description' => '如果选择该选项,该阈值将不会在周末发出报警.',
			'method' => 'checkbox',
			'default' => 'off',
			'value' => isset($thold_item_data['exempt']) ? $thold_item_data['exempt'] : ''
			),
		'restored_alert' => array(
			'friendly_name' => '禁用恢复邮件',
			//'description' => '如果选择该选项,当阈值恢复到正常状态时将不会发出邮件通知.',
			'method' => 'checkbox',
			'default' => 'off',
			'value' => isset($thold_item_data['restored_alert']) ? $thold_item_data['restored_alert'] : ''
			),
		'thold_type' => array(
			'friendly_name' => '阈值类型',
			'method' => 'drop_array',
			'on_change' => 'changeTholdType()',
			'array' => $thold_types,
			'default' => read_config_option('thold_type'),
			//'description' => '该阈值的监视类型,根据不同的需要来选择.',
			'value' => isset($thold_item_data['thold_type']) ? $thold_item_data['thold_type'] : ''
		),
		'thold_header' => array(
			'friendly_name' => '上/下限设置',
			'method' => 'spacer',
		),
		'thold_hi' => array(
			'friendly_name' => '上限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果设置该值并且数据源的值高于该值,报警将被触发.',
			'value' => isset($thold_item_data['thold_hi']) ? $thold_item_data['thold_hi'] : ''
		),
		'thold_low' => array(
			'friendly_name' => '下限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果设置该值并且数据源的值低于该值,报警将会触发.',
			'value' => isset($thold_item_data['thold_low']) ? $thold_item_data['thold_low'] : ''
		),
		'thold_fail_trigger' => array(
			'friendly_name' => '容忍时长',
			'method' => 'drop_array',
			'array' => $alertarray,
			'default' => read_config_option('alert_trigger'),
			//'description' => '数据源的值高于上限或低于下限的容忍时间,超过该时间之后将会触发报警.',
			'value' => isset($thold_item_data['thold_fail_trigger']) ? $thold_item_data['thold_fail_trigger'] : ''
		),
		'time_header' => array(
			'friendly_name' => '基于次数设置',
			'method' => 'spacer',
		),
		'time_hi' => array(
			'friendly_name' => '上限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果设置该值并且数据源的值高于该值,报警将被触发.',
			'value' => isset($thold_item_data['time_hi']) ? $thold_item_data['time_hi'] : ''
		),
		'time_low' => array(
			'friendly_name' => '下限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果设置该值并且数据源的值低于该值,报警将会触发.',
			'value' => isset($thold_item_data['time_low']) ? $thold_item_data['time_low'] : ''
		),
		'time_fail_trigger' => array(
			'friendly_name' => '容忍次数',
			'method' => 'textbox',
			'max_length' => 5,
			'default' => read_config_option('thold_time_fail_trigger'),
			//'description' => '数据源的值高于上限或低于下限的连续次数,超过该次数之后将会触发报警.',
			'value' => isset($thold_item_data['time_fail_trigger']) ? $thold_item_data['time_fail_trigger'] : ''
		),
		'time_fail_length' => array(
			'friendly_name' => '容忍时长',
			'method' => 'drop_array',
			'array' => $timearray,
			'default' => (read_config_option('thold_time_fail_length') > 0 ? read_config_option('thold_time_fail_length') : 1),
			//'description' => '数据源的值高于上限或低于下限的容忍时间,超过该时间之后将会触发报警.',
			'value' => isset($thold_item_data['time_fail_length']) ? $thold_item_data['time_fail_length'] : ''
		),
		'baseline_header' => array(
			'friendly_name' => '基线设置',
			'method' => 'spacer',
		),
		'bl_enabled' => array(
			'friendly_name' => '基线监视',
			'method' => 'checkbox',
			'default' => 'off',
			//'description' => '当启用该选项,基线监视将会检查当前数据源的值并对比过去的值,您还需要以下面指定过去值的时间范围和高于基线偏差和低于基线偏差,超过这个偏差将会产生报警.',
			'value' => isset($thold_item_data['bl_enabled']) ? $thold_item_data['bl_enabled'] : ''
		),
		'bl_ref_time' => array(
			'friendly_name' => '参考过去时间',
			'method' => 'textbox',
			'max_length' => 20,
			'default' => read_config_option('alert_bl_past_default'),
			//'description' => '指定相对的过去时间点作为参考.该值单位为秒,所以1天指定为86400,1周指定为604800,等等.建议参考1天前的数据.',
			'value' => isset($thold_item_data['bl_ref_time']) ? $thold_item_data['bl_ref_time'] : ''
		),
		'bl_ref_time_range' => array(
			'friendly_name' => '时间范围',
			'method' => 'textbox',
			'max_length' => 20,
			'default' => read_config_option('alert_bl_timerange_def'),
			//'description' => '指定需要参考过去值的时间范围.',
			'value' => isset($thold_item_data['bl_ref_time_range']) ? $thold_item_data['bl_ref_time_range'] : ''
		),
		'bl_pct_up' => array(
			'friendly_name' => '高于基线偏差',
			'method' => 'textbox',
			'max_length' => 3,
			'size' => 3,
			//'description' => '指定允许的高于基线偏差百分比.如果未设置,将不会检查高于基线偏差.',
			'value' => isset($thold_item_data['bl_pct_up']) ? $thold_item_data['bl_pct_up'] : ''
		),
		'bl_pct_down' => array(
			'friendly_name' => '低于基线偏差',
			'method' => 'textbox',
			'max_length' => 3,
			'size' => 3,
			//'description' => '指定允许的低于基线偏差百分比.如果未设置,将不会检查低于基线偏差.',
			'value' => isset($thold_item_data['bl_pct_down']) ? $thold_item_data['bl_pct_down'] : ''
		),
		'bl_fail_trigger' => array(
			'friendly_name' => '基线触发记数器',
			'method' => 'textbox',
			'max_length' => 3,
			'size' => 3,
			'default' => read_config_option('alert_bl_trigger'),
			//'description' => '数据源连续超过基线阈值的次数,次数超过这个值便触发报警.<br>留空使用默认值(<b>默认: ' . read_config_option('alert_bl_trigger') . '次</b>)',
			'value' => isset($thold_item_data['bl_fail_trigger']) ? $thold_item_data['bl_fail_trigger'] : ''
		),
		'data_manipulation' => array(
			'friendly_name' => '数据操作',
			'method' => 'spacer',
		),
		'data_type' => array(
			'friendly_name' => '数据类型',
			'method' => 'drop_array',
			'on_change' => 'changeDataType()',
			'array' => $data_types,
			'default' => read_config_option('data_type'),
			//'description' => '指定数据格式.',
			'value' => isset($thold_item_data['data_type']) ? $thold_item_data['data_type'] : ''
		),
		'cdef' => array(
			'friendly_name' => '阈值CDEF',
			'method' => 'drop_array',
			'default' => 'NULL',
			//'description' => '返回数据前应用这个CDEF.',
			'value' => isset($thold_item_data['cdef']) ? $thold_item_data['cdef'] : 0,
			'array' => thold_cdef_select_usable_names()
		),
		'percent_ds' => array(
			'friendly_name' => '百分比数据源',
			'method' => 'drop_array',
			'default' => 'NULL',
			//'description' => '这个数据源对象作为计算百分比的总共值.例如添加磁盘空间的阈值时,这个对象应当是磁盘的总共空间大小,而被监视的对象是已用空间大小.',
			'value' => isset($thold_item_data['percent_ds']) ? $thold_item_data['percent_ds'] : 0,
			'array' => $data_fields2,
		),
		'other_header' => array(
			'friendly_name' => '其它设置',
			'method' => 'spacer',
		),

		'repeat_alert' => array(
			'friendly_name' => '再次报警周期',
			'method' => 'drop_array',
			'array' => $repeatarray,
			'default' => read_config_option('alert_repeat'),
			//'description' => '每隔多久再次报警.',
			'value' => isset($thold_item_data['repeat_alert']) ? $thold_item_data['repeat_alert'] : ''
		),
		'notify_accounts' => array(
			'friendly_name' => '通知帐户',
			'method' => 'drop_multi',
			//'description' => '当这个阈值报警被触发时通知这些帐户,需要被通知的帐户需要设置电子邮件.<br><br><br><br>',
			'array' => $send_notification_array,
			'sql' => $sql,
		),
		'notify_extra' => array(
			'friendly_name' => '额外报警邮件',
			'method' => 'textarea',
			'textarea_rows' => 3,
			'textarea_cols' => 50,
			//'description' => '您可以在这里指定额外的接收报警邮件的地址(多个邮件地址用英文逗号分隔)',
			'value' => isset($thold_item_data['notify_extra']) ? $thold_item_data['notify_extra'] : ''
		),
	);

	draw_edit_form(
		array(
			'config' => array(
				'no_form_tag' => true
				),
			'fields' => $form_array
			)
	);

	html_end_box();
	form_save_button('thold_templates.php?id=' . $id, 'save');

	?>
	<!-- Make it look intelligent :) -->
	<script language="JavaScript">
	function BL_EnableDisable()
	{
		var _f = document.THold;
		var status = !_f.bl_enabled.checked;

		_f.bl_ref_time.disabled = status;
		_f.bl_ref_time_range.disabled = status;
		_f.bl_pct_down.disabled = status;
		_f.bl_pct_up.disabled = status;
		_f.bl_fail_trigger.disabled = status;
	}

	BL_EnableDisable();
	document.THold.bl_enabled.onclick = BL_EnableDisable;

	function changeTholdType () {
		type = document.getElementById('thold_type').value;
		switch(type) {
		case '0':
			thold_toggle_hilow ('');
			thold_toggle_baseline ('none');
			thold_toggle_time ('none');
			break;
		case '1':
			thold_toggle_hilow ('none');
			thold_toggle_baseline ('');
			thold_toggle_time ('none');
			break;
		case '2':
			thold_toggle_hilow ('none');
			thold_toggle_baseline ('none');
			thold_toggle_time ('');
			break;
		}
	}

	function changeDataType () {
		type = document.getElementById('data_type').value;
		switch(type) {
		case '0':
			document.getElementById('row_cdef').style.display  = 'none';
			document.getElementById('row_percent_ds').style.display  = 'none';
			break;
		case '1':
			document.getElementById('row_cdef').style.display  = '';
			document.getElementById('row_percent_ds').style.display  = 'none';
			break;
		case '2':
			document.getElementById('row_cdef').style.display  = 'none';
			document.getElementById('row_percent_ds').style.display  = '';
			break;
		}
	}

	function thold_toggle_hilow (status) {
		document.getElementById('row_thold_header').style.display  = status;
		document.getElementById('row_thold_hi').style.display  = status;
		document.getElementById('row_thold_low').style.display  = status;
		document.getElementById('row_thold_fail_trigger').style.display  = status;
	}

	function thold_toggle_baseline (status) {
		document.getElementById('row_baseline_header').style.display  = status;
		document.getElementById('row_bl_enabled').style.display  = status;
		document.getElementById('row_bl_ref_time').style.display  = status;
		document.getElementById('row_bl_ref_time_range').style.display  = status;
		document.getElementById('row_bl_pct_up').style.display  = status;
		document.getElementById('row_bl_pct_down').style.display  = status;
		document.getElementById('row_bl_fail_trigger').style.display  = status;
	}

	function thold_toggle_time (status) {
		document.getElementById('row_time_header').style.display  = status;
		document.getElementById('row_time_hi').style.display  = status;
		document.getElementById('row_time_low').style.display  = status;
		document.getElementById('row_time_fail_trigger').style.display  = status;
		document.getElementById('row_time_fail_length').style.display  = status;
	}

	changeTholdType ();
	changeDataType ();

	</script>
	<?php

}

function templates() {
	global $colors, $ds_actions;

	html_start_box('<strong>阈值模板</strong>', '100%', $colors['header'], '3', 'center', 'thold_templates.php?action=add');

	html_header_checkbox(array('名称', '数据模板', 'DS名称', '类型', '上限', '下限', '触发器', '时长', '重复'));

	$template_list = db_fetch_assoc('SELECT *
		FROM thold_template
		ORDER BY data_template_name');

	$i = 0;
	$types = array('上/下限', '基线', '基于次数');
	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $template["id"]); $i++;
			form_selectable_cell('<a class="linkEditMain" href="thold_templates.php?action=edit&id=' . $template['id'] . '">' . ($template['name'] == '' ? $template['data_template_name'] . ' [' . $template['data_source_name'] . ']' : $template['name']) . '</a>', $template["id"]);
			form_selectable_cell($template['data_template_name'], $template["id"]);
			form_selectable_cell($template['data_source_name'], $template["id"]);
			form_selectable_cell($types[$template['thold_type']], $template["id"]);
			form_selectable_cell(($template['thold_type'] == 0 ? $template['thold_hi'] : $template['time_hi']), $template["id"]);
			form_selectable_cell(($template['thold_type'] == 0 ? $template['thold_low'] : $template['time_low']), $template["id"]);
			form_selectable_cell("<i><span style='white-space:nowrap;'>" . ($template['thold_type'] == 0 ? plugin_thold_duration_convert($template['data_template_id'], $template['thold_fail_trigger'], 'alert', 'data_template_id') : $template['time_fail_trigger']) . "</span></i>", $template["id"]);
			form_selectable_cell(($template['thold_type'] == 2 ? plugin_thold_duration_convert($template['data_template_id'], $template['time_fail_length'], 'time', 'data_template_id') : ''), $template["id"]);
			form_selectable_cell(plugin_thold_duration_convert($template['data_template_id'], $template['repeat_alert'], 'repeat', 'data_template_id'), $template['id']);
			form_checkbox_cell($template['data_template_name'], $template["id"]);
			form_end_row();
		}
	}else{
		print "<tr><td><em>无数据模板</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_actions);

	print "</form>\n";
}

	?>
	<script type="text/javascript">
	<!--

	function applyTholdFilterChange(objForm, type) {
		if ((type == 'dt') && (document.getElementById("data_source_id"))) {
			document.getElementById("data_source_id").value = "";
		}

		if (document.getElementById("save")) {
			document.getElementById("save").value = "";
		}

		document.tholdform.submit();
	}

	-->
	</script>
