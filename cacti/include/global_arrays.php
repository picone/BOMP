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

global $menu;

$messages = array(
	1  => array(
		"message" => '保存成功.',
		"type" => "info"),
	2  => array(
		"message" => '保存失败.',
		"type" => "error"),
	3  => array(
		"message" => '保存失败:请检查红色字段.',
		"type" => "error"),
	4  => array(
		"message" => '密码不匹配,请重新输入.',
		"type" => "error"),
	5  => array(
		"message" => '您必需至少选择一个字段.',
		"type" => "error"),
	6  => array(
		"message" => '要使用这个功能,您必需使用内建用户验证.',
		"type" => "error"),
	7  => array(
		"message" => 'XML解析出错.',
		"type" => "error"),
	12 => array(
		"message" => '用户名已存在.',
		"type" => "error"),
	15 => array(
		"message" => 'XML:Cacti版本不存在.',
		"type" => "error"),
	16 => array(
		"message" => 'XML:哈希版本不存在.',
		"type" => "error"),
	17 => array(
		"message" => 'XML:被更新的Cacti版本产生.',
		"type" => "error"),
	18 => array(
		"message" => 'XML:无法定位代码.',
		"type" => "error"),
	19 => array(
		"message" => '用户名已存在.',
		"type" => "error"),
	20 => array(
		"message" => '模板设计或来宾用户不充许更改用户名.',
		"type" => "error"),
	21 => array(
		"message" => '模板设计或来宾用户不充许删除.',
		"type" => "error"),
	22 => array(
		"message" => '图形导出用户不充许删除.',
		"type" => "error")
	);

$cdef_operators = array(1 =>
	"+",
	"-",
	"*",
	"/",
	"%");

$cdef_functions = array(1 =>
	"SIN",
	"COS",
	"LOG",
	"EXP",
	"FLOOR",
	"CEIL",
	"LT",
	"LE",
	"GT",
	"GE",
	"EQ",
	"IF",
	"MIN",
	"MAX",
	"LIMIT",
	"DUP",
	"EXC",
	"POP",
	"UN",
	"UNKN",
	"PREV",
	"INF",
	"NEGINF",
	"NOW",
	"TIME",
	"LTIME");

$input_types = array(
	DATA_INPUT_TYPE_SNMP => "SNMP", // Action 0:
	DATA_INPUT_TYPE_SNMP_QUERY => "SNMP查询",
	DATA_INPUT_TYPE_SCRIPT => "脚本/命令",  // Action 1:
	DATA_INPUT_TYPE_SCRIPT_QUERY => "脚本查询", // Action 1:
	DATA_INPUT_TYPE_PHP_SCRIPT_SERVER => "脚本-脚本服务器(PHP)",
	DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER => "脚本查询-脚本服务器"
	);

$reindex_types = array(
	DATA_QUERY_AUTOINDEX_NONE => "无",
	DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME => "时间回滚",
	DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE => "改变索引户数",
	DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION => "核实所有字段"
	);

$snmp_query_field_actions = array(1 =>
	"SNMP字段名(下拉菜单)",
	"SNMP字段值(来自用户)",
	"SNMP输出类型(下拉菜单)");

$consolidation_functions = array(1 =>
	"AVERAGE",
	"MIN",
	"MAX",
	"LAST");

$data_source_types = array(1 =>
	"GAUGE",
	"COUNTER",
	"DERIVE",
	"ABSOLUTE");

$graph_item_types = array(
	GRAPH_ITEM_TYPE_COMMENT => "COMMENT",
	GRAPH_ITEM_TYPE_HRULE   => "HRULE",
	GRAPH_ITEM_TYPE_VRULE   => "VRULE",
	GRAPH_ITEM_TYPE_LINE1   => "LINE1",
	GRAPH_ITEM_TYPE_LINE2   => "LINE2",
	GRAPH_ITEM_TYPE_LINE3   => "LINE3",
	GRAPH_ITEM_TYPE_AREA    => "AREA",
	GRAPH_ITEM_TYPE_STACK   => "STACK",
	GRAPH_ITEM_TYPE_GPRINT  => "GPRINT",
	GRAPH_ITEM_TYPE_LEGEND  => "LEGEND"
	);

