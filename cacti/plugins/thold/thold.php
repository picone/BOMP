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
include_once($config['library_path'] . '/rrd.php');
include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

input_validate_input_number(get_request_var('view_rra'));
input_validate_input_number(get_request_var('hostid'));
input_validate_input_number(get_request_var('rra'));
input_validate_input_number(get_request_var('id'));

$hostid = '';
if (isset($_REQUEST['rra'])) {
	$rra = $_REQUEST['rra'];
	$hostid = db_fetch_assoc('select host_id from thold_data where rra_id = ' . $rra);
	if (isset($hostid[0]['host_id'])) {
		$hostid = $hostid[0]['host_id'];
	} else {
		$hostid = db_fetch_assoc('select host_id from poller_item where local_data_id = ' . $rra);
		if (isset($hostid[0]['host_id'])) {
			$hostid = $hostid[0]['host_id'];
		}
	}
	if (is_array($hostid)) {
		$hostid = '';
	}
	if (!thold_user_auth_threshold ($rra)) {
		include_once($config['include_path'] . '/top_header.php');
		print '<font size=+1 color=red>拒绝访问 - 您没有访问阈值的权限.</font>';
		include_once($config['include_path'] . '/bottom_footer.php');
		exit;
	}
} else {
	$_REQUEST['rra'] = '';
	$rra = '';
	if (isset($_REQUEST['hostid'])) {
		$hostid = $_REQUEST['hostid'];
	} else {
		$_REQUEST['hostid'] = '';
		if (isset($_GET['hostid'])) {
			$hostid=$_GET['hostid'];
		}
		if (isset($_POST['hostid'])) {
			$hostid=$_POST['hostid'];
		}
	}
}

if (!isset($_REQUEST['action'])) {
	$_REQUEST['action'] = '';
}

if ((substr_count($_SERVER["HTTP_REFERER"], "graph_view.php")) || (substr_count($_SERVER["HTTP_REFERER"], "graph.php"))) {
	$_SESSION["graph_return"] = $_SERVER["HTTP_REFERER"];
}

switch($_REQUEST['action']) {
	case 'save':
		save_thold();


		if (isset($_SESSION["graph_return"])) {
			$return_to = $_SESSION["graph_return"];
			unset($_SESSION["graph_return"]);
			kill_session_var("graph_return");
			header('Location: ' . $return_to);
		}else{
			include_once($config['include_path'] . '/top_header.php');
		}

		break;
	case 'autocreate':
		$c = autocreate($hostid);
		if ($c == 0) {
			$_SESSION['thold_message'] = '<font size=-1>没有已存在的模板或阈值 - 未添加新阈值.</font>';
		}
		raise_message('thold_created');

		if (isset($_SESSION["graph_return"])) {
			$return_to = $_SESSION["graph_return"];
			unset($_SESSION["graph_return"]);
			kill_session_var("graph_return");
			header('Location: ' . $return_to);
		}else{
			Header('Location: ../../graphs_new.php?host_id=' . $hostid);
		}
		exit;

		break;
	case 'disable':
		thold_threshold_disable($_REQUEST["id"]);
		Header('Location: ' . $_SERVER["HTTP_REFERER"]);
		exit;
	case 'enable':
		thold_threshold_enable($_REQUEST["id"]);
		Header('Location: ' . $_SERVER["HTTP_REFERER"]);
		exit;
}

include_once($config['include_path'] . '/top_header.php');

$t = db_fetch_assoc('SELECT id, name, name_cache FROM data_template_data WHERE local_data_id=' . $rra . ' LIMIT 1');
$desc = $t[0]['name_cache'];
unset($t);

$rrdsql = db_fetch_assoc("SELECT id FROM data_template_rrd WHERE local_data_id=$rra ORDER BY id");
$sql = '';
foreach ($rrdsql as $r) {
	if ($sql == '') {
		$sql = ' task_item_id = ' . $r['id'];
	} else {
		$sql .= ' or task_item_id = ' . $r['id'];
	}
}

$rrdlookup = $rrdsql[0]["id"];

