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

$using_guest_account = false;
$show_console_tab = true;

$oper_mode = api_plugin_hook_function('top_header', OPER_MODE_NATIVE);
if ($oper_mode == OPER_MODE_RESKIN) {
	return;
}

/* ================= input validation ================= */
input_validate_input_number(get_request_var_request("local_graph_id"));
input_validate_input_number(get_request_var_request("graph_start"));
input_validate_input_number(get_request_var_request("graph_end"));
/* ==================================================== */

if (read_config_option("auth_method") != 0) {
	/* at this point this user is good to go... so get some setting about this
	user and put them into variables to save excess SQL in the future */
	$current_user = db_fetch_row("select * from user_auth where id=" . $_SESSION["sess_user_id"]);

	/* find out if we are logged in as a 'guest user' or not */
	if (db_fetch_cell("select id from user_auth where username='" . read_config_option("guest_user") . "'") == $_SESSION["sess_user_id"]) {
		$using_guest_account = true;
	}

	/* find out if we should show the "console" tab or not, based on this user's permissions */
	if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where realm_id=8 and user_id=" . $_SESSION["sess_user_id"])) == 0) {
		$show_console_tab = false;
	}
}

/* need to correct $_SESSION["sess_nav_level_cache"] in zoom view */
if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "zoom") {
	$_SESSION["sess_nav_level_cache"][2]["url"] = "graph.php?local_graph_id=" . $_REQUEST["local_graph_id"] . "&rra_id=all";
}

$page_title = api_plugin_hook_function('page_title', draw_navigation_text("title"));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php
	if (isset($_SESSION["custom"]) && $_SESSION["custom"] == true) {
		print "<meta http-equiv=refresh content='99999'>\r\n";
	}else{
		$refresh = api_plugin_hook_function('top_graph_refresh', htmlspecialchars(read_graph_config_option("page_refresh"),ENT_QUOTES));
		print "<meta http-equiv=refresh content='" . $refresh . "'>\r\n";
	}
	?>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link href="<?php echo $config['url_path']; ?>include/main.css" type="text/css" rel="stylesheet">
	<link href="<?php echo $config['url_path']; ?>images/favicon.ico" rel="shortcut icon"/>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/layout.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/treeview/ua.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/treeview/ftiens4.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/lang/calendar-zh_CN.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar-setup.js"></script>
	<?php api_plugin_hook('page_head'); ?>
</head>

<?php if ($oper_mode == OPER_MODE_NATIVE) {?>
<body <?php print api_plugin_hook_function("body_style", "");?>>
<a name='page_top'></a>
<?php }else{?>
<body <?php print api_plugin_hook_function("body_style", "");?>>
<?php }?>

<table style="width:100%;height:100%;" cellspacing="0" cellpadding="0">
<?php if ($oper_mode == OPER_MODE_NATIVE) { ;?>
	<tr style="height:5px;" bgcolor="#e9e9e9" class="noprint">
		<td colspan="2">
			<table width="100%">
				<tr>
					<td>
						
					</td>
					<td align="right">

					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="noprint">
		<td bgcolor="#efefef" colspan="1" style="height:8px;background-image: url(<?php echo $config['url_path']; ?>images/shadow_gray.gif); background-repeat: repeat-x; border-right: #aaaaaa 1px solid;">
			<img src="<?php echo $config['url_path']; ?>images/transparent_line.gif" width="<?php print htmlspecialchars(read_graph_config_option("default_dual_pane_width"));?>" style="height:2px;" border="0"><br>
		</td>
		<td bgcolor="#ffffff" colspan="1" style="height:8px;background-image: url(<?php echo $config['url_path']; ?>images/shadow.gif); background-repeat: repeat-x;">

		</td>
	</tr>

	<?php if ((basename($_SERVER["PHP_SELF"]) == "graph.php") && ($_REQUEST["action"] == "properties")) {?>
	<tr>
		<td valign="top" style="height:1px;" colspan="3" bgcolor="#efefef">
			<?php
			$graph_data_array["print_source"] = true;

			/* override: graph start time (unix time) */
			if (!empty($_GET["graph_start"])) {
				$graph_data_array["graph_start"] = get_request_var_request("graph_start");
			}

			/* override: graph end time (unix time) */
			if (!empty($_GET["graph_end"])) {
				$graph_data_array["graph_end"] = get_request_var_request("graph_end");
			}

			print trim(@rrdtool_function_graph(get_request_var_request("local_graph_id"), get_request_var_request("rra_id"), $graph_data_array));
			?>
		</td>
	</tr>
	<?php }
	}
	global $graph_views;
	load_current_session_value("action", "sess_cacti_graph_action", $graph_views[read_graph_config_option("default_tree_view_mode")]);
	?>
	<tr>
		<?php if (basename($_SERVER["PHP_SELF"]) == "graph_view.php" && ($_REQUEST["action"] == "tree" || (isset($_REQUEST["view_type"]) && $_REQUEST["view_type"] == "tree"))) { ?>
		<td valign="top" style="padding: 5px; border-right: #aaaaaa 1px solid;background-repeat:repeat-y;background-color:#efefef;" bgcolor='#efefef' width='<?php print htmlspecialchars(read_graph_config_option("default_dual_pane_width"));?>' class='noprint'>
			<table border=0 cellpadding=0 cellspacing=0><tr><td><a style="font-size:7pt;text-decoration:none;color:silver" href="http://www.treemenu.net/" target=_blank></a></td></tr></table>
			<?php grow_dhtml_trees(); ?>
			<script type="text/javascript">initializeDocument();</script>

			<?php if (isset($_GET["select_first"])) { ?>
			<script type="text/javascript">
			var tobj;
			tobj = findObj(1);

			if (tobj) {
				if (!tobj.isOpen) {
					clickOnNode(1);
				}
			}
			</script>
			<?php } ?>
		</td>
		<?php } ?>
		<td valign="top" style="padding: 5px; border-right: #aaaaaa 1px solid;"><div style='position:static;' id='main'>