$image_types = array(1 =>
	"PNG",
	"GIF",
	"SVG");

$snmp_versions = array(0 =>
	"未使用",
	"版本1",
	"版本2",
	"版本3");

$snmp_auth_protocols = array(
	"MD5" => "MD5(默认)",
	"SHA" => "SHA");

$snmp_priv_protocols = array(
	"[None]" => "[无]",
	"DES" => "DES(无)",
	"AES128" => "AES");

$banned_snmp_strings = array(
	"End of MIB",
	"No Such");

$logfile_options = array(1 =>
	"仅日志文件",
	"日志文件和Syslog/Eventlog",
	"仅Syslog/Eventlog");

$availability_options = array(
	AVAIL_NONE => "无",
	AVAIL_SNMP_AND_PING => "检测和SNMP",
	AVAIL_SNMP_OR_PING => "检测或SNMP",
	AVAIL_SNMP => "SNMP",
	AVAIL_PING => "检测");

$ping_methods = array(
	PING_ICMP => "ICMP检测",
	PING_TCP => "TCP检测",
	PING_UDP => "UDP检测");

$logfile_verbosity = array(
	POLLER_VERBOSITY_NONE => "无-仅Syslog",
	POLLER_VERBOSITY_LOW => "低-统计和错误",
	POLLER_VERBOSITY_MEDIUM => "中-统计,错误和结果",
	POLLER_VERBOSITY_HIGH => "高-统计,错误,结果和主机I/O事件",
	POLLER_VERBOSITY_DEBUG => "调试-统计,错误,结果,I/O和程序流",
	POLLER_VERBOSITY_DEVDBG => "开发-开发者调试级别");

$poller_options = array(1 =>
	"cmd.php",
	"spine");

$poller_intervals = array(
	10 => "每10秒",
	15 => "每15秒",
	20 => "每20秒",
	30 => "每30秒",
	60 => "每分钟",
	300 => "每5分钟");

$cron_intervals = array(
	60 => "每分钟",
	300 => "每5分钟");

$registered_cacti_names = array(
	"path_cacti");

$graph_views = array(1 =>
	"树状查看",
	"列表查看",
	"预览查看");

$graph_tree_views = array(1 =>
	"单面版",
	"双面版");

$auth_methods = array(
	0 => "无",
	1 => "内建验证",
	2 => "基本WEB验证");
if (function_exists("ldap_connect")) {
	$auth_methods[3] = "LDAP验证";
}

$auth_realms = array(0 =>
	"本地",
	"LDAP",
	"基本WEB");

$ldap_versions = array(
	2 => "版本2",
	3 => "版本3"
	);

$ldap_encryption = array(
	0 => "无",
	1 => "SSL",
	2 => "TLS");

$ldap_modes = array(
	0 => "不搜索",
	1 => "匿名搜索",
	2 => "指定搜索");

$snmp_implimentations = array(
	"ucd-snmp" => "UCD-SNMP 4.x",
	"net-snmp" => "NET-SNMP 5.x");

if ($config["cacti_server_os"] != "win32") {
	$rrdtool_versions = array(
		"rrd-1.0.x" => "RRDTool 1.0.x",
		"rrd-1.2.x" => "RRDTool 1.2.x",
		"rrd-1.3.x" => "RRDTool 1.3.x",
		"rrd-1.4.x" => "RRDTool 1.4.x");
}else{
	$rrdtool_versions = array(
		"rrd-1.0.x" => "RRDTool 1.0.x",
		"rrd-1.2.x" => "RRDTool 1.2.x");
}

$cdef_item_types = array(
	1 => "函数",
	2 => "运算符",
	4 => "指定数据源",
	5 => "另一个CDEF",
	6 => "自定义字符串");

$graph_color_alpha = array(
		"00" => "  0%",
		"19" => " 10%",
		"33" => " 20%",
		"4C" => " 30%",
		"66" => " 40%",
		"7F" => " 50%",
		"99" => " 60%",
		"B2" => " 70%",
		"CC" => " 80%",
		"E5" => " 90%",
		"FF" => "100%"
		);

$tree_sort_types = array(
	TREE_ORDERING_NONE => "手动排序",
	TREE_ORDERING_ALPHABETIC => "字母排序",
	TREE_ORDERING_NATURAL => "自然排序",
	TREE_ORDERING_NUMERIC => "数字排序"
	);

