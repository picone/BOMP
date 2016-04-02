<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 Reinhard Scheck aka gandalf                          |
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
*/
chdir('../../');
#global $config;
#include_once($config['include_path'] . "/auth.php");
include_once("./include/auth.php");

define("MAX_DISPLAY_PAGES", 21);

$aggregate_actions = array(
	1 => "删除",
	2 => "复制"
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
#	case 'template_remove':
#		template_remove();
#
#		header("Location: color_templates.php");
#		break;
#	case 'input_remove':
#		input_remove();
#
#		header("Location: color_templates.php?action=template_edit&color_template_id=" . $_GET["color_template_id"]);
#		break;
#	case 'input_edit':
#		include_once($config['include_path'] . "/top_header.php");
#		
#		input_edit();
#
#		include_once($config['include_path'] . "/bottom_footer.php");
#		break;
	case 'template_edit':
		include_once($config['include_path'] . "/top_header.php");
		
		template_edit();

		include_once($config['include_path'] . "/bottom_footer.php");
		break;
	default:
		include_once($config['include_path'] . "/top_header.php");
		
		template();

		include_once($config['include_path'] . "/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_color"])) {
		if (isset($_POST["color_template_id"])) {
			$save1["color_template_id"] = $_POST["color_template_id"];
		} else {
			$save1["color_template_id"] = 0;
		}
		$save1["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
			cacti_log("AGGREGATE   Save ID: " . $save1["color_template_id"] . " Name: " . $save1["name"], FALSE);
		}
		
		if (!is_error_message()) {
			$color_template_id = sql_save($save1, "plugin_aggregate_color_templates", "color_template_id");
			if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
				cacti_log("AGGREGATE   Saved ID: " . $color_template_id, FALSE);
			}
			
			if ($color_template_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}
	}

	if ((is_error_message()) || (empty($_POST["color_template_id"]))) {
		header("Location: color_templates.php?action=template_edit&color_template_id=" . (empty($color_template_id) ? $_POST["color_template_id"] : $color_template_id));
	}else{
		header("Location: color_templates.php");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $aggregate_actions, $config;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from plugin_aggregate_color_templates where " . array_to_sql_or($selected_items, "color_template_id"));
			db_execute("delete from plugin_aggregate_color_template_items where " . array_to_sql_or($selected_items, "color_template_id"));
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_color_template($selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: color_templates.php");
		exit;
	}

	/* setup some variables */
	$color_list = ""; $i = 0;

	/* loop through each of the color templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			$color_list .= "<li>" . db_fetch_cell("select name from plugin_aggregate_color_templates where color_template_id=" . $matches[1]) . "<br>";
			$color_array[$i] = $matches[1];
		}
		$i++;
	}
	//print "<pre>";print_r($_POST);print "</pre>";
	
	include_once($config['include_path'] . "/top_header.php");
	
	html_start_box("<strong>" . $aggregate_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='color_templates.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>您真的希望删除以下颜色模板吗?</p>
					<p>$color_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>当您点击保存,以下颜色模板将被复制.您可以任意的更改新颜色模板的标题格式.</p>
					<p>$color_list</p>
					<p><strong>模板格式:</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($color_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>您必需至少选择一个颜色模板.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='../../images/button_yes.gif' alt='Save' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($color_array) ? serialize($color_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='color_templates.php'><img src='../../images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once($config['include_path'] . "/bottom_footer.php");
}

function item() {
	global $colors, $config;

	include_once($config['base_path'] . "/plugins/aggregate/color_html.php");
	
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */

	if (empty($_GET["color_template_id"])) {
		$template_item_list = array();

		$header_label = "[new]";
	}else{
		$template_item_list = db_fetch_assoc("select
			plugin_aggregate_color_template_items.color_template_item_id,
			plugin_aggregate_color_template_items.sequence,
			colors.hex
			from plugin_aggregate_color_template_items
			left join colors on (color_id=colors.id)
			where plugin_aggregate_color_template_items.color_template_id=" . $_GET["color_template_id"] . "
			order by plugin_aggregate_color_template_items.sequence ASC");

		$header_label = "[编辑: " . db_fetch_cell("select name from plugin_aggregate_color_templates where color_template_id=" . $_GET["color_template_id"]) . "]";
	}

	html_start_box("<strong>颜色模板对象</strong> $header_label", "98%", $colors["header"], "3", "center", "color_templates_items.php?action=item_edit&color_template_id=" . $_GET["color_template_id"]);
	# draw the list
	draw_color_template_items_list($template_item_list, "color_templates_items.php", "color_template_id=" . $_GET["color_template_id"], false);
	//print "<pre>";print_r($template_item_list);print "</pre>";
	
	html_end_box();
}

/* ----------------------------
    template - Color Templates
   ---------------------------- */

function template_edit() {
	global $colors, $image_types, $fields_color_template_template_edit, $struct_aggregate;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */
	if (!empty($_GET["color_template_id"])) {
		if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
			cacti_log("AGGREGATE   Edit ID: " . $_GET["color_template_id"], FALSE);
		}
		$template = db_fetch_row("select * from plugin_aggregate_color_templates where color_template_id=" . $_GET["color_template_id"]);
		$header_label = "[编辑: " . $template["name"] . "]";
	}else{
		$header_label = "[新]";
	}

	html_start_box("<strong>颜色模板</strong> $header_label", "98%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_color_template_template_edit, (isset($template) ? $template : array()))
		));
	//print "<pre>";print_r($fields_color_template_template_edit);print "</pre>";
	//print "<pre>";print_r($template);print "</pre>";

	html_end_box();

	/* color item list goes here */
	if (!empty($_GET["color_template_id"])) {
		item();
	}

	form_save_button("color_templates.php", "", "color_template_id");
}

