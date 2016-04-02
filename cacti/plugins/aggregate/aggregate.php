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
 * create a new graph based on an existing one
 * take all basic graph definitions from this one, but omit graph items
 * wipe out host_id and graph_template_id
 * @arg $_local_graph_id		id of an already existing graph
 * @arg $_graph_title			title for new graph
 * return						id of the new graph
 *  */
function aggregate_graphs_create_graph($_local_graph_id, $_graph_title) {
	global $config, $struct_graph, $struct_graph_item;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	#if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
	#	cacti_log("function: aggregate_graphs_create_graph from: " . $_local_graph_id, true, "AGGREGATE");
	#}

	if (!empty($_local_graph_id)) {
		$local_graph = db_fetch_row("select * from graph_local where id=$_local_graph_id");
		$graph_template_graph = db_fetch_row("select * from graph_templates_graph where local_graph_id=$_local_graph_id");

		/* create new entry: graph_local */
		$local_graph["id"] 					= 0;
		$local_graph["graph_template_id"] 	= 0; 	# no templating
		$local_graph["host_id"] 			= 0;  	# no host to be referred to
		$local_graph["snmp_query_id"] 		= 0;	# no templating
		$local_graph["snmp_index"] 			= 0;	# no templating
		$local_graph_id 					= sql_save($local_graph, "graph_local");

		/* create new entry: graph_templates_graph */
		$graph_template_graph["id"] 							= 0;
		$graph_template_graph["local_graph_id"] 				= (isset($local_graph_id) ? $local_graph_id : 0);
		$graph_template_graph["local_graph_template_graph_id"] 	= 0; 	# no templating
		$graph_template_graph["graph_template_id"] 				= 0; 	# no templating
		$graph_template_graph["title"] 							= $_graph_title;
		$graph_templates_graph_id 			= sql_save($graph_template_graph, "graph_templates_graph");

		# update title cache
		if (!empty($_local_graph_id)) {
			update_graph_title_cache($local_graph_id);
		}
	}

	/* restore original error handler */
	restore_error_handler();

	# return the id of the newly inserted graph
	return $local_graph_id;

}

/**
 * inserts all graph items of an existing graph
 * @arg $_new_graph_id				id of the new graph
 * @arg $_old_graph_id				id of the old graph
 * @arg $_skip						graph items to be skipped, array starts at 1
 * @arg $_hr						graph items that should have a <HR>
 * @arg $_graph_item_sequence					sequence number of the next graph item to be inserted
 * @arg $_selected_graph_index		index of current graph to be inserted
 * @arg $_color_templates			the color templates to be used
 * @arg $_type_action				conversion to AREA/STACK or LINE required?
 * return							id of the next graph item to be inserted
 *  */