$tree_item_types = array(
	TREE_ITEM_TYPE_HEADER => "头分支",
	TREE_ITEM_TYPE_GRAPH => "图形",
	TREE_ITEM_TYPE_HOST => "主机"
	);

$host_group_types = array(
	HOST_GROUPING_GRAPH_TEMPLATE => "图形模板",
	HOST_GROUPING_DATA_QUERY_INDEX => "数据查询索引"
	);

$custom_data_source_types = array(
	"CURRENT_DATA_SOURCE" => "当前图形对象数据源",
	"ALL_DATA_SOURCES_NODUPS" => "所有数据源(不包含重复)",
	"ALL_DATA_SOURCES_DUPS"	=> "所有数据源(包含重复)",
	"SIMILAR_DATA_SOURCES_NODUPS" => "所有类似的数据源(不包含重复)",
	"SIMILAR_DATA_SOURCES_DUPS" => "所有类似的数据源(包含重复)",
	"CURRENT_DS_MINIMUM_VALUE" => "当前数据源对象:最小值",
	"CURRENT_DS_MAXIMUM_VALUE" => "当前数据源对象:最大值",
	"CURRENT_GRAPH_MINIMUM_VALUE" => "图形:低限制",
	"CURRENT_GRAPH_MAXIMUM_VALUE" => "图形:高限制",
	"COUNT_ALL_DS_NODUPS" => "所有数据源记数(不包含重复)",
	"COUNT_ALL_DS_DUPS" => "所有数据源记数(包含重复)",
	"COUNT_SIMILAR_DS_NODUPS" => "所有类似数据源记数(不包含重复)",
	"COUNT_SIMILAR_DS_DUPS"	=> "所有数据源记数(包含重复)");

$menu = array(
	"添加" => array(
		"graphs_new.php" => "添加新图形"
		),
	"管理" => array(
		"graphs.php" => array(
			"graphs.php" => "图形管理",
			"cdef.php" => "CDEF",
			"color.php" => "颜色",
			"gprint_presets.php" => "GPRINT预设"
			),
		"tree.php" => "图形树",
		"data_sources.php" => array(
			"data_sources.php" => "数据源",
			"rra.php" => "循环归档"
			),
		"host.php" => '主机'
		),
	"采集方法" => array(
		"data_queries.php" => "数据查询",
		"data_input.php" => "数据输入方法"
		),
	"模板" => array(
		"graph_templates.php" => "图形模板",
		"host_templates.php" => "主机模板",
		"data_templates.php" => "数据模板"
		),
	"导出/导出" => array(
		"templates_import.php" => "导入模板",
		"templates_export.php" => "导出模板"
		),
	"配置"  => array(
		"settings.php" => "设置"
		),
	"工具" => array(
		"utilities.php" => " 系统工具",
		"user_admin.php" => "用户管理",
		"logout.php" => "用户登出"
	));

$log_tail_lines = array(
	-1 => "所有行",
	10 => "10行",
	15 => "15行",
	20 => "20行",
	50 => "50行",
	100 => "100行",
	200 => "200行",
	500 => "500行",
	1000 => "1000行",
	2000 => "2000行",
	3000 => "3000行",
	5000 => "5000行",
	10000 => "10000行"
	);

$item_rows = array(
	10 => "10",
	15 => "15",
	20 => "20",
	25 => "25",
	30 => "30",
	40 => "40",
	50 => "50",
	100 => "100",
	250 => "250",
	500 => "500",
	1000 => "1000",
	2000 => "2000",
	5000 => "5000"
	);

$graphs_per_page = array(
	4 => "4",
	6 => "6",
	8 => "8",
	10 => "10",
	14 => "14",
	20 => "20",
	24 => "24",
	30 => "30",
	40 => "40",
	50 => "50",
	100 => "100"
	);

$page_refresh_interval = array(
	5 => "5秒",
	10 => "10秒",
	20 => "20秒",
	30 => "30秒",
	60 => "1分钟",
	300 => "5分钟",
	600 => "10分钟",
	9999999 => "永不");

