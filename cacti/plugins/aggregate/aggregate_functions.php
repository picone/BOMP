<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 Reinhard Scheck aka gandalf                          |
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
 */

/**
 * PHP error handler
 * @arg $errno
 * @arg $errmsg
 * @arg $filename
 * @arg $linenum
 * @arg $vars
 */
function aggregate_error_handler($errno, $errmsg, $filename, $linenum, $vars) {

	$errno = $errno & error_reporting();
	# return if error handling disabled by @
	if($errno == 0) return;
	# define constants not available with PHP 4
	if(!defined('E_STRICT'))            define('E_STRICT', 2048);
	if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);

	if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_HIGH) {
		/* define all error types */
		$errortype = array(
		E_ERROR             => 'Error',
		E_WARNING           => 'Warning',
		E_PARSE             => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE       => 'User Notice',
		E_STRICT            => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
		);

		/* create an error string for the log */
		$err = "ERRNO:'"  . $errno   . "' TYPE:'"    . $errortype[$errno] .
			"' MESSAGE:'" . $errmsg  . "' IN FILE:'" . $filename .
			"' LINE NO:'" . $linenum . "'";

		/* let's ignore some lesser issues */
		if (substr_count($errmsg, "date_default_timezone")) return;
		if (substr_count($errmsg, "Only variables")) return;

		/* log the error to the Cacti log */
		#cacti_log("PROGERR: " . $err, false, "AGGREGATE");
		print("PROGERR: " . $err . "<br><pre>");# print_r($vars); print("</pre>");

		# backtrace, if available
		if (function_exists('debug_backtrace')) {
			//print "backtrace:\n";
			$backtrace = debug_backtrace();
			array_shift($backtrace);
			foreach($backtrace as $i=>$l) {
				print "[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
				if($l['file']) print " in <b>{$l['file']}</b>";
				if($l['line']) print " on line <b>{$l['line']}</b>";
				print "\n";
			}
		}
		if (isset($GLOBALS['error_fatal'])) {
			if($GLOBALS['error_fatal'] & $errno) die('fatal');
		}
	}
	
	return;
}

/* get_next_sequence - returns the next available sequence id
   @arg $id - (int) the current id
   @arg $field - the field name that contains the target id
   @arg $table_name - the table name that contains the target id
   @arg $group_query - an SQL "where" clause to limit the query
   @returns - (int) the next available sequence id */
function get_next_sequence($id, $field, $table_name, $group_query, $key_field="id") {
	if (empty($id)) {
		$data = db_fetch_row("select max($field)+1 as seq from $table_name where $group_query");

		if ($data["seq"] == "") {
			return 1;
		}else{
			return $data["seq"];
		}
	}else{
		$data = db_fetch_row("select $field from $table_name where $key_field = id");
		return $data[$field];
	}
}

/**
 * Convert graph_type to STACK, if appropriate
 *
 * @param unknown_type $_graph_item_type	current graph_item_type
 * @param unknown_type $_graph_no			No of graph, the first one MUST NOT be changed
 */
function aggregate_convert_to_stack($_graph_item_type, $_graph_no) {
	# globals used
	global $graph_item_types;

	#cacti_log("function: aggregate_convert_to_stack called. Graph#:" . $_graph_no . " Graph Item Type:" . $_graph_item_type, true, "AGGREGATE");
	if (ereg("(LINE[123])", $graph_item_types{$_graph_item_type})) { 	
		/* change LINEx statements to STACK */
		$_graph_item_type = array_search('STACK', $graph_item_types);
	} elseif (ereg("(AREA)", $graph_item_types{$_graph_item_type}) && !($_graph_no === 0)) { 	
		/* change AREA statements, but not for the first graph */
		$_graph_item_type = array_search('STACK', $graph_item_types);
	}
	
	if (ereg("(STACK)", $graph_item_types{$_graph_item_type}) && ($_graph_no === 0)) { 	
		/* change STACK statements for the first graph only, we need at least one AREA! */
		/* yes, if you WANT one AREA and a STACK even for the first graph               */
		/* this will yield wrong results                                                */
		$_graph_item_type = array_search('AREA', $graph_item_types);
	}

	return $_graph_item_type;
	
}

/**
 * Convert graph_type of to LINE1
 *
 * @param unknown_type $_graph_item_type	current graph_item_type
 * @return unknown
 */
function aggregate_convert_to_line($_graph_item_type) { 
	# globals used
	global $graph_item_types;
	
	#if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_LOW) {
	#	cacti_log("function: aggregate_convert_to_line called.", true, "AGGREGATE");
	#}

	if (ereg("(AREA|STACK)", $graph_item_types{$_graph_item_type})) { 
		/* change AREA|STACK statements only */ 		
		return array_search('LINE1', $graph_item_types);		
	} else {
		return $_graph_item_type;
	}
}