function aggregate_graphs_insert_graph_items($_new_graph_id, $_old_graph_id, $_skip, $_graph_item_sequence, $_selected_graph_index, $_color_templates, $_type_action, $_gprint_prefix) {
	global $struct_graph_item, $config;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	#cacti_log("function: aggregate_graphs_insert_graph_items called. Insert " . $_old_graph_id . " into " . $_new_graph_id . " sequence: " . $_graph_item_sequence  . " Graph_No: " . $_selected_graph_index  . " Type Action: " . $_type_action, true, "AGGREGATE");
	#cacti_log("skipping: " . print_r($_skip, TRUE), true, "AGGREGATE");

	# take graph item data from old one
	if (!empty($_old_graph_id)) {
		$graph_template_items = db_fetch_assoc("select * from graph_templates_item where local_graph_id=$_old_graph_id ORDER BY SEQUENCE");
	}

	/* create new entry(s): graph_templates_item */
	$num_items = sizeof($graph_template_items);
	if ($num_items > 0) {

		# take care of items having a HR that shall be skipped
		$i = 0;
		for ($i; $i < $num_items; $i++) {
			# remember existing hard returns (array must start at 1 to match $skipped_items
			$_hr[$i+1] = ($graph_template_items[$i]["hard_return"] != "");
		}
		# move "skipped hard returns" to previous graph item
		$_hr = auto_hr($_skip, $_hr);

		# next entry will have to have a prepended text format
		$prepend = true;
		$i = 0;
		foreach ($graph_template_items as $graph_template_item) {
			# loop starts at 0, but $_skip starts at 1, so increment before comparing
			$i++;
			#cacti_log("function: sequence: " . $_graph_item_sequence . "/" . $graph_template_item["sequence"] . " loop: " . $i . "/" . $_skip[$i], true, "AGGREGATE");
			# go ahead, if this graph item is to be skipped
			if (isset($_skip[$i])) {
				continue;
			}

			$save = array();
			reset($struct_graph_item);

			$save["id"] 							= 0;
			$save["local_graph_id"] 				= (isset($_new_graph_id) ? $_new_graph_id : 0);
			$save["graph_template_id"] 				= 0;
			$save["local_graph_template_item_id"]	= (isset($graph_template_item["local_graph_template_item_id"]) ? $graph_template_item["local_graph_template_item_id"] : 0);

			while (list($field, $array) = each($struct_graph_item)) {
				$save{$field} = $graph_template_item{$field};
				#cacti_log("function: aggregate_graphs_insert_graph_items field:" . $field . " value: " . $save{$field}, true, "AGGREGATE");
			}

			# now it's time for some "special purpose" processing
			# selected fields will need special treatment

			# take care of color changes only if not set to None
			#cacti_log("function: aggregate_graphs_insert_graph_items i:" . $i . " value: " . $_color_templates[$i] . " Graph_No:" . $_selected_graph_index . " Current Color:" . $save["color_id"] . " Templating: " . $_color_templates[$i], true, "AGGREGATE");
			if (isset($_color_templates[$i])) {
				if ($_color_templates[$i] > 0) {
					# templating required, get color for current graph item
					$sql = "SELECT color_id " .
							"FROM plugin_aggregate_color_template_items " .
							"WHERE color_template_id = " . $_color_templates[$i] .
							" LIMIT " . $_selected_graph_index . ",1";
					$save["color_id"] = db_fetch_cell($sql);
				} else {
					/* set a color even if no color templating is required */
					$save["color_id"] = $graph_template_item["color_id"];
				}
			} /* else: no color templating defined, e.g. GPRINT entry */
			#cacti_log("function: aggregate_graphs_insert_graph_items color:" . $save["color_id"] . " Graph_No:" . $_selected_graph_index, true, "AGGREGATE");

			# take care of the graph_item_type
			switch ($_type_action) {
				case AGGREGATE_GRAPH_TYPE_KEEP: /* keep entry as defined by the Graph */
					break;
				case AGGREGATE_GRAPH_TYPE_STACK: /* Change graph type to create an AREA/STACK graph */
					$save["graph_type_id"] = aggregate_convert_to_stack($save["graph_type_id"], $_selected_graph_index);
					break;
				case AGGREGATE_GRAPH_TYPE_LINE: /* Change graph type to create a LINEx graph */
					$save["graph_type_id"] = aggregate_convert_to_line($save["graph_type_id"]);
					break;
			}


			# new item title required?
			#cacti_log("gprint format:" . $_gprint_prefix, true, "AGGREGATE");
			if ($prepend && ($_gprint_prefix === "ALL ITEMS")) {  # bad, bad, magic string
				# pointless to add any item name here
				$save["text_format"] = $_gprint_prefix;
			} elseif ($prepend && (strlen($save["text_format"]) > 0) && (strlen($_gprint_prefix) > 0)) {
				# prepend text format for this item?
				# get host id for current graph item
				$sql = "SELECT host.id " .
					"FROM graph_templates_item " .
					"LEFT JOIN graph_local ON (graph_templates_item.local_graph_id = graph_local.id) " .
					"LEFT JOIN host ON (graph_local.host_id = host.id) " .
					"WHERE graph_templates_item.id = " . $graph_template_item["id"];
				$host_id = db_fetch_cell($sql);
				$_substituted_text = substitute_host_data($_gprint_prefix . " " . $save["text_format"], "|", "|", $host_id);
				#cacti_log("substituted:" . $_substituted_text, true, "AGGREGATE");
				$save["text_format"] = substitute_host_data($_gprint_prefix . " " . $save["text_format"], "|", "|", $host_id);
				# no more prepending until next line break is encountered
				$prepend = false;
			}

			# <HR> wanted?
			if (isset($_hr[$i]) && $_hr[$i] > 0) {
				$save["hard_return"] = "on";
			}

			# provide new sequence number
			$save["sequence"] = $_graph_item_sequence;
			#cacti_log("function: aggregate_graphs_insert_graph_items hard return: " . $save["hard_return"] . " sequence: " . $_graph_item_sequence, true, "AGGREGATE");

			# if this item defines a line break, remember to prepend next line
			$prepend = ($save["hard_return"] == "on");
			//print "<pre>";print_r($save);print "</pre>";

			$graph_item_mappings{$graph_template_item["id"]} = sql_save($save, "graph_templates_item");

			$_graph_item_sequence++;
		}
	}

	/* restore original error handler */
	restore_error_handler();

	# return with next sequence number to be filled
	return $_graph_item_sequence;
}

