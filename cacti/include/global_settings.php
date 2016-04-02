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

/* tab information */
$tabs = array(
	"general" => "常规",
	//"path" => "路径",
	//"poller" => "采集器",
	//"export" => "导出图形",
	//"visual" => "效果",
	//"authentication" => "验证"
        );

$tabs_graphs = array(
	"general" => "常规",
	"thumbnail" => "缩略图",
	"tree" => "树状查看",
	"preview" => "预览查看",
	"list" => "列表查看",
	"fonts" => "图形字体");

/* setting information */
$settings = array(
	"path" => array(
		"dependent_header" => array(
			"friendly_name" => "需要设置的路径",
			"method" => "spacer",
			),
		"path_rrdtool_default_font" => array(
			"friendly_name" => "图形使用的默认字体",
			"method" => "font",
			"max_length" => "255"
			),
		"logging_header" => array(
			"friendly_name" => "日志",
			"method" => "spacer",
			),
		"path_cactilog" => array(
			"friendly_name" => "BOMP日志文件路径",
			"method" => "filepath",
			"default" => $config["base_path"] . "/var/www/new_zabbix/cacti/log/cacti.log",
			"max_length" => "255"
			),
		"extendedpaths_header" => array(
			"friendly_name" => "结构化RRD路径",
			"method" => "spacer",
			),
		"extended_paths" => array(
			"friendly_name" => "结构化RRD路径(/var/www/new_zabbix/cacti/host_id/local_data_id.rrd)",
			//"description" => "使用子目录分隔每一个主机的RRD文件.",
			"method" => "checkbox"
 			)
		),
	"general" => array(
//		"logging_header" => array(
//			"friendly_name" => "事件日志",
//			"method" => "spacer",
//			),
//		"log_destination" => array(
//			"friendly_name" => "日志文件描述",
//			//"description" => "Cacti将日志记录在哪里.",
//			"method" => "drop_array",
//			"default" => 1,
//			"array" => $logfile_options,
//			),
//		"web_log" => array(
//			"friendly_name" => "Web事件",
//			//"description" => "哪些Cacti事件将被写入日志.",
//			"method" => "checkbox_group",
//			"tab" => "general",
//			"items" => array(
//				"log_snmp" => array(
//					"friendly_name" => "SNMP消息",
//					"default" => ""
//					),
//				"log_graph" => array(
//					"friendly_name" => "RRD图形语法",
//					"default" => ""
//					),
//				"log_export" => array(
//					"friendly_name" => "图形导出消息",
//					"default" => ""
//					)
//				),
//			),
//		"poller_header" => array(
//			"friendly_name" => "采集器日志",
//			"method" => "spacer",
//			),
//		"log_verbosity" => array(
//			"friendly_name" => "采集器日志级别",
//			//"description" => "您希望记录什么级别的日志?警告:选择'无'或'低'以外的级别都会快速消耗您的磁盘空间.",
//			"method" => "drop_array",
//			"default" => POLLER_VERBOSITY_LOW,
//			"array" => $logfile_verbosity,
//			),
//		"poller_log" => array(
//			"friendly_name" => "Syslog日志选择",
//			//"description" => "如果您使用Syslog记录采集器日志,哪些采集器消息需要被记录到Syslog.",
//			"method" => "checkbox_group",
//			"tab" => "poller",
//			"items" => array(
//				"log_pstats" => array(
//					"friendly_name" => "采集器统计",
//					"default" => ""
//					),
//				"log_pwarn" => array(
//					"friendly_name" => "采集器警告",
//					"default" => ""
//					),
//				"log_perror" => array(
//					"friendly_name" => "采集器日志",
//					"default" => "on"
//					)
//				),
//			),
		"snmp_header" => array(
			"friendly_name" => "SNMP默认选项",
			"method" => "spacer",
			),
		"snmp_ver" => array(
			"friendly_name" => "SNMP版本",
			//"description" => "当添加新主机时使用的默认SNMP版本.",
			"method" => "drop_array",
			"default" => "1",
			"array" => $snmp_versions,
			),
		"snmp_community" => array(
			"friendly_name" => "SNMP团体名称",
			//"description" => "当添加新主机时使用的默认SNMP读团体名称.",
			"method" => "textbox",
			"default" => "public",
			"max_length" => "100",
			),
		"snmp_username" => array(
			"friendly_name" => "SNMP用户名(v3)",
			//"description" => "当添加新主机时使用的默认SNMP v3用户名.",
			"method" => "textbox",
			"default" => "",
			"max_length" => "100",
			),
		"snmp_password" => array(
			"friendly_name" => "SNMP密码(v3)",
			//"description" => "当添加新主机时使用的默认SNMP v3密码.",
			"method" => "textbox_password",
			"default" => "",
			"max_length" => "100",
			),
		"snmp_auth_protocol" => array(
			"method" => "drop_array",
			"friendly_name" => "SNMP验证协议(v3)",
			//"description" => "当添加新主机时使用的默认SNMP v3验证协议.",
			"default" => "MD5",
			"array" => $snmp_auth_protocols,
			),
		"snmp_priv_passphrase" => array(
			"method" => "textbox",
			"friendly_name" => "SNMP私有密码短语(v3)",
			//"description" => "当添加新主机时使用的默认SNMP v3私有密码短语.",
			"default" => "",
			"max_length" => "200"
			),
		"snmp_priv_protocol" => array(
			"method" => "drop_array",
			"friendly_name" => "SNMP私有协议(v3)",
			//"description" => "当添加新主机时使用的默认SNMP v3私有协议.",
			"default" => "DES",
			"array" => $snmp_priv_protocols,
			),
		"snmp_timeout" => array(
			"friendly_name" => "SNMP超时",
			//"description" => "当添加新主机时使用的默认SNMP超时上限(毫秒).",
			"method" => "textbox",
			"default" => "500",
			"max_length" => "10",
			"size" => "5"
			),
		"snmp_port" => array(
			"friendly_name" => "SNMP端口",
			//"description" => "当添加新主机时SNMP使用的默认UDP端口.通常是161.",
			"method" => "textbox",
			"default" => "161",
			"max_length" => "10",
			"size" => "5"
			),
		"snmp_retries" => array(
			"friendly_name" => "SNMP重试",
			//"description" => "当添加新主机时SNMP判定主机宕机的默认重试次数.",
			"method" => "textbox",
			"default" => "3",
			"max_length" => "10",
			"size" => "5"
			),
//		"other_header" => array(
//			"friendly_name" => "其它默认值",
//			"method" => "spacer",
//			),
//		"reindex_method" => array(
//			"friendly_name" => "数据查询的重新索引方法",
//			//"description" => "应用在所有数据查询上的默认重新索引方法.",
//			"method" => "drop_array",
//			"default" => "1",
//			"array" => $reindex_types,
//			),
//		"deletion_verification" => array(
//			"friendly_name" => "删除确认",
//			//"description" => "对象被删除前提示用户.",
//			"default" => "on",
//			"method" => "checkbox"
//			)
		),
	"export" => array(
		"export_hdr_general" => array(
			"friendly_name" => "常规",
			"method" => "spacer",
			),
		"export_type" => array(
			"friendly_name" => "导出方法",
			//"description" => "选择导出图形使用的方法.",
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"disabled" => "禁用(不排序)",
						"local" => "经典(本地路径)",
						"ftp_php" => "FTP(远程)",
						/*"ftp_ncftpput" => "FTP (远程) - 使用ncftpput",*/
						"sftp_php" => "SFTP(远程)"
						),
			),
		"export_presentation" => array(
			"friendly_name" => "呈现方式",
			//"description" => "选择您希望HTML生成的呈现方式.如果您选择经典呈现方式,图形将呈现在一张单独的HTML页面内,如果您选择树状呈现方式,图形树结构将保留在静态HTML页面.",
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"classical" => "经典呈现方式",
						"tree" => "树状呈现方式",
						),
			),
		"export_tree_options" => array(
			"friendly_name" => "树设置",
			"method" => "spacer",
			),
		"export_tree_isolation" => array(
			"friendly_name" => "分隔树",
			//"description" => "这个设置决定了是单独树结构还是分隔树结构.如果选择成多树结构,图形之间将会相互独立.",
			"method" => "drop_array",
			"default" => "off",
			"array" => array(
						"off" => "呈现单树",
						"on" => "呈现多树"
						),
			),
		"export_user_id" => array(
			"friendly_name" => "有效用户",
			//"description" => "这个设置决定了导出图形时使用哪个用户的权限,这个设置也决定了将会导出哪些图形.",
			"method" => "drop_sql",
			"sql" => "SELECT id, username AS name FROM user_auth ORDER BY name",
			"default" => "1"
			),
		"export_tree_expand_hosts" => array(
			"friendly_name" => "展开树结构",
			//"description" => "这个设置决定了树结构是否展开.如果选择展开,将会为每个主机建立一个子目录存放数据模板或数据查询对象.",
			"method" => "drop_array",
			"default" => "off",
			"array" => array(
						"off" => "关",
						"on" => "开"
						),
			),
		"export_thumb_options" => array(
			"friendly_name" => "缩略图设置",
			"method" => "spacer",
			),
		"export_default_height" => array(
			"friendly_name" => "缩略图高度",
			//"description" => "缩略图的高度(像素).",
			"method" => "textbox",
			"default" => "100",
			"max_length" => "10",
			"size" => "5"
			),
		"export_default_width" => array(
			"friendly_name" => "缩略图宽度",
			//"description" => "缩略图的宽度(像素).",
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10",
			"size" => "5"
			),
		"export_num_columns" => array(
			"friendly_name" => "缩略图列数",
			//"description" => "显示缩略图时显示为几列,如果您有更宽的屏幕,建立选择更多的列.",
			"method" => "textbox",
			"default" => "2",
			"max_length" => "5",
			"size" => "5"
			),
		"export_hdr_paths" => array(
			"friendly_name" => "路径",
			"method" => "spacer",
			),
		"path_html_export" => array(
			"friendly_name" => "导出目录(本地和FTP)",
			//"description" => "导出到本地或远程的路径,这个目录里将会包含导出的数据.",
			"method" => "dirpath",
			"max_length" => "255"
			),
		"export_temporary_directory" => array(
			"friendly_name" => "临时目录(权FTP)",
			//"description" => "导出到FTP时,先将数据导出到本地的临时目录然后传输到FTP服务器,之后便会删除这个目录里的文件.",
			"method" => "dirpath",
			"max_length" => "255"
			),
		"export_hdr_timing" => array(
			"friendly_name" => "定时",
			"method" => "spacer",
			),
		"export_timing" => array(
			"friendly_name" => "导出定时器",
			//"description" => "选择在什么时候导出.",
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"disabled" => "禁用",
						"classic" => "经典(每隔多久导出一次)",
						"export_hourly" => "每小时的第几分钟",
						"export_daily" => "每天的特定时间"
						),
			),
		"path_html_export_skip" => array(
			"friendly_name" => "每隔多久导出一次",
			//"description" => "如果您希望Cacti每隔一定时间导出一次,输入一个数字,Cacti将会在N*5分钟导出一次.例如输入3将会每15分钟导出一次.",
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_hourly" => array(
			"friendly_name" => "每小时的第几分钟",
			//"description" => "如果您希望Cacti在每小时的第几分钟导出,输入一个数字,Cacti将会在每小时的第N分钟导出一次,但由于采集器每5分钟运行一次,如果您指定了43分钟,您看到的图形将会是第40分钟的数据.",
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_daily" => array(
			"friendly_name" => "每天的特定时间",
			//"description" => "如果您希望Cacti在每天的特定时间导出,输入一个时间,Cacti将会在每天指定的时间导出一次,但由于采集器每5分钟运行一次,如果您指定了21:23,您看到的图形将会是21:20的数据.",
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_hdr_ftp" => array(
			"friendly_name" => "FTP选项",
			"method" => "spacer",
			),
		"export_ftp_sanitize" => array(
			"friendly_name" => "清理远程目录",
			//"description" => "这个选项将会清除远程FTP目录已存在的任何文件.",
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_host" => array(
			"friendly_name" => "FTP服务器",
			//"description" => "指定您上传用的FTP服务器.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_port" => array(
			"friendly_name" => "FTP端口",
			//"description" => "FTP服务器的通迅端口(留空使用默认).默认值:21.",
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_ftp_passive" => array(
			"friendly_name" => "使用被动模式",
			//"description" => "这个选项将会使用被动FTP模板传输文件.",
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_user" => array(
			"friendly_name" => "FTP用户",
			//"description" => "登录到FTP服务器的账户(留空使用默认).默认值:Anonymous.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_password" => array(
			"friendly_name" => "FTP密码",
			//"description" => "该账户对应的密码(留空使用空密码).",
			"method" => "textbox_password",
			"max_length" => "255"
			)
		),
	"visual" => array(
		"graphmgmt_header" => array(
			"friendly_name" => "图形管理",
			"method" => "spacer",
			),
		"num_rows_graph" => array(
			"friendly_name" => "每页图形数",
			//"description" => "图形管理页面每页显示的图形数量.",
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"max_title_graph" => array(
			"friendly_name" => "标题最大长度",
			//"description" => "图形标题显示的最大字节长度.",
			"method" => "textbox",
			"default" => "80",
			"max_length" => "10",
			"size" => "5"
			),
		"dataqueries_header" => array(
			"friendly_name" => "数据查询",
			"method" => "spacer",
			),
		"max_data_query_field_length" => array(
			"friendly_name" => "最大字段长度",
			//"description" => "数据查询的最大字节长度.",
			"method" => "textbox",
			"default" => "15",
			"max_length" => "10",
			"size" => "5"
			),
		"graphs_new_header" => array(
			"friendly_name" => "添加新图形",
			"method" => "spacer",
			),
		"default_graphs_new_dropdown" => array(
			"friendly_name" => "默认图形类型",
			//"description" => "当添加新图形时,您希望看到的图形类型.",
			"method" => "drop_array",
			"default" => "-2",
			"array" => array("-2" => "所有类型", "-1" => "基于模板/数据查询"),
			),
		"num_rows_data_query" => array(
			"friendly_name" => "数据查询数",
			//"description" => "当添加新图形时,每个数据查询显示的最大查询数量.",
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"datasources_header" => array(
			"friendly_name" => "数据源",
			"method" => "spacer",
			),
		"num_rows_data_source" => array(
			"friendly_name" => "每页数据查询数",
			//"description" => "数据查询页面每页显示的数据查询数量.",
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"max_title_data_source" => array(
			"friendly_name" => "标题最大长度",
			//"description" => "数据源标题显示的最大字节长度.",
			"method" => "textbox",
			"default" => "45",
			"max_length" => "10",
			"size" => "5"
			),
		"devices_header" => array(
			"friendly_name" => "主机",
			"method" => "spacer",
			),
		"num_rows_device" => array(
			"friendly_name" => "每页主机数",
			//"description" => "主机页面每页显示的主机数量.",
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"logmgmt_header" => array(
			"friendly_name" => "日志管理",
			"method" => "spacer",
			),
		"num_rows_log" => array(
			"friendly_name" => "默认日志数量",
			//"description" => "Cacti日志默认显示多少条日志.",
			"method" => "drop_array",
			"default" => 500,
			"array" => $log_tail_lines,
			),
		"log_refresh_interval" => array(
			"friendly_name" => "日志刷新",
			//"description" => "您希望隔多久自动刷新一次日志页面.",
			"method" => "drop_array",
			"default" => 60,
			"array" => $page_refresh_interval,
			),
		),
	"poller" => array(
		"poller_header" => array(
			"friendly_name" => "常规",
			"method" => "spacer",
			),
		"poller_enabled" => array(
			"friendly_name" => "采集器开关",
			//"description" => "如果您希望停止采集数据,不要选中该选项.",
			"method" => "checkbox",
			"default" => "on",
			"tab" => "poller"
			),
		"poller_interval" => array(
			"friendly_name" => "采集器周期",
			//"description" => "采集器每隔多久检查和更新RRD.<strong><u>注意:不建议修改该值,如果您修改了该值,请重建采集器缓存,该操作可能会导致数据丢失.</u></strong>",
			"method" => "drop_array",
			"default" => 300,
			"array" => $poller_intervals,
			),
		"cron_interval" => array(
			"friendly_name" => "CRON周期",
			//"description" => "CRON周期,这个值需要跟您的crontab里的周期一致,默认:次/5分钟.<strong><u>注意:不建议值改该值.</u></strong>",
			"method" => "drop_array",
			"default" => 300,
			"array" => $cron_intervals,
			),
		"spine_header" => array(
			"friendly_name" => "采集器参数",
			"method" => "spacer",
			),
		"max_threads" => array(
			"friendly_name" => "最大线程数",
			//"description" => "采集器的最大进程数量.提高该值可以提高采集器性能,但并不是越高越好,最佳设置取决于您的实际环境.",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"php_servers" => array(
			"friendly_name" => "脚本服务器数量",
			//"description" => "采集器调度的脚本服务器进程最大并发量.可设置1到10,这个设置可以允许Cacti同时运行多个脚本,如果您需要使用脚本查询的被监控主机较多,可增加该值.",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"script_timeout" => array(
			"friendly_name" => "脚本和脚本服务器超时",
			//"description" => "Cacti等待脚本完成的最长时间(秒)",
			"method" => "textbox",
			"default" => "25",
			"max_length" => "10",
			"size" => "5"
			),
		"max_get_size" => array(
			"friendly_name" => "每个SNMP获取请求的OID数量",
			//"description" => "每个snmpbulkwalk获取SNMP的OID的数量,每个snmpbulkwalk获取OID的最大数字.扩大这个值可以提高采集器性能.最大值是100.缩小该值到0或1将禁用snmpbulkwalk.",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10",
			"size" => "5"
			),
		"availability_header" => array(
			"friendly_name" => "主机可用性检查",
			"method" => "spacer",
			),
		"availability_method" => array(
			"friendly_name" => "主机宕机检测",
			//"description" => "Cacti用什么方法检测主机是否存活.<br><i>注意:无论如何Cacti都会使用SNMP检测主机是否存活.</i>",
			"method" => "drop_array",
			"default" => AVAIL_SNMP,
			"array" => $availability_options,
			),
		"ping_method" => array(
			"friendly_name" => "检测类型",
			//"description" => "发送检测数据包的类型.</i>",
			"method" => "drop_array",
			"default" => PING_UDP,
			"array" => $ping_methods,
			),
		"ping_port" => array(
			"friendly_name" => "检测端口",
			//"description" => "当使用TCP或UDP检测方法时需要输入被检测的端口,请确认您服务器上开启了哪个端口可被检查..",
			"method" => "textbox",
			"default" => "23",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_timeout" => array(
			"friendly_name" => "检测超时",
			//"description" => "由于ICMP和UDP都不会建议连接,所以需要指定ICMP和UDP检测的超时上限.",
			"method" => "textbox",
			"default" => "400",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_retries" => array(
			"friendly_name" => "检测重试次数",
			//"description" => "由于网络或其它原因,有时检测会失败,建议设置2次或3次连续检测,但设置太大会影响采集器性能.",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"updown_header" => array(
			"friendly_name" => "主机在线/宕机设置",
			"method" => "spacer",
			),
		"ping_failure_count" => array(
			"friendly_name" => "失败记数器",
			//"description" => "多少个连续的采集器周期检测到主机失败时记录并报告主机状态为宕机.",
			"method" => "textbox",
			"default" => "2",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_recovery_count" => array(
			"friendly_name" => "恢复记数器",
			//"description" => "多少个连续的采集器周期检测到主机正常时记录并报告主机状态为正在恢复.",
			"method" => "textbox",
			"default" => "3",
			"max_length" => "10",
			"size" => "5"
			)
		),
	"authentication" => array(
		"general_header" => array(
			"friendly_name" => "常规",
			"method" => "spacer",
			),
		"auth_method" => array(
			"friendly_name" => "验证方法",
			//"description" => "<blockquote><i>无</i> - 无验证模式,所有的用户都将拥有全部.<br>
			//	<br><i>内建验证</i> - Cacti控制用户验证,这将允许您建立用户并设置他们在Cacti的权限.<br>
			//	<br><i>WEB基本验证</i> - Apache控制用户验证.如果定义了模板用户,新用户将在第一次登录时被创建,否则,将会使用已定义的来宾用户权限.<br>
			//	<br><i>LDAP验证</i> - 允许将验证交给LDAP服务器.如果定义了模板用户,新用户将在第一次登录时被创建,否则,将会使用已民定义的来宾用户权限.如果没有启用PHP的LDAP模块,下拉菜单里不会出现LDAP验证.</blockquote>",
			"method" => "drop_array",
			"default" => 1,
			"array" => $auth_methods
			),
		"special_users_header" => array(
			"friendly_name" => "特别用户",
			"method" => "spacer",
			),
		"guest_user" => array(
			"friendly_name" => "来宾用户",
			//"description" => "可以查看图形的来宾用户的用户名,默认:\"无用户\".",
			"method" => "drop_sql",
			"none_value" => "无用户",
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "0"
			),
		"user_template" => array(
			"friendly_name" => "模板用户",
			//"description" => "选择一个模板用户,当使用WEB基本验证或LDAP验证时会从模板用户复制权限和设置,默认:\"无用户\".",
			"method" => "drop_sql",
			"none_value" => "无用户",
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "0"
			),
		"ldap_general_header" => array(
			"friendly_name" => "LDAP常规设置",
			"method" => "spacer"
			),
		"ldap_server" => array(
			"friendly_name" => "服务器",
			//"description" => "服务器的主机名或IP地址.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_port" => array(
			"friendly_name" => "标准端口",
			//"description" => "非SSL通讯时使用的TCP/UDP端口.",
			"method" => "textbox",
			"max_length" => "5",
			"default" => "389",
			"size" => "5"
			),
		"ldap_port_ssl" => array(
			"friendly_name" => "SSL端口",
			//"description" => "SSL通迅时使用的TCP/UDP端口.",
			"method" => "textbox",
			"max_length" => "5",
			"default" => "636",
			"size" => "5"
			),
		"ldap_version" => array(
			"friendly_name" => "协议版本",
			//"description" => "服务器支持的协议版本.",
			"method" => "drop_array",
			"default" => "3",
			"array" => $ldap_versions
			),
		"ldap_encryption" => array(
			"friendly_name" => "加密",
			//"description" => "服务器支持的加密方法.TLS只被第3版协议支持.",
			"method" => "drop_array",
			"default" => "0",
			"array" => $ldap_encryption
			),
		"ldap_referrals" => array(
			"friendly_name" => "REFERRAL",
			//"description" => "启用或禁用LDAP的REFERRAL.如果禁用,它将提高搜索的速度.",
			"method" => "drop_array",
			"default" => "0",
			"array" => array( "0" => "禁用", "1" => "启用")
			),
		"ldap_mode" => array(
			"friendly_name" => "模式",
		//"description" => "Cacti使用哪种模式交给LDAP服务器验证.<blockquote>
		//		<i>不搜索</i> - 无Distinguished Name (DN)搜索,只尝试绑定已提供的DN格式.<br>
		//		<br><i>匿名搜索</i> - 尝试使用匿名搜索LDAP目录中的用户名并定位DN.<br>
		//		<br><i>指定搜索</i> - 尝试使用指定的DN和密码搜索LDAP目录中的用户名并定位DN.",
			"method" => "drop_array",
			"default" => "0",
			"array" => $ldap_modes
			),
		"ldap_dn" => array(
			"friendly_name" => "Distinguished Name (DN)",
			//"description" => "DN语法,例如WINDOWS: <i>\"&lt;username&gt;@win2kdomain.local\"</i> 或OpenLDAP: <i>\"uid=&lt;username&gt;,ou=people,dc=domain,dc=local\"</i>.在登录提示符支持将\"&lt;username&gt\"替换成用户名,但这只在 \"不搜索\" 模式中使用.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_require" => array(
			"friendly_name" => "要求组成员",
			//"description" => "要求用户加入到用户组再验证.但必需设置组设置才能工作,启用该选项但组设置不正确可能导致验证失败.",
			"default" => "",
			"method" => "checkbox"
			),
		"ldap_group_header" => array(
			"friendly_name" => "LDAP组设置",
			"method" => "spacer"
			),
		"ldap_group_dn" => array(
			"friendly_name" => "用户组DN",
			//"description" => "用户组的DN.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_attrib" => array(
			"friendly_name" => "用户组成员属性",
			//"description" => "用户组成员属性的名称.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_member_type" => array(
			"friendly_name" => "用户组成员类型",
			//"description" => "定义用户使用完整的DN还是只使用用户名.",
			"method" => "drop_array",
			"default" => 1,
			"array" => array( 1 => "完整DN", 2 => "用户名" )
			),
		"ldap_search_base_header" => array(
			"friendly_name" => "LDAP搜索设置",
			"method" => "spacer"
			),
		"ldap_search_base" => array(
			"friendly_name" => "搜索(基本内容)",
			//"description" => "搜索LDAP目录的基本内容,例如 <i>\"dc=win2kdomain,dc=local\"</i> 或 <i>\"ou=people,dc=domain,dc=local\"</i>.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_search_filter" => array(
			"friendly_name" => "搜索过滤器",
			//"description" => "在LDAP目录中定位用户时使用的搜索过滤器,例如:WINDOWS使用: <i>\"(&amp;(objectclass=user)(objectcategory=user)(userPrincipalName=&lt;username&gt;*))\"</i> 或OpenLDAP使用: <i>\"(&(objectClass=account)(uid=&lt;username&gt))\"</i>.在登录提示符支持将 \"&lt;username&gt\" 替换成用户名. ",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_specific_dn" => array(
			"friendly_name" => "搜索DN",
			//"description" => "绑定到LDAP目录搜索的DN.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_specific_password" => array(
			"friendly_name" => "搜索密码",
			//"description" => "绑定到LDAP目录搜索的密码.",
			"method" => "textbox_password",
			"max_length" => "255"
			)
		)
	);

$settings_graphs = array(
	"general" => array(
		"default_rra_id" => array(
			"friendly_name" => "默认循环归档",
			//"description" => "当缩略图不被显示或'缩略图时间段'设置为'0'的时候使用的默认循环归档.",
			"method" => "drop_sql",
			"sql" => "select id,name from rra order by timespan",
			"default" => "1"
			),
		"default_view_mode" => array(
			"friendly_name" => "默认查看模式",
			//"description" => "当浏览'图形'时希望以哪种模式显示",
			"method" => "drop_array",
			"array" => $graph_views,
			"default" => "1"
			),
		"default_timespan" => array(
			"friendly_name" => "默认查看图形的时间段",
			//"description" => "当查看'图形'时希望默认显示的时间段",
			"method" => "drop_array",
			"array" => $graph_timespans,
			"default" => GT_LAST_DAY
			),
		"timespan_sel" => array(
			"friendly_name" => "图形时间段选择器",
			//"description" => "当查看'图形'时是否希望显示时间段选择器.",
			"method" => "checkbox",
			"default" => "on"
		),
		"default_timeshift" => array(
			"friendly_name" => "默认查看图形的时移",
			//"description" => "当查看'图形'时希望默认显示的时移",
			"method" => "drop_array",
			"array" => $graph_timeshifts,
			"default" => GTS_1_DAY
			),
		"allow_graph_dates_in_future" => array(
			"friendly_name" => "允许图形扩展到未来",
			//"description" => "当显示图形时,允许图形数据扩展到未来'",
			"method" => "checkbox",
			"default" => "on"
		),
		"first_weekdayid" => array(
			"friendly_name" => "每周的第一天",
			//"description" => "显示每一周图形时每周的第一天",
			"method" => "drop_array",
			"array" => $graph_weekdays,
			"default" => WD_MONDAY
			),
		"day_shift_start" => array(
			"friendly_name" => "上班时间",
			//"description" => "每个工作日的上班时间.",
			"method" => "textbox",
			"default" => "07:00",
			"max_length" => "5"
			),
		"day_shift_end" => array(
			"friendly_name" => "下班时间",
			//"description" => "每个工作日的下班时间.",
			"method" => "textbox",
			"default" => "18:00",
			"max_length" => "5"
			),
		"default_date_format" => array(
			"friendly_name" => "图形显示日期格式",
			//"description" => "图形上显示的日期格式",
			"method" => "drop_array",
			"array" => $graph_dateformats,
			"default" => GD_Y_MO_D
			),
		"default_datechar" => array(
			"friendly_name" => "图形显示日期分隔符",
			//"description" => "图形上显示的日期分隔符",
			"method" => "drop_array",
			"array" => $graph_datechar,
			"default" => GDC_SLASH
			),
		"page_refresh" => array(
			"friendly_name" => "页面刷新",
			//"description" => "每隔多少秒自动刷新页面,时间太长可能导致图形更新不及时,时间太短可能影响查看图形.",
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			)
		),
	"thumbnail" => array(
		"default_height" => array(
			"friendly_name" => "缩略图高度",
			//"description" => "缩略图的高度像素.",
			"method" => "textbox",
			"default" => "100",
			"max_length" => "10"
			),
		"default_width" => array(
			"friendly_name" => "缩略图宽度",
			//"description" => "缩略图的宽度像素.",
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			),
		"num_columns" => array(
			"friendly_name" => "缩略图列数量",
			//"description" => "当显示缩略图时显示成多少列.如果您的分辨率较高,建议使用3例或4例,这样您就可以在同一个页面查看到更多的图形.",
			"method" => "textbox",
			"default" => "2",
			"max_length" => "5"
			),
		"thumbnail_sections" => array(
			"friendly_name" => "选择性显示缩略图",
			//"description" => "缩略图应该被显示在哪些页面上.当显示缩略图的时候,会省掉一些时间和文本数据只显示图形数据,方便直观的查看性能.",
			"method" => "checkbox_group",
			"items" => array(
				"thumbnail_section_preview" => array(
					"friendly_name" => "预览查看",
					"default" => "on"
					),
				"thumbnail_section_tree_1" => array(
					"friendly_name" => "树状查看(单面板)",
					"default" => "on"
					),
				"thumbnail_section_tree_2" => array(
					"friendly_name" => "树状查看(双面板)",
					"default" => ""
					)
				)
			)
		),
	"tree" => array(
		"default_tree_id" => array(
			"friendly_name" => "默认图形树",
			//"description" => "当使用树状查看时默认显示的图形树.",
			"method" => "drop_sql",
			"sql" => "select id,name from graph_tree order by name",
			"default" => "0"
			),
		"default_tree_view_mode" => array(
			"friendly_name" => "默认树状查看模式",
			//"description" => "当使用树状查看时默认显示的图形树模式.",
			"method" => "drop_array",
			"array" => $graph_tree_views,
			"default" => "2"
			),
		"treeview_graphs_per_page" => array(
			"friendly_name" => "每页图形数量",
			//"description" => "在预览查看时每页显示多少图形.",
			"method" => "drop_array",
			"default" => "10",
			"array" => $graphs_per_page
			),
		"default_dual_pane_width" => array(
			"friendly_name" => "双面版图形树的宽度",
			//"description" => "当使用双面版图形树查看时,左边面版的宽度.",
			"method" => "textbox",
			"max_length" => "5",
			"default" => "200"
			),
		"expand_hosts" => array(
			"friendly_name" => "展开主机",
			//"description" => "当使用双面板图形树查看时,是否展开图形树.",
			"method" => "checkbox",
			"default" => ""
			),
		"show_graph_title" => array(
			"friendly_name" => "显示图形标题",
			//"description" => "是否将图形的标题显示在页面上,当图形很多时可以方便的使用浏览器的搜索功能快速找到图形的位置.",
			"method" => "checkbox",
			"default" => ""
			)
		),
	"preview" => array(
		"preview_graphs_per_page" => array(
			"friendly_name" => "每页图形数量",
			//"description" => "预览查看时每页显示的图形数量.",
			"method" => "drop_array",
			"default" => "10",
			"array" => $graphs_per_page
			)
		),
	"list" => array(
		"list_graphs_per_page" => array(
			"friendly_name" => "每页图形数量",
			//"description" => "列表查看时每页显示的图形数量.",
			"method" => "drop_array",
			"default" => "30",
			"array" => $graphs_per_page
			)
		)
	);

api_plugin_hook('config_settings');

?>