/**
 * draw graph item list
 *
 * @param unknown_type $_graph_id	id of the graph for which the items shall be listed
 */
function draw_aggregate_graph_items_list($_graph_id) {
	global $colors, $config;

	if (file_exists($config["include_path"] . "/global_arrays.php")) {
		include($config["include_path"] . "/global_arrays.php");
	} else {
		include($config["include_path"] . "/config_arrays.php");
	}
	
	#if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_LOW) {
	#	cacti_log("function: draw_aggregate_graph_items_list called. Id:" . $_graph_id, true, "AGGREGATE");
	#}
	
	# fetch graph items
	$item_list = db_fetch_assoc("SELECT " .
			"graph_templates_item.id, " .
			"graph_templates_item.text_format, " .
			"graph_templates_item.value, " .
			"graph_templates_item.hard_return, " .
			"graph_templates_item.graph_type_id, " .
			"graph_templates_item.consolidation_function_id, " .
			"cdef.name as cdef_name, " .
			"colors.hex " .
		"FROM graph_templates_item " .
		"LEFT JOIN cdef ON (cdef_id=cdef.id) " .
		"LEFT JOIN colors ON (color_id=colors.id) " .
		"WHERE graph_templates_item.local_graph_id=$_graph_id " .
		"ORDER BY graph_templates_item.sequence");

	# fetch color templates
	$color_templates = db_fetch_assoc("SELECT " .
		"color_template_id, " .
		"name " .
		"FROM plugin_aggregate_color_templates " .
		"ORDER BY name");
	
	# draw list of graph items
	html_start_box("<strong>图形模板对象</strong>", "98%", $colors["header"], "3", "center", "");
	
	# print column header
	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("图形对象",$colors["header_text"],1);
		DrawMatrixHeaderItem("数据源",$colors["header_text"],1);
		DrawMatrixHeaderItem("图形对象类型",$colors["header_text"],1);
		DrawMatrixHeaderItem("CF 类型",$colors["header_text"],1);
		DrawMatrixHeaderItem("对象颜色",$colors["header_text"],2);
		DrawMatrixHeaderItem("颜色模板",$colors["header_text"],1);
		DrawMatrixHeaderItem("跳过",$colors["header_text"],1);
	DrawMatrixHeaderItem("总共",$colors["header_text"],1);
	print "</tr>";

	$group_counter = 0; $_graph_type_name = ""; $i = 0;
	$alternate_color_1 = $colors["alternate"]; $alternate_color_2 = $colors["alternate"];

	if (sizeof($item_list) > 0) {
		form_hidden_box("item_no", sizeof($item_list), sizeof($item_list));
	foreach ($item_list as $item) {
		/* graph grouping display logic */
		$this_row_style = ""; $use_custom_row_color = false; $hard_return = "";

		if ($graph_item_types{$item["graph_type_id"]} != "GPRINT") {
			$this_row_style = "font-weight: bold;"; $use_custom_row_color = true;

			if ($group_counter % 2 == 0) {
				$alternate_color_1 = "EEEEEE";
				$alternate_color_2 = "EEEEEE";
				$custom_row_color = "D5D5D5";
			}else{
				$alternate_color_1 = $colors["alternate"];
				$alternate_color_2 = $colors["alternate"];
				$custom_row_color = "D2D6E7";
			}

			$group_counter++;
		}

		$_graph_type_name = $graph_item_types{$item["graph_type_id"]};

		/* alternating row color */
		if ($use_custom_row_color == false) {
			form_alternate_row_color($alternate_color_1,$alternate_color_2,$i);
		}else{
			print "<tr bgcolor='#$custom_row_color'>";
		}

		print "<td>";
		print "<strong>对象 # " . ($i+1) . "</strong>";
		print "</td>\n";

		#if (empty($item["data_source_name"])) { $item["data_source_name"] = "No Task"; }

		switch (true) {
		case ereg("(AREA|STACK|GPRINT|LINE[123])", $_graph_type_name):
			$matrix_title = $item["text_format"];
			break;
		case ereg("(HRULE|VRULE)", $_graph_type_name):
			$matrix_title = "HRULE: " . $item["value"];
			break;
		case ereg("(COMMENT)", $_graph_type_name):
			$matrix_title = "COMMENT: " . $item["text_format"];
			break;
		}

		if ($item["hard_return"] == "on") {
			$hard_return = "<strong><font color=\"#FF0000\">&lt;HR&gt;</font></strong>";
		}

		# agg_# will denote the graph item to be skipped. Index starts at 1
		print "<td style='$this_row_style'>" . htmlspecialchars($matrix_title) . $hard_return . "</td>\n";
		print "<td style='$this_row_style'>" . $graph_item_types{$item["graph_type_id"]} . "</td>\n";
		print "<td style='$this_row_style'>" . $consolidation_functions{$item["consolidation_function_id"]} . "</td>\n";
		print "<td" . ((!empty($item["hex"])) ? " bgcolor='#" . $item["hex"] . "'" : "") . " width='1%'>&nbsp;</td>\n";
		print "<td style='$this_row_style'>" . $item["hex"] . "</td>\n";
		# dropdown for color templates
		print "<td>";
		if (!empty($item["hex"])) {
			print "<select name='agg_color_" . ($i+1) ."'>";
			print "<option value='0' selected>无</option>\n";
			html_create_list($color_templates, "name", "color_template_id", "None");
			print "</select>\n";
		}
		print "</td>";
			  # select boxes for skipping unwanted items
		print "<td style='" . get_checkbox_style() ."' width='1%' align='center'>" .
			  "<input type='checkbox' style='margin: 0px;' name='agg_skip_" . ($i+1) . "' title=" . $item["text_format"] .">" .
			  "</td>";
			# select boxes for totalling items
			print "<td style='" . get_checkbox_style() ."' width='1%' align='center'>" .
			  "<input type='checkbox' style='margin: 0px;' name='agg_total_" . ($i+1) . "' title=" . $item["text_format"] .">" .
			  "</td>";
	
		print "</tr>";

		$i++;
	}
	}else{
		print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td colspan='7'><em>无对象</em></td></tr>";
	}

	html_end_box();
	
}

