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

if (!defined('VALID_HOST_FIELDS')) {
	$string = api_plugin_hook_function('valid_host_fields', '(hostname|snmp_community|snmp_username|snmp_password|snmp_auth_protocol|snmp_priv_passphrase|snmp_priv_protocol|snmp_context|snmp_version|snmp_port|snmp_timeout)');
	define('VALID_HOST_FIELDS', $string);
}

/* file: cdef.php, action: edit */
$fields_cdef_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "这个CDEF的名称.",
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "60"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_cdef" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: color.php, action: edit */
$fields_color_edit = array(
	"hex" => array(
		"method" => "textbox",
		"friendly_name" => "Hex值",
		//"description" => "这个颜色的Hex值,值范围:000000-FFFFFF.",
		"value" => "|arg1:hex|",
		"max_length" => "6",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_color" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: edit */
$fields_data_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "为这个数据输入方法取一个好记的名字.",
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"type_id" => array(
		"method" => "drop_array",
		"friendly_name" => "输入类型",
		//"description" => "选择这个数据输入的类型.",
		"value" => "|arg1:type_id|",
		"array" => $input_types,
		),
	"input_string" => array(
		"method" => "textarea",
		"friendly_name" => "输入字符串",
		//"description" => "发送到脚本的数据,它包含脚本和输入源的完整路径.",
		"value" => "|arg1:input_string|",
		"textarea_rows" => "4",
		"textarea_cols" => "60",
		"class" => "textAreaNotes",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_data_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: field_edit (dropdown) */
$fields_data_input_field_edit_1 = array(
	"data_name" => array(
		"method" => "drop_array",
		"friendly_name" => "字段[|arg1:|]",
		//"description" => "从|arg1:|字段选择已关联的字段.",
		"value" => "|arg3:data_name|",
		"array" => "|arg2:|",
		)
	);

/* file: data_input.php, action: field_edit (textbox) */
$fields_data_input_field_edit_2 = array(
	"data_name" => array(
		"method" => "textbox",
		"friendly_name" => "字段[|arg1:|]",
		//"description" => "为字段|arg1:|输入一个名称.",
		"value" => "|arg2:data_name|",
		"max_length" => "50",
		)
	);

/* file: data_input.php, action: field_edit */
$fields_data_input_field_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "友好名称",
		//"description" => "为这个数据输入方法输入一个好记的名称.",
		"value" => "|arg1:name|",
		"max_length" => "200",
		),
	"update_rra" => array(
		"method" => "checkbox",
		"friendly_name" => "更新RRD文件",
		//"description" => "从这个输出字段输出的数据是否写入RRD文件.",
		"value" => "|arg1:update_rra|",
		"default" => "on",
		"form_id" => "|arg1:id|"
		),
	"regexp_match" => array(
		"method" => "textbox",
		"friendly_name" => "正则表达式匹配",
		//"description" => "如果您希望从输入的数据中匹配正则表达式,在这里输入(ereg格式).",
		"value" => "|arg1:regexp_match|",
		"max_length" => "200",
		"size" => "70"
		),
	"allow_nulls" => array(
		"method" => "checkbox",
		"friendly_name" => "允许输入为空",
		//"description" => "如果您希望允许用户输入空内容到这个字段.",
		"value" => "|arg1:allow_nulls|",
		"default" => "",
		"form_id" => false
		),
	"type_code" => array(
		"method" => "textbox",
		"friendly_name" => "指定类型代码",
		//"description" => "如果这个字段专门由主机模板指定,这里应该说明.这个字段可用的关键字是 " . (str_replace(")", "'", str_replace("(", "'", str_replace("|", "', '", VALID_HOST_FIELDS)))),
		"value" => "|arg1:type_code|",
		"max_length" => "40"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"input_output" => array(
		"method" => "hidden",
		"value" => "|arg2:|"
		),
	"sequence" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:sequence|"
		),
	"data_input_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:data_input_id|"
		),
	"save_component_field" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_templates.php, action: template_edit */
$fields_data_template_template_edit = array(
	"template_name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "为这个数据模板取一个名称.",
		"value" => "|arg1:name|",
		"max_length" => "150",
		),
	"data_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:data_template_id|"
		),
	"data_template_data_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:id|"
		),
	"current_rrd" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:view_rrd|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source = array(
	"name" => array(
		"friendly_name" => "名称",
		"method" => "textbox",
		"max_length" => "250",
		"default" => "",
		//"description" => "为这个数据源选择一个名称.",
		"flags" => ""
		),
	"data_source_path" => array(
		"friendly_name" => "数据源路径",
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		//"description" => "RRD文件的完整路径.",
		"flags" => "NOTEMPLATE"
		),
	"data_input_id" => array(
		"friendly_name" => "数据输入方法",
		"method" => "drop_sql",
		"sql" => "select id,name from data_input order by name",
		"default" => "",
		"none_value" => "无",
		//"description" => "脚本/源采集这个数据源的数据的方法.",
		"flags" => "ALWAYSTEMPLATE"
		),
	"rra_id" => array(
		"method" => "drop_multi_rra",
		"friendly_name" => "已关联的循环归档",
		//"description" => "当输入数据的时候使用哪个循环归档.(推荐您选择这些全部的值).",
		"form_id" => "|arg1:id|",
		"sql" => "select rra_id as id,data_template_data_id from data_template_data_rra where data_template_data_id=|arg1:id|",
		"sql_all" => "select rra.id from rra order by id",
		"sql_print" => "select rra.name from (data_template_data_rra,rra) where data_template_data_rra.rra_id=rra.id and data_template_data_rra.data_template_data_id=|arg1:id|",
		"flags" => "ALWAYSTEMPLATE"
		),
	"rrd_step" => array(
		"friendly_name" => "步进",
		"method" => "textbox",
		"max_length" => "10",
		"size" => "20",
		"default" => "300",
		//"description" => "两次更新之间间隔多少秒.",
		"flags" => ""
		),
	"active" => array(
		"friendly_name" => "数据源状态",
		"method" => "checkbox",
		"default" => "on",
		//"description" => "Cacti是否应该从这个数据源采集数据.",
		"flags" => ""
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source_item = array(
	"data_source_name" => array(
		"friendly_name" => "数据源内部名称",
		"method" => "textbox",
		"max_length" => "19",
		"default" => "",
		//"description" => "为这个数据选择一个不同的名称,用在RRD文件内部."
		),
	"rrd_minimum" => array(
		"friendly_name" => "最小值",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		//"description" => "可以采集数据的最小值."
		),
	"rrd_maximum" => array(
		"friendly_name" => "最大值",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		//"description" => "可以采集数据的最大值."
		),
	"data_source_type_id" => array(
		"friendly_name" => "数据源类型",
		"method" => "drop_array",
		"array" => $data_source_types,
		"default" => "",
		//"description" => "在循环归档数据内应该如何表示."
		),
	"rrd_heartbeat" => array(
		"friendly_name" => "心跳",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "600",
		//"description" => "在数据输入为\"未知\"之前允许多少秒.
			//(Usually 2x300=600)"
		),
	"data_input_field_id" => array(
		"friendly_name" => "输出字段",
		"method" => "drop_sql",
		"default" => "0",
		//"description" => "当数据已采集,这个字段的数据将放入这个数据源."
		)
	);

/* file: grprint_presets.php, action: edit */
$fields_grprint_presets_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "为这个GPRINT输入一个名称.",
		"value" => "|arg1:name|",
		"max_length" => "50",
		),
	"gprint_text" => array(
		"method" => "textbox",
		"friendly_name" => "GPRINT文本",
		//"description" => "在这里输入自定义GPRINT字符串.",
		"value" => "|arg1:gprint_text|",
		"max_length" => "50",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_gprint_presets" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: (graphs.php|graph_templates.php), action: (graph|template)_edit */
$struct_graph = array(
	"title" => array(
		"friendly_name" => "标题",
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		//"description" => "打印在图形上方的名称,通常是主机名或IP地址和监控对象."
		),
	"image_format_id" => array(
		"friendly_name" => "图形格式",
		"method" => "drop_array",
		"array" => $image_types,
		"default" => "1",
		//"description" => "生成的图形格式:PNG,GIF或SVG.推荐使用PNG格式"
		),
	"height" => array(
		"friendly_name" => "高度",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "120",
		//"description" => "这个图形的高度像素."
		),
	"width" => array(
		"friendly_name" => "宽度",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "500",
		//"description" => "这个图形的宽度像素."
		),
	"slope_mode" => array(
		"friendly_name" => "平滑模式",
		"method" => "checkbox",
		"default" => "on",
		//"description" => "使用平滑模式可以很好的减少图形中的锯齿,使图形更美观,但会牺牲一些精度."
		),
	"auto_scale" => array(
		"friendly_name" => "自动缩放",
		"method" => "checkbox",
		"default" => "on",
		//"description" => "自动缩放Y轴高度,而上限和下限.注意:如果这个选项被选中会同时跳过上限和下限."
		),
	"auto_scale_opts" => array(
		"friendly_name" => "自动缩放选项",
		"method" => "radio",
		"default" => "2",
		//"description" => "请在右边选择,建议使用默认.<br>
		 //   ",
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => "自动缩放,跳过限制"
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => "自动缩放最大值,允许下限"
				),
			2 => array(
				"radio_value" => "3",
				"radio_caption" => "自动缩放最小值,允许上限"
				),
			3 => array(
				"radio_value" => "4",
				"radio_caption" => "自动缩放,允许两个限制"
				)
			)
		),
/*	"auto_scale_log" => array(
		"friendly_name" => "对数缩放",
		"method" => "checkbox",
		"default" => "",
		"on_change" => "changeScaleLog()",
		"description" => "使用对数缩放Y轴高度"
		),
	"scale_log_units" => array(
		"friendly_name" => "SI Units for Logarithmic Scaling (--units=si)",
		"method" => "checkbox",
		"default" => "",
		"description" => "Use SI Units for Logarithmic Scaling instead of using exponential notation (not available for rrdtool-1.0.x).<br>
			Note: Linear graphs use SI notation by default."
		),
*/
	"auto_scale_rigid" => array(
		"friendly_name" => "硬性边界模式",
		"method" => "checkbox",
		"default" => "",
		//"description" => "当图形超出上限和下限时不要扩展图形,自动缩放时图形中的峰值到延伸到顶部."
		),
	"auto_padding" => array(
		"friendly_name" => "自动填充",
		"method" => "checkbox",
		"default" => "on",
		//"description" => "填充文本到图形上."
		),
	"export" => array(
		"friendly_name" => "允许导出图形",
		"method" => "checkbox",
		"default" => "on",
		//"description" => "选择是否允许Cacti导出功能导出这个图形到静态HTML/PNG."
		),
	"upper_limit" => array(
		"friendly_name" => "上限",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "100",
		//"description" => "图形的最大Y轴值."
		),
	"lower_limit" => array(
		"friendly_name" => "下限",
		"method" => "textbox",
		"max_length" => "255",
		"default" => "0",
		//"description" => "图形的最小Y轴值."
		),
	"base_value" => array(
		"friendly_name" => "进位单位",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "1000",
		//"description" => "内存设置为1024进位,流量设置为1000进位."
		),
/*	"unit_value" => array(
		"friendly_name" => "Unit Grid Value (--unit/--y-grid)",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		"description" => "Sets the xponent value on the Y-axis for numbers. Note: This option was
			added in rrdtool 1.0.36 and depricated in 1.2.x.  In RRDtool 1.2.x, this value is replaced by the --y-grid option.
			In this option, Y-axis grid lines appear at each grid step interval.  Labels are placed every label factor lines."
		),
*/
	"unit_exponent_value" => array(
		"friendly_name" => "单位",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		//"description" => "Cacti应该显示什么单位在Y轴上.设置为3使用'k'为单位,设置为-6使用'u'为单位(微)."
		),
	"vertical_label" => array(
		"friendly_name" => "垂直标签",
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		//"description" => "打印在图形左侧的垂直标签."
		)
	);

