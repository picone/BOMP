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

include("./include/auth.php");
include_once('./lib/api_tree.php');
include_once('./lib/tree.php');
include_once('./lib/html_tree.php');

input_validate_input_number(get_request_var('tree_id'));
input_validate_input_number(get_request_var('leaf_id'));
input_validate_input_number(get_request_var_post('graph_tree_id'));
input_validate_input_number(get_request_var_post('parent_item_id'));

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_movedown':
		item_movedown();

		header("Location: tree.php?action=edit&id=" . $_GET["tree_id"]);
		break;
	case 'item_moveup':
		item_moveup();

		header("Location: tree.php?action=edit&id=" . $_GET["tree_id"]);
		break;
	case 'item_edit':
		include_once("./include/top_header.php");

		item_edit();

		include_once("./include/bottom_footer.php");
		break;
	case 'item_remove':
		item_remove();

		header("Location: tree.php?action=edit&id=" . $_GET["tree_id"]);
		break;
	case 'remove':
		tree_remove();

		header("Location: tree.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		tree_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		tree();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */
function form_save() {

	/* clear graph tree cache on save - affects current user only, other users should see changes in <5 minutes */
	if (isset($_SESSION['dhtml_tree'])) {
		unset($_SESSION['dhtml_tree']);
	}

	if (isset($_POST["save_component_tree"])) {
		$save["id"] = $_POST["id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["sort_type"] = form_input_validate($_POST["sort_type"], "sort_type", "", true, 3);

		if (!is_error_message()) {
			$tree_id = sql_save($save, "graph_tree");

			if ($tree_id) {
				raise_message(1);

				/* sort the tree using the algorithm chosen by the user */
				sort_tree(SORT_TYPE_TREE, $tree_id, $_POST["sort_type"]);
			}else{
				raise_message(2);
			}
		}

		header("Location: tree.php?action=edit&id=" . (empty($tree_id) ? $_POST["id"] : $tree_id));
	}elseif (isset($_POST["save_component_tree_item"])) {
		$tree_item_id = api_tree_item_save($_POST["id"], $_POST["graph_tree_id"], $_POST["type"], $_POST["parent_item_id"],
			(isset($_POST["title"]) ? $_POST["title"] : ""), (isset($_POST["local_graph_id"]) ? $_POST["local_graph_id"] : "0"),
			(isset($_POST["rra_id"]) ? $_POST["rra_id"] : "0"), (isset($_POST["host_id"]) ? $_POST["host_id"] : "0"),
			(isset($_POST["host_grouping_type"]) ? $_POST["host_grouping_type"] : "1"), (isset($_POST["sort_children_type"]) ? $_POST["sort_children_type"] : "1"),
			(isset($_POST["propagate_changes"]) ? true : false));

		if (is_error_message()) {
			header("Location: tree.php?action=item_edit&tree_item_id=" . (empty($tree_item_id) ? $_POST["id"] : $tree_item_id) . "&tree_id=" . $_POST["graph_tree_id"] . "&parent_id=" . $_POST["parent_item_id"]);
		}else{
			header("Location: tree.php?action=edit&id=" . $_POST["graph_tree_id"]);
		}
	}
}

/* -----------------------
    Tree Item Functions
   ----------------------- */

function item_edit() {
	global $colors, $tree_sort_types;
	global $tree_item_types, $host_group_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("tree_id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$tree_item = db_fetch_row("select * from graph_tree_items where id=" . $_GET["id"]);

		if ($tree_item["local_graph_id"] > 0) { $db_type = TREE_ITEM_TYPE_GRAPH; }
		if ($tree_item["title"] != "") { $db_type = TREE_ITEM_TYPE_HEADER; }
		if ($tree_item["host_id"] > 0) { $db_type = TREE_ITEM_TYPE_HOST; }
	}

	if (isset($_GET["type_select"])) {
		$current_type = $_GET["type_select"];
	}elseif (isset($db_type)) {
		$current_type = $db_type;
	}else{
		$current_type = TREE_ITEM_TYPE_HEADER;
	}

	$tree_sort_type = db_fetch_cell("select sort_type from graph_tree where id='" . $_GET["tree_id"] . "'");

	print "<form method='post' action='tree.php' name='form_tree'>\n";

	html_start_box("<strong>树对象</strong>", "100%", $colors["header"], "3", "center", "");

	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">上层对象</font><br>
			选择这个头分支/图形的上层对象.
		</td>
		<td>
			<?php grow_dropdown_tree($_GET["tree_id"], "parent_item_id", (isset($_GET["parent_id"]) ? $_GET["parent_id"] : get_parent_id($tree_item["id"], "graph_tree_items", "graph_tree_id=" . $_GET["tree_id"])));?>
		</td>
	</tr>
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">树对象类型</font><br>
			选择这个对象是什么类型的树.
		</td>
		<td>
			<select name="type_select" onChange="window.location=document.form_tree.type_select.options[document.form_tree.type_select.selectedIndex].value">
				<?php
				while (list($var, $val) = each($tree_item_types)) {
					print "<option value='tree.php?action=item_edit" . (isset($_GET["id"]) ? "&id=" . $_GET["id"] : "") . (isset($_GET["parent_id"]) ? "&parent_id=" . $_GET["parent_id"] : "") . "&tree_id=" . $_GET["tree_id"] . "&type_select=$var'"; if ($var == $current_type) { print " selected"; } print ">$val</option>\n";
				}
				?>
			</select>
		</td>
	</tr>
	<tr bgcolor='#<?php print $colors["header_panel"];?>'>
		<td colspan="2" class='textSubHeaderDark'>树对象值</td>
	</tr>
	<?php
	switch ($current_type) {
	case TREE_ITEM_TYPE_HEADER:
		$i = 0;

		/* it's nice to default to the parent sorting style for new items */
		if (empty($_GET["id"])) {
			$default_sorting_type = db_fetch_cell("select sort_children_type from graph_tree_items where id=" . $_GET["parent_id"]);
		}else{
			$default_sorting_type = TREE_ORDERING_NONE;
		}

		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
			<td width="50%">
				<font class="textEditTitle">标题</font><br>
				如果这个对象是一个头分支,输入一个标题.
			</td>
			<td>
				<?php form_text_box("title", (isset($tree_item["title"]) ? $tree_item["title"] : ""), "", "255", 30, "text", (isset($_GET["id"]) ? $_GET["id"] : "0"));?>
			</td>
		</tr>
		<?php
		/* don't allow the user to change the tree item ordering if a tree order has been specified */
		if ($tree_sort_type == TREE_ORDERING_NONE) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
				<td width="50%">
					<font class="textEditTitle">排序方法</font><br>
					选择这个分支的子对象应该如何排序.
				</td>
				<td>
					<?php form_dropdown("sort_children_type", $tree_sort_types, "", "", (isset($tree_item["sort_children_type"]) ? $tree_item["sort_children_type"] : $default_sorting_type), "", "");?>
				</td>
			</tr>
			<?php
		}

		if ((!empty($_GET["id"])) && ($tree_sort_type == TREE_ORDERING_NONE)) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
				<td width="50%">
					<font class="textEditTitle">传播更改</font><br>
					传播除标题外所有选项到所有的子"头分支"对象.
				</td>
				<td>
					<?php form_checkbox("propagate_changes", "", "传播更改", "", "", "", 0);?>
				</td>
			</tr>
			<?php
		}
		break;
	case TREE_ITEM_TYPE_GRAPH:
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
			<td width="50%">
				<font class="textEditTitle">图形</font><br>
				从这个列表选择一个图形添加到树.
			</td>
			<td>
				<?php form_dropdown("local_graph_id", db_fetch_assoc("select graph_templates_graph.local_graph_id as id,graph_templates_graph.title_cache as name from (graph_templates_graph,graph_local) where graph_local.id=graph_templates_graph.local_graph_id and local_graph_id != 0 order by title_cache"), "name", "id", (isset($tree_item["local_graph_id"]) ? $tree_item["local_graph_id"] : ""), "", "");?>
			</td>
		</tr>
		<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
			<td width="50%">
				<font class="textEditTitle">循环归档</font><br>
				当使用导出树功能时显示缩略图的循环归档.
			</td>
			<td>
				<?php form_dropdown("rra_id", db_fetch_assoc("select id,name from rra order by timespan"), "name", "id", (isset($tree_item["rra_id"]) ? $tree_item["rra_id"] : ""), "", "");?>
			</td>
		</tr>
		<?php
		break;
	case TREE_ITEM_TYPE_HOST:
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
			<td width="50%">
				<font class="textEditTitle">主机</font><br>
				选择一个主机添加到树.
			</td>
			<td>
				<?php form_dropdown("host_id", db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"), "name", "id", (isset($tree_item["host_id"]) ? $tree_item["host_id"] : ""), "", "");?>
			</td>
		</tr>
		<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
			<td width="50%">
				<font class="textEditTitle">图形分组样式</font><br>
				当这个主机显示在树里时应该如何分组显示图形.
			</td>
			<td>
				<?php form_dropdown("host_grouping_type", $host_group_types, "", "", (isset($tree_item["host_grouping_type"]) ? $tree_item["host_grouping_type"] : "1"), "", "");?>
			</td>
		</tr>
		<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
			<td width="50%">
				<font class="textEditTitle">循环归档</font><br>
				当使用导出树功能时显示缩略图的循环归档.
			</td>
			<td>
				<?php form_dropdown("rra_id", db_fetch_assoc("select id,name from rra order by timespan"), "name", "id", (isset($tree_item["rra_id"]) ? $tree_item["rra_id"] : ""), "", "");?>
			</td>
		</tr>
		<?php
		break;
	}
	?>
	</tr>
	<?php

	form_hidden_box("id", (isset($_GET["id"]) ? $_GET["id"] : "0"), "");
	form_hidden_box("graph_tree_id", $_GET["tree_id"], "");
	form_hidden_box("type", $current_type, "");
	form_hidden_box("save_component_tree_item", "1", "");

	html_end_box();

	form_save_button("tree.php?action=edit&id=" . $_GET["tree_id"]);
}

function item_moveup() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("tree_id"));
	/* ==================================================== */

	$order_key = db_fetch_cell("SELECT order_key FROM graph_tree_items WHERE id=" . $_GET["id"]);
	if ($order_key > 0) { branch_up($order_key, 'graph_tree_items', 'order_key', 'graph_tree_id=' . $_GET["tree_id"]); }

	/* clear graph tree cache on save - affects current user only, other users should see changes in <5 minutes */
	if (isset($_SESSION['dhtml_tree'])) {
		unset($_SESSION['dhtml_tree']);
	}

}

