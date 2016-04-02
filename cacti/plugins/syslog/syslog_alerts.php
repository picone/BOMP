<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007-2011 The Cacti Group                                 |
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
include("./include/auth.php");
include_once('plugins/syslog/functions.php');

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'edit':
	case 'newedit':
		include_once($config['base_path'] . "/include/top_header.php");

		syslog_action_edit();

		include_once($config['base_path'] . "/include/bottom_footer.php");
		break;
	default:
		include_once($config['base_path'] . "/include/top_header.php");

		syslog_alerts();

		include_once($config['base_path'] . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ((isset($_POST["save_component_alert"])) && (empty($_POST["add_dq_y"]))) {
		$alertid = api_syslog_alert_save($_POST["id"], $_POST["name"], $_POST["method"],
			$_POST["num"], $_POST["type"], $_POST["message"], $_POST["email"],
			$_POST["notes"], $_POST["enabled"], $_POST["severity"], $_POST["command"],
			$_POST["repeat_alert"], $_POST["open_ticket"]);

		if ((is_error_message()) || ($_POST["id"] != $_POST["_id"])) {
			header("Location: syslog_alerts.php?action=edit&id=" . (empty($id) ? $_POST["id"] : $id));
		}else{
			header("Location: syslog_alerts.php");
		}
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $config, $syslog_actions, $fields_syslog_action_edit;

	include(dirname(__FILE__) . "/config.php");

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_syslog_alert_remove($selected_items[$i]);
			}
		}else if ($_POST["drp_action"] == "2") { /* disable */
			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_syslog_alert_disable($selected_items[$i]);
			}
		}else if ($_POST["drp_action"] == "3") { /* enable */
			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_syslog_alert_enable($selected_items[$i]);
			}
		}

		header("Location: syslog_alerts.php");

		exit;
	}

	include_once($config['base_path'] . "/include/top_header.php");

	html_start_box("<strong>" . $syslog_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='syslog_alerts.php' method='post'>\n";

	/* setup some variables */
	$alert_array = array(); $alert_list = "";

	/* loop through each of the clusters selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$alert_info = syslog_db_fetch_cell("SELECT name FROM `" . $syslogdb_default . "`.`syslog_alert` WHERE id=" . $matches[1]);
			$alert_list .= "<li>" . $alert_info . "</li>";
			$alert_array[] = $matches[1];
		}
	}

	if (sizeof($alert_array)) {
		if ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续', 以下Syslog报警规则将被删除</p>
						<ul>$alert_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "删除Syslog报警规则";
		}else if ($_POST["drp_action"] == "2") { /* disable */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续', 以下Syslog报警规则将被禁用</p>
						<ul>$alert_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "禁用Syslog报警规则";
		}else if ($_POST["drp_action"] == "3") { /* enable */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续', 以下Syslog报警规则将被启用</p>
						<ul>$alert_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "启用Syslog报警规则";
		}

		$save_html = "<input type='button' value='取消' onClick='window.history.back()'>&nbsp;<input type='submit' value='继续' title='$title'";
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>您必需至少选择一个报警规则</span></td></tr>\n";
		$save_html = "<input type='button' value='返回' onClick='window.history.back()'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($alert_array) ? serialize($alert_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				$save_html
			</td>
		</tr>";

	html_end_box();

	include_once($config['base_path'] . "/include/bottom_footer.php");
}

