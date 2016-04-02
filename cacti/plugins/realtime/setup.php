<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2008 Mathieu Virbel <mathieu.v@capensis.fr>               |
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
 |                                                                         |
 |  Author ......... Mathieu Virbel                                        |
 |  Contact ........ mathieu.v@capensis.fr                                 |
 |  Home Site ...... http://capensis.org/                                  |
 |  Program ........ realtime                                              |
 |  Version ........ 0.42                                                  |
 |  Purpose ........ add realtime view of any graph                        |
 +-------------------------------------------------------------------------+
*/

define('REALTIME_REALM_ID', '81');

function plugin_init_realtime() {
	global $plugin_hooks;

	// This is where you hook into the plugin archetecture
	$plugin_hooks['config_arrays']['realtime']            = 'realtime_config_arrays';
	$plugin_hooks['graph_buttons']['realtime']            = 'realtime_graph_buttons';
	$plugin_hooks['graph_buttons_thumbnails']['realtime'] = 'realtime_graph_buttons';
	$plugin_hooks['config_settings']['realtime']          = 'realtime_config_settings';
}

function plugin_realtime_install() {
	api_plugin_register_hook('realtime', 'config_arrays',            'realtime_config_arrays',   "setup.php");
	api_plugin_register_hook('realtime', 'config_settings',          'realtime_config_settings', "setup.php");
	api_plugin_register_hook('realtime', 'graph_buttons',            'realtime_graph_buttons',   "setup.php");
	api_plugin_register_hook('realtime', 'graph_buttons_thumbnails', 'realtime_graph_buttons',   "setup.php");

	api_plugin_register_realm('realtime', 'graph_image_rt.php,graph_popup_rt.php,graph_ajax_rt.php', 'Plugin -> Realtime', 1);

	realtime_setup_table_new ();
}

function plugin_realtime_uninstall () {
	/* Do any extra Uninstall stuff here */
}

function plugin_realtime_check_config () {
	/* Here we will check to ensure everything is configured */
	realtime_check_upgrade();
	return true;
}

function plugin_realtime_upgrade () {
	/* Here we will upgrade to the newest version */
	realtime_check_upgrade();
	return false;
}

function plugin_realtime_version () {
	return realtime_version();
}