/**
 * cleanup of graph items of the new graph
 * return
 *  */
function aggregate_graphs_cleanup() {
	global $config, $struct_graph, $struct_graph_item;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	#if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
	#	cacti_log("function: aggregate_graphs_cleanup called. Working on Graph: " . $_new_graph_id . " totalling: " . $_total_action, true, "AGGREGATE");
	#}

	/* restore original error handler */
	restore_error_handler();
}

function aggregate_totalling_cdef($_new_graph_id, $_graph_item_sequence, $_total_action) {
	global $config;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");
	include_once($config['base_path'] . "/lib/cdef.php");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	#cacti_log("function: aggregate_totalling_cdef called. Working on Graph: " . $_new_graph_id . " sequence: " .  $_graph_item_sequence  . " totalling: " . $_total_action, true, "AGGREGATE");

	# take graph item data for the totalling items
	if (!empty($_new_graph_id)) {
		$sql = "SELECT id, cdef_id " .
			"FROM graph_templates_item " .
			"WHERE local_graph_id=$_new_graph_id " .
			"AND sequence >= $_graph_item_sequence " .
			"ORDER BY sequence";
		#cacti_log("sql: " . $sql, true, "AGGREGATE");
		$graph_template_items = db_fetch_assoc($sql);
	}

	# now get the list of cdefs
	$sql = "SELECT id, name " .
				"FROM cdef " .
				"ORDER BY id";
	#cacti_log("sql: " . $sql, true, "AGGREGATE");
	$_cdefs = db_fetch_assoc($sql); # index the cdefs by their id's
	$cdefs = array();
	# build cdefs array to allow for indexing on cdef_id
	foreach ($_cdefs as $_cdef) {
		$cdefs[$_cdef["id"]]["id"] = $_cdef["id"];
		$cdefs[$_cdef["id"]]["name"] = $_cdef["name"];
		$cdefs[$_cdef["id"]]["cdef_text"] = get_cdef($_cdef["id"]);
	}
	# add pseudo CDEF for CURRENT_DATA_SOURCE, in case CDEF=NONE
	# we then may apply the standard CDEF procedure to create a new CDEF
	$cdefs[0]["id"] = 0;
	$cdefs[0]["name"] = "Items";
	$cdefs[0]["cdef_text"] = "CURRENT_DATA_SOURCE";

	/* new CDEF(s) are required! */
	$num_items = sizeof($graph_template_items);
	if ($num_items > 0) {
		$i = 0;
		foreach ($graph_template_items as $graph_template_item) {
			# current cdef
			$cdef_id = $graph_template_item["cdef_id"];
			$cdef_name = $cdefs[$cdef_id]["name"];
			$cdef_text = $cdefs[$cdef_id]["cdef_text"];
			#cacti_log("cdef id: " . $cdef_id . " name: " . $cdef_name . " value: " . $cdef_text, true, "AGGREGATE");
			# new cdef
			$new_cdef_text = "INVALID";	# in case sth goes wrong
			switch ($_total_action) {
				case AGGREGATE_TOTAL_SIMILAR:
					$new_cdef_text = str_replace("CURRENT_DATA_SOURCE", "SIMILAR_DATA_SOURCES_NODUPS", $cdef_text);
					break;
				case AGGREGATE_TOTAL_ALL:
					$new_cdef_text = str_replace("CURRENT_DATA_SOURCE", "ALL_DATA_SOURCES_NODUPS", $cdef_text);
					break;
			}
			# is the new cdef already present?
			$new_cdef_id = "";
			reset($cdefs);
			foreach ($cdefs as $cdef) {
				#cacti_log("verify matching cdef: " . $cdef["id"] . " on: " . $cdef["cdef_text"], true, "AGGREGATE");
				if ($cdef["cdef_text"] == $new_cdef_text) {
					$new_cdef_id = $cdef["id"];
					#cacti_log("matching cdef: " . $new_cdef_id, true, "AGGREGATE");
					# leave on first match
					break;
				}
			}
			# in case, we have NO match
			if (empty($new_cdef_id)) {
				# create a new cdef entry
				$save["id"] = 0;
				$save["hash"] = get_hash_cdef(0);
				$new_cdef_name = "INVALID " . $cdef_name; # in case anything goes wrong
				switch ($_total_action) {
					case AGGREGATE_TOTAL_SIMILAR:
						$new_cdef_name = "_AGGREGATE SIMILAR " . $cdef_name;
						break;
					case AGGREGATE_TOTAL_ALL:
						$new_cdef_name = "_AGGREGATE ALL " . $cdef_name;
						break;
				}
				$save["name"] = $new_cdef_name;
				# save the cdef itself
				$new_cdef_id = sql_save($save, "cdef");
				#cacti_log("created new cdef: " . $new_cdef_id . " name: " . $new_cdef_name . " value: " . $new_cdef_text, true, "AGGREGATE");
				unset($save);

				# create a new cdef item entry
				$save["id"] = 0;
				$save["hash"] = get_hash_cdef(0, "cdef_item");
				$save["cdef_id"] = $new_cdef_id;
				$save["sequence"] = 1;
				$save["type"] = 6;		# this will be replaced by a define as soon as it exists for a pure text field
				$save["value"] = $new_cdef_text;
				# save the cdef item, there's only one!
				$cdef_item_id = sql_save($save, "cdef_items");
				#cacti_log("created new cdef item: " . $cdef_item_id, true, "AGGREGATE");
				# now extend the cdef array to learn the newly entered cdef for the next loop
				$cdefs[$new_cdef_id]["id"] = $new_cdef_id;
				$cdefs[$new_cdef_id]["name"] = $new_cdef_name;
				$cdefs[$new_cdef_id]["cdef_text"] = $new_cdef_text;
			}
			# now that we have a new cdef id, update record accordingly
			$sql = "UPDATE graph_templates_item SET cdef_id=$new_cdef_id WHERE id=" . $graph_template_item["id"];
			#cacti_log("sql: " . $sql, true, "AGGREGATE");
			$ok = db_execute($sql);
			#cacti_log("updated new cdef id: " . $new_cdef_id . " for item: " . $graph_template_item["id"], true, "AGGREGATE");
		}
	}

	/* restore original error handler */
	restore_error_handler();
}