function api_syslog_alert_save($id, $name, $method, $num, $type, $message, $email, $notes,
	$enabled, $severity, $command, $repeat_alert, $open_ticket) {
	include(dirname(__FILE__) . "/config.php");

	/* get the username */
	$username = db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);

	if ($id) {
		$save["id"] = $id;
	}else{
		$save["id"] = "";
	}

	$save["name"]            = form_input_validate($name,            "name",     "", false, 3);
	$save["num"]             = form_input_validate($num,             "num",      "", false, 3);
	$save["message"]         = form_input_validate($message,         "message",  "", false, 3);
	$save["email"]           = form_input_validate(trim($email),     "email",    "", true, 3);
	$save["command"]         = form_input_validate($command,         "command",  "", true, 3);
	$save["notes"]           = form_input_validate($notes,           "notes",    "", true, 3);
	$save["enabled"]         = ($enabled == "on" ? "on":"");
	$save["repeat_alert"]    = form_input_validate($repeat_alert,    "repeat_alert", "", true, 3);
	$save["open_ticket"]     = ($open_ticket == "on" ? "on":"");
	$save["type"]            = $type;
	$save["severity"]        = $severity;
	$save["method"]          = $method;
	$save["user"]            = $username;
	$save["date"]            = time();

	//print "<pre>";print_r($save);print "</pre>";exit;

	if (!is_error_message()) {
		$id = 0;
		$id = syslog_sql_save($save, "`" . $syslogdb_default . "`.`syslog_alert`", "id");
		if ($id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $id;
}

function api_syslog_alert_remove($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("DELETE FROM `" . $syslogdb_default . "`.`syslog_alert` WHERE id='" . $id . "'");
}

function api_syslog_alert_disable($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("UPDATE `" . $syslogdb_default . "`.`syslog_alert` SET enabled='' WHERE id='" . $id . "'");
}

function api_syslog_alert_enable($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("UPDATE `" . $syslogdb_default . "`.`syslog_alert` SET enabled='on' WHERE id='" . $id . "'");
}

/* ---------------------
    Alert Functions
   --------------------- */

function syslog_get_alert_records(&$sql_where, $row_limit) {
	include(dirname(__FILE__) . "/config.php");

	if (get_request_var_request("filter") != "") {
		$sql_where .= (strlen($sql_where) ? " AND ":"WHERE ") .
			"(message LIKE '%%" . get_request_var_request("filter") . "%%' OR " .
			"email LIKE '%%" . get_request_var_request("filter") . "%%' OR " .
			"notes LIKE '%%" . get_request_var_request("filter") . "%%' OR " .
			"name LIKE '%%" . get_request_var_request("filter") . "%%')";
	}

	if (get_request_var_request("enabled") == "-1") {
		// Display all status'
	}elseif (get_request_var_request("enabled") == "1") {
		$sql_where .= (strlen($sql_where) ? " AND ":"WHERE ") .
			"enabled='on'";
	}else{
		$sql_where .= (strlen($sql_where) ? " AND ":"WHERE ") .
			"enabled=''";
	}

	$query_string = "SELECT *
		FROM `" . $syslogdb_default . "`.`syslog_alert`
		$sql_where
		ORDER BY ". get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction") .
		" LIMIT " . ($row_limit*(get_request_var_request("page")-1)) . "," . $row_limit;

	return syslog_db_fetch_assoc($query_string);
}

