<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2008 The Cacti Group                                      |
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

function plugin_settings_install () {
	api_plugin_register_hook('settings', 'config_settings', 'settings_config_settings', 'setup.php');
	api_plugin_register_realm('settings', 'email-test.php', 'Send Test Email', 1);
}

function plugin_settings_uninstall () {
	// Do any extra Uninstall stuff here
}

function plugin_settings_check_config () {
	// Here we will check to ensure everything is configured
	settings_check_upgrade ();
	return true;
}

function plugin_settings_upgrade () {
	// Here we will upgrade to the newest version
	settings_check_upgrade ();
	return false;
}

function settings_version () {
	return plugin_settings_version();
}

function settings_check_upgrade () {
	$current = plugin_settings_version ();
	$current = $current['version'];
	$old = read_config_option('plugin_settings_version');
	if ($current != $old) {
		// settings_setup_table ();
	}
	db_execute("REPLACE INTO settings (name, value) VALUES ('plugin_settings_version', '$current')");
}

function plugin_settings_version () {
	return array(
			'name' 	=> 'settings',
			'version'  => '0.7',
			'longname' => 'Global Plugin Settings',
			'author'   => 'Jimmy Conner',
			'homepage' => 'http://cactiusers.org',
			'email'    => 'jimmy@sqmail.org',
			'url'      => 'http://versions.cactiusers.org/'
		);
}

function settings_config_settings () {
	global $tabs, $settings, $config;

	include_once($config['base_path'] . '/plugins/settings/include/functions.php');
	if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) != 'settings.php')
		return;

	$tabs['mail'] = '邮件/域名解析';

      $javascript = '<script type="text/javascript">
<!--
   function emailtest() {
      w = 420;
      h = 350;
      email = window.open("plugins/settings/email-test.php", "EmailTest", "width=" + w + ",height=" + h + ",resizable=0,status=0");
      email.moveTo((screen.width - w) /2 , (screen.height - h) /2 );
   }
//-->
</script>';

	$temp = array(
		"settings_email_header" => array(
			"friendly_name" => "\n$javascript\n<table width='99%' cellspacing=0 cellpadding=0 align=left><tr><td class='textSubHeaderDark'>电子邮件选项</td><td align=right class='textSubHeaderDark'><a href='javascript:emailtest();' class='textSubHeaderDark'><font color=white>发送一封测试邮件</font></a></td></tr></table>",
			"method" => "spacer",
			),
		"settings_test_email" => array(
			"friendly_name" => "测试邮件",
			//"description" => "使用这个邮件账户发送一封测试邮件.",
			"method" => "textbox",
			"max_length" => 255,
			),
		"settings_how" => array(
			"friendly_name" => "邮件服务",
			//"description" => "使用哪种方式发送测试邮件.使用Sendmail可能无法发送到公共邮箱,推荐使用SMTP方式登录到公共服务器发送邮件.",
			"method" => "drop_array",
			"default" => "PHP Mail() Function",
			"array" => array("PHP的Mail()函数", "Sendmail", "SMTP"),
			),
		"settings_from_email" => array(
			"friendly_name" => "发件人地址",
			//"description" => "发送测试邮件的发件人地址.",
			"method" => "textbox",
			"max_length" => 255,
			),
		"settings_from_name" => array(
			"friendly_name" => "发件人",
			//"description" => "发送测试邮件的发件人.",
			"method" => "textbox",
			"max_length" => 255,
			),
		"settings_wordwrap" => array(
			"friendly_name" => "自动换行",
			//"description" => "一行允许多少个字符并自动换行. (0 = 不自动换行)",
			"method" => "textbox",
			'default' => 120,
			"max_length" => 4,
			),
		"settings_sendmail_header" => array(
			"friendly_name" => "Sendmail选项",
			"method" => "spacer",
			),
		"settings_sendmail_path" => array(
			"friendly_name" => "Sendmail路径",
			//"description" => "Sendmail服务器程序的路径. (仅当选择以Sendmail方式发送邮件时生效)",
			"method" => "filepath",
			"max_length" => 255,
			"default" => "/usr/sbin/sendmail",
			),
		"settings_smtp_header" => array(
			"friendly_name" => "SMTP选项",
			"method" => "spacer",
			),
		"settings_smtp_host" => array(
			"friendly_name" => "SMTP服务器主机名",
			//"description" => "SMTP服务器的主机名或IP,如:smtp.163.com.",
			"method" => "textbox",
			"default" => "localhost",
			"max_length" => 255,
			),
		"settings_smtp_port" => array(
			"friendly_name" => "SMTP端口",
			//"description" => "SMTP服务器的端口,默认:25.",
			"method" => "textbox",
			"max_length" => 255,
			"default" => 25,
			),
		"settings_smtp_username" => array(
			"friendly_name" => "SMTP用户名",
			//"description" => "登录到SMTP服务器的用户名. (如果SMTP服务器不需要验证请留空.)",
			"method" => "textbox",
			"max_length" => 255,
			),
		"settings_smtp_password" => array(
			"friendly_name" => "SMTP密码",
			//"description" => "登录到SMTP服务器的用户名对应的密码. (如果SMTP服务器不需要验证请留空.)",
			"method" => "textbox_password",
			"max_length" => 255,
			),
		"settings_dns_header" => array(
			"friendly_name" => "DNS选项",
			"method" => "spacer",
			),
		"settings_dns_primary" => array(
			"friendly_name" => "主要DNS服务器的IP地址",
			//"description" => "输入主要DNS服务器的IP地址,如果您在系统里配>
//置了可用的DNS,这里可以留空,如果您不知道您的DNS,建议您尝试使用Google的公共DNS服务器:8.
//8.8.8.",
			"method" => "textbox",
			"default" => "",
			"max_length" => "30"
			),
		"settings_dns_secondary" => array(
			"friendly_name" => "次要DNS服务器IP地址",
			//"description" => "输入次要DNS服务器的IP地址.",
			"method" => "textbox",
			"default" => "",
			"max_length" => "30"
			),
		"settings_dns_timeout" => array(
			"friendly_name" => "DNS超时",
			//"description" => "输入DNS解析的超时时间,单位:毫秒.Cacti使用基于PHP的DNS解析器.",
			"method" => "textbox",
			"default" => "500",
			"max_length" => "10"
			),
	);

	if (isset($settings['mail']))
		$settings['mail'] = array_merge($settings['mail'], $temp);
	else
		$settings['mail']=$temp;
}




