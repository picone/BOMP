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

		syslog_removal();

		include_once($config['base_path'] . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ((isset($_POST["save_component_removal"])) && (empty($_POST["add_dq_y"]))) {
		$removalid = api_syslog_removal_save($_POST["id"], $_POST["name"], $_POST["type"],
			$_POST["message"], $_POST["method"], $_POST["notes"], $_POST["enabled"]);

		if ((is_error_message()) || ($_POST["id"] != $_POST["_id"])) {
			header("Location: syslog_removal.php?action=edit&id=" . (empty($id) ? $_POST["id"] : $id));
		}else{
			header("Location: syslog_removal.php");
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

				api_syslog_removal_remove($selected_items[$i]);
			}
		}else if ($_POST["drp_action"] == "2") { /* disable */
			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_syslog_removal_disable($selected_items[$i]);
			}
		}else if ($_POST["drp_action"] == "3") { /* enable */
			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_syslog_removal_enable($selected_items[$i]);
			}
		}

		header("Location: syslog_removal.php");

		exit;
	}

	include_once($config['base_path'] . "/include/top_header.php");

	html_start_box("<strong>" . $syslog_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='syslog_removal.php' method='post'>\n";

	/* setup some variables */
	$removal_array = array(); $removal_list = "";

	/* loop through each of the clusters selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$removal_info = syslog_db_fetch_cell("SELECT name FROM `" . $syslogdb_default . "`.`syslog_remove` WHERE id=" . $matches[1]);
			$removal_list  .= "<li>" . $removal_info . "<br>";
			$removal_array[] = $matches[1];
		}
	}

	if (sizeof($removal_array)) {
		if ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续',以下Syslog删除规则将被启用</p>
						<ul>$removal_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "Delete Syslog Removal Rule(s)";
		}else if ($_POST["drp_action"] == "2") { /* disable */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续',以下Syslog删除规则将被禁用</p>
						<ul>$removal_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "禁用Syslog删除规则";
		}else if ($_POST["drp_action"] == "3") { /* enable */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>如果您点击 '继续',以下Syslog删除规则将被启用</p>
						<ul>$removal_list</ul>";
						print "</td></tr>
					</td>
				</tr>\n";

			$title = "启用Syslog删除规则";
		}

		$save_html = "<input type='button' value='取消' onClick='window.history.back()'>&nbsp;<input type='submit' value='继续' title='$title'";
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>您必需至少选择一个Syslog删除规则.</span></td></tr>\n";
		$save_html = "<input type='button' value='返回' onClick='window.history.back()'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($removal_array) ? serialize($removal_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once($config['base_path'] . "/include/bottom_footer.php");
}

