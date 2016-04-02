<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 Platform Computing, Inc.                             |
 | Portions Copyright (C) 2004-2006 The Cacti Group                        |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU Lesser General Public              |
 | License as published by the Free Software Foundation; either            |
 | version 2.1 of the License, or (at your option) any later version.      |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU Lesser General Public License for more details.                     |
 |                                                                         |
 | You should have received a copy of the GNU Lesser General Public        |
 | License along with this library; if not, write to the Free Software     |
 | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA           |
 | 02110-1301, USA                                                         |
 +-------------------------------------------------------------------------+
 | - Platform - http://www.platform.com/                                   |
 | - Cacti - http://www.cacti.net/                                         |
 +-------------------------------------------------------------------------+
*/

global $colors, $config;

$using_guest_account = false;
$show_console_tab = true;

$oper_mode = api_plugin_hook_function('top_header', OPER_MODE_NATIVE);
if ($oper_mode == OPER_MODE_RESKIN) {
	return;
}

if (read_config_option("auth_method") != 0) {
	global $colors, $config;

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
?>
<html>
<head>
	<title>Syslog</title>
	<link href="<?php echo $config['url_path'];?>include/main.css" rel="stylesheet">
	<link href="<?php echo $config['url_path'];?>plugins/syslog/images/favicon.ico" rel="shortcut icon"/>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/layout.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar-setup.js"></script>
	<?php if (isset($_REQUEST["refresh"])) {
	print "<meta http-equiv=refresh content='" . $_REQUEST["refresh"] . "'; url='" . $config["url_path"] . "plugins/syslog/syslog.php'>";
	}
	api_plugin_hook('page_head'); ?>
</head>

<?php if ($oper_mode == OPER_MODE_NATIVE) {?>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?php print api_plugin_hook_function("body_style", "");?>>
<script language="JavaScript" type="text/javascript" src="<?php print $config['url_path'] . 'plugins/syslog/wz_tooltip.js';?>"></script>
<a name='page_top'></a>
<?php }else{?>
<body leftmargin="15" topmargin="15" marginwidth="15" marginheight="15" <?php print api_plugin_hook_function("body_style", "");?>>
<script language="JavaScript" type="text/javascript" src="<?php print $config['url_path'] . 'plugins/grid/wz_tooltip.js';?>"></script>
<a name='page_top'></a>
<?php }?>