/* file: (graphs.php|graph_templates.php), action: item_edit */
$struct_graph_item = array(
	"task_item_id" => array(
		"friendly_name" => "数据源",
		"method" => "drop_sql",
		"sql" => "select
			CONCAT_WS('',case when host.description is null then 'No Host' when host.description is not null then host.description end,' - ',data_template_data.name,' (',data_template_rrd.data_source_name,')') as name,
			data_template_rrd.id
			from (data_template_data,data_template_rrd,data_local)
			left join host on (data_local.host_id=host.id)
			where data_template_rrd.local_data_id=data_local.id
			and data_template_data.local_data_id=data_local.id
			order by name",
		"default" => "0",
		"none_value" => "无",
		//"description" => "这个图形所使用的数据源."
		),
	"color_id" => array(
		"friendly_name" => "颜色",
		"method" => "drop_color",
		"default" => "0",
		"on_change" => "changeColorId()",
		//"description" => "该数据在图形中使用的颜色."
		),
	"alpha" => array(
		"friendly_name" => "不透明度",
		"method" => "drop_array",
		"default" => "FF",
		"array" => $graph_color_alpha,
		//"description" => "该数据在图形的不透明度,当某些数据不可以叠加显示时,使其中一个数据呈半透明状态可以同时看到多个数据的对比状态."
		),
	"graph_type_id" => array(
		"friendly_name" => "图形对象类型",
		"method" => "drop_array",
		"array" => $graph_item_types,
		"default" => "0",
		//"description" => "这个数据以哪种显示效果显示在图形上."
		),
	"consolidation_function_id" => array(
		"friendly_name" => "合并函数",
		"method" => "drop_array",
		"array" => $consolidation_functions,
		"default" => "0",
		//"description" => "这个数据以哪种统计方法显示在图形上."
		),
	"cdef_id" => array(
		"friendly_name" => "CDEF函数",
		"method" => "drop_sql",
		"sql" => "select id,name from cdef order by name",
		"default" => "0",
		"none_value" => "无",
		//"description" => "应用到这个数据上的CDEF函数."
		),
	"value" => array(
		"friendly_name" => "HRULE或VRULE的值",
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		//"description" => "如果这个图形对象是一个HRULE或VRULE,您可以在这里输入它的值."
		),
	"gprint_id" => array(
		"friendly_name" => "GPRINT类型",
		"method" => "drop_sql",
		"sql" => "select id,name from graph_templates_gprint order by name",
		"default" => "2",
		//"description" => "如果这个图形对象是一个GPRINT,您可以选择它的类型,您还可以在 \"GPRINT预设\" 里添加更多的类型."
		),
	"text_format" => array(
		"friendly_name" => "文本",
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		//"description" => "这个文本将会显示在数据图形上."
		),
	"hard_return" => array(
		"friendly_name" => "插入换行符",
		"method" => "checkbox",
		"default" => "",
		//"description" => "强制换行显示下一个图形数据."
		),
	"sequence" => array(
		"friendly_name" => "显示顺序",
		"method" => "view"
		)
	);