$user_auth_realms = array(
	1 => "用户管理",
	2 => "数据输入",
	3 => "更新数据源",
	4 => "更新图形树",
	5 => "更新图形",
	7 => "查看图形",
	8 => "访问控制台",
	9 => "更新循环归档",
	10 => "更新图形模板",
	11 => "更新数据模板",
	12 => "更新主机模板",
	13 => "数据查询",
	14 => "更新CDEF",
	15 => "全局设置",
	16 => "导出数据",
	17 => "导入数据"
	);

$user_auth_realm_filenames = array(
	"about.php" => 8,
	"cdef.php" => 14,
	"color.php" => 5,
	"data_input.php" => 2,
	"data_sources.php" => 3,
	"data_templates.php" => 11,
	"gprint_presets.php" => 5,
	"graph.php" => 7,
	"graph_image.php" => 7,
	"graph_xport.php" => 7,
	"graph_settings.php" => 7,
	"graph_templates.php" => 10,
	"graph_templates_inputs.php" => 10,
	"graph_templates_items.php" => 10,
	"graph_view.php" => 7,
	"graphs.php" => 5,
	"graphs_items.php" => 5,
	"graphs_new.php" => 5,
	"host.php" => 3,
	"host_templates.php" => 12,
	"index.php" => 8,
	"rra.php" => 9,
	"settings.php" => 15,
	"data_queries.php" => 13,
	"templates_export.php" => 16,
	"templates_import.php" => 17,
	"tree.php" => 4,
	"user_admin.php" => 1,
	"utilities.php" => 15,
	"smtp_servers.php" => 8,
	"email_templates.php" => 8,
	"event_queue.php" => 8,
	"smtp_queue.php" => 8,
	"logout.php" => -1
	);

$hash_type_codes = array(
	"round_robin_archive" => "15",
	"cdef" => "05",
	"cdef_item" => "14",
	"gprint_preset" => "06",
	"data_input_method" => "03",
	"data_input_field" => "07",
	"data_template" => "01",
	"data_template_item" => "08",
	"graph_template" => "00",
	"graph_template_item" => "10",
	"graph_template_input" => "09",
	"data_query" => "04",
	"data_query_graph" => "11",
	"data_query_sv_graph" => "12",
	"data_query_sv_data_source" => "13",
	"host_template" => "02"
	);

$hash_version_codes = array(
	"0.8.4"  => "0000",
	"0.8.5"  => "0001",
	"0.8.5a" => "0002",
	"0.8.6"  => "0003",
	"0.8.6a" => "0004",
	"0.8.6b" => "0005",
	"0.8.6c" => "0006",
	"0.8.6d" => "0007",
	"0.8.6e" => "0008",
	"0.8.6f" => "0009",
	"0.8.6g" => "0010",
	"0.8.6h" => "0011",
	"0.8.6i" => "0012",
	"0.8.6j" => "0013",
	"0.8.7"  => "0014",
	"0.8.7a" => "0015",
	"0.8.7b" => "0016",
	"0.8.7c" => "0017",
	"0.8.7d" => "0018",
	"0.8.7e" => "0019",
	"0.8.7f" => "0020",
	"0.8.7g" => "0021",
        "0.8.7h" => "0022",
        "0.8.7i" => "0023",
        "0.8.8"  => "0024",
        "0.8.8a" => "0025"
	);

$hash_type_names = array(
	"cdef" => "CDEF",
	"cdef_item" => "CDEF对象",
	"gprint_preset" => "GPRINT预设",
	"data_input_method" => "数据输入方法",
	"data_input_field" => "数据输入字段",
	"data_template" => "数据模板",
	"data_template_item" => "数据模板对象",
	"graph_template" => "图形模板",
	"graph_template_item" => "图形模板对象",
	"graph_template_input" => "图形模板输入",
	"data_query" => "数据查询",
	"host_template" => "主机模板",
	"round_robin_archive" => "循环归档"
	);

$host_struc = array(
	"host_template_id",
	"description",
	"hostname",
	"notes",
	"snmp_community",
	"snmp_version",
	"snmp_username",
	"snmp_password",
	"snmp_auth_protocol",
	"snmp_priv_passphrase",
	"snmp_priv_protocol",
	"snmp_context",
	"snmp_port",
	"snmp_timeout",
	"max_oids",
	"availability_method",
	"ping_method",
	"ping_port",
	"ping_timeout",
	"ping_retries",
	"disabled",
	"status",
	"status_event_count",
	"status_fail_date",
	"status_rec_date",
	"status_last_error",
	"min_time",
	"max_time",
	"cur_time",
	"avg_time",
	"total_polls",
	"failed_polls",
	"availability"
	);