$template_data_rrds = db_fetch_assoc("SELECT id, data_source_name FROM data_template_rrd WHERE local_data_id=" . $rra . " ORDER BY id");

$grapharr = db_fetch_assoc("SELECT DISTINCT local_graph_id FROM graph_templates_item WHERE $sql");

// Take the first one available
$graph = (isset($grapharr[0]["local_graph_id"]) ? $grapharr[0]["local_graph_id"] : "");

?>
<table width="98%" align="center">
	<tr>
		<td class="textArea">
	<?php
		if (isset($banner)) {
			echo $banner . "<br><br>";

		}; ?>

<form name="THold" action=thold.php method=post>
	数据源描述: <br><strong><?php echo $desc; ?></strong><br><br>
	已关联的图形 (图形使用这些RRD): <br>
	<select name='element'>
<?php
foreach($grapharr as $g) {
	$graph_desc = db_fetch_assoc("SELECT local_graph_id,
		title,
		title_cache
		FROM graph_templates_graph
		WHERE local_graph_id = " . $g["local_graph_id"]);

	echo "<option value=" . $graph_desc[0]["local_graph_id"];
	if($graph_desc[0]["local_graph_id"] == $graph) echo " selected";
	echo "> " . $graph_desc[0]["local_graph_id"] . " - " . $graph_desc[0]["title_cache"] . " </option>\n";
}
?>
	</select>
	<br>
	<br>
		</td>
		<td>
			<img id=graphimage src="<?php echo $config["url_path"]; ?>graph_image.php?local_graph_id=<?php echo $graph ?>&rra_id=0&graph_start=-32400&graph_height=100&graph_width=300&graph_nolegend=true">
		</td>
	</tr>
</table>
<?php

/* select the first "rrd" of this data source by default */
if (empty($_GET["view_rrd"])) {
	if(isset($_POST["data_template_rrd_id"])) {
		$_GET["view_rrd"] = $_POST["data_template_rrd_id"];
	} else {
		/* Check and see if we already have a threshold set, and use that if so */
		$thold_data = db_fetch_cell("SELECT data_id FROM thold_data WHERE rra_id = $rra ORDER BY data_id");

		if ($thold_data) {
			$_GET["view_rrd"] = $thold_data;
		} else {
			$_GET["view_rrd"] = (isset($template_data_rrds[0]["id"]) ? $template_data_rrds[0]["id"] : "0");
		}
	}
}

/* get more information about the rrd we chose */
if (!empty($_GET["view_rrd"])) {
	$template_rrd = db_fetch_row("select * from data_template_rrd where id=" . $_GET["view_rrd"]);
}

//-----------------------------
// Tabs (if more than one item)
//-----------------------------
$i = 0;
$ds = 0;
if (isset($template_data_rrds)) {
	if (sizeof($template_data_rrds) > 1) {
		/* draw the data source tabs on the top of the page */
		print "	<table class='tabs' width='98%' cellspacing='0' cellpadding='3' align='center'>
		<tr>\n";

		foreach ($template_data_rrds as $template_data_rrd) {
			if($template_data_rrd["id"] == $_GET["view_rrd"]) $ds = $template_data_rrd["data_source_name"];

			$item = db_fetch_assoc("select * from thold_data where data_id = " . $template_data_rrd["id"]);
			$item = count($item) > 0 ? $item[0] : $item;

			if(count($item) == 0) {
				$cur_setting = "n/a";
			} else {
				$cur_setting = "Hi: " . ($item["thold_hi"] == "" ? "n/a" : $item["thold_hi"]);
				$cur_setting .= " Lo: " . ($item["thold_low"] == "" ? "n/a" : $item["thold_low"]);
				$cur_setting .= " BL: " . $item["bl_enabled"];
			}
			$tab_len = max(strlen($cur_setting), strlen($template_data_rrd["data_source_name"]));
			if($cur_setting == "n/a") { $cur_setting = "<font color='red'>" . $cur_setting . "</font>"; }

			$i++;
			echo "	<td bgcolor=" . (($template_data_rrd["id"] == $_GET["view_rrd"]) ? "'silver'" : "'#DFDFDF'");
			echo " nowrap='nowrap' width='" . (($tab_len * 8) + 30) . "' align='center' class='tab'>";
			echo "<span class='textHeader'><a href='thold.php?rra=" . $rra . "&view_rrd=" . $template_data_rrd["id"] . "'>$i: " . $template_data_rrd["data_source_name"] . "</a><br>";
			echo $cur_setting;
			echo "</span>\n</td>\n<td width='1'></td>\n";
			unset($thold_item_data);
		}

		print "
		<td></td>\n
		</tr>
		</table>\n";

	}elseif (sizeof($template_data_rrds) == 1) {
		$_GET["view_rrd"] = $template_data_rrds[0]["id"];
	}
}

//----------------------
// Data Source Item Form
//----------------------
$thold_item_data = db_fetch_assoc("select * from thold_data where data_id = " . $_GET["view_rrd"]);
$thold_item_data = count($thold_item_data) > 0 ? $thold_item_data[0] : $thold_item_data;
$thold_item_data_cdef = (isset($thold_item_data['cdef']) ? $thold_item_data['cdef'] : 0);


html_start_box("", "98%", $colors["header"], "3", "center", "");
//------------------------
// Data Source Item header
//------------------------
print "	<tr>
	<td colspan=2 bgcolor='#" . $colors["header"] . "' class='textHeaderDark'>
	<strong>数据源对象</strong> [" . (isset($template_rrd) ? $template_rrd["data_source_name"] : "") . "] " .
	" - <strong>当前值: </strong>[" . get_current_value($rra, $ds, $thold_item_data_cdef) .
	"]</td>
	</tr>\n";

$send_notification_array = array();

$users = db_fetch_assoc("SELECT plugin_thold_contacts.id, plugin_thold_contacts.data, plugin_thold_contacts.type, user_auth.full_name FROM plugin_thold_contacts, user_auth WHERE user_auth.id = plugin_thold_contacts.user_id AND plugin_thold_contacts.data != '' ORDER BY user_auth.full_name ASC, plugin_thold_contacts.type ASC");
if (!empty($users)) {
	foreach ($users as $user) {
		$send_notification_array[$user['id']] = $user['full_name'] . ' - ' . ucfirst($user['type']);
	}
}

if (isset($thold_item_data['id'])) {
	$sql = 'SELECT contact_id as id FROM plugin_thold_threshold_contact WHERE thold_id=' . $thold_item_data['id'];
	$step = db_fetch_cell('SELECT rrd_step FROM data_template_data WHERE local_data_id = ' . $thold_item_data['rra_id'], FALSE);
} else {
	$sql = 'SELECT contact_id as id FROM plugin_thold_threshold_contact WHERE thold_id=0';
	$step = db_fetch_cell('SELECT rrd_step FROM data_template_data WHERE local_data_id = ' . $rra, FALSE);
}

if ($step == 60) {
	$repeatarray = array(0 => '永不', 1 => '每分钟', 2 => '每2分钟', 3 => '每3分钟', 4 => '每4分钟', 5 => '每5分钟', 10 => '每10分钟', 15 => '每15分钟', 20 => '每20分钟', 30 => '每30分钟', 45 => '每45分钟', 60 => '每小时', 120 => '每2小时', 180 => '每3小时', 240 => '每4小时', 360 => '每6小时', 480 => '每8小时', 720 => '每12小时', 1440 => '每天', 2880 => '每2天', 10080 => '每周', 20160 => '每2周', 43200 => '每月');
	$alertarray  = array(0 => '永不', 1 => '1分钟', 2 => '2分钟', 3 => '3分钟', 4 => '4分钟', 5 => '5分钟', 10 => '10分钟', 15 => '15分钟', 20 => '20分钟', 30 => '30分钟', 45 => '45分钟', 60 => '1小时', 120 => '2小时', 180 => '3小时', 240 => '4小时', 360 => '6小时', 480 => '8小时', 720 => '12小时', 1440 => '1天', 2880 => '2天', 10080 => '1周', 20160 => '2周', 43200 => '1月');
	$timearray   = array(1 => '1分钟', 2 => '2分钟', 3 => '3分钟', 4 => '4分钟', 5 => '5分钟', 10 => '10分钟', 15 => '15分钟', 20 => '20分钟', 30 => '30分钟', 45 => '45分钟', 60 => '1小时', 120 => '2小时', 180 => '3小时', 240 => '4小时', 360 => '6小时', 480 => '8小时', 720 => '12小时', 1440 => '1天', 2880 => '2天', 10080 => '1周', 20160 => '2周', 43200 => '1月');
} else if ($step == 300) {
	$repeatarray = array(0 => '永不', 1 => '每5分钟', 2 => '每10分钟', 3 => '每15分钟', 4 => '每20分钟', 6 => '每30分钟', 8 => '每45分钟', 12 => '每小时', 24 => '每2小时', 36 => '每3小时', 48 => '每4小时', 72 => '每6小时', 96 => '每8小时', 144 => '每12小时', 288 => '每天', 576 => '每2天', 2016 => '每周', 4032 => '每2周', 8640 => '每月');
	$alertarray  = array(0 => '永不', 1 => '5分钟', 2 => '10分钟', 3 => '15分钟', 4 => '20分钟', 6 => '30分钟', 8 => '45分钟', 12 => '小时', 24 => '2小时', 36 => '3小时', 48 => '4小时', 72 => '6小时', 96 => '8小时', 144 => '12小时', 288 => '1天', 576 => '2天', 2016 => '1周', 4032 => '2周', 8640 => '1月');
	$timearray   = array(1 => '5分钟', 2 => '10分钟', 3 => '15分钟', 4 => '20分钟', 6 => '30分钟', 8 => '45分钟', 12 => '小时', 24 => '2小时', 36 => '3小时', 48 => '4小时', 72 => '6小时', 96 => '8小时', 144 => '12小时', 288 => '1天', 576 => '2天', 2016 => '1周', 4032 => '2周', 8640 => '1月');
} else {
	$repeatarray = array(0 => '永不', 1 => '每次采集', 2 => '每2次采集', 3 => '每3次采集', 4 => '每4次采集', 6 => '每6次采集', 8 => '每8次采集', 12 => '每12次采集', 24 => '每24次采集', 36 => '每36次采集', 48 => '每48次采集', 72 => '每72次采集', 96 => '每96次采集', 144 => '每144次采集', 288 => '每288次采集', 576 => '每576次采集', 2016 => '每2016次采集');
	$alertarray  = array(0 => '永不', 1 => '1次采集', 2 => '2次采集', 3 => '3次采集', 4 => '4次采集', 6 => '6次采集', 8 => '8次采集', 12 => '12次采集', 24 => '24次采集', 36 => '36次采集', 48 => '48次采集', 72 => '72次采集', 96 => '96次采集', 144 => '144次采集', 288 => '288次采集', 576 => '576次采集', 2016 => '2016次采集');
	$timearray   = array(1 => '1次采集', 2 => '2次采集', 3 => '3次采集', 4 => '4次采集', 6 => '6次采集', 8 => '8次采集', 12 => '12次采集', 24 => '24次采集', 36 => '36次采集', 48 => '48次采集', 72 => '72次采集', 96 => '96次采集', 144 => '144次采集', 288 => '288次采集', 576 => '576次采集', 2016 => '2016次采集');
}

$thold_types = array (
	0 => '上/下限',
	1 => '基线',
	2 => '基于次数',
	);

$data_types = array (
	0 => '绝对值',
	1 => 'CDEF',
	2 => '百分比',
);

$data_fields = array();

if (isset($thold_item_data['data_template_id'])) {
	$temp = db_fetch_assoc('select id, local_data_template_rrd_id, data_source_name, data_input_field_id from data_template_rrd where local_data_id = ' . $thold_item_data['rra_id']);
} else {
	$temp = db_fetch_assoc('select id, local_data_template_rrd_id, data_source_name, data_input_field_id from data_template_rrd where local_data_id = ' . $rra);
}

foreach ($temp as $d) {
	if ($d['data_input_field_id'] != 0) {
		$temp2 = db_fetch_assoc('select name from data_input_fields where id = ' . $d['data_input_field_id']);
	} else {
		$temp2[0]['name'] = $d['data_source_name'];
	}
	if ((isset($_GET['view_rrd']) && $d['id'] != $_GET['view_rrd']) || (isset($thold_item_data['data_id']) && $d['id'] != $thold_item_data['data_id'])) {
		$data_fields[$d['data_source_name']] = $temp2[0]['name'];
	}
}

$form_array = array(
		'template_header' => array(
			'friendly_name' => '模板设置',
			'method' => 'spacer',
		),
		'template_enabled' => array(
			'friendly_name' => '启用模板继承',
			'method' => 'checkbox',
			'default' => '',
			//'description' => '以下这些设置是否应该从模板继承.',
			'value' => isset($thold_item_data['template_enabled']) ? $thold_item_data['template_enabled'] : '',
		),
		'general_header' => array(
			'friendly_name' => '独立设置(非继承选项)',
			'method' => 'spacer',
		),
		'name' => array(
			'friendly_name' => '阈值名称',
			'method' => 'textbox',
			'max_length' => 100,
			'default' => $desc . ' [' . $template_rrd['data_source_name'] . ']',
			//'description' => '给该阈值设置一个有意义的名字',
			'value' => isset($thold_item_data['name']) ? $thold_item_data['name'] : ''
		),
		'thold_enabled' => array(
			'friendly_name' => '启用阈值',
			'method' => 'checkbox',
			'default' => 'on',
			//'description' => '这个阈值是否启用并在触发时产生报警.',
			'value' => isset($thold_item_data['thold_enabled']) ? $thold_item_data['thold_enabled'] : ''
		),
		'exempt' => array(
			'friendly_name' => '周末休息',
			//'description' => '选中该选项,将不在周末时产生报警.',
			'method' => 'checkbox',
			'default' => 'off',
			'value' => isset($thold_item_data['exempt']) ? $thold_item_data['exempt'] : ''
			),
		'restored_alert' => array(
			'friendly_name' => '禁用恢复邮件',
			//'description' => '选中该选项,当阈值恢复到正常值时将不发送提醒邮件.',
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
			//'description' => '将被监视的阈值类型.',
			'value' => isset($thold_item_data['thold_type']) ? $thold_item_data['thold_type'] : ''
		),
		'thold_header' => array(
			'friendly_name' => '上/下限设置',
			'method' => 'spacer',
		),
		'thold_hi' => array(
			'friendly_name' => '阈值上限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果数据源大于该上限,将会触发报警',
			'value' => isset($thold_item_data['thold_hi']) ? $thold_item_data['thold_hi'] : ''
		),
		'thold_low' => array(
			'friendly_name' => '阈值下限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果数据源小于该下限,将会触发报警',
			'value' => isset($thold_item_data['thold_low']) ? $thold_item_data['thold_low'] : ''
		),
		'thold_fail_trigger' => array(
			'friendly_name' => '容忍时长',
			'method' => 'drop_array',
			'array' => $alertarray,
			'default' => read_config_option('alert_trigger'),
			//'description' => '数据源大于上限或小于下限持续的时间,超过该时间将会触发报警.',
			'value' => isset($thold_item_data['thold_fail_trigger']) ? $thold_item_data['thold_fail_trigger'] : ''
		),
		'time_header' => array(
			'friendly_name' => '基于次数设置',
			'method' => 'spacer',
		),
		'time_hi' => array(
			'friendly_name' => '阈值上限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果数据源大于该上限,将会触发报警.',
			'value' => isset($thold_item_data['time_hi']) ? $thold_item_data['time_hi'] : ''
		),
		'time_low' => array(
			'friendly_name' => '阈值下限',
			'method' => 'textbox',
			'max_length' => 100,
			//'description' => '如果数据源小于该上限,将会触发报警.',
			'value' => isset($thold_item_data['time_low']) ? $thold_item_data['time_low'] : ''
		),
		'time_fail_trigger' => array(
			'friendly_name' => '容忍次数',
			'method' => 'textbox',
			'max_length' => 5,
			'default' => read_config_option('thold_time_fail_trigger'),
			//'description' => '数据源连续大于阈值上限或小于阈值下限的次数,超过该次数将会触发报警.',
			'value' => isset($thold_item_data['time_fail_trigger']) ? $thold_item_data['time_fail_trigger'] : ''
		),
		'time_fail_length' => array(
			'friendly_name' => '容忍时长',
			'method' => 'drop_array',
			'array' => $timearray,
			'default' => (read_config_option('thold_time_fail_length') > 0 ? read_config_option('thold_time_fail_length') : 1),
			//'description' => '每隔多久检查一次.PS:总的容忍时长等于容忍次数乘以容忍时长',
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
			//'description' => '指定数据格式',
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
			'array' => $data_fields,
		),
		'other_header' => array(
			'friendly_name' => '其它设置',
			'method' => 'spacer',
		),
		'repeat_alert' => array(
			'friendly_name' => '重复报警周期',
			'method' => 'drop_array',
			'array' => $repeatarray,
			'default' => read_config_option('alert_repeat'),
			//'description' => '每隔多久再次报警.',
			'value' => isset($thold_item_data['repeat_alert']) ? $thold_item_data['repeat_alert'] : ''
		),
		'notify_accounts' => array(
			'friendly_name' => '通知账户',
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
		'fields' => $form_array + array(
			'data_template_rrd_id' => array(
				'method' => 'hidden',
				'value' => (isset($template_rrd) ? $template_rrd['id'] : '0')
			),
			'hostid' => array(
				'method' => 'hidden',
				'value' => $hostid
			),
			'rra' => array(
				'method' => 'hidden',
				'value' => $rra
			)
		)
	)
);

html_end_box();
form_save_button('thold.php?rra=' . $rra . '&view_rrd=' . $_GET['view_rrd'], 'save');

unset($template_data_rrds);
?>
<!-- Make it look intelligent :) -->
<script language="JavaScript">
function BL_EnableDisable()
{
	var _f = document.THold;
	var status = !_f.bl_enabled.checked;
	if (_f.bl_enabled.disabled)
		status = true;

	_f.bl_ref_time.disabled = status;
	_f.bl_ref_time_range.disabled = status;
	_f.bl_pct_down.disabled = status;
	_f.bl_pct_up.disabled = status;
	_f.bl_fail_trigger.disabled = status;
}

