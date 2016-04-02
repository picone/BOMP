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
global $config;
include_once("./include/auth.php");
include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_remove':
		item_remove();

		header("Location: color_templates.php?action=template_edit&color_template_id=" . $_GET["color_template_id"]);
		break;
	case 'item_movedown':
		item_movedown();

		header("Location: color_templates.php?action=template_edit&color_template_id=" . $_GET["color_template_id"]);
		break;
	case 'item_moveup':
		item_moveup();

		header("Location: color_templates.php?action=template_edit&color_template_id=" . $_GET["color_template_id"]);
		break;
	case 'item_edit':
		include_once($config['include_path'] . "/top_header.php");
		
		item_edit();

		include_once($config['include_path'] . "/bottom_footer.php");
		break;
	case 'item':
		include_once($config['include_path'] . "/top_header.php");
						
		item();

		include_once($config['include_path'] . "/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	
	if (isset($_POST["save_component_item"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("color_template_id"));
		/* ==================================================== */
		$items[0] = array();

		foreach ($items as $item) {
			/* generate a new sequence if needed */
			if (empty($_POST["sequence"])) {
				$_POST["sequence"] = get_next_sequence($_POST["sequence"], "sequence", "plugin_aggregate_color_template_items", "color_template_id=" . $_POST["color_template_id"], "color_template_id");
			}
			
			$save["color_template_item_id"] = $_POST["color_template_item_id"];
			$save["color_template_id"] = $_POST["color_template_id"];
			$save["color_id"] = form_input_validate((isset($item["color_id"]) ? $item["color_id"] : $_POST["color_id"]), "color_id", "", true, 3);
			$save["sequence"] = $_POST["sequence"];
			
			if (!is_error_message()) {
				$color_template_item_id = sql_save($save, "plugin_aggregate_color_template_items", "color_template_item_id");
				if ($color_template_item_id) {
					raise_message(1);
				}else{
					raise_message(2);
				}
			}

			$_POST["sequence"] = 0;
		}

		if (is_error_message()) {
			header("Location: color_templates_items.php?action=item_edit&color_template_item_id=" . (empty($color_template_item_id) ? $_POST["color_template_item_id"] : $color_template_item_id) . "&color_template_id=" . $_POST["color_template_id"]);
			exit;
		}else{
			header("Location: color_templates.php?action=template_edit&color_template_id=" . $_POST["color_template_id"]);
			exit;
		}
	}
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item_movedown() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_item_id"));
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */
	$current_sequence = db_fetch_row("select color_template_item_id, sequence 
										from plugin_aggregate_color_template_items 
										where color_template_item_id=" . $_GET["color_template_item_id"],
										true);
	#cacti_log("AGGREGATE   movedown Id: " . $current_sequence["color_template_item_id"] . " Seq:" . $current_sequence["sequence"], FALSE);
	$next_sequence = db_fetch_row("select color_template_item_id, sequence 
										from plugin_aggregate_color_template_items 
										where sequence > " . $current_sequence["sequence"] .
										" and color_template_id=" . $_GET["color_template_id"] . 
										" order by sequence ASC limit 1",
										true);
	#cacti_log("AGGREGATE   movedown Id: " . $next_sequence["color_template_item_id"] . " Seq:" . $next_sequence["sequence"], FALSE);
	db_execute("update plugin_aggregate_color_template_items 
				set sequence					=" . $next_sequence["sequence"] .
				" where color_template_id	=" . $_GET["color_template_id"] . 
				" and color_template_item_id=" . $current_sequence["color_template_item_id"]);
	db_execute("update plugin_aggregate_color_template_items 
				set sequence					=" . $current_sequence["sequence"] .
				" where color_template_id	=" . $_GET["color_template_id"] . 
				" and color_template_item_id=" . $next_sequence["color_template_item_id"]);
}

function item_moveup() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_item_id"));
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */
	$current_sequence = db_fetch_row("select color_template_item_id, sequence 
										from plugin_aggregate_color_template_items 
										where color_template_item_id=" . $_GET["color_template_item_id"],
										true);
	#cacti_log("AGGREGATE   moveup Id: " . $current_sequence["color_template_item_id"] . " Seq:" . $current_sequence["sequence"], FALSE);
	$previous_sequence = db_fetch_row("select color_template_item_id, sequence 
										from plugin_aggregate_color_template_items 
										where sequence < " . $current_sequence["sequence"] .
										" and color_template_id=" . $_GET["color_template_id"] . 
										" order by sequence DESC limit 1",
										true);
	#cacti_log("AGGREGATE   moveup Id: " . $previous_sequence["color_template_item_id"] . " Seq:" . $previous_sequence["sequence"], FALSE);
	db_execute("update plugin_aggregate_color_template_items 
				set sequence					=" . $previous_sequence["sequence"] .
				" where color_template_id	=" . $_GET["color_template_id"] . 
				" and color_template_item_id=" . $current_sequence["color_template_item_id"]);
	db_execute("update plugin_aggregate_color_template_items 
				set sequence					=" . $current_sequence["sequence"] .
				" where color_template_id	=" . $_GET["color_template_id"] . 
				" and color_template_item_id=" . $previous_sequence["color_template_item_id"]);
}

function item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_item_id"));
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */

	db_execute("delete from plugin_aggregate_color_template_items where color_template_item_id=" . $_GET["color_template_item_id"]);
}

function item_edit() {
	global $colors, $struct_color_template_item;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("color_template_item_id"));
	input_validate_input_number(get_request_var("color_template_id"));
	/* ==================================================== */

	$header_label = "[编号颜色模板: " . db_fetch_cell("select name from plugin_aggregate_color_templates where color_template_id=" . $_GET["color_template_id"]) . "]";

	html_start_box("<strong>颜色模板对象</strong> $header_label", "98%", $colors["header"], "3", "center", "");

	if (!empty($_GET["color_template_item_id"])) {
		$template_item = db_fetch_row("select * from plugin_aggregate_color_template_items where color_template_item_id=" . $_GET["color_template_item_id"]);
	}
	
	$form_array = array();

	while (list($field_name, $field_array) = each($struct_color_template_item)) {
		$form_array += array($field_name => $struct_color_template_item[$field_name]);

		$form_array[$field_name]["value"] = (isset($template_item) ? $template_item[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($template_item) ? $template_item["color_template_item_id"] : "0");

	}

	draw_edit_form(
		array(
			"config" => array(
				),
			"fields" => $form_array
			)
		);

	html_end_box();

	form_hidden_box("color_template_item_id", (isset($template_item) ? $template_item["color_template_item_id"] : "0"), "");
	form_hidden_box("color_template_id", $_GET["color_template_id"], "0");
	form_hidden_box("sequence", (isset($template_item) ? $template_item["sequence"] : "0"), "");
	form_hidden_box("save_component_item", "1", "");

	form_save_button("color_templates.php?action=template_edit&color_template_id=" . $_GET["color_template_id"], "", "color_template_item_id");
}
?>