/**
 * perform aggregate_graph execute action
 * @arg $action				action to be performed
 * return				-
 *  */
function aggregate_graphs_action_execute($action) {
	global $config;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");
	#cacti_log("function: aggregate_graphs_action_execute called", true, "AGGREGATE");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	/* loop through each of the graph_items selected on the previous page for skipped items */
	$skipped_items   = array();
	$hr_items        = array();
	$color_templates = array();
	while (list($var,$val) = each($_POST)) {
		# work on color_templates
		if (ereg("^agg_color_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			# create an array element with index of graph item to be skipped
			# index starts at 1
			$color_templates[$matches[1]] = $val;
		}
		# work on checkboxed for adding <HR> to a graph item
		if (ereg("^agg_hr_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			# create an array element with index of graph item to be skipped
			# index starts at 1
			$hr_items[$matches[1]] = $matches[1];
		}
		# work on checkboxed for skipping items
		if (ereg("^agg_skip_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			# create an array element with index of graph item to be skipped
			# index starts at 1
			$skipped_items[$matches[1]] = $matches[1];
		}
		# work on checkboxed for totalling items
		if (ereg("^agg_total_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			# create an array element with index of graph item to be totalled
			# index starts at 1
			$total_items[$matches[1]] = $matches[1];
		}

	}
	#cacti_log("function: aggregate_graphs_action_execute called. CT:" . print_r($color_templates, TRUE), true, "AGGREGATE");

	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($action == "plugin_aggregate") { /* aggregate */
			# create new graph based on first graph selected
			/* ================= input validation ================= */
			input_validate_input_number($selected_items[0]);
			$graph_title 	= form_input_validate($_POST["title_format"], "title_format", "^([-a-zA-Z0-9,_| ]+)$", true, 3);
			$gprint_prefix 	= form_input_validate($_POST["gprint_prefix"], "gprint_prefix", "^([-a-zA-Z0-9,_| ]+)$", true, 3);
			$_graph_type 	= form_input_validate($_POST["aggregate_graph_type"], "aggregate_graph_type", "", true, 3);
			$_total 		= form_input_validate($_POST["aggregate_total"], "aggregate_total", "", true, 3);
			$item_no 		= form_input_validate($_POST["item_no"], "item_no", "", true, 3);
			/* ==================================================== */
			#cacti_log("function: aggregate_graphs_action_execute called. gprint_prefix: " . $gprint_prefix, true, "AGGREGATE");
			# leave on error
			if (is_error_message()) {
				/* restore original error handler */
				restore_error_handler();
				return;
			}

			$new_graph_id 	= aggregate_graphs_create_graph($selected_items[0], $graph_title);

			# sequence number of next graph item to be added, index starts at 1
			$next_item_sequence = 1;

			# loop for all selected graphs
			for ($selected_graph_index=0;($selected_graph_index<count($selected_items));$selected_graph_index++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$selected_graph_index]);
				/* ==================================================== */
				# insert all graph items of selected graph
				# next items to be inserted have to be in sequence
				#cacti_log("function: aggregate_graphs_action_execute called. New: " . $new_graph_id . " Selected: " . $selected_items[$selected_graph_index] . " Next: " . $next_item_sequence . " Graph: " . $selected_graph_index . " Graph_Type: " . $_graph_type, true, "AGGREGATE");
				$next_item_sequence = aggregate_graphs_insert_graph_items($new_graph_id,
				$selected_items[$selected_graph_index],
				$skipped_items,
				$next_item_sequence,
				$selected_graph_index,
				$color_templates,
				$_graph_type,
				$gprint_prefix);
			}

			if ($_total != AGGREGATE_TOTAL_NONE) {
				# the totalling line is always a LINE, even if STACK was required
				$_graph_type = AGGREGATE_GRAPH_TYPE_LINE;
				# make the GPRINT read TOTAL
				switch ($_total) {
					case AGGREGATE_TOTAL_SIMILAR:
						$gprint_prefix = "TOTAL";
						break;
					case AGGREGATE_TOTAL_ALL:
						$gprint_prefix = "ALL ITEMS";
						break;
				}
				# now skip all items, that are
				#   explicitely marked as skipped (based on $skipped_items)
				#   OR NOT marked as "totalling" items
				for ($i=1; $i<=$item_no; $i++) {
					#cacti_log("old skip: " . $skipped_items[$i], true, "AGGREGATE");
					# skip all items, that shall not be totalled
					if (!isset($total_items[$i])) $skipped_items[$i] = $i;
					#cacti_log("new skip: " . $skipped_items[$i], true, "AGGREGATE");
				}
				# add the "templating" graph to the new graph, honoring skipped, hr and color
				$foo = aggregate_graphs_insert_graph_items($new_graph_id,
				$selected_items[0],
				$skipped_items,
				$next_item_sequence,
				++$selected_graph_index,
				$color_templates,
				$_graph_type,
				$gprint_prefix);

				# now pay attention to CDEFs
				# next_item_sequence still points to the first totalling graph item
				aggregate_totalling_cdef($new_graph_id,
				$next_item_sequence,
				$_total);
			}

		}
	}

	/* restore original error handler */
	restore_error_handler();
}

/**
 *
 * perform aggregate_graph 				prepare action
 * @arg $save			Array:
 *						drp_action:		selected action from dropdown
 *						graph_array:	graphs titles selected from graph management's list
 *						graph_list:		graphs selected from graph management's list
 * return				-
 *  */
function aggregate_graphs_action_prepare($save) {
	# globals used
	global $colors, $config, $struct_aggregate, $help_file;
	include_once($config['base_path'] . "/plugins/aggregate/aggregate_functions.php");

	/* suppress warnings */
	error_reporting(0);

	/* install own error handler */
	set_error_handler("aggregate_error_handler");

	#if (read_config_option("log_verbosity", TRUE) == POLLER_VERBOSITY_DEBUG) {
	#	cacti_log("function: aggregate_graphs_action_prepare called.", true, "AGGREGATE");
	#}

	# initialize return code
	$return_code = false;
	# it's our turn
	if ($save["drp_action"] == "plugin_aggregate") { /* aggregate */
		$graphs = array();

		/* find out which (if any) data sources are being used by this graph, so we can tell the user */
		if (isset($save["graph_array"])) {
			//print "<pre>";print_r($save["graph_array"]);print "</pre>";
			# close the html_start_box, because it is too small
			print "<td align='right' class='textHeaderDark' bgcolor='#6d88ad'><a class='linkOverDark' href='$help_file' target='_blank'><strong>[点击这里下载聚合图形的使用说明(英文)]</strong></a></td>";
			html_end_box();

			# provide a new prefix for GPRINT lines
			$gprint_prefix = "|host_hostname|";

			# fetch all data sources for all selected graphs
			$data_sources = db_fetch_assoc("select
				data_template_data.local_data_id,
				data_template_data.name_cache
				from (data_template_rrd,data_template_data,graph_templates_item)
				where graph_templates_item.task_item_id=data_template_rrd.id
				and data_template_rrd.local_data_id=data_template_data.local_data_id
				and " . array_to_sql_or($save["graph_array"], "graph_templates_item.local_graph_id") . "
				and data_template_data.local_data_id > 0
				group by data_template_data.local_data_id
				order by data_template_data.name_cache");

			# open a new html_start_box ...
			html_start_box("", "98%", $colors["header_panel"], "3", "center", "");

			# verify, that only a single graph template is used, else
			# aggregate will look funny
			$sql = "SELECT DISTINCT graph_templates.name " .
				"FROM graph_local " .
				"LEFT JOIN graph_templates ON (graph_local.graph_template_id=graph_templates.id) " .
				"WHERE " . array_to_sql_or($save["graph_array"], "graph_local.id");
			$used_graph_templates = db_fetch_assoc($sql);

			if (sizeof($used_graph_templates) > 1) {
				# this is invalid! STOP
				print "<tr><td class='textArea'>" .
				"<p class='textError'>所选图形来自不同的图形模板.不可能添加聚合图形</p>";
				foreach ($used_graph_templates as $graph_template) {
					print "<li>" . $graph_template["name"] . "<br>\n";
				}
				print "<p class='textError'>请点击取消</p><p>然后重新重新选择相同图形模板的图形</p>";
				form_hidden_box("title_format", $graph_prefix . " ERROR", $graph_prefix . " ERROR");
			} else {
				/* list affected graphs */
				print "<tr>";
				print "<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>" .
				"<p>您真的希望聚合以下图形吗?</p>" .
				$save["graph_list"] . "</td>";

				/* list affected data sources */
				if (sizeof($data_sources) > 0) {
					print "<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>" .
					"<p>以下数据源被这些图形使用:</p>";
					foreach ($data_sources as $data_source) {
						print "<li>" . $data_source["name_cache"] . "<br>\n";
					}
					print "</td>";
				}
				print "</tr>";

				/* aggregate form */
				$_aggregate_defaults = array(
					"title_format" 	=> auto_title($save["graph_array"][0]),
					"gprint_prefix"	=> $gprint_prefix
				);

				draw_edit_form(array(
					"config" => array(),
					"fields" => inject_form_variables($struct_aggregate, $_aggregate_defaults)
				));

				html_end_box();

				# draw all graph items of first graph, including a html_start_box
				draw_aggregate_graph_items_list($save["graph_array"][0]);

				# again, a new html_start_box. Using the one from above would yield ugly formatted NO and YES buttons
				html_start_box("<strong>请确认</strong>", "98%", $colors["header"], "3", "center", "");

				# now everything is fine
				$return_code = true;
			}
		}
	}

	/* restore original error handler */
	restore_error_handler();

	return $return_code;
}

?>
