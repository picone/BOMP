<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

include("./include/auth.php");

define("MAX_DISPLAY_PAGES", 21);

$actions = array("install" => "安装",
	"enable" => "启用",
	"disable" => "禁用",
	"uninstall" => "卸载",
//	"check" => "Check"
);

$status_names = array(
	-2 => '已禁用',
	-1 => '激活',
	0 => '未安装',
	1 => '激活',
	2 => '等待配置',
	3 => '等待更新',
	4 => '已安装'
);

/* get the comprehensive list of plugins */
$pluginslist = retrieve_plugin_list();

/* Check to see if we are installing, etc... */
$modes = array('installold', 'uninstallold', 'install', 'uninstall', 'disable', 'enable', 'check', 'moveup', 'movedown');

if (isset($_GET['mode']) && in_array($_GET['mode'], $modes)  && isset($_GET['id'])) {
	input_validate_input_regex(get_request_var("id"), "^([a-zA-Z0-9]+)$");

	$mode = $_GET['mode'];
	$id   = sanitize_search_string($_GET['id']);

	switch ($mode) {
		case 'installold':
			api_plugin_install_old($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'uninstallold':
			api_plugin_uninstall_old($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'install':
			api_plugin_install($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'uninstall':
			if (!in_array($id, $pluginslist)) break;
			api_plugin_uninstall($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'disable':
			if (!in_array($id, $pluginslist)) break;
			api_plugin_disable($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'enable':
			if (!in_array($id, $pluginslist)) break;
			api_plugin_enable($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'check':
			if (!in_array($id, $pluginslist)) break;
			break;
		case 'moveup':
			if (!in_array($id, $pluginslist)) break;
			if (is_system_plugin($id)) break;
			api_plugin_moveup($id);
			header("Location: plugins.php");
			exit;
			break;
		case 'movedown':
			if (!in_array($id, $pluginslist)) break;
			if (is_system_plugin($id)) break;
			api_plugin_movedown($id);
			header("Location: plugins.php");
			exit;
			break;
	}
}

function retrieve_plugin_list () {
	$pluginslist = array();
	$temp = db_fetch_assoc('SELECT directory FROM plugin_config ORDER BY name');
	foreach ($temp as $t) {
		$pluginslist[] = $t['directory'];
	}
	return $pluginslist;
}

include("./include/top_header.php");

update_show_current();

include("./include/bottom_footer.php");


function api_plugin_install_old ($plugin) {
	global $config;
	if (!file_exists($config['base_path'] . "/plugins/$plugin/setup.php")) {
		return false;
	}
	$oldplugins = read_config_option('oldplugins');
	$oldplugins = explode(',', $oldplugins);
	if (!in_array($plugin, $oldplugins)) {
		include_once($config['base_path'] . "/plugins/$plugin/setup.php");
		$function = 'plugin_init_' . $plugin;
		if (function_exists($function)){
			$oldplugins[] = $plugin;
			$oldplugins = implode(',', $oldplugins);
			set_config_option('oldplugins', $oldplugins);
			unset($_SESSION['sess_config_array']['oldplugins']);
			return true;
		} else {
			return false;
		}
	}
	return false;
}

function api_plugin_uninstall_old ($plugin) {
	global $config;
	$oldplugins = read_config_option('oldplugins');
	$oldplugins = explode(',', $oldplugins);
	if (!empty($oldplugins)) {
		if (in_array($plugin, $oldplugins)) {
			for ($a = 0; $a < count($oldplugins); $a++) {
				if ($oldplugins[$a] == $plugin) {
					unset($oldplugins[$a]);
					break;
				}
			}
			$oldplugins = implode(',', $oldplugins);
			set_config_option('oldplugins', $oldplugins);
			unset($_SESSION['sess_config_array']['oldplugins']);
			return true;
		}
	}
	return false;
}

function update_show_updates () {
	global $pluginslist, $colors, $config, $plugin_architecture;

	$cinfo = array();
	sort($pluginslist);

	$cinfo = update_get_plugin_info ();

	$x = 0;

	$info = update_get_cached_plugin_info();

	$cactinew = update_check_if_newer($cinfo['cacti']['version'], $info['cacti']['version']) ;
	if (isset($cinfo['cacti_plugin_arch']['version'])) {
		$archnew =  update_check_if_newer($cinfo['cacti_plugin_arch']['version'], $info['cacti_plugin_arch']['version']);
	} else {
		$archnew = 0;
	}

	if ($cactinew) {
		$x++;
		print "<tr><td width='25%' valign=top><table width='100%'>";
		html_header(array("Cacti"), 2);
		form_alternate_row_color($colors["alternate"],$colors["light"], 0);
		print "<td width='25%'><strong>版本:</strong></td><td>" . $config["cacti_version"] . "</td></tr>";
		form_alternate_row_color($colors["alternate"],$colors["light"], 0);
		print "<td valign=top><strong>更改:</strong></td><td>" . str_replace("\n", '<br>', $info['cacti']['changes']) . "</td></tr></table>";
	}
	if (isset($plugin_architecture['version']) && $archnew) {
		$x++;
		print "<table width='100%'>";
		html_header(array("插件平台"), 2);
		form_alternate_row_color($colors["alternate"],$colors["light"], 0);
		print "<td width='25%'><strong>版本:</strong></td><td>" . $plugin_architecture['version'] . "</td>";
		form_alternate_row_color($colors["alternate"],$colors["light"], 0);
		print "<td valign=top><strong>更改:</strong></td><td>" . str_replace("\n", '<br>', $info['cacti_plugin_arch']['changes']) . "</td></tr></table>";
	}
	print "<table width='100%' cellspacing=0 cellpadding=3>";

	foreach ($pluginslist as $plugin) {
		if (isset($cinfo[$plugin]) && update_check_if_newer($cinfo[$plugin]['version'], $info[$plugin]['version'])) {
			$x++;
			print "<table width='100%'>";
			html_header(array((isset($cinfo[$plugin]['longname']) ? $cinfo[$plugin]['longname'] : $plugin)), 2);
			form_alternate_row_color($colors["alternate"],$colors["light"], 0);
			print "<td width='50%'><strong>目录:</strong></td><td>$plugin</td>";
			form_alternate_row_color($colors["alternate"],$colors["light"], 0);
			print "<td><strong>版本:</strong></td><td>" . $info[$plugin]['version'] . "</td>";
			form_alternate_row_color($colors["alternate"],$colors["light"], 0);
			print "<td><strong>作者:</strong></td><td>" . (isset($cinfo[$plugin]['author']) && $cinfo[$plugin]['author'] != '' ? (isset($cinfo[$plugin]['email']) && $cinfo[$plugin]['email'] != '' ? "<a href='mailto:" . $cinfo[$plugin]['email'] . "'>" . $cinfo[$plugin]['author'] . "</a>"  : $cinfo[$plugin]['author']) : "") . "</td>";
			form_alternate_row_color($colors["alternate"],$colors["light"], 0);
			print "<td><strong>主页:</strong></td><td>" . (isset($cinfo[$plugin]['webpage']) && $cinfo[$plugin]['webpage'] != '' ? "<a href='" . $cinfo[$plugin]['webpage'] . "'>" . $cinfo[$plugin]['webpage'] . "</a>" : "") . "</td>";
			form_alternate_row_color($colors["alternate"],$colors["light"], 0);
			print "<td valign=top><strong>更改:</strong></td><td>" . str_replace("\n", '<br>', $info[$plugin]['changes']) . "</td>";

			print "</tr></table>";
		}
	}
	if ($x == 0)
		print "<br><center><b>当前未更新!</b></center><br>";
	print "</table>";
	html_end_box(TRUE);
}

function update_check_if_newer() {
	return false;
}

function plugins_temp_table_exists($table) {
	return sizeof(db_fetch_row("SHOW TABLES LIKE '$table'"));
}

function plugins_load_temp_table() {
	global $config, $plugins;

	$pluginslist = retrieve_plugin_list();

	if (isset($_SESSION["plugin_temp_table"])) {
		$table = $_SESSION["plugin_temp_table"];
	}else{
		$table = "plugin_temp_table_" . rand();
	}

	while (true) {
		if (!plugins_temp_table_exists($table)) {
			$_SESSION["plugin_temp_table"] = $table;
			db_execute("CREATE TEMPORARY TABLE $table LIKE plugin_config");
			db_execute("INSERT INTO $table SELECT * FROM plugin_config");
			break;
		}else{
			$table = "plugin_temp_table_" . rand();
		}
	}

	$path = $config['base_path'] . '/plugins/';
	$dh = opendir($path);
	while (($file = readdir($dh)) !== false) {
		if ((is_dir("$path/$file")) && (file_exists("$path/$file/setup.php")) && (!in_array($file, $pluginslist))) {
			if (!function_exists('plugin_' . $file . '_install')) {
				include_once("$path/$file/setup.php");
				if (!function_exists('plugin_' . $file . '_install') && function_exists($file . '_version')) {
					$function = $file . '_version';
					$cinfo[$file] = $function();
					if (!isset($cinfo[$file]['author'])) $cinfo[$file]['author'] = 'Unknown';
					if (!isset($cinfo[$file]['homepage'])) $cinfo[$file]['homepage'] = 'Not Stated';
					if (isset($cinfo[$file]['webpage'])) $cinfo[$file]['homepage'] = $cinfo[$file]['webpage'];
					if (!isset($cinfo[$file]['longname'])) $cinfo[$file]['longname'] = ucfirst($file);
					$cinfo[$file]['status'] = -2;
					if (in_array($file, $plugins)) {
						$cinfo[$file]['status'] = -1;
					}
					db_execute("REPLACE INTO $table (directory, name, status, author, webpage, version)
						VALUES ('" .
							$file . "', '" .
							$cinfo[$file]['longname'] . "', '" .
							$cinfo[$file]['status'] . "', '" .
							$cinfo[$file]['author'] . "', '" .
							$cinfo[$file]['homepage'] . "', '" .
							$cinfo[$file]['version'] . "')");
					$pluginslist[] = $file;
				} else if (function_exists('plugin_' . $file . '_install') && function_exists('plugin_' . $file . '_version')) {
					$function = $file . '_version';
					$cinfo[$file] = $function();
					$cinfo[$file]['status'] = 0;
					if (!isset($cinfo[$file]['author'])) $cinfo[$file]['author'] = 'Unknown';
					if (!isset($cinfo[$file]['homepage'])) $cinfo[$file]['homepage'] = 'Not Stated';
					if (isset($cinfo[$file]['webpage'])) $cinfo[$file]['homepage'] = $cinfo[$file]['webpage'];
					if (!isset($cinfo[$file]['longname'])) $cinfo[$file]['homepage'] = ucfirst($file);
					db_execute("REPLACE INTO $table (directory, name, status, author, webpage, version)
						VALUES ('" .
							$file . "', '" .
							$cinfo[$file]['longname'] . "', '" .
							$cinfo[$file]['status'] . "', '" .
							$cinfo[$file]['author'] . "', '" .
							$cinfo[$file]['homepage'] . "', '" .
							$cinfo[$file]['version'] . "')");
					$pluginslist[] = $file;
				}
			}else{
				cacti_log("WARNING: Install function already exists for plugin directory '$file'", false);
			}
		}
	}
	closedir($dh);

	return $table;
}

function update_show_current () {
	global $plugins, $pluginslist, $colors, $plugin_architecture, $config, $status_names, $actions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up search string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_plugins_filter");
		kill_session_var("sess_plugins_sort_column");
		kill_session_var("sess_plugins_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
		$_REQUEST["page"] = 1;
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("filter", "sess_plugins_filter", "");
	load_current_session_value("sort_column", "sess_plugins_sort_column", "name");
	load_current_session_value("sort_direction", "sess_plugins_sort_direction", "ASC");
	load_current_session_value("page", "sess_plugins_current_page", "1");

	$table = plugins_load_temp_table();

	html_start_box("<strong>插件管理</strong> (Cacti版本: " . $config["cacti_version"] .
		(isset($plugin_architecture['version']) ? ", 插件平台版本: " . $plugin_architecture['version']:"") .
		")", "100%", $colors["header"], "3", "center", "");

	?>
	<tr bgcolor="#<?php print $colors['panel'];?>">
		<td class="noprint">
		<form name="form_plugins" method="get" action="plugins.php">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr class="noprint">
					<td nowrap style='white-space: nowrap;' width="50">
						搜索:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print get_request_var_request("filter");?>">
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" value="确定" title="设置/刷新过滤器">
						<input type="submit" name="clear_x" value="清除" title="清除过滤器">
					</td>
				</tr>
			</table>
			<input type='hidden' name='page' value='1'>
		</form>
		</td>
	</tr>
	<?php

	html_end_box();

	/* print checkbox form for validation */
	print "<form name='chk' method='post' action='plugins.php'>\n";

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE ($table.name LIKE '%%" . get_request_var_request("filter") . "%%')";

	if (get_request_var_request("sort_column") == "version") {
		$sortc = "version+0";
	}else{
		$sortc = get_request_var_request("sort_column");
	}

	if (get_request_var_request("sort_column") == "id") {
		$sortd = "ASC";
	}else{
		$sortd = get_request_var_request("sort_direction");
	}

	$total_rows = db_fetch_cell("SELECT
		count(*)
		FROM $table
		$sql_where");

	$plugins = db_fetch_assoc("SELECT *
		FROM $table
		$sql_where
		ORDER BY " . $sortc . " " . $sortd . "
		LIMIT " . (read_config_option("num_rows_device")*(get_request_var_request("page")-1)) . "," . read_config_option("num_rows_device"));

	db_execute("DROP TABLE $table");

	/* generate page list */
	$url_page_select = get_page_list(get_request_var_request("page"), MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "plugins.php?filter=" . get_request_var_request("filter"));

	if ($total_rows == 0) {
		$nav = "<tr bgcolor='#" . $colors["header"] . "'>
				<td colspan='9'>
					<table width='100%' cellspacing='0' cellpadding='0' border='0'>
						<tr>
							<td align='center' class='textHeaderDark'>
								未找到插件
							</td>\n
						</tr>
					</table>
				</td>
			</tr>\n";
	}elseif ($total_rows < read_config_option("num_rows_device")) {
		$nav = "<tr bgcolor='#" . $colors["header"] . "'>
				<td colspan='9'>
					<table width='100%' cellspacing='0' cellpadding='0' border='0'>
						<tr>
							<td align='center' class='textHeaderDark'>
								显示全部 $total_rows 行
							</td>\n
						</tr>
					</table>
				</td>
			</tr>\n";
	}else{
		$nav = "<tr bgcolor='#" . $colors["header"] . "'>
				<td colspan='9'>
					<table width='100%' cellspacing='0' cellpadding='0' border='0'>
						<tr>
							<td align='left' class='textHeaderDark'>
								<strong>&lt;&lt; "; if (get_request_var_request("page") > 1) { $nav .= "<a class='linkOverDark' href='" . htmlspecialchars("plugins.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page")-1)) . "'>"; } $nav .= "上一页"; if (get_request_var_request("page") > 1) { $nav .= "</a>"; } $nav .= "</strong>
							</td>\n
							<td align='center' class='textHeaderDark'>
								" . ((read_config_option("num_rows_device")*(get_request_var_request("page")-1))+1) . " 到 " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device")*get_request_var_request("page")))) ? $total_rows : (read_config_option("num_rows_device")*get_request_var_request("page"))) . " 行,共 $total_rows 行 [ 第 $url_page_select 页 ]
							</td>\n
							<td align='right' class='textHeaderDark'>
								<strong>"; if ((get_request_var_request("page") * read_config_option("num_rows_device")) < $total_rows) { $nav .= "<a class='linkOverDark' href='" . htmlspecialchars("plugins.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page")+1)) . "'>"; } $nav .= "下一页"; if ((get_request_var_request("page") * read_config_option("num_rows_device")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
							</td>\n
						</tr>
					</table>
				</td>
			</tr>\n";
	}

	print $nav;

	$display_text = array(
		"nosort" => array("操作", ""),
		"directory" => array("名称", "ASC"),
		"id" => array("加载顺序", "ASC"),
		"name" => array("描述", "ASC"),
		"nosort1" => array("类型", "ASC"),
		"status" => array("状态", "ASC"),
		"author" => array("作者", "ASC"),
		"webpage" => array("网站", "ASC"),
		"version" => array("版本", "ASC"));

	html_header_sort($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"), 1);

	$i = 0;
	if (sizeof($plugins)) {
		if (get_request_var_request("sort_column") == "id") {
			$inst_system_plugins = get_system_plugins($plugins);
			if (sizeof($inst_system_plugins)) {
				foreach($inst_system_plugins as $plugin) {
					form_alternate_row_color($colors["alternate"], $colors["light"], $i); $i++;
					print format_plugin_row($plugin, false, false, true);
				}
			}
		}

		$j = 0;
		foreach ($plugins as $plugin) {
			if ((isset($plugins[$j+1]) && $plugins[$j+1]['status'] < 0) || (!isset($plugins[$j+1]))) {
				$last_plugin = true;
			}else{
				$last_plugin = false;
			}
			if ($plugin['status'] <= 0 || is_system_plugin($plugin) || (get_request_var_request('sort_column') != 'id')) {
				$load_ordering = false;
			}else{
				$load_ordering = true;
			}

			if (get_request_var_request("sort_column") == "id") {
				if (!is_system_plugin($plugin)) {
					form_alternate_row_color($colors["alternate"], $colors["light"], $i);
					print format_plugin_row($plugin, $last_plugin, $load_ordering, false);
					$i++;
				}
			}else{
				form_alternate_row_color($colors["alternate"], $colors["light"], $i);
				print format_plugin_row($plugin, $last_plugin, $load_ordering, is_system_plugin($plugin));
				$i++;
			}

			$j++;
		}

		print $nav;
	}else{
		print "<tr><td><em>未找到插件</em></td></tr>";
	}

	html_end_box(false);

	html_start_box("", "100%", $colors["header"], "3", "center", "");
	echo "<tr><td colspan=10><strong>说明:</strong>请按'加载顺序'排序以更改加载顺序.<br><strong>说明:</strong>系统插件无法排序.</td></tr>";
	html_end_box();

	print "</form>\n";
}

function format_plugin_row($plugin, $last_plugin, $include_ordering, $system_plugin) {
	global $status_names;
	static $first_plugin = true;

	$row = plugin_actions($plugin);
	$row .= "<td><strong>" . (strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", ucfirst($plugin["directory"])) : ucfirst($plugin["directory"])) . "</strong></td>";
	if ($include_ordering) {
		$row .= "<td style='white-space:nowrap;'>";
		if (!$first_plugin) {
			$row .= "<a href='" . htmlspecialchars("plugins.php?mode=moveup&id=" . $plugin['directory']) . "' title='上个插件之前' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/move_up.gif'></a>";
		}else{
			$row .= "<a href='#' title='无法缩减' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/view_none.gif'></a>";
		}
		if (!$last_plugin) {
			$row .= "<a href='" . htmlspecialchars("plugins.php?mode=movedown&id=" . $plugin['directory']) . "' title='下个插件之后' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/move_down.gif'></a>";
		}else{
			$row .= "<a href='#' title='无法增加' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/view_none.gif'></a>";
		}
		$row .= "</td>\n";
	}else{
		$row .= "<td></td>\n";
	}

	$row .= "<td style='white-space:nowrap;'>" . (strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $plugin["name"]) : $plugin["name"]) . "</td>\n";
	$row .= "<td style='white-space:nowrap;'>" . ($system_plugin ? "系统": ($plugin['status'] < 0 ? "老插件平台":"常规")) . "</td>\n";
	$row .= "<td style='white-space:nowrap;'>" . $status_names[$plugin["status"]] . "</td>\n";
	$row .= "<td style='white-space:nowrap;'>" . $plugin["author"] . "</td>\n";
	$row .= "<td><a href='" . htmlspecialchars($plugin["webpage"]) . "'>" . htmlspecialchars($plugin["webpage"]) . "</a></td>\n";
	$row .= "<td>" . $plugin["version"] . "</td>\n";
	$row .= "</tr>\n";

	if ($include_ordering) {
		$first_plugin = false;
	}

	return $row;
}

function plugin_actions($plugin) {
	$link = "<td>";
	switch ($plugin['status']) {
		case "-2": // Old PA Not Installed
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=installold&id=" . $plugin['directory']) . "' title='安装老插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/install_icon.png'></a>";
			$link .= "<img style='padding:1px;' border='0' align='absmiddle' src='images/view_none.gif'>";
			break;
		case "-1":	// Old PA Currently Active
			$oldplugins = read_config_option('oldplugins');
			$oldplugins = explode(',', $oldplugins);
			if (in_array($plugin['directory'], $oldplugins)) {
				$link .= "<a href='" . htmlspecialchars("plugins.php?mode=uninstallold&id=" . $plugin['directory']) . "' title='卸载老插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/uninstall_icon.gif'></a>";
			} else {
				$link .= "<a href='#' title='请从config.php卸载' class='linkEditMain'><img style='padding:1px;' align='absmiddle' border='0' src='images/install_icon_disabled.png'></a>";
			}
			$link .= "<img style='padding:1px;' border='0' align='absmiddle' src='images/view_none.gif'>";
			break;
		case "0": // Not Installed
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=install&id=" . $plugin['directory']) . "' title='安装插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/install_icon.png'></a>";
			$link .= "<img style='padding:1px;' border='0' align='absmiddle' src='images/view_none.gif'>";
			break;
		case "1":	// Currently Active
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=uninstall&id=" . $plugin['directory']) . "' title='卸载插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/uninstall_icon.gif'></a>";
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=disable&id=" . $plugin['directory']) . "' title='禁用插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/disable_icon.png'></a>";
			break;
		case "4":	// Installed but not active
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=uninstall&id=" . $plugin['directory']) . "' title='卸载插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/uninstall_icon.gif'></a>";
			$link .= "<a href='" . htmlspecialchars("plugins.php?mode=enable&id=" . $plugin['directory']) . "' title='启用插件' class='linkEditMain'><img style='padding:1px;' border='0' align='absmiddle' src='images/enable_icon.png'></a>";
			break;
		default: // Old PIA
			$link .= "<a href='#' title='Please Install/Uninstall from config.php' class='linkEditMain'><img style='padding:1px;' align='absmiddle' border='0' src='images/install_icon_disabled.png'></a>";
			$link .= "<a href='#' title='Enabling from the UI is not supported' class='linkEditMain'><img style='padding:1px;' align='absmiddle' border='0' src='images/enable_icon_disabled.png'></a>";
			break;
	}
	$link .= "</td>";

	return $link;
}

function is_system_plugin($plugin) {
	global $plugins_system;

	if (is_array($plugin)) {
		$plugin = $plugin["directory"];
	}

	if (!in_array($plugin, $plugins_system)) {
		return false;
	}else{
		return true;
	}
}

function get_system_plugins($plugins) {
	$inst_system_plugins = array();

	if (sizeof($plugins)) {
		foreach($plugins as $plugin) {
			if (is_system_plugin($plugin)) {
				$inst_system_plugins[] = $plugin;
			}
		}
	}

	return $inst_system_plugins;
}


