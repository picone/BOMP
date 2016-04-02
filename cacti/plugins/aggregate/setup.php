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

/**
 * Initialize the plugin and setup all hooks
 */
function plugin_init_aggregate() {
	global $plugin_hooks;

	# Add a new dropdown Action for Graph Management
	$plugin_hooks['graphs_action_array']['aggregate'] = 'aggregate_graphs_action_array';
	# setup all arrays needed for aggregation
	$plugin_hooks['config_arrays']['aggregate'] = 'aggregate_config_arrays';
	# setup all forms needed for aggregation
	$plugin_hooks['config_form']['aggregate'] = 'aggregate_config_form';
	# provide navigation texts
	$plugin_hooks['draw_navigation_text'] ['aggregate'] = 'aggregate_draw_navigation_text';
	# Graph Management Action dropdown selected: prepare the list of graphs for a confirmation request
	$plugin_hooks['graphs_action_prepare']['aggregate'] = 'aggregate_graphs_action_prepare';
	# Graph Management Action dropdown selected: execute list of graphs
	$plugin_hooks['graphs_action_execute']['aggregate'] = 'aggregate_graphs_action_execute';
}

function plugin_aggregate_install () {

	# setup all arrays needed for aggregation
	api_plugin_register_hook('aggregate', 'config_arrays', 'aggregate_config_arrays', 'setup.php');
	# setup all forms needed for aggregation
	api_plugin_register_hook('aggregate', 'config_form', 'aggregate_config_form', 'setup.php');
	# provide navigation texts
	api_plugin_register_hook('aggregate', 'draw_navigation_text', 'aggregate_draw_navigation_text', 'setup.php');
	# sAdd a new dropdown Action for Graph Management
	api_plugin_register_hook('aggregate', 'graphs_action_array', 'aggregate_graphs_action_array', 'setup.php');
	# Graph Management Action dropdown selected: prepare the list of graphs for a confirmation request
	api_plugin_register_hook('aggregate', 'graphs_action_prepare', 'aggregate_graphs_action_prepare', 'aggregate.php');
	# Graph Management Action dropdown selected: execute list of graphs
	api_plugin_register_hook('aggregate', 'graphs_action_execute', 'aggregate_graphs_action_execute', 'aggregate.php');

	aggregate_setup_table_new ();
}

function plugin_aggregate_uninstall () {
	// Do any extra Uninstall stuff here
}

function plugin_aggregate_check_config () {
	// Here we will check to ensure everything is configured
	aggregate_check_upgrade ();
	return true;
}

function plugin_aggregate_upgrade () {
	// Here we will upgrade to the newest version
	aggregate_check_upgrade ();
	return true;
}

function plugin_aggregate_version () {
	return aggregate_version();
}

function aggregate_check_upgrade () {
	global $config, $database_default;
	include_once($config["library_path"] . "/database.php");

	// Let's only run this check if we are on a page that actually needs the data
	$files = array('aggregate_templates.php, aggregate_templates_items.php, color_templates.php, color_templates_items.php');
	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files))
		return;

	$current = aggregate_version ();
	$current = $current['version'];
	$old = read_config_option('plugin_aggregate_version');
	if ($current != $old) {
		// do sth
	}
}

function aggregate_check_dependencies() {
	global $plugins, $config;
	return true;
}


