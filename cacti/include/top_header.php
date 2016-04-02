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

global $colors, $config;
//定义它的板式
$oper_mode=OPER_MODE_IFRAME_NONAV;


$page_title = api_plugin_hook_function('page_title', draw_navigation_text("title"));

?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title><?php echo $page_title; ?></title>
		<link href="<?php echo $config['url_path']; ?>include/main.css" type="text/css" rel="stylesheet">
		<link href="<?php echo $config['url_path']; ?>images/favicon.ico" rel="shortcut icon">
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/layout.js"></script>
		<?php if (isset($refresh)) {
			print "<meta http-equiv=refresh content=\"" . $refresh["seconds"] . "; url='" . $refresh["page"] . "'\">";
		}
		api_plugin_hook('page_head'); ?>
	</head>
<body <?php print api_plugin_hook_function("body_style", "");?>>
<table width="100%" cellspacing="0" cellpadding="0">
	<td width="100%" valign="top"><?php display_output_messages();?>