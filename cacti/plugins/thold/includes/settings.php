<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2009 The Cacti Group                                      |
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

function thold_draw_navigation_text ($nav) {
	$nav['thold.php:'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'thold.php', 'level' => '1');
	$nav['thold.php:save'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'thold.php', 'level' => '1');
	$nav['thold.php:autocreate'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'thold.php', 'level' => '1');
	$nav['listthold.php:'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'listthold.php', 'level' => '1');
	$nav['listthold.php:actions'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'listthold.php', 'level' => '1');
	$nav['thold_graph.php:'] = array('title' => '阈值', 'mapping' => 'index.php:', 'url' => 'thold_graph.php', 'level' => '1');
	$nav['thold_view_failures.php:'] = array('title' => '阈值 - 失败', 'mapping' => 'index.php:', 'url' => 'thold_view_failures.php', 'level' => '1');
	$nav['thold_view_normal.php:'] = array('title' => '阈值 - 正常', 'mapping' => 'index.php:', 'url' => 'thold_view_normal.php', 'level' => '1');
	$nav['thold_view_recover.php:'] = array('title' => '阈值 - 恢复中', 'mapping' => 'index.php:', 'url' => 'thold_view_recover.php', 'level' => '1');
	$nav['thold_view_recent.php:'] = array('title' => '最新阈值', 'mapping' => 'index.php:', 'url' => 'thold_view_recent.php', 'level' => '1');
	$nav['thold_view_host.php:'] = array('title' => '最新主机错误', 'mapping' => 'index.php:', 'url' => 'thold_view_host.php', 'level' => '1');

	$nav['thold_templates.php:'] = array('title' => '阈值模板', 'mapping' => 'index.php:', 'url' => 'thold_templates.php', 'level' => '1');
	$nav['thold_templates.php:edit'] = array('title' => '阈值模板', 'mapping' => 'index.php:', 'url' => 'thold_templates.php', 'level' => '1');
	$nav['thold_templates.php:save'] = array('title' => '阈值模板', 'mapping' => 'index.php:', 'url' => 'thold_templates.php', 'level' => '1');
	$nav['thold_templates.php:add'] = array('title' => '阈值模板', 'mapping' => 'index.php:', 'url' => 'thold_templates.php', 'level' => '1');
	$nav['thold_templates.php:actions'] = array('title' => '阈值模板', 'mapping' => 'index.php:', 'url' => 'thold_templates.php', 'level' => '1');

	$nav['thold_add.php:'] = array('title' => '添加阈值', 'mapping' => 'index.php:', 'url' => 'thold_add.php', 'level' => '1');

	return $nav;
}

function thold_config_arrays () {
	global $menu, $messages, $thold_menu;

	$menu['管理']['plugins/thold/listthold.php'] = '阈值';
	$menu['模板']['plugins/thold/thold_templates.php'] = '阈值模板';
	$messages['thold_save'] = array(
		'message' => '已有模板使用这个数据源!',
		'type' => 'error');
	if (isset($_SESSION['thold_message']) && $_SESSION['thold_message'] != '') {
		$messages['thold_created'] = array('message' => $_SESSION['thold_message'], 'type' => 'info');
	}
	if (isset($_GET['thold_vrule'])) {
		if ($_GET['thold_vrule'] == 'on') {
			$_SESSION['sess_config_array']['thold_draw_vrules'] = 'on';
			$_SESSION['sess_config_array']['boost_png_cache_enable'] = false;
		} elseif ($_GET['thold_vrule'] == 'off') {
			$_SESSION['sess_config_array']['thold_draw_vrules'] = 'off';
		}
	}
	$thold_menu = array(
		'Thresholds' => array(
			'plugins/thold/thold_graph.php' => 'All',
			'plugins/thold/thold_view_failures.php' => 'Current Failures',
			'plugins/thold/thold_view_recover.php' => 'Current Recovering',
			'plugins/thold/thold_view_normal.php' => 'Current Normal',
			'' => '',

			),
		'Reports' => array(
			'plugins/thold/thold_view_recent.php' => 'All Threshold Alerts',
			'plugins/thold/thold_view_host.php' => 'All Host Down Alerts',
			),
		);
}

function thold_config_settings () {
	global $tabs, $settings, $config;

	if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) != 'settings.php')
		return;

	define_syslog_variables();

	if ($config["cacti_server_os"] == "unix") {
		$syslog_facil_array = array(LOG_AUTH => 'Auth', LOG_AUTHPRIV => 'Auth Private', LOG_CRON => 'Cron', LOG_DAEMON => 'Daemon', LOG_KERN => 'Kernel', LOG_LOCAL0 => 'Local 0', LOG_LOCAL1 => 'Local 1', LOG_LOCAL2 => 'Local 2', LOG_LOCAL3 => 'Local 3', LOG_LOCAL4 => 'Local 4', LOG_LOCAL5 => 'Local 5', LOG_LOCAL6 => 'Local 6', LOG_LOCAL7 => 'Local 7', LOG_LPR => 'LPR', LOG_MAIL => 'Mail', LOG_NEWS => 'News', LOG_SYSLOG => 'Syslog', LOG_USER => 'User', LOG_UUCP => 'UUCP');
		$default_facility = LOG_DAEMON;
	} else {
		$syslog_facil_array = array(LOG_USER => 'User');
		$default_facility = LOG_USER;
	}

	$tabs['alerts'] = '报警/阈值';
	$settings['alerts'] = array(
		'general_header' => array(
			'friendly_name' => '常规',
			'method' => 'spacer',
			),
		'thold_disable_all' => array(
			'friendly_name' => '禁用所有阈值',
			//'description' => '选择该选项将禁止所有阈值报警.当您的网络需要大规模维护的时候可以使用该功能,以启产生不必要的报警.',
			'method' => 'checkbox',
			'default' => 'off'
			),
		'alert_base_url' => array(
			'friendly_name' => '基本URL',
			//'description' => 'Cacti的基本URL,该URL将会自动填充在报警邮件的附件链接里.',
			'method' => 'textbox',
			// Set the default only if called from 'settings.php'
			'default' => ((isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'settings.php') ? ('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/') : ''),
			'max_length' => 255,
			),
		'alert_syslog' => array(
			'friendly_name' => '记录Syslog',
			//'description' => '这些消息将会发送到Cacti的Syslog.',
			'method' => 'checkbox'
			),
		'thold_syslog_level' => array(
			'friendly_name' => 'Syslog级别',
			//'description' => '这些消息将以什么级别发送到Syslog.',
			'method' => 'drop_array',
			'default' => LOG_WARNING,
			'array' => array(LOG_EMERG => 'Emergency', LOG_ALERT => 'Alert', LOG_CRIT => 'Critical', LOG_ERR => 'Error', LOG_WARNING => 'Warning', LOG_NOTICE => 'Notice', LOG_INFO => 'Info', LOG_DEBUG => 'Debug'),
			),
		'thold_syslog_facility' => array(
			'friendly_name' => 'Syslog类型',
			//'description' => '这些消息将以什么类型发送到Syslog.',
			'method' => 'drop_array',
			'default' => $default_facility,
			'array' => $syslog_facil_array,
			),
		'alert_num_rows' => array(
			'friendly_name' => '每页阈值数',
			//'description' => '每页显示的阈值数量',
			'method' => 'textbox',
			'size' => 4,
			'max_length' => 4,
			'default' => 30
			),
		'thold_log_cacti' => array(
			'friendly_name' => '记录超出阈值',
			//'description' => '记录所有超出阈值并记录到Cacti日志',
			'method' => 'checkbox',
			'default' => 'off'
			),
		'thold_log_changes' => array(
			'friendly_name' => '记录阈值更改',
			//'description' => '启用所有阈值更改并记录到Cacti日志',
			'method' => 'checkbox',
			'default' => 'off'
			),
		'thold_alerting_header' => array(
			'friendly_name' => '默认报警选项',
			'method' => 'spacer',
			),
		'alert_deadnotify' => array(
			'friendly_name' => '主机宕机通知',
			//'description' => '启用主机宕机/恢复通知',
			'method' => 'checkbox',
			'default' => 'on'
			),
		'alert_email' => array(
			'friendly_name' => '主机宕机通知邮件',
			//'description' => '当主机宕机时将会发送报警邮件到该邮件地址.',
			'method' => 'textbox',
			'max_length' => 255,
			),
		'thold_send_text_only' => array(
			'friendly_name' => '以文本方式发送报警邮件',
			//'description' => '以文本方发发送所有报警邮件,这会导致所有邮件无图形.只有HTML邮件才会嵌入图形.',
			'method' => 'checkbox',
			'default' => 'off'
			),
		'alert_exempt' => array(
			'friendly_name' => '周末休息',
			//'description' => '如果选中该选项,所有阈值将在周末失效.',
			'method' => 'checkbox',
			),
		'alert_trigger' => array(
			'friendly_name' => '触发容忍次数',
			//'description' => '数据源连续多少次超过阈值才会产生报警.',
			'method' => 'textbox',
			'size' => 4,
			'max_length' => 4,
			'default' => 1
			),
		'alert_repeat' => array(
			'friendly_name' => '再次报警',
			//'description' => '指定次数之后再次报警.',
			'method' => 'textbox',
			'size' => 4,
			'max_length' => 4,
			'default' => 12
			),
		'thold_alert_text' => array(
			'friendly_name' => '报警文本消息',
			//'description' => '这个消息将显示在所有阈值报警的顶部(最多255个字符). 允许HTML,但当设置为以文本方式发送邮件时无效.以下描述符可用.<br>&#060DESCRIPTION&#062 &#060HOSTNAME&#062 &#060TIME&#062 &#060URL&#062 &#060GRAPHID&#062 &#060CURRENTVALUE&#062 &#060THRESHOLDNAME&#062  &#060DSNAME&#062 &#060SUBJECT&#062 &#060GRAPH&#062',
			'method' => 'textarea',
			'textarea_rows' => '5',
			'textarea_cols' => '80',
			'default' => '<html><body>请注意,已产生一个新报警. <br><br><strong>主机</strong>: <DESCRIPTION> (<HOSTNAME>)<br><strong>URL</strong>: <URL><br><strong>消息</strong>: <SUBJECT><br><br><GRAPH></body></html>',
			),
		'thold_baseline_header' => array(
			'friendly_name' => '默认基线选项',
			'method' => 'spacer',
			),
		'alert_notify_bl' => array(
			'friendly_name' => '基线通知',
			//'description' => '启用发送基线通知报警.',
			'method' => 'checkbox',
			'default' => 'on'
			),
		'alert_bl_trigger' => array(
			'friendly_name' => '基线触发容忍次数',
			//'description' => '数据源连续多少次超过基线阈值才会产生报警.',
			'method' => 'textbox',
			'size' => 4,
			'max_length' => 4,
			'default' => 2
			),
		'alert_bl_past_default' => array(
			'friendly_name' => '基线参考过去值',
			//'description' => '这个值将用来添加阈值或阈值模板.',
			'method' => 'textbox',
			'size' => 12,
			'max_length' => 12,
			'default' => 86400
			),
		'alert_bl_timerange_def' => array(
			'friendly_name' => '默认基线范围',
			//'description' => '这个值将用来添加阈值或阈值模板.',
			'method' => 'textbox',
			'size' => 12,
			'max_length' => 12,
			'default' => 10800
			),
		'alert_bl_percent_def' => array(
			'friendly_name' => '基线偏差百分比',
			//'description' => '这个值将用来添加阈值或阈值模板.',
			'method' => 'textbox',
			'size' => 3,
			'max_length' => 3,
			'default' => 15
			),
		'thold_email_header' => array(
			'friendly_name' => '邮件选项',
			'method' => 'spacer',
			),
		'thold_from_email' => array(
			'friendly_name' => '发件人地址',
			//'description' => '该设置将应用在邮件的发件人地址,确保该地址是真实地址,大多数邮件服务器拒绝假地址.',
			'method' => 'textbox',
			'max_length' => 255,
			),
		'thold_from_name' => array(
			'friendly_name' => '发件人名称',
			//'description' => '该设置将应用在邮件的发件人名称,该名称将显示在邮件头部,可以使用中文.',
			'method' => 'textbox',
			'max_length' => 255,
			),
		);
}