function realtime_check_upgrade () {
	global $config;

	$files = array('index.php', 'plugins.php', 'realtime.php');
	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files)) {
		return;
	}

	$current = plugin_realtime_version();
	$current = $current['version'];
	$old     = db_fetch_row("SELECT * FROM plugin_config WHERE directory='realtime'");

	if (sizeof($old) && $current != $old["version"]) {
		if ($old < "4.1" || $old = '') {
			api_plugin_register_realm('realtime', 'graph_image_rt.php, graph_popup_rt.php, graph_ajax_rt.php', 'Plugin -> Realtime', 1);

			/* get the realm id's and change from old to new */
			$user  = db_fetch_cell("SELECT id FROM plugin_realms WHERE file='graph_image_rt.php'")+100;
			$users = db_fetch_assoc("SELECT user_id FROM user_auth_realm WHERE realm_id=36");
			if (sizeof($users)) {
				foreach($users as $u) {
					db_execute("INSERT INTO user_auth_realm
						(realm_id, user_id) VALUES ($user, " . $u["user_id"] . ")
						ON DUPLICATE KEY UPDATE realm_id=VALUES(realm_id)");
					db_execute("DELETE FROM user_auth_realm
						WHERE user_id=" . $u["user_id"] . "
						AND realm_id=$user");
				}
			}
		}

		/* if the plugin is installed and/or active */
		if ($old["status"] == 1 || $old["status"] == 4) {
			/* re-register the hooks */
			plugin_realtime_install();

			/* perform a database upgrade */
			realtime_database_upgrade();
		}

		/* update the plugin information */
		$info = plugin_realtime_version();
		$id   = db_fetch_cell("SELECT id FROM plugin_config WHERE directory='realtime'");
		db_execute("UPDATE plugin_config
			SET name='" . $info["longname"] . "',
			author='"   . $info["author"]   . "',
			webpage='"  . $info["homepage"] . "',
			version='"  . $info["version"]  . "'
			WHERE id='$id'");
	}
}

function realtime_database_upgrade () {
}

function realtime_check_dependencies() {
	global $plugins, $config;

	return true;
}

function realtime_setup_table_new () {
}

function realtime_version () {
	return array(
		'name'     => 'realtime',
		'version'  => '0.43',
		'longname' => 'Realtime Graph Viewer',
		'author'   => 'Mathieu Virbel',
		'homepage' => 'http://www.cacti.net',
		'email'    => 'mathieu.v@capensis.fr',
		'url'      => 'http://versions.cactiusers.org'
	);
}

function realtime_config_settings () {
//	global $tabs, $settings, $realtime_refresh, $realtime_window, $realtime_sizes;
//
//	/* check for an upgrade */
//	plugin_realtime_check_config();
//
//	$tabs["misc"] = "Misc";
//
//	$temp = array(
//		"realtime_header" => array(
//			"friendly_name" => "实时图形",
//			"method" => "spacer",
//			),
//		"realtime_gwindow" => array(
//			"friendly_name" => "图形时间段",
//			"description" => "您默认查看图形的时间段.",
//			"method" => "drop_array",
//			"default" => 60,
//			"array" => $realtime_window,
//			),
//		"realtime_sync" => array(
//			"friendly_name" => "同步图形",
//			"description" => "选项修改以后,所有图形都会使用该设置.",
//			"method" => "checkbox",
//			"default" => "on"
//			),
//		"realtime_ajax" => array(
//			"friendly_name" => "使用 Ajax",
//			"description" => "当使用这个功能,更新图形时不需要刷新浏览器.但某些浏览器可能不支持 Ajax 方法.",
//			"method" => "checkbox",
//			"default" => "on"
//			),
//		"realtime_interval" => array(
//			"friendly_name" => "刷新周期",
//			"description" => "图形每隔多久更新一次.",
//			"method" => "drop_array",
//			"default" => 15,
//			"array" => $realtime_refresh,
//			),
//	);
//
//	if (isset($settings["misc"])) {
//		$settings["misc"] = array_merge($settings["misc"], $temp);
//	}else {
//		$settings["misc"] = $temp;
//	}
}

function realtime_config_arrays () {
	global $user_auth_realm_filenames, $realtime_refresh, $realtime_window;

	$realtime_window = array(
		30   => "30 秒",
		45   => "45 秒",
		60   => "1 分钟",
		90   => "1.5 分钟",
		120  => "2 分钟",
		300  => "5 分钟",
		600  => "10 分钟",
		1200 => "20 分钟",
		1800 => "30 分钟",
		3600 => "1 小时");

	$realtime_refresh = array(
		5   => "5 秒",
		10  => "10 秒",
		15  => "15 秒",
		20  => "20 秒",
		30  => "30 秒",
		60  => "1 分钟",
		120 => "2 分钟");
}

function realtime_graph_buttons($args) {
	global $config;

	$local_graph_id = $args[1]['local_graph_id'];

	if (api_user_realm_auth("graph_popup_rt.php")) {
		echo "<a href='#' onclick=\"window.open('".$config['url_path']."plugins/realtime/graph_popup_rt.php?local_graph_id=".$local_graph_id."', 'popup_".$local_graph_id."', 'toolbar=no,menubar=no,location=no,scrollbars=no,status=no,titlebar=no,width=650,height=300')\"><img src='".$config['url_path']."plugins/realtime/monitor_rt.gif' border='0' alt='Realtime' title='实时图形' style='padding: 3px;'></a><br/>";
	}

	realtime_setup_table();
}

function realtime_setup_table() {
	global $config, $database_default;

	include_once($config["library_path"] . "/database.php");

	/* tables for realtime */
	$result = db_fetch_assoc("SHOW TABLES LIKE 'poller_output_rt%%'");

	if (count($result) == 0) {
		db_execute('
			CREATE TABLE IF NOT EXISTS poller_output_rt (
				local_data_id mediumint(8) unsigned NOT NULL default \'0\',
				rrd_name varchar(19) NOT NULL default \'\',
				`time` datetime NOT NULL default \'0000-00-00 00:00:00\',
				output text NOT NULL,
				poller_id int(11) NOT NULL,
				PRIMARY KEY  (local_data_id,rrd_name,`time`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		');
	}
}

?>
