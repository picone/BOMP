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


chdir('../../');

include_once("./include/auth.php");
include_once($config["base_path"] . "/plugins/settings/include/functions.php");

global $config;
print "<html><head>";
print '<link type="text/css" href="../../include/main.css" rel="stylesheet">';
print "</head><body>";
$message =  "这是一封从BOMP产生的测试邮件.这封邮件用来测试您的邮件配置.<br><br>";
$message .= "以下是您当前的设置<br><br>";
$message .= "<b>发送方法</b>: ";
print "检查配置...<br>";
$how = read_config_option("settings_how");
if ($how < 0 || $how > 2)
	$how = 0;
if ($how == 0) {
	$mail = "PHP's Mailer Class";
} else if ($how == 1) {
	$mail = "Sendmail<br><b>Sendmail Path</b>: ";
	$sendmail = read_config_option("settings_sendmail_path");
	$mail .= $sendmail;
} else if ($how == 2) {
	print "方法: SMTP<br>";
	$mail = "SMTP<br>";
	$smtp_host = read_config_option("settings_smtp_host");
	$smtp_port = read_config_option("settings_smtp_port");
	$smtp_username = read_config_option("settings_smtp_username");
	$smtp_password = read_config_option("settings_smtp_password");

	$mail .= "<b>SMTP服务器Host</b>: $smtp_host<br>";
	$mail .= "<b>SMTP端口</b>: $smtp_port<br>";

	if ($smtp_username != '' && $smtp_password != '') {
		$mail .= "<b>验证</b>: 是<br>";
		$mail .= "<b>用户名</b>: $smtp_username<br>";
		$mail .= "<b>密码</b>: (*********)";
	} else {
		$mail .= "<b>验证</b>: 否";
	}
}
$message .= $mail;
$message .= "<br>";

print "创建邮件文本...<br><br>";
print "<center><table width='95%' cellpadding=1 cellspacing=0 bgcolor=black><tr><td>";
print "<table width='100%' bgcolor=white><tr><td>$message</td><tr></table></table></center><br>";
print "发送邮件...<br><br>";
$global_alert_address = read_config_option("settings_test_email");
$title = iconv("UTF-8", "GB2312//IGNORE", "Cacti测试邮件");
$message = iconv("UTF-8", "GB2312//IGNORE", $message);
$errors = send_mail($global_alert_address, '', $title, $message, '');
if ($errors == '')
	$errors = "成功!";

print "<center><table width='95%' cellpadding=1 cellspacing=0 bgcolor=black><tr><td>";
print "<table width='100%' bgcolor=white><tr><td>$errors</td><tr></table></table></center>";

print "</body></html>";