function aggregate_setup_table_new () {
	global $config, $database_default;
	include_once($config["library_path"] . "/database.php");

	/* list all tables */
	$result = db_fetch_assoc("show tables from `" . $database_default . "`") or die (mysql_error());
	$tables = array();
	foreach($result as $index => $arr) {
		foreach ($arr as $t) {
			$tables[] = $t;
		}
	}
	/* V064 -> V065 tables were renamed */
	if (in_array('plugin_color_templates', $tables)) {
		db_execute("RENAME TABLE $database_default.`plugin_color_templates`  TO $database_default.`plugin_aggregate_color_templates`");
	}
	if (in_array('plugin_color_templates_item', $tables)) {
		db_execute("RENAME TABLE $database_default.`plugin_color_templates_item`  TO $database_default.`plugin_aggregate_color_template_items`");
	}

	$data = array();
	$data['columns'][] = array('name' => 'color_template_id', 'type' => 'mediumint(8)', 'unsigned' => 'unsigned', 'NULL' => false, 'auto_increment' => true);
	$data['columns'][] = array('name' => 'name', 'type' => 'varchar(255)', 'NULL' => false, 'default' => '');
	$data['primary'] = 'color_template_id';
	$data['type'] = 'MyISAM';
	$data['comment'] = 'Color Templates';
	api_plugin_db_table_create ('aggregate', 'plugin_aggregate_color_templates', $data);

	$sql[] = "INSERT INTO `plugin_aggregate_color_templates` " .
			"(`color_template_id`, `name`) " .
			"VALUES " .
			"(1, 'Yellow: light -> dark, 4 colors'), " .
			"(2, 'Red: light yellow > dark red, 8 colors'), " .
			"(3, 'Red: light -> dark, 16 colors'), " .
			"(4, 'Green: dark -> light, 16 colors');";

	$data = array();
	$data['columns'][] = array('name' => 'color_template_item_id', 'type' => 'int(12)', 'unsigned' => 'unsigned', 'NULL' => false, 'auto_increment' => true);
	$data['columns'][] = array('name' => 'color_template_id', 'type' => 'mediumint(8)', 'unsigned' => 'unsigned', 'NULL' => false, 'default' => 0);
	$data['columns'][] = array('name' => 'color_id', 'type' => 'mediumint(8)', 'unsigned' => 'unsigned', 'NULL' => false, 'default' => 0);
	$data['columns'][] = array('name' => 'sequence', 'type' => 'mediumint(8)', 'unsigned' => 'unsigned', 'NULL' => false, 'default' => 0);
	$data['primary'] = 'color_template_item_id';
	$data['type'] = 'MyISAM';
	$data['comment'] = 'Color Items for Color Templates';
	api_plugin_db_table_create ('aggregate', 'plugin_aggregate_color_template_items', $data);

	$sql[] = "INSERT INTO `plugin_aggregate_color_template_items` " .
			"(`color_template_item_id`, `color_template_id`, `color_id`, `sequence`) " .
			"VALUES " .
			"(1, 1, 4, 1), " .
			"(2, 1, 24, 2), " .
			"(3, 1, 98, 3), " .
			"(4, 1, 25, 4), " .
			"" .
			"(5, 2, 25, 1), " .
			"(6, 2, 29, 2), " .
			"(7, 2, 30, 3), " .
			"(8, 2, 31, 4), " .
			"(9, 2, 33, 5), " .
			"(10, 2, 35, 6), " .
			"(11, 2, 41, 7), " .
			"(12, 2, 9, 8), " .
			"" .
			"(13, 3, 15, 1), " .
			"(14, 3, 31, 2), " .
			"(15, 3, 28, 3), " .
			"(16, 3, 8, 4), " .
			"(17, 3, 34, 5), " .
			"(18, 3, 33, 6), " .
			"(19, 3, 35, 7), " .
			"(20, 3, 41, 8), " .
			"(21, 3, 36, 9), " .
			"(22, 3, 42, 10), " .
			"(23, 3, 44, 11), " .
			"(24, 3, 48, 12), " .
			"(25, 3, 9, 13), " .
			"(26, 3, 49, 14), " .
			"(27, 3, 51, 15), " .
			"(28, 3, 52, 16), " .
			"" .
			"(29, 4, 76, 1), " .
			"(30, 4, 84, 2), " .
			"(31, 4, 89, 3), " .
			"(32, 4, 17, 4), " .
			"(33, 4, 86, 5), " .
			"(34, 4, 88, 6), " .
			"(35, 4, 90, 7), " .
			"(36, 4, 94, 8), " .
			"(37, 4, 96, 9), " .
			"(38, 4, 93, 10), " .
			"(39, 4, 91, 11), " .
			"(40, 4, 22, 12), " .
			"(41, 4, 12, 13), " .
			"(42, 4, 95, 14), " .
			"(43, 4, 6, 15), " .
			"(44, 4, 92, 16);";

	# now run all SQL commands
	if (!empty($sql)) {
		for ($a = 0; $a < count($sql); $a++) {
			$result = db_execute($sql[$a]);
		}
	}

}

/**
 * Version information (used by update plugin)
 */
function aggregate_version () {

return array( 'name' 	=> 'aggregate',
			'version' 	=> '0.70 B2',
			'longname'	=> 'Create Aggregate Graphs',
			'author'	=> 'Reinhard Scheck',
			'homepage'	=> 'http://forums.cacti.net/about30695.html',
			'email'		=> 'Gandalf@forums.cacti.net',
#			'url'		=> 'http://forums.cacti.net/about30695.html'
			);
}

/**
 * Draw navigation texts
 * @arg $nav		all navigation texts
 */
function aggregate_draw_navigation_text ($nav) {
    // Displayed navigation text under the blue tabs of Cacti
	$nav["color_templates.php:"] 				= array("title" => "颜色模板", "mapping" => "index.php:", "url" => "color_templates.php", "level" => "1");
	$nav["color_templates.php:template_edit"] 	= array("title" => "(编辑)", "mapping" => "index.php:,color_templates.php:", "url" => "", "level" => "2");
	$nav["color_templates.php:actions"] 		= array("title" => "动作", "mapping" => "index.php:,color_templates.php:", "url" => "", "level" => "2");
	$nav["color_templates_items.php:item_edit"] = array("title" => "颜色模板对象", "mapping" => "index.php:,color_templates.php:,color_templates.php:template_edit", "url" => "", "level" => "3");

    return $nav;
}

/**
 * Setup the new dropdown action for Graph Management
 * @arg $action		actions to be performed from dropdown
 */
function aggregate_graphs_action_array($action) {
	$action['plugin_aggregate'] = '添加聚合图形';
	return $action;
}

/**
 * Setup forms needed for this plugin
 */