function item_movedown() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("tree_id"));
	/* ==================================================== */

	$order_key = db_fetch_cell("SELECT order_key FROM graph_tree_items WHERE id=" . $_GET["id"]);
	if ($order_key > 0) { branch_down($order_key, 'graph_tree_items', 'order_key', 'graph_tree_id=' . $_GET["tree_id"]); }

	/* clear graph tree cache on save - affects current user only, other users should see changes in <5 minutes */
	if (isset($_SESSION['dhtml_tree'])) {
		unset($_SESSION['dhtml_tree']);
	}

}

function item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("tree_id"));
	/* ==================================================== */

	if ((read_config_option("deletion_verification") == "on") && (!isset($_GET["confirm"]))) {
		$graph_tree_item = db_fetch_row("select title,local_graph_id,host_id from graph_tree_items where id=" . $_GET["id"]);

		if (!empty($graph_tree_item["local_graph_id"])) {
			$text = "您真的想要删除图形对象 <strong>'" . db_fetch_cell("select title_cache from graph_templates_graph where local_graph_id=" . $graph_tree_item["local_graph_id"]) . "'</strong>?";
		}elseif ($graph_tree_item["title"] != "") {
			$text = "您真的想要删除头分支对象 <strong>'" . $graph_tree_item["title"] . "'</strong>?";
		}elseif (!empty($graph_tree_item["host_id"])) {
			$text = "您真的想要删除主机对象 <strong>'" . db_fetch_cell("select CONCAT_WS('',description,' (',hostname,')') as hostname from host where id=" . $graph_tree_item["host_id"]) . "'</strong>?";
		}

		include("./include/top_header.php");
		form_confirm("您确定吗?", $text, htmlspecialchars("tree.php?action=edit&id=" . $_GET["tree_id"]), htmlspecialchars("tree.php?action=item_remove&id=" . $_GET["id"] . "&tree_id=" . $_GET["tree_id"]));
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("deletion_verification") == "") || (isset($_GET["confirm"]))) {
		delete_branch($_GET["id"]);
	}

	/* clear graph tree cache on save - affects current user only, other users should see changes in <5 minutes */
	if (isset($_SESSION['dhtml_tree'])) {
		unset($_SESSION['dhtml_tree']);
	}

	header("Location: tree.php?action=edit&id=" . $_GET["tree_id"]); exit;
}