/**
 * duplicate color template
 *
 * @param unknown_type $_color_template_id		id of the base color template
 * @param unknown_type $color_template_title	title of the duplicated color template
 *  *  */
function duplicate_color_template($_color_template_id, $color_template_title) {

	/* fetch data from table plugin_aggregate_color_templates */
	$color_template = db_fetch_row("select * 
									from plugin_aggregate_color_templates
									where color_template_id=$_color_template_id");
	/* fetch data from table plugin_aggregate_color_template_items */
	$color_template_items = db_fetch_assoc("select *
									from plugin_aggregate_color_template_items
									where color_template_id=$_color_template_id");

	/* create new entry: plugin_aggregate_color_templates */
	$save["color_template_id"] = 0;
	/* substitute the title variable */
	$save["name"] = str_replace("<template_title>", $color_template["name"], $color_template_title);
	#cacti_log("function: duplicate_color_template called. Id:" . $_color_template_id . " Title: " . $color_template_title . " Replaced: " . $save["name"], true, "AGGREGATE");
	$new_color_template_id = sql_save($save, "plugin_aggregate_color_templates", "color_template_id");
	unset($save);
	
	/* create new entry(s): plugin_aggregate_color_template_items */
	if (sizeof($color_template_items) > 0) {
		foreach ($color_template_items as $color_template_item) {
			$save["color_template_item_id"] = 0;
			$save["color_template_id"] = $new_color_template_id;
			$save["color_id"] = $color_template_item["color_id"];
			$save["sequence"] = $color_template_item["sequence"];
			#cacti_log("function: duplicate_color_template called. Id:" . $new_color_template_id . " Color: " . $save["color_id"] . " sequence: " . $save["sequence"], true, "AGGREGATE");
			$new_color_template_item_id = sql_save($save, "plugin_aggregate_color_template_items", "color_template_item_id");
			unset($save);
		}
	}
}

/* auto_hr:		set a new hr when items are skipped
 * @arg $s		array of skipped items
 * @arg $h		array of items with HR
 * returns		array with new HR markers
 */
function auto_hr($s, $h) {
	# start at end of array, both arrays are from 1 .. count(array)
	$i = count($h);
	# make sure, that last item always has a HR, even if template does not have any
	$h[$i] = true;
	do {
		# if skipped item has a HR
		if (isset($s[$i]) && ($s[$i] > 0) && $h[$i]) {
			# set previous item (if any) to HR
			if (isset($h[$i-1])) $h[$i-1] = $h[$i];
		}
	} while($i-- > 0);
	return $h;
}

/*auto_title:				generate a title suggested to the user
 * @arg $_local_graph_id	the id of the graph stanza
 * returns					the title
 */
function auto_title($_local_graph_id) {
	# apply given graph title, but drop host and query variables
	$graph_title = "聚合图形 ";
	$graph_title .= db_fetch_cell("select title from graph_templates_graph where local_graph_id=$_local_graph_id");
	#cacti_log("title:" . $graph_title, true, "AGGREGATE");
	# remove all "- |query_*|" occurences
	$pattern = "/-?\s+\|query_\w+\|/";
	$graph_title = preg_replace($pattern, "", $graph_title);
	#cacti_log("title:" . $graph_title, true, "AGGREGATE");
	# remove all "- |host_*|" occurences
	$pattern = "/-?\s+\|host_\w+\|/";
	$graph_title = preg_replace($pattern, "", $graph_title);
	#cacti_log("title:" . $graph_title, true, "AGGREGATE");
	return $graph_title;
}
?>