function aggregate_config_form () {
	# globals defined for use with Color Templates
	global $struct_aggregate, $struct_color_template, $struct_color_template_item;
	global $fields_color_template_template_edit, $help_file, $config;

	# unless a hook for 'global_constants' is available, all DEFINEs go here
	define("AGGREGATE_GRAPH_TYPE_KEEP", 1);
	define("AGGREGATE_GRAPH_TYPE_STACK", 2);
	define("AGGREGATE_GRAPH_TYPE_LINE", 3);

	define("AGGREGATE_TOTAL_NONE", 1);
	define("AGGREGATE_TOTAL_SIMILAR", 2);
	define("AGGREGATE_TOTAL_ALL", 3);

	$help_file = $config['url_path'] . "/plugins/aggregate/aggregate_manual.pdf";

	# ------------------------------------------------------------
	# Main Aggregate Parameters
	# ------------------------------------------------------------
	/* file: aggregate.php */
	$struct_aggregate = array(
		"title_format" => array(
			"friendly_name" => "标题",
			"method" => "textbox",
			"max_length" => "255",
			"value" => "|arg1:title_format|",
			"description" => "新的聚合图形的标题."
			),
		"gprint_prefix" => array(
			"friendly_name" => "前缀",
			"method" => "textbox",
			"max_length" => "255",
			"value" => "|arg1:gprint_prefix|",
			"description" => "所有GRPINT行前缀,用来分别不同的主机."
			),
		"aggregate_graph_type" => array(
			"friendly_name" => "图形类型",
			"method" => "radio",
			"default" => AGGREGATE_GRAPH_TYPE_STACK,
			"description" => "使用这个选项添加图形",
			"items" => array(
				AGGREGATE_GRAPH_TYPE_KEEP => array(
					"radio_value" => AGGREGATE_GRAPH_TYPE_KEEP,
					"radio_caption" => "保持图形类型"
					),
				AGGREGATE_GRAPH_TYPE_STACK => array(
					"radio_value" => AGGREGATE_GRAPH_TYPE_STACK,
					"radio_caption" => "转换成AREA/STACK图形"
					),
				AGGREGATE_GRAPH_TYPE_LINE => array(
					"radio_value" => AGGREGATE_GRAPH_TYPE_LINE,
					"radio_caption" => "转换成一个LINE1图形"
					)
				)
			),
		"aggregate_total" => array(
			"friendly_name" => "求和GPRINT",
			"method" => "radio",
			"default" => AGGREGATE_TOTAL_NONE,
			"description" => "请选对象的择求和方法",
			"items" => array(
				AGGREGATE_TOTAL_NONE => array(
					"radio_value" => AGGREGATE_TOTAL_NONE,
					"radio_caption" => "不求和"
					),
				AGGREGATE_TOTAL_SIMILAR => array(
					"radio_value" => AGGREGATE_TOTAL_SIMILAR,
					"radio_caption" => "同类数据源求和"
					),
				AGGREGATE_TOTAL_ALL => array(
					"radio_value" => AGGREGATE_TOTAL_ALL,
					"radio_caption" => "所有数据源求和"
					)
				)
			),
	);

	# ------------------------------------------------------------
	# Color Templates
	# ------------------------------------------------------------
	/* file: color_templates.php, action: template_edit */
	$struct_color_template = array(
		"title" => array(
			"friendly_name" => "标题",
			"method" => "textbox",
			"max_length" => "255",
			"default" => "",
			"description" => "这个颜色模板的名称."
			),
		);

	/* file: color_templates.php, action: item_edit */
	$struct_color_template_item = array(
		"color_id" => array(
			"friendly_name" => "颜色",
			"method" => "drop_color",
			"default" => "0",
			"description" => "一个漂亮的颜色"
			),
		);

	/* file: color_templates.php, action: template_edit */
	$fields_color_template_template_edit = array(
		"name" => array(
			"method" => "textbox",
			"friendly_name" => "名称",
			"description" => "这个模板的名称.",
			"value" => "|arg1:name|",
			"max_length" => "255",
			),
		"color_template_id" => array(
			"method" => "hidden_zero",
			"value" => "|arg1:color_template_id|"
			),
		"save_component_color" => array(
			"method" => "hidden",
			"value" => "1"
			)
		);

}

/**
 * Setup arrays needed for this plugin
 */
function aggregate_config_arrays () {
	# globals changed
	global $user_auth_realms, $user_auth_realm_filenames;
	global $menu;

	if (function_exists('api_plugin_register_realm')) {
		# register all php modules required for this plugin
		api_plugin_register_realm('aggregate', 'aggregate_templates.php,aggregate_templates_items.php,color_templates.php,color_templates_items.php', '聚合插件 -> 添加颜色模板', 1);
	} else {
		# realms
		# Check this Item for each user to allow access to Aggregate Templates
		$user_auth_realms[72]='聚合插件 -> 添加颜色模板';
		# these are the files protected by our realm id
		$user_auth_realm_filenames['aggregate_templates.php'] = 72;
		$user_auth_realm_filenames['aggregate_templates_items.php'] = 72;
		$user_auth_realm_filenames['color_templates.php'] = 72;
		$user_auth_realm_filenames['color_templates_items.php'] = 72;
	}

	# menu titles
	$menu["模板"]['plugins/aggregate/color_templates.php'] = "颜色模板";
}

?>
