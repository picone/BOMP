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

/* draw_color_template_items_list - draws a nicely formatted list of color items for display
     on an edit form
   @arg $item_list - an array representing the list of color items. this array should
     come directly from the output of db_fetch_assoc()
   @arg $filename - the filename to use when referencing any external url
   @arg $url_data - any extra GET url information to pass on when referencing any
     external url
   @arg $disable_controls - whether to hide all edit/delete functionality on this form */
function draw_color_template_items_list($item_list, $filename, $url_data, $disable_controls) {
	global $colors, $config;
	global $struct_color_template_item;

	#print "<pre>";print_r($item_list);print "</pre>";

	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("颜色对象",$colors["header_text"],1);
		DrawMatrixHeaderItem("编号",$colors["header_text"],1);
		DrawMatrixHeaderItem("对象颜色",$colors["header_text"],4);
	print "</tr>";

	$group_counter = 0; $_graph_type_name = ""; $i = 0;
	$alternate_color_1 = $colors["alternate"]; $alternate_color_2 = $colors["alternate"];

	if (sizeof($item_list) > 0) {
	foreach ($item_list as $item) {
		/* color grouping display logic */
		$this_row_style = ""; $use_custom_row_color = false; $hard_return = "";
		
		/* alternating row color */
		if ($use_custom_row_color == false) {
			form_alternate_row_color($alternate_color_1,$alternate_color_2,$i);
		}else{
			print "<tr bgcolor='#$custom_row_color'>";
		}

		# print item no.
		print "<td>";
		if ($disable_controls == false) { print "<a href='$filename?action=item_edit&color_template_item_id=" . $item["color_template_item_id"] . "&$url_data'>"; }
		print "<strong>对象 # " . ($i+1) . "</strong>";
		if ($disable_controls == false) { print "</a>"; }
		print "</td>\n";

		# print function
		
		print "<td style='$this_row_style'>" . $item["sequence"] . "</td>\n";
		print "<td" . ((isset($item["hex"])) ? " bgcolor='#" . $item["hex"] . "'" : "") . " width='1%'>&nbsp;</td>\n";
		print "<td style='$this_row_style'>" . $item["hex"] . "</td>\n";

		if ($disable_controls == false) {
			print "<td><a href='$filename?action=item_movedown&color_template_item_id=" . $item["color_template_item_id"] . "&$url_data'><img src='../../images/move_down.gif' border='0' alt='Move Down'></a>
					<a href='$filename?action=item_moveup&color_template_item_id=" . $item["color_template_item_id"] . "&$url_data'><img src='../../images/move_up.gif' border='0' alt='Move Up'></a></td>\n";
			print "<td align='right'><a href='$filename?action=item_remove&color_template_item_id=" . $item["color_template_item_id"] . "&$url_data'><img src='../../images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a></td>\n";
		}

		print "</tr>";

		$i++;
	}
	}else{
		print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td colspan='7'><em>无对象</em></td></tr>";
	}
}
?>