BL_EnableDisable();
document.THold.bl_enabled.onclick = BL_EnableDisable;

function Template_EnableDisable()
{
	var _f = document.THold;
	var status = _f.template_enabled.checked;
	_f.name.disabled = status;
	_f.thold_type.disabled = status;
	_f.thold_hi.disabled = status;
	_f.thold_low.disabled = status;
	_f.thold_fail_trigger.disabled = status;
	_f.bl_enabled.disabled = status;
	_f.repeat_alert.disabled = status;
	_f.notify_extra.disabled = status;
	_f.cdef.disabled = status;
	_f.thold_enabled.disabled = status;
	_f["notify_accounts[]"].disabled = status;
	_f.time_hi.disabled = status;
	_f.time_low.disabled = status;
	_f.time_fail_trigger.disabled = status;
	_f.time_fail_length.disabled = status;
	_f.data_type.disabled = status;
	_f.percent_ds.disabled = status;
	_f.exempt.disabled = status;
	_f.restored_alert.disabled = status;

	BL_EnableDisable();

}

Template_EnableDisable();
document.THold.template_enabled.onclick = Template_EnableDisable;
<?php
if (!isset($thold_item_data['template']) || $thold_item_data['template'] == '') {
?>
	document.THold.template_enabled.disabled = true;

<?php
}
?>


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

include_once($config["include_path"] . "/bottom_footer.php");
?>

<script language="JavaScript">
function GraphImage()
{
	var _f = document.THold;
	var id = _f.element.options[_f.element.selectedIndex].value;
	document.graphimage.src = "../../graph_image.php?local_graph_id=" + id + "&rra_id=0&graph_start=-32400&graph_height=100&graph_width=300&graph_nolegend=true";
}

document.THold.element.onchange = GraphImage;

</script>