$graph_timespans = array(
	GT_LAST_HALF_HOUR => "最后半小时",
	GT_LAST_HOUR => "最后1小时",
	GT_LAST_2_HOURS => "最后2小时",
	GT_LAST_4_HOURS => "最后4小时",
	GT_LAST_6_HOURS =>"最后6小时",
	GT_LAST_12_HOURS =>"最后12小时",
	GT_LAST_DAY =>"最后1天",
	GT_LAST_2_DAYS =>"最后2天",
	GT_LAST_3_DAYS =>"最后3天",
	GT_LAST_4_DAYS =>"最后4天",
	GT_LAST_WEEK =>"最后1周",
	GT_LAST_2_WEEKS =>"最后2周",
	GT_LAST_MONTH =>"最后1月",
	GT_LAST_2_MONTHS =>"最后2个月",
	GT_LAST_3_MONTHS =>"最后3个月",
	GT_LAST_4_MONTHS =>"最后4个月",
	GT_LAST_6_MONTHS =>"最后6个月",
	GT_LAST_YEAR =>"最后1年",
	GT_LAST_2_YEARS =>"最后2年",
	GT_DAY_SHIFT => "时移",
	GT_THIS_DAY => "今天",
	GT_THIS_WEEK => "本周",
	GT_THIS_MONTH => "本月",
	GT_THIS_YEAR => "本年",
	GT_PREV_DAY => "昨天",
	GT_PREV_WEEK => "上周",
	GT_PREV_MONTH => "上个月",
	GT_PREV_YEAR => "去年"
	);

$graph_timeshifts = array(
	GTS_HALF_HOUR => "30分钟",
	GTS_1_HOUR => "1小时",
	GTS_2_HOURS => "2小时",
	GTS_4_HOURS => "4小时",
	GTS_6_HOURS => "6小时",
	GTS_12_HOURS => "12小时",
	GTS_1_DAY => "1天",
	GTS_2_DAYS => "2天",
	GTS_3_DAYS => "3天",
	GTS_4_DAYS => "4天",
	GTS_1_WEEK => "1周",
	GTS_2_WEEKS => "2周",
	GTS_1_MONTH => "1个月",
	GTS_2_MONTHS => "2个月",
	GTS_3_MONTHS => "3个月",
	GTS_4_MONTHS => "4个月",
	GTS_6_MONTHS => "6个月",
	GTS_1_YEAR => "1年",
	GTS_2_YEARS => "2年"
	);

$graph_weekdays = array(
	WD_SUNDAY => "星期天",
	WD_MONDAY => "星期一",
	WD_TUESDAY => "星期二",
	WD_WEDNESDAY => "星期三",
	WD_THURSDAY => "星期四",
	WD_FRIDAY => "星期五",
	WD_SATURDAY => "星期六"
	);
/*
$graph_weekdays = array(
	WD_SUNDAY => date("l", strtotime("Sunday")),
	WD_MONDAY => date("l", strtotime("Monday")),
	WD_TUESDAY => date("l", strtotime("Tuesday")),
	WD_WEDNESDAY => date("l", strtotime("Wednesday")),
	WD_THURSDAY => date("l", strtotime("Thursday")),
	WD_FRIDAY => date("l", strtotime("Friday")),
	WD_SATURDAY => date("l", strtotime("Saturday"))
	);
*/
$graph_dateformats = array(
	GD_MO_D_Y => "月数字,天,年",
	GD_MN_D_Y => "月名字,天,年",
	GD_D_MO_Y => "天,月数字,年",
	GD_D_MN_Y => "天,月名字,年",
	GD_Y_MO_D => "年,月数字,天",
	GD_Y_MN_D => "年,月名字,天"
	);

$graph_datechar = array(
	GDC_HYPHEN => "-",
	GDC_SLASH => "/"
	);

$plugin_architecture = array(
	'version' => '2.8'
	);

api_plugin_hook('config_arrays');

?>