/* file: graph_templates.php, action: template_edit */
$fields_graph_template_template_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "这个图形模板的名称.",
		"value" => "|arg1:name|",
		"max_length" => "150",
		),
	"graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:graph_template_id|"
		),
	"graph_template_graph_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: graph_templates.php, action: input_edit */
$fields_graph_template_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "为这个图形对象输入输入一个名称,确保您可以识别它.",
		"value" => "|arg1:name|",
		"max_length" => "50"
		),
	"description" => array(
		"method" => "textarea",
		"friendly_name" => "目标",
		//"description" => "为这个图形对象输入输入一个描述,描述它的作用.",
		"value" => "|arg1:description|",
		"textarea_rows" => "5",
		"textarea_cols" => "40"
		),
	"column_name" => array(
		"method" => "drop_array",
		"friendly_name" => "字段类型",
		//"description" => "数据在这个图形上应该如何表示.",
		"value" => "|arg1:column_name|",
		"array" => "|arg2:|",
		),
	"graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:graph_template_id|"
		),
	"graph_template_input_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:id|"
		),
	"save_component_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: host.php, action: edit */
$fields_host_edit = array(
	"host_header" => array(
		"method" => "spacer",
		"friendly_name" => "常规主机选项"
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => "描述",
		//"description" => "为这个主机选一个容易识别的描述,如主机名等(支持中文).",
		"value" => "|arg1:description|",
		"max_length" => "250",
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => "主机名或IP地址",
		//"description" => "该主机的完整主机名或IP地址,如果是主机名,Cacti需要能通过该主机名解析出正确的IP地址.",
		"value" => "|arg1:hostname|",
		"max_length" => "250",
		),
	"host_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => "主机模板",
		//"description" => "根据这个主机的类型选择正确的主机模板,不同的主机模板可以自动的添加需要监控的指标,您也可以手动的添加或去掉监控指标.",
		"value" => "|arg1:host_template_id|",
		"none_value" => "无",
		"sql" => "select id,name from host_template order by name",
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => "禁用主机",
		//"description" => "禁用该主机将会停止这个主机上所有的数据采集.",
		"value" => "|arg1:disabled|",
		"default" => "",
		"form_id" => false
		),
	"availability_header" => array(
		"method" => "spacer",
		"friendly_name" => "可用性选项"
		),
	"availability_method" => array(
		"friendly_name" => "主机存活状态检测",
		//"description" => "Cacti是否需要检测这个主机是否存活,无论如何Cacti都会使用SNMP检测主机是否存活.",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:availability_method|",
		"method" => "drop_array",
		"default" => read_config_option("availability_method"),
		"array" => $availability_options
		),
	"ping_method" => array(
		"friendly_name" => "检测方法",
		//"description" => "Cacti发送检测数据包的类型.",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:ping_method|",
		"method" => "drop_array",
		"default" => read_config_option("ping_method"),
		"array" => $ping_methods
		),
	"ping_port" => array(
		"method" => "textbox",
		"friendly_name" => "检测端口",
		"value" => "|arg1:ping_port|",
		//"description" => "当使用TCP或UDP检测方法时需要输入被检测的端口,请确认您服务器上开启了哪个端口可被检查.",
		"default" => read_config_option("ping_port"),
		"max_length" => "50",
		"size" => "15"
		),
	"ping_timeout" => array(
		"friendly_name" => "检测超时",
		//"description" => "由于ICMP和UDP都不会建议连接,所以需要指定ICMP和UDP检测的超时上限.",
		"method" => "textbox",
		"value" => "|arg1:ping_timeout|",
		"default" => read_config_option("ping_timeout"),
		"max_length" => "10",
		"size" => "15"
		),
	"ping_retries" => array(
		"friendly_name" => "检测重试次数",
		//"description" => "由于网络或其它原因,有时检测会失败,建立设置2次或3次连续检测,但设置太大会影响采集器性能.",
		"method" => "textbox",
		"value" => "|arg1:ping_retries|",
		"default" => read_config_option("ping_retries"),
		"max_length" => "10",
		"size" => "15"
		),
	"spacer1" => array(
		"method" => "spacer",
		"friendly_name" => "SNMP选项"
		),
	"snmp_version" => array(
		"method" => "drop_array",
		"friendly_name" => "SNMP版本",
		//"description" => "选择这个主机的SNMP版本.",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:snmp_version|",
		"default" => read_config_option("snmp_ver"),
		"array" => $snmp_versions,
		),
	"snmp_community" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP团体名称",
		//"description" => "这个主机的可读SNMP团体名称.",
		"value" => "|arg1:snmp_community|",
		"form_id" => "|arg1:id|",
		"default" => read_config_option("snmp_community"),
		"max_length" => "100",
		"size" => "15"
		),
	"snmp_username" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP用户名(v3)",
		//"description" => "这个主机的SNMP v3用户名.",
		"value" => "|arg1:snmp_username|",
		"default" => read_config_option("snmp_username"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_password" => array(
		"method" => "textbox_password",
		"friendly_name" => "SNMP密码(v3)",
		//"description" => "这个主机的SNMP v3密码.",
		"value" => "|arg1:snmp_password|",
		"default" => read_config_option("snmp_password"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_auth_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => "SNMP验证协议(v3)",
		//"description" => "这个主机的SNMP v3验证协议.",
		"value" => "|arg1:snmp_auth_protocol|",
		"default" => read_config_option("snmp_auth_protocol"),
		"array" => $snmp_auth_protocols,
		),
	"snmp_priv_passphrase" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP私有密码短语(v3)",
		//"description" => "这个主机的SNMP v3私有密码短语.",
		"value" => "|arg1:snmp_priv_passphrase|",
		"default" => read_config_option("snmp_priv_passphrase"),
		"max_length" => "200",
		"size" => "40"
		),
	"snmp_priv_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => "SNMP私有协议(v3)",
		//"description" => "这个主机的SNMP v3私有协议.",
		"value" => "|arg1:snmp_priv_protocol|",
		"default" => read_config_option("snmp_priv_protocol"),
		"array" => $snmp_priv_protocols,
		),
	"snmp_context" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP上下文",
		//"description" => "这个主机的SNMP上下文.",
		"value" => "|arg1:snmp_context|",
		"default" => "",
		"max_length" => "64",
		"size" => "25"
		),
	"snmp_port" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP端口",
		//"description" => "输入SNMP使用的UDP端口(默认是161).",
		"value" => "|arg1:snmp_port|",
		"max_length" => "5",
		"default" => read_config_option("snmp_port"),
		"size" => "15"
		),
	"snmp_timeout" => array(
		"method" => "textbox",
		"friendly_name" => "SNMP超时",
		//"description" => "Cacti等待SNMP响应的最大时间(毫秒).",
		"value" => "|arg1:snmp_timeout|",
		"max_length" => "8",
		"default" => read_config_option("snmp_timeout"),
		"size" => "15"
		),
	"max_oids" => array(
		"method" => "textbox",
		"friendly_name" => "获取请求的最大OID",
		//"description" => "指定每个SNMP获取请求的最大OID长度.",
		"value" => "|arg1:max_oids|",
		"max_length" => "8",
		"default" => read_config_option("max_get_size"),
		"size" => "15"
		),
	'alert_setting'=>array(
		'method'=>'spacer',
		'friendly_name'=>'告警选项'
	),
	'alert_email'=>array(
		'method'=>'checkbox',
		'friendly_name'=>'启用邮件通知',
		'description'=>'当这些主机宕机时是否使用邮件通知',
		'value'=>'|arg1:alert_email|',
		'default'=>'on'
	),
	'alert_sms'=>array(
		'method'=>'checkbox',
		'friendly_name'=>'启用短信通知',
		'description'=>'当这些主机宕机时是否使用短信通知',
		'value'=>'|arg1:alert_sms|',
		'default'=>'on'
	),
	"header4" => array(
		"method" => "spacer",
		"friendly_name" => "额外选项"
		),
	"notes" => array(
		"method" => "textarea",
		"friendly_name" => "说明",
		//"description" => "输入这个主机的说明.",
		"class" => "textAreaNotes",
		"value" => "|arg1:notes|",
		"textarea_rows" => "5",
		"textarea_cols" => "50"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_host_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:host_template_id|"
		),
	"save_component_host" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: host_templates.php, action: edit */