function template() {
	global $colors, $aggregate_actions, $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column string */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	} else {
		$_REQUEST["sort_column"] = "name";
	}

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	} else {
		$_REQUEST["sort_direction"] = "ASC";
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_color_template_current_page");
		kill_session_var("sess_color_template_filter");
		kill_session_var("sess_color_template_sort_column");
		kill_session_var("sess_color_template_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);

	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_color_template_current_page", "1");
	load_current_session_value("filter", "sess_color_template_filter", "");
	load_current_session_value("sort_column", "sess_color_template_sort_column", "name");
	load_current_session_value("sort_direction", "sess_color_template_sort_direction", "ASC");

	html_start_box("<strong>颜色模板</strong>", "98%", $colors["header"], "3", "center", "color_templates.php?action=template_edit");

	include($config['base_path'] . "/plugins/aggregate/inc_color_template_filter_table.php");
	
	html_end_box();

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (plugin_aggregate_color_templates.name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "98%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(plugin_aggregate_color_templates.color_template_id)
		FROM plugin_aggregate_color_templates
		$sql_where");

	$template_list = db_fetch_assoc("SELECT
		plugin_aggregate_color_templates.color_template_id,
		plugin_aggregate_color_templates.name
		FROM plugin_aggregate_color_templates
		$sql_where
		ORDER BY " . $_REQUEST['sort_column'] . " " . $_REQUEST['sort_direction'] .
		" LIMIT " . (read_config_option("num_rows_device")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_device"));

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "color_templates.php?filter=" . $_REQUEST["filter"]);

	$nav = "<tr bgcolor='#" . $colors["header"] . "'>
		<td colspan='7'>
			<table width='100%' cellspacing='0' cellpadding='0' border='0'>
				<tr>
					<td align='left' class='textHeaderDark'>
						<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='color_templates.php?filter=" . $_REQUEST["filter"] . "&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "上一页"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
					</td>\n
					<td align='center' class='textHeaderDark'>
						" . ((read_config_option("num_rows_device")*($_REQUEST["page"]-1))+1) . " 到 " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device")*$_REQUEST["page"]))) ? $total_rows : (read_config_option("num_rows_device")*$_REQUEST["page"])) . " 行,共 $total_rows 行 [ 第 $url_page_select 页 ]
					</td>\n
					<td align='right' class='textHeaderDark'>
						<strong>"; if (($_REQUEST["page"] * read_config_option("num_rows_device")) < $total_rows) { $nav .= "<a class='linkOverDark' href='color_templates.php?filter=" . $_REQUEST["filter"] . "&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "下一页"; if (($_REQUEST["page"] * read_config_option("num_rows_device")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
					</td>\n
				</tr>
			</table>
		</td>
		</tr>\n";

	print $nav;

	$display_text = array(
		"name" => array("模板标题", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	$i = 0;
	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color($colors["alternate"],$colors["light"],$i);
				?>
			<td>
				<a class="linkEditMain" href="color_templates.php?action=template_edit&color_template_id=<?php print $template["color_template_id"];?>"><?php print $template["name"];?></a>
			</td>
			<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
				<input type='checkbox' style='margin: 0px;' name='chk_<?php print $template["color_template_id"];?>' title="<?php print $template["name"];?>">
			</td>
			</tr>
			<?php
			$i++;
		}
		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>无颜色模板</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($aggregate_actions);

	print "</form>\n";
}
?>