function syslog_action_edit() {
	global $colors, $message_types, $severities;

	include(dirname(__FILE__) . "/config.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("type"));
	/* ==================================================== */

	if (isset($_GET["id"]) && $_GET["action"] == "edit") {
		$alert = syslog_db_fetch_row("SELECT *
			FROM `" . $syslogdb_default . "`.`syslog_alert`
			WHERE id=" . $_GET["id"]);
		$header_label = "[编辑: " . $alert["name"] . "]";
	}else if (isset($_GET["id"]) && $_GET["action"] == "newedit") {
		$syslog_rec = syslog_db_fetch_row("SELECT * FROM `" . $syslogdb_default . "`.`syslog` WHERE seq=" . $_GET["id"] . " AND logtime='" . $_GET["date"] . "'");

		$header_label = "[新建]";
		if (sizeof($syslog_rec)) {
			$alert["message"] = $syslog_rec["message"];
		}
		$alert["name"]    = "新建报警规则";
	}else{
		$header_label = "[新建]";

		$alert["name"] = "新建报警规则";
	}

	$alert_retention = read_config_option("syslog_alert_retention");
	if ($alert_retention != '' && $alert_retention > 0 && $alert_retention < 365) {
		$repeat_end = ($alert_retention * 24 * 60) / 5;
	}

	$repeatarray = array(
		0 => '未设置', 
		1 => '5分钟', 
		2 => '10分钟', 
		3 => '15分钟', 
		4 => '20分钟', 
		6 => '30分钟', 
		8 => '45分钟', 
		12 => '1小时', 
		24 => '2小时', 
		36 => '3小时', 
		48 => '4小时', 
		72 => '6小时', 
		96 => '8小时', 
		144 => '12小时', 
		288 => '1天', 
		576 => '2天', 
		2016 => '1周', 
		4032 => '2周', 
		8640 => '1个月');

	if ($repeat_end) {
		foreach ($repeatarray as $i => $value) {
			if ($i > $repeat_end) {
				unset($repeatarray[$i]);
			}
		}
	}

	html_start_box("<strong>编辑报警</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	$fields_syslog_alert_edit = array(
	"spacer0" => array(
		"method" => "spacer",
		"friendly_name" => "报警详细"
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "报警名称",
		"description" => "请描述这个报警.",
		"value" => "|arg1:name|",
		"max_length" => "250",
		"size" => 80
		),
	"severity" => array(
		"method" => "drop_array",
		"friendly_name" => "严重级别",
		"description" => "这个报警的严重级别.",
		"value" => "|arg1:severity|",
		"array" => $severities,
		"default" => "1"
		),
	"method" => array(
		"method" => "drop_array",
		"friendly_name" => "报告方法",
		"description" => "定义如果在Syslog消息中报警.",
		"value" => "|arg1:method|",
		"array" => array("0" => "独立的", "1" => "阈值"),
		"default" => "0"
		),
	"num" => array(
		"method" => "textbox",
		"friendly_name" => "阈值",
		"description" => "当选择 '阈值' 方法时,如果数值高于这个值将会触发报警.",
		"value" => "|arg1:num|",
		"size" => "4",
		"max_length" => "10",
		"default" => "1"
		),
	"type" => array(
		"method" => "drop_array",
		"friendly_name" => "字符串匹配类型",
		"description" => "定义如何匹配这个字符串.如果使用SQL表达式类型,您需要使用可用的SQL表达式创建报警.可用字段包括 'message', 'facility', 'priority', 和 'host'.",
		"value" => "|arg1:type|",
		"array" => $message_types,
		"on_change" => "changeTypes()",
		"default" => "matchesc"
		),
	"message" => array(
		"friendly_name" => "Syslog消息匹配字符串",
		"description" => "Syslog消息匹配的字符串.",
		"textarea_rows" => "2",
		"textarea_cols" => "70",
		"method" => "textarea",
		"class" => "textAreaNotes",
		"value" => "|arg1:message|",
		"default" => ""
		),
	"enabled" => array(
		"method" => "drop_array",
		"friendly_name" => "启用报警",
		"description" => "这个报警是否启用?",
		"value" => "|arg1:enabled|",
		"array" => array("on" => "启用", "" => "禁用"),
		"default" => "on"
		),
	"repeat_alert" => array(
		"friendly_name" => "重新报警",
		"method" => "drop_array",
		"array" => $repeatarray,
		"default" => "0",
 		"description" => "在这个时间内不要为相同的主机发送报警.如果是基于阈值的报警,这将应用到所有主机.",
		"value" => "|arg1:repeat_alert|"
		),
	"notes" => array(
		"friendly_name" => "报警规则说明",
		"textarea_rows" => "5",
		"textarea_cols" => "70",
		"description" => "报警规则的备注说明",
		"method" => "textarea",
		"class" => "textAreaNotes",
		"value" => "|arg1:notes|",
		"default" => "",
		),
	"spacer1" => array(
		"method" => "spacer",
		"friendly_name" => "报警操作"
		),
//	"open_ticket" => array(
//		"method" => "drop_array",
//		"friendly_name" => "Open Ticket",
//		"description" => "Should a Help Desk Ticket be opened for this Alert",
//		"value" => "|arg1:open_ticket|",
//		"array" => array("on" => "是", "" => "否"),
//		"default" => ""
//		),
	"email" => array(
		"method" => "textarea",
		"friendly_name" => "E-Mails通知",
		"textarea_rows" => "5",
		"textarea_cols" => "70",
		"description" => "请输入以逗号分隔的邮件地址.如果您希望以短信格式发送邮件,请使用 <b>'sms@'</b> 为收件人邮件地址添加前缀.例如,要以短信格式发发送邮件,接收人的邮件地址是 <b>'admin@Cacti.com'</b>, 您应该输入 <b>'sms@admin@Cacti.com'</b>.",
		"class" => "textAreaNotes",
		"value" => "|arg1:email|",
		"max_length" => "255"
		),
	"command" => array(
		"friendly_name" => "报警命令",
		"textarea_rows" => "5",
		"textarea_cols" => "70",
		"description" => "当报警被触发,将运行这个命令.<br>以下变量可用: <b>'&lt;HOSTNAME&gt;'</b>, <b>'&lt;ALERTID&gt;'</b>, <b>'&lt;MESSAGE&gt;'</b>, <b>'&lt;FACILITY&gt;'</b>, <b>'&lt;PRIORITY&gt;'</b>, <b>'&lt;SEVERITY&gt;'</b>.<br>请注意 <b>'&lt;HOSTNAME&gt;'</b> 只能用在独立的报告方法.",
		"method" => "textarea",
		"class" => "textAreaNotes",
		"value" => "|arg1:command|",
		"default" => "",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_alert" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

	echo "<form method='post' autocomplete='off' onsubmit='changeTypes()' action='syslog_alerts.php' name='chk'>";

	draw_edit_form(array(
		"config" => array("no_form_tag" => true),
		"fields" => inject_form_variables($fields_syslog_alert_edit, (isset($alert) ? $alert : array()))
		));


	html_end_box();

	form_save_button("syslog_alerts.php", "", "id");

	?>
	<script type='text/javascript'>
	function changeTypes() {
		if (document.getElementById('type').value == 'sql') {
			document.getElementById('message').rows = 6;
		}else{
			document.getElementById('message').rows = 2;
		}
	}
	</script>
	<?php
}

function syslog_filter() {
	global $colors, $config, $item_rows;

	?>
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="alert">
		<td>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="70">
						启用:&nbsp;
					</td>
					<td width="1">
						<select name="enabled" onChange="applyChange(document.alert)">
						<option value="-1"<?php if ($_REQUEST["enabled"] == "-1") {?> selected<?php }?>>全部</option>
						<option value="1"<?php if ($_REQUEST["enabled"] == "1") {?> selected<?php }?>>是</option>
						<option value="0"<?php if ($_REQUEST["enabled"] == "0") {?> selected<?php }?>>否</option>
						</select>
					</td>
					<td width="45">
						&nbsp;行:&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyChange(document.alert)">
						<option value="-1"<?php if ($_REQUEST["rows"] == "-1") {?> selected<?php }?>>默认</option>
						<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print '<option value="' . $key . '"'; if ($_REQUEST["rows"] == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
						?>
						</select>
					</td>
					<td>
						&nbsp;<input type="submit" value="确定">
					</td>
					<td>
						&nbsp;<input type="submit" name="clear" value="清除">
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="70">
						搜索:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="30" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>
	<?php
}

function syslog_alerts() {
	global $colors, $syslog_actions, $config, $message_types, $severities;

	include(dirname(__FILE__) . "/config.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("id"));
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("enabled"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

	/* clean up filter */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort direction */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear"])) {
		kill_session_var("sess_syslog_alerts_page");
		kill_session_var("sess_syslog_alerts_rows");
		kill_session_var("sess_syslog_alerts_filter");
		kill_session_var("sess_syslog_alerts_enabled");
		kill_session_var("sess_syslog_alerts_sort_column");
		kill_session_var("sess_syslog_alerts_sort_direction");

		$_REQUEST["page"] = 1;
		unset($_REQUEST["filter"]);
		unset($_REQUEST["enabled"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}else{
		/* if any of the settings changed, reset the page number */
		$changed = 0;
		$changed += syslog_check_changed("filter", "sess_syslog_alerts_filter");
		$changed += syslog_check_changed("enabled", "sess_syslog_alerts_enabled");
		$changed += syslog_check_changed("rows", "sess_syslog_alerts_rows");
		$changed += syslog_check_changed("sort_column", "sess_syslog_alerts_sort_column");
		$changed += syslog_check_changed("sort_direction", "sess_syslog_alerts_sort_direction");

		if ($changed) {
			$_REQUEST["page"] = "1";
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_syslog_alerts_paage", "1");
	load_current_session_value("rows", "sess_syslog_alerts_rows", "-1");
	load_current_session_value("enabled", "sess_syslog_alerts_enabled", "-1");
	load_current_session_value("filter", "sess_syslog_alerts_filter", "");
	load_current_session_value("sort_column", "sess_syslog_alerts_sort_column", "name");
	load_current_session_value("sort_direction", "sess_syslog_alerts_sort_direction", "ASC");

	html_start_box("<strong>Syslog报警规则过滤器</strong>", "100%", $colors["header"], "3", "center", "syslog_alerts.php?action=edit");

	syslog_filter();

	html_end_box();

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$sql_where = "";

	if ($_REQUEST["rows"] == "-1") {
		$row_limit = read_config_option("num_rows_syslog");
	}elseif ($_REQUEST["rows"] == -2) {
		$row_limit = 999999;
	}else{
		$row_limit = $_REQUEST["rows"];
	}

	$alerts = syslog_get_alert_records($sql_where, $row_limit);

	$rows_query_string = "SELECT COUNT(*)
		FROM `" . $syslogdb_default . "`.`syslog_alert`
		$sql_where";

	$total_rows = syslog_db_fetch_cell($rows_query_string);

	?>
	<script type="text/javascript">
	<!--
	function applyChange(objForm) {
		strURL = '?enabled=' + objForm.enabled.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		strURL = strURL + '&rows=' + objForm.rows.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $row_limit, $total_rows, "syslog_alerts.php?filter=". $_REQUEST["filter"]);

	if ($total_rows > 0) {
		$nav = "<tr bgcolor='#" . $colors["header"] . "'>
					<td colspan='13'>
						<table width='100%' cellspacing='0' cellpadding='0' border='0'>
							<tr>
								<td align='left' class='textHeaderDark'>
									<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='syslog_alerts.php?report=arp&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "上一页"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
								</td>\n
								<td align='center' class='textHeaderDark'>
									" . ($total_rows == 0 ? "无" : (($row_limit*($_REQUEST["page"]-1))+1) . " 到 " . ((($total_rows < $row_limit) || ($total_rows < ($row_limit*$_REQUEST["page"]))) ? $total_rows : ($row_limit*$_REQUEST["page"])) . " 行,共 $total_rows 行 第 [$url_page_select]") . " 页
								</td>\n
								<td align='right' class='textHeaderDark'>
									<strong>"; if (($_REQUEST["page"] * $row_limit) < $total_rows) { $nav .= "<a class='linkOverDark' href='syslog_alerts.php?report=arp&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "下一页"; if (($_REQUEST["page"] * $row_limit) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
								</td>\n
							</tr>
						</table>
					</td>
				</tr>\n";
	}

	$display_text = array(
		"name" => array("报警名称", "ASC"),
		"severity" => array("严重级别", "ASC"),
		"method" => array("报告方法", "ASC"),
		"num" => array("阈值记数", "ASC"),
		"enabled" => array("启用", "ASC"),
		"type" => array("关键字类型", "ASC"),
		"message" => array("关键字", "ASC"),
		"email" => array("邮件地址", "DESC"),
		"date" => array("最后修改", "ASC"),
		"user" => array("操作用户", "DESC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);
	$i = 0;
	if (sizeof($alerts) > 0) {
		foreach ($alerts as $alert) {
			form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $alert["id"]); $i++;
			form_selectable_cell("<a class='linkEditMain' href='" . $config['url_path'] . "plugins/syslog/syslog_alerts.php?action=edit&id=" . $alert["id"] . "'>" . (($_REQUEST["filter"] != "") ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $alert["name"]) : $alert["name"]) . "</a>", $alert["id"]);
			form_selectable_cell($severities[$alert["severity"]], $alert["id"]);
			form_selectable_cell(($alert["method"] == 1 ? "阈值":"独立"), $alert["id"]);
			form_selectable_cell(($alert["method"] == 1 ? $alert["num"]:"无"), $alert["id"]);
			form_selectable_cell((($alert["enabled"] == "on") ? "是" : "否"), $alert["id"]);
			form_selectable_cell($message_types[$alert["type"]], $alert["id"]);
			form_selectable_cell(title_trim($alert["message"],60), $alert["id"]);
			form_selectable_cell((substr_count($alert["email"], ",") ? "多个地址":$alert["email"]), $alert["id"]);
			form_selectable_cell(date("Y年m月d日 H:i:s", $alert["date"]), $alert["id"]);
			form_selectable_cell($alert["user"], $alert["id"]);
			form_checkbox_cell($alert["name"], $alert["id"]);
			form_end_row();
		}
	}else{
		print "<tr><td colspan='4'><em>未定义报警规则</em></td></tr>";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($syslog_actions);
}

?>