/* ---------------------
    Tree Functions
   --------------------- */

function tree_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if ((read_config_option("deletion_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm("您确定吗?", "您真的想要删除树 <strong>'" . db_fetch_cell("select name from graph_tree where id=" . $_GET["id"]) . "'</strong>?", htmlspecialchars("tree.php"), htmlspecialchars("tree.php?action=remove&id=" . $_GET["id"]));
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("deletion_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from graph_tree where id=" . $_GET["id"]);
		db_execute("delete from graph_tree_items where graph_tree_id=" . $_GET["id"]);
	}

	/* clear graph tree cache on save - affects current user only, other users should see changes in <5 minutes */
	if (isset($_SESSION['dhtml_tree'])) {
		unset($_SESSION['dhtml_tree']);
	}

}

function tree_edit() {
	global $colors, $fields_tree_edit;
    $fields_tree_edit["name"]["description"] = "这个区域的名称";
    $fields_tree_edit["sort_type"]["description"] = "选择这个区域里的子对象的排序方法";

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	/* clean up subaction */
	if (isset($_REQUEST["subaction"])) {
		$_REQUEST["subaction"] = sanitize_search_string(get_request_var("subaction"));
	}

	if (!empty($_GET["id"])) {
		$tree = db_fetch_row("select * from graph_tree where id=" . $_GET["id"]);
		$header_label = "[编辑: " . htmlspecialchars($tree["name"]) . "]";
	}else{
		$header_label = "[新]";
	}

	html_start_box("<strong>区域</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_tree_edit, (isset($tree) ? $tree : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		html_start_box("<strong>区域对象</strong>", "100%", $colors["header"], "3", "center", "tree.php?action=item_edit&tree_id=" . $tree["id"] . "&parent_id=0");

		?>
		<td>
		<input type='button' onClick='return document.location="tree.php?action=edit&id=<?php print $_GET["id"];?>&subaction=expand_all"' value='展开所有' title='展开所有树'>
		<input type='button' onClick='return document.location="tree.php?action=edit&id=<?php print $_GET["id"];?>&subaction=collapse_all"' value='收缩所有' title='收缩所有树'></a>
		</td>
		<?php

		print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
			DrawMatrixHeaderItem("对象",$colors["header_text"],1);
			DrawMatrixHeaderItem("值",$colors["header_text"],1);
			DrawMatrixHeaderItem("&nbsp;",$colors["header_text"],2);
		print "</tr>";

		grow_edit_graph_tree($_GET["id"], "", "");
		html_end_box();
	}

	form_save_button("tree.php", "return");
}

function tree() {
	global $colors;
    print '<a href="tree.php?action=edit"><button type="button">添加区域</button></a>';
	html_start_box("<strong>区域</strong>", "100%", $colors["header"], "3", "center",'');

	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("名称",$colors["header_text"],1);
		DrawMatrixHeaderItem("&nbsp;",$colors["header_text"],1);
	print "</tr>";

	$trees = db_fetch_assoc("SELECT * FROM graph_tree ORDER BY id ");
    $trees[0]["name"] = "默认区域";

    $i = 0;
	if (sizeof($trees) > 0) {
	foreach ($trees as $tree) {
		form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="<?php print htmlspecialchars("tree.php?action=edit&id=" . $tree["id"]);?>"><?php print htmlspecialchars($tree["name"]);?></a>
			</td>
			<td align="right">
				<a href="<?php print htmlspecialchars("tree.php?action=remove&id=" . $tree["id"]);?>"><img src="images/delete_icon.gif" style="height:10px;width:10px;" border="0" alt="Delete"></a>
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td><em>无图形树</em></td></tr>\n";
	}
	html_end_box();
}
 ?>
