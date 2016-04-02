<?php

chdir('../../');

include("./include/auth.php");

include_once("./include/global.php");

$p = dirname(__FILE__);
include_once($p . '/manage_lib.php');
$permit=manage_accounts_permit("groups");
if ($permit == 0) {
  print "Unauthorized access.";
  exit;
}

define("MAX_DISPLAY_PAGES", 21);

$device_actions = array(1 => "Delete");

if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"] = "";
}
switch ($_REQUEST["action"]) {
case 'actions':
	if (!isset($_REQUEST["manage_groups_id2"])) {
	  manage_form_actions();
	} else {
		/* ================= input validation ================= */
//		input_validate_input_number(get_request_var_request("id2"));
//		input_validate_input_number(get_request_var_request("force"));
		/* ==================================================== */

		/* clean up name string */
		if (isset($_REQUEST["manage_groups_name"])) {
			$_REQUEST["manage_groups_name"] = sanitize_search_string(get_request_var("manage_groups_name"));
		}

		/* clean up name string */
		if (isset($_REQUEST["manage_groups_site"])) {
			$_REQUEST["manage_groups_site"] = sanitize_search_string(get_request_var("manage_groups_site"));
		}

		if (isset($_GET['force'])) {
			db_execute("INSERT INTO manage_groups ( id , name , site_id) VALUES ('', '" . $_GET['manage_groups_name'] . "', '" . $_GET['manage_groups_site'] . "')");
		}else{
			db_execute("UPDATE manage_groups SET name = '" . $_GET['manage_groups_name'] . "' where id='" . $_GET['manage_groups_id2'] . "' AND site_id='" . $_GET['manage_groups_site'] . "'");
		}
	}
default:
	include_once("./include/top_header.php");
	if (isset($_REQUEST["edit"])) {
	  manage_edit();
	}else{
	if (isset($_REQUEST["manage_groups_site"])) {
			manage_host();
		}else{
	?>
	<form name="form_devices">
		<table width='98%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center' cellpadding='1'>
			<tr bgcolor="E5E5E5">

				<td>
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td class="textHeader" nowrap>


						<td width="5"></td>
						<td width="80">
							Select a site:&nbsp;
						</td>
						<td width="1">
							<select name="cbo_host_status" onChange="window.location=document.form_devices.cbo_host_status.options[document.form_devices.cbo_host_status.selectedIndex].value">
							<?php

			$sql4 = "SELECT * FROM manage_sites";
			$result4 = mysql_query($sql4);

			print "<option value='manage_groups.php'>Select";

			while ($row4 = mysql_fetch_array($result4, MYSQL_ASSOC)) {
				print "<option value='manage_groups.php?manage_groups_site=" . $row4['id'];

				print "'";
				print ">" . $row4['name'] . "</option>";

			}


							?>
							</select>
						</td>
					</tr>
			  </table>
		</td>
	</form>
</tr>
</table>
<?php
		}
	}

	include_once("./include/bottom_footer.php");
	break;
}

function manage_form_actions() {
	global $colors, $device_actions, $fields_host_edit;

	/* clean up search string */
	if (isset($_REQUEST["selected_items"])) {
		$_REQUEST["selected_items"] = sanitize_search_string(get_request_var("selected_items"));
	}

	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { //delete
			for ($i=0;($i<count($selected_items));$i++) {
				input_validate_input_number($selected_items[$i]);
				db_execute("delete FROM manage_groups where id='" . $selected_items[$i] . "'");
			}
		}

		header("Location: manage_groups.php");
		exit;
	}

	$host_list = ""; $i = 0;

	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			input_validate_input_number($matches[1]);

			$host_list .= "<li>" . db_fetch_cell("SELECT name FROM manage_groups WHERE id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $device_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");
	print "<form action='manage_groups.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { //delete
		print "	<tr><td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'><p>To Delete this Group, Press the \"yes\" button below.</p><p>$host_list</p></td></tr>";
	}

	if (!isset($host_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one entry.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='../../images/button_yes.gif' alt='Save' align='absmiddle'>";
	}

	print "	<tr><td colspan='2' align='right' bgcolor='#eaeaea'><input type='hidden' name='action' value='actions'><input type='hidden' name='selected_items' value='" . (isset($host_array) ? serialize($host_array) : '') . "'><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'><a href='manage_groups.php'><img src='../../images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a>$save_html</td></tr>";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

function manage_host() {
	global $colors, $device_actions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("manage_groups_id"));
	input_validate_input_number(get_request_var_request("manage_groups_site"));
	/* ==================================================== */

	$vv = db_fetch_cell("SELECT name FROM manage_sites where id='" . $_REQUEST['manage_groups_site'] . "'");

	html_start_box("<strong>Device Groups for Site " . $vv . "</strong>", "98%", $colors["header"], "3", "center", "manage_groups.php?edit=1&manage_groups_site=" . $_REQUEST['manage_groups_site'] . "&manage_groups_id=-1");

	html_end_box();

	print "<br>";

	html_start_box("", "98%", $colors["header"], "3", "center", "");

	$hosts = db_fetch_assoc("SELECT id, name, site_id FROM manage_groups WHERE site_id=" . $_REQUEST['manage_groups_site']);

	html_header_checkbox(array("Name"));

	$i = 0;

	if (sizeof($hosts) > 0) {
		foreach ($hosts as $host) {
			form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
			?>
			<td width=250>
			<?php
			print "<a class='linkEditMain' href='manage_groups.php?edit=1&manage_groups_site=".$host["site_id"]."&manage_groups_id=".$host["id"]."'>";
			print $host["name"];
			?>

			<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
			<input type='checkbox' style='margin: 0px;' name='chk_<?php print $host["id"];?>' title="<?php print $host["name"];?>">
			</td>
			</tr>
			<?php
		}
	}else{
		print "<tr><td><em>No groups</em></td></tr>";
	}

	html_end_box(false);

	draw_actions_dropdown($device_actions);
}

function manage_edit() {
	global $colors;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("manage_groups_id"));
	input_validate_input_number(get_request_var_request("manage_groups_site"));
	/* ==================================================== */

	$sql = "SELECT * FROM manage_groups WHERE id='" . $_GET['manage_groups_id'] . "' AND site_id='" . $_GET['manage_groups_site'] . "'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	$vv = db_fetch_cell("SELECT name FROM manage_sites where id='" . $_REQUEST['manage_groups_site'] . "'");

	html_start_box("<strong>Group " . $row['name'] . " for site ".$vv." :</strong>", "98%", $colors["header"], "4", "center", "");

	print "<form method='get' action='manage_groups.php'><br>";

	?>
	<input type='hidden' name='manage_groups_id2' value='<?php print $_GET['manage_groups_id'];?>'>
	<input type='hidden' name='manage_groups_site' value='<?php print $_GET['manage_groups_site'];?>'>
	<?php

	$i = 0;
	form_alternate_row_color($colors["alternate"],$colors["light"],$i);

	print "<td>Name : </td><td>";
	print "<input type='text' name='manage_groups_name' size='80' value='" . $row['name'] . "'>";

	if ($_GET['manage_groups_id'] == "-1") {
		?>
		<input type='hidden' name='force' value='null'>
		<?php
	}
	html_end_box(false);

	$ds_actions = array(1 => "Save");

	draw_actions_dropdown($ds_actions);

	print "</form>\n";
	print "</td></tr></table>";
}

?>