$fields_host_template_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "输入这个主机模板的名称.",
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: rra.php, action: edit */
$fields_rra_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "数据应该如何写入循环归档.",
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"consolidation_function_id" => array(
		"method" => "drop_multi",
		"friendly_name" => "合并函数",
		//"description" => "数据应该如何写入循环归档.",
		"array" => $consolidation_functions,
		"sql" => "select consolidation_function_id as id,rra_id from rra_cf where rra_id=|arg1:id|",
		),
	"x_files_factor" => array(
		"method" => "textbox",
		"friendly_name" => "未知文件比例",
		//"description" => "多少未知数据可作为已知数据.",
		"value" => "|arg1:x_files_factor|",
		"max_length" => "10",
		),
	"steps" => array(
		"method" => "textbox",
		"friendly_name" => "步进",
		//"description" => "多少数据点需要当成一个数据写入循环归档.",
		"value" => "|arg1:steps|",
		"max_length" => "8",
		),
	"rows" => array(
		"method" => "textbox",
		"friendly_name" => "行",
		//"description" => "多少行数据保持在循环归档内.",
		"value" => "|arg1:rows|",
		"max_length" => "12",
		),
	"timespan" => array(
		"method" => "textbox",
		"friendly_name" => "时间段",
		//"description" => "这个循环归档显示多少秒数据在图形上.",
		"value" => "|arg1:timespan|",
		"max_length" => "12",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_rra" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_queries.php, action: edit */
$fields_data_query_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "这个数据查询的名称.",
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => "描述",
		//"description" => "这个数据查询的描述.",
		"value" => "|arg1:description|",
		"size" => "80",
		"max_length" => "255",
		),
	"xml_path" => array(
		"method" => "textbox",
		"friendly_name" => "XML路径",
		//"description" => "这个数据查询的XML文件的完整路径.",
		"value" => "|arg1:xml_path|",
		"default" => "<path_cacti>/resource/",
		"size" => "80",
		"max_length" => "255",
		),
	"data_input_id" => array(
		"method" => "drop_sql",
		"friendly_name" => "数据输入方法",
		//"description" => "根据主机类型选择数据采集的方法.",
		"value" => "|arg1:data_input_id|",
		"sql" => "select id,name from data_input where (type_id=3 or type_id=4 or type_id=5 or type_id=6) order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|",
		),
	"save_component_snmp_query" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_queries.php, action: item_edit */