function api_syslog_removal_save($id, $name, $type, $message, $method, $notes, $enabled) {
	global $config;

	include(dirname(__FILE__) . "/config.php");

	/* get the username */
	$username = db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);

	if ($id) {
		$save["id"] = $id;
	}else{
		$save["id"] = "";
	}

	$save["name"]    = form_input_validate($name,    "name",    "", false, 3);
	$save["type"]    = form_input_validate($type,    "type",    "", false, 3);
	$save["message"] = form_input_validate($message, "message", "", false, 3);
	$save["method"]  = form_input_validate($method,  "method",  "", false, 3);
	$save["notes"]   = form_input_validate($notes,   "notes",   "", true, 3);
	$save["enabled"] = ($enabled == "on" ? "on":"");
	$save["date"]    = time();
	$save["user"]    = $username;

	if (!is_error_message()) {
		$id = 0;
		$id = syslog_sql_save($save, "`" . $syslogdb_default . "`.`syslog_remove`", "id");

		if ($id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $id;
}

function api_syslog_removal_remove($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("DELETE FROM `" . $syslogdb_default . "`.`syslog_remove` WHERE id='" . $id . "'");
}

function api_syslog_removal_disable($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("UPDATE `" . $syslogdb_default . "`.`syslog_remove` SET enabled='' WHERE id='" . $id . "'");
}

function api_syslog_removal_enable($id) {
	include(dirname(__FILE__) . "/config.php");
	syslog_db_execute("UPDATE `" . $syslogdb_default . "`.`syslog_remove` SET enabled='on' WHERE id='" . $id . "'");
}

/* ---------------------
    Removal Functions
   --------------------- */

function syslog_get_removal_records(&$sql_where, $row_limit) {
	include(dirname(__FILE__) . "/config.php");

	if (get_request_var_request("filter") != "") {
		$sql_where .= (strlen($sql_where) ? " AND ":"WHERE ") .
			"(message LIKE '%%" . get_request_var_request("filter") . "%%' OR " .
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
		FROM `" . $syslogdb_default . "`.`syslog_remove`
		$sql_where
		ORDER BY ". get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction") .
		" LIMIT " . ($row_limit*(get_request_var_request("page")-1)) . "," . $row_limit;

	return syslog_db_fetch_assoc($query_string);
}

function syslog_action_edit() {
	global $colors, $message_types;

	include(dirname(__FILE__) . "/config.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("type"));
	/* ==================================================== */

	if (isset($_GET["id"]) && $_GET["action"] == "edit") {
		$removal = syslog_db_fetch_row("SELECT *
			FROM `" . $syslogdb_default . "`.`syslog_remove`
			WHERE id=" . $_GET["id"]);
		$header_label = "[编辑: " . $removal["name"] . "]";
	}else if (isset($_GET["id"]) && $_GET["action"] == "newedit") {
		$syslog_rec = syslog_db_fetch_row("SELECT * FROM `" . $syslogdb_default . "`.`syslog` WHERE seq=" . $_GET["id"] . " AND logtime='" . $_GET["date"] . "'");

		$header_label = "[新建]";
		if (sizeof($syslog_rec)) {
			$removal["message"] = $syslog_rec["message"];
		}
		$removal["name"]    = "新建删除规则";
	}else{
		$header_label = "[新建]";

		$removal["name"] = "新建删除规则";
	}

	html_start_box("<strong>编辑删除规则</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	$fields_syslog_removal_edit = array(
	"spacer0" => array(
		"method" => "spacer",
		"friendly_name" => "删除规则详细"
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "删除规则名称",
		"description" => "请描述删除规则.",
		"value" => "|arg1:name|",
		"max_length" => "250",
		"size" => 80
		),
	"enabled" => array(
		"method" => "drop_array",
		"friendly_name" => "启用?",
		"description" => "是否启用这个删除规则?",
		"value" => "|arg1:enabled|",
		"array" => array("on" => "启用", "" => "禁用"),
		"default" => "on"
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
		"description" => "Syslog消息匹配的字符串",
		"method" => "textarea",
		"textarea_rows" => "2",
		"textarea_cols" => "70",
		"class" => "textAreaNotes",
		"value" => "|arg1:message|",
		"default" => "",
		),
	"method" => array(
		"method" => "drop_array",
		"friendly_name" => "删除方法",
		"value" => "|arg1:method|",
		"array" => array("del" => "删除", "trans" => "转移"),
		"default" => "del"
		),
	"notes" => array(
		"friendly_name" => "删除规则说明",
		"textarea_rows" => "5",
		"textarea_cols" => "70",
		"description" => "删除规则的备注说明",
		"method" => "textarea",
		"class" => "textAreaNotes",
		"value" => "|arg1:notes|",
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
	"save_component_removal" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

	echo "<form method='post' autocomplete='off' onsubmit='changeTypes()' action='syslog_removal.php' name='chk'>";

	draw_edit_form(array(
		"config" => array("no_form_tag" => true),
		"fields" => inject_form_variables($fields_syslog_removal_edit, (isset($removal) ? $removal : array()))
		));

	html_end_box();

	form_save_button("syslog_removal.php", "", "id");

	?>
	<script type='text/javascript'>
	function changeTypes() {
		if (document.getElementById('type').value == 'sql') {
			document.getElementById('message').rows = 5;
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
		<form name="removal">
		<td>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="70">
						启用:&nbsp;
					</td>
					<td width="1">
						<select name="enabled" onChange="applyChange(document.removal)">
						<option value="-1"<?php if ($_REQUEST["enabled"] == "-1") {?> selected<?php }?>>全部</option>
						<option value="1"<?php if ($_REQUEST["enabled"] == "1") {?> selected<?php }?>>是</option>
						<option value="0"<?php if ($_REQUEST["enabled"] == "0") {?> selected<?php }?>>否</option>
						</select>
					</td>
					<td width="45">
						&nbsp;行:&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyChange(document.removal)">
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
						&nbsp;<input type="submit" name="go" value="确定" title="搜索">
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

function syslog_removal() {
	global $colors, $syslog_actions, $message_types, $config;

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
		kill_session_var("sess_syslog_removal_page");
		kill_session_var("sess_syslog_removal_rows");
		kill_session_var("sess_syslog_removal_filter");
		kill_session_var("sess_syslog_removal_enabled");
		kill_session_var("sess_syslog_removal_sort_column");
		kill_session_var("sess_syslog_removal_sort_direction");

		$_REQUEST["page"] = 1;
		unset($_REQUEST["filter"]);
		unset($_REQUEST["enabled"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}else{
		/* if any of the settings changed, reset the page number */
		$changed = 0;
		$changed += syslog_check_changed("filter", "sess_syslog_removal_filter");
		$changed += syslog_check_changed("enabled", "sess_syslog_removal_enabled");
		$changed += syslog_check_changed("rows", "sess_syslog_removal_rows");
		$changed += syslog_check_changed("sort_column", "sess_syslog_removal_sort_column");
		$changed += syslog_check_changed("sort_direction", "sess_syslog_removal_sort_direction");

		if ($changed) {
			$_REQUEST["page"] = "1";
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_syslog_removal_paage", "1");
	load_current_session_value("rows", "sess_syslog_removal_rows", "-1");
	load_current_session_value("enabled", "sess_syslog_removal_enabled", "-1");
	load_current_session_value("filter", "sess_syslog_removal_filter", "");
	load_current_session_value("sort_column", "sess_syslog_removal_sort_column", "name");
	load_current_session_value("sort_direction", "sess_syslog_removal_sort_direction", "ASC");

	html_start_box("<strong>Syslog删除规则过滤器</strong>", "100%", $colors["header"], "3", "center", "syslog_removal.php?action=edit&type=1");
	syslog_filter();
	html_end_box();

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$sql_where = "";

	if ($_REQUEST["rows"] == -1) {
		$row_limit = read_config_option("num_rows_syslog");
	}elseif ($_REQUEST["rows"] == -2) {
		$row_limit = 999999;
	}else{
		$row_limit = $_REQUEST["rows"];
	}

	$removals = syslog_get_removal_records($sql_where, $row_limit);

	$rows_query_string = "SELECT COUNT(*)
		FROM `" . $syslogdb_default . "`.`syslog_remove`
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
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $row_limit, $total_rows, "syslog_removal.php?filter=" . $_REQUEST["filter"]);

	if ($total_rows > 0) {
		$nav = "<tr bgcolor='#" . $colors["header"] . "'>
					<td colspan='13'>
						<table width='100%' cellspacing='0' cellpadding='0' border='0'>
							<tr>
								<td align='left' class='textHeaderDark'>
									<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='syslog_removal.php?report=arp&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "上一页"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
								</td>\n
								<td align='center' class='textHeaderDark'>
									" . ($total_rows == 0 ? "无" : (($row_limit*($_REQUEST["page"]-1))+1) . " 到 " . ((($total_rows < $row_limit) || ($total_rows < ($row_limit*$_REQUEST["page"]))) ? $total_rows : ($row_limit*$_REQUEST["page"])) . " 行,共 $total_rows 行 第 [$url_page_select]") . " 页
								</td>\n
								<td align='right' class='textHeaderDark'>
									<strong>"; if (($_REQUEST["page"] * $row_limit) < $total_rows) { $nav .= "<a class='linkOverDark' href='syslog_removal.php?report=arp&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "下一页"; if (($_REQUEST["page"] * $row_limit) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
								</td>\n
							</tr>
						</table>
					</td>
				</tr>\n";
        echo $nav;
	}

	$display_text = array(
		"name" => array("规则名称", "ASC"),
		"enabled" => array("启用", "ASC"),
		"type" => array("关键字类型", "ASC"),
		"message" => array("关键字", "ASC"),
		"method" => array("方法", "DESC"),
		"date" => array("最后修改", "ASC"),
		"user" => array("操作用户", "DESC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	$i = 0;
	if (sizeof($removals) > 0) {
		foreach ($removals as $removal) {
			form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $removal["id"]); $i++;
			form_selectable_cell("<a class='linkEditMain' href='" . $config['url_path'] . "plugins/syslog/syslog_removal.php?action=edit&id=" . $removal["id"] . "'>" . (($_REQUEST["filter"] != "") ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", title_trim(htmlentities($removal["name"],ENT_COMPAT, "UTF-8"), read_config_option("max_title_data_source"))) : htmlentities($removal["name"], ENT_COMPAT, "UTF-8")) . "</a>", $removal["id"]);
//			form_selectable_cell("<a class='linkEditMain' href='" . $config['url_path'] . "plugins/syslog/syslog_removal.php?action=edit&id=" . $removal["id"] . "'>" . (($_REQUEST["filter"] != "") ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", title_trim(htmlentities($removal["name"]), read_config_option("max_title_data_source"))) : htmlentities($removal["name"])) . "</a>", $removal["id"]);
			form_selectable_cell((($removal["enabled"] == "on") ? "是" : "否"), $removal["id"]);
			form_selectable_cell($message_types[$removal["type"]], $removal["id"]);
			form_selectable_cell($removal["message"], $removal["id"]);
			form_selectable_cell((($removal["method"] == "del") ? "删除" : "转移"), $removal["id"]);
			form_selectable_cell(date("Y年m月日 H:i:s", $removal["date"]), $removal["id"]);
			form_selectable_cell($removal["user"], $removal["id"]);
			form_checkbox_cell($removal["name"], $removal["id"]);
			form_end_row();
		}
	}else{
		print "<tr><td colspan='4'><em>未定义删除规则</em></td></tr>";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($syslog_actions);
}

?>