$fields_data_query_item_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "这个已关联的图形名称.",
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"graph_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => "图形模板",
		//"description" => "根据主机类型选择数据采集的方法.",
		"value" => "|arg1:graph_template_id|",
		"sql" => "select id,name from graph_templates order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"snmp_query_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:snmp_query_id|"
		),
	"_graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:graph_template_id|"
		),
	"save_component_snmp_query_item" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: tree.php, action: edit */
$fields_tree_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "名称",
		//"description" => "这个图形树的名称.",
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"sort_type" => array(
		"method" => "drop_array",
		"friendly_name" => "排序方法",
		//"description" => "选择这个树里的子对象的排序方法.",
		"value" => "|arg1:sort_type|",
		"array" => $tree_sort_types,
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_tree" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: user_admin.php, action: user_edit (host) */
$fields_user_user_edit_host = array(
	"username" => array(
		"method" => "textbox",
		"friendly_name" => "用户名",
		//"description" => "这个用户的登录名,请使用英文或数字.",
		"value" => "|arg1:username|",
		"max_length" => "255"
		),
	"full_name" => array(
		"method" => "textbox",
		"friendly_name" => "全名",
		//"description" => "这个用户的描述名称,可以使用空格或特殊字符.",
		"value" => "|arg1:full_name|",
		"max_length" => "255"
		),
	"password" => array(
		"method" => "textbox_password",
		"friendly_name" => "密码",
		//"description" => "输入两次密码,请注意密码是区分大小写的!",
		"value" => "",
		"max_length" => "255"
		),
	"enabled" => array(
		"method" => "checkbox",
		"friendly_name" => "启用",
		//"description" => "是否启用该用户.",
		"value" => "|arg1:enabled|",
		"default" => ""
		),
	"grp1" => array(
		"friendly_name" => "账户选项",
		"method" => "checkbox_group",
		//"description" => "请在此指定账户相关设置.",
		"items" => array(
			"must_change_password" => array(
				"value" => "|arg1:must_change_password|",
				"friendly_name" => "用户下次登录时须更改密码",
				"form_id" => "|arg1:id|",
				"default" => ""
				),
			"graph_settings" => array(
				"value" => "|arg1:graph_settings|",
				"friendly_name" => "允许这个用户保持自定义图形设置,请注意用户有可能弄乱显示设置",
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"grp2" => array(
		"friendly_name" => "图形选项",
		"method" => "checkbox_group",
		//"description" => "请在此指定图形相关选项.",
		"items" => array(
			"show_tree" => array(
				"value" => "|arg1:show_tree|",
				"friendly_name" => "用户有权限以树状查看",
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_list" => array(
				"value" => "|arg1:show_list|",
				"friendly_name" => "用户有权限以列表查看",
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_preview" => array(
				"value" => "|arg1:show_preview|",
				"friendly_name" => "用户有权限以预览查看",
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"login_opts" => array(
		"friendly_name" => "登录选项",
		"method" => "radio",
		"default" => "1",
		//"description" => "当这个用户登录时显示什么.",
		"value" => "|arg1:login_opts|",
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => "显示用户指定的页面."
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => "显示默认控制台页面."
				),
			2 => array(
				"radio_value" => "3",
				"radio_caption" => "显示默认图形页面."
				)
			)
		),
	"realm" => array(
		"method" => "drop_array",
		"friendly_name" => "验证方法",
		//"description" => "除非您有LDAP或WEB基本验证服务,否则只可使用本地验证.更改到另一种有效的验证方法会禁用当前用户.",
		"value" => "|arg1:realm|",
		"default" => 0,
		"array" => $auth_realms,
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_policy_graphs" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graphs|"
		),
	"_policy_trees" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_trees|"
		),
	"_policy_hosts" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_hosts|"
		),
	"_policy_graph_templates" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graph_templates|"
		),
	"save_component_user" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

$export_types = array(
	"graph_template" => array(
		"name" => "图形模板",
		"title_sql" => "select name from graph_templates where id=|id|",
		"dropdown_sql" => "select id,name from graph_templates order by name"
		),
	"data_template" => array(
		"name" => "数据模板",
		"title_sql" => "select name from data_template where id=|id|",
		"dropdown_sql" => "select id,name from data_template order by name"
		),
	"host_template" => array(
		"name" => "主机模板",
		"title_sql" => "select name from host_template where id=|id|",
		"dropdown_sql" => "select id,name from host_template order by name"
		),
	"data_query" => array(
		"name" => "数据查询",
		"title_sql" => "select name from snmp_query where id=|id|",
		"dropdown_sql" => "select id,name from snmp_query order by name"
		)
	);


api_plugin_hook('config_form');

