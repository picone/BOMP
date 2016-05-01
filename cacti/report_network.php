<?php
if(getRequestVar('print')==1){
    $post=array('title','linkman','department','telphone','description','introduction','problem');
    $file_path=$_SERVER['DOCUMENT_ROOT'].'/output/network.pdf';
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie Cacti '.$_COOKIE['Cacti'];
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequestVar($val).'\'';
    }
    $cmd.=' \'http://127.0.0.1'.$_SERVER['REQUEST_URI'].'\' '.$file_path;
    exec($cmd);
    ob_clean();
    if(file_exists($file_path)){
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename="'.getRequestVar('title').date('YmdHi').'.pdf"');
        readfile($file_path);
    }else{
        echo 'Cannot read file.';
    }
    exit;
}
include("./include/auth.php");

define("MAX_DISPLAY_PAGES", 21);
global $colors, $item_rows;

/* ================= input validation ================= */
input_validate_input_number(get_request_var_request("host_template_id"));
input_validate_input_number(get_request_var_request("page"));
input_validate_input_number(get_request_var_request("host_status"));
input_validate_input_number(get_request_var_request("host_rows"));
/* ==================================================== */

/* clean up search string */
if (isset($_REQUEST["filter"])) {
    $_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
}

/* clean up sort_column */
if (isset($_REQUEST["sort_column"])) {
    $_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
}

/* clean up search string */
if (isset($_REQUEST["sort_direction"])) {
    $_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
}

/* if the user pushed the 'clear' button */
if (isset($_REQUEST["clear_x"])) {
    kill_session_var("sess_device_current_page");
    kill_session_var("sess_device_filter");
    kill_session_var("sess_device_host_template_id");
    kill_session_var("sess_host_status");
    kill_session_var("sess_host_rows");
    kill_session_var("sess_host_sort_column");
    kill_session_var("sess_host_sort_direction");

    unset($_REQUEST["page"]);
    unset($_REQUEST["filter"]);
    unset($_REQUEST["host_template_id"]);
    unset($_REQUEST["host_status"]);
    unset($_REQUEST["host_rows"]);
    unset($_REQUEST["sort_column"]);
    unset($_REQUEST["sort_direction"]);
}

if ((!empty($_SESSION["sess_host_status"])) && (!empty($_REQUEST["host_status"]))) {
    if ($_SESSION["sess_host_status"] != $_REQUEST["host_status"]) {
        $_REQUEST["page"] = 1;
    }
}

/* remember these search fields in session vars so we don't have to keep passing them around */
load_current_session_value("page", "sess_device_current_page", "1");
load_current_session_value("filter", "sess_device_filter", "");
load_current_session_value("host_template_id", "sess_device_host_template_id", "-1");
load_current_session_value("host_status", "sess_host_status", "-1");
load_current_session_value("host_rows", "sess_host_rows", read_config_option("num_rows_device"));
load_current_session_value("sort_column", "sess_host_sort_column", "description");
load_current_session_value("sort_direction", "sess_host_sort_direction", "ASC");

/* if the number of rows is -1, set it to the default */
if ($_REQUEST["host_rows"] == -1) {
    $_REQUEST["host_rows"] = read_config_option("num_rows_device");
}

?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <title><?php getRequestVar('title','网络监控报告')?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <link href="/cacti/include/main.css" type="text/css" rel="stylesheet">
        <link href="/cacti/images/favicon.ico" rel="shortcut icon">
        <link href="/public/report.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="/cacti/include/layout.js"></script>
        <script src="/public/jq.js" type="application/javascript"></script>
        <script src="/public/report.js" type="application/javascript"></script>
        <style type="text/css">
            .show-margin{
                padding:6px 0 6px;
            }
            .show-margin label{
                margin-right:15px;
            }
        </style>
        <script type="application/javascript">
            function applyViewDeviceFilterChange(objForm) {
                var strURL = '?host_status=' + objForm.host_status.value;
                strURL = strURL + '&host_template_id=' + objForm.host_template_id.value;
                strURL = strURL + '&host_rows=' + objForm.host_rows.value;
                strURL = strURL + '&filter=' + objForm.filter.value;
                $(':checkbox[name^=show]').each(function(i,v){
                    strURL+='&'+$(v).attr('name')+'='+($(v).prop('checked')?1:0);
                });
                document.location = strURL;
            }
            $(function(){
                $('.show-margin').find(':checkbox').change(function(){
                    applyViewDeviceFilterChange(document.form_devices);
                });
            });
        </script>
    </head>
<body>
    <form method="post" target="_blank">
        <h1 class="report-title"><?php if(getRequestVar('title')){echo utf8_decode(getRequestVar('title','网络监控报告'));}else{?><input type="text" name="title" value="网络监控报告"><?php }?></h1>
        <input type="hidden" name="print" value="1">
        <table class="table">
            <thead>
            <tr>
                <th colspan="1">生成时间</th>
                <td colspan="5"><?php echo date('Y-m-d H:i:s');if(!isset($_POST['linkman'])){?><button type="submit">导出PDF</button><?php }?></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th><label for="linkman">联系人</label></th>
                <td><input type="text" name="linkman" id="linkman" value="<?php if(isset($_POST['linkman'])){echo utf8_decode($_POST['linkman']);}?>"></td>
                <th><label for="department">相关部门</label></th>
                <td><input type="text" name="department" id="department" value="<?php if(isset($_POST['department'])){echo utf8_decode($_POST['department']);}?>"></td>
                <th><label for="telphone">电话</label></th>
                <td><input type="text" name="telphone" id="telphone" value="<?php if(isset($_POST['telphone'])){echo utf8_decode($_POST['telphone']);}?>"></td>
            </tr>
            <tr>
                <th>总体健康评价</th>
                <td colspan="5">
                    <table>
                        <tr>
                            <td data-role="color" data-class="report-strong"><input type="checkbox" name="description" id="strong" value="1"<?php if(getRequestVar('description',1)==1){echo 'checked';}?>><label for="strong">健壮</label></td>
                            <td data-role="color" data-class="report-increase"><input type="checkbox" name="description" id="increase" value="2"<?php if(getRequestVar('description',1)==2){echo 'checked';}?>><label for="increase">待提高</label></td>
                            <td data-role="color" data-class="report-unhealthy"><input type="checkbox" name="description" id="unhealthy" value="3"<?php if(getRequestVar('description',1)==3){echo 'checked';}?>><label for="unhealthy">不健康</label></td>
                            <td data-role="color" data-class="report-serious"><input type="checkbox" name="description" id="serious" value="4"<?php if(getRequestVar('description',1)==4){echo 'checked';}?>><label for="serious">严重问题</label></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th><label for="introduction">报告说明</label></th>
                <td colspan="5"><?php if(isset($_POST['introduction'])){?><pre><?php echo utf8_decode($_POST['introduction']);?></pre><?php }else{?><textarea name="introduction" id="introduction" rows="10" onclick="javascript:ResizeTextarea(this,10);" onkeyup="javascript:ResizeTextarea(this,10);"></textarea><?php }?></td>
            </tr>
            <tr>
                <th><label for="problem">关键问题</label></th>
                <td colspan="5"><?php if(isset($_POST['problem'])){?><pre><?php echo utf8_decode($_POST['problem']);?></pre><?php }else{?><textarea name="problem" id="problem" rows="1" onclick="javascript:ResizeTextarea(this,1);" onkeyup="javascript:ResizeTextarea(this,1);"></textarea><?php }?></td>
            </tr>
            </tbody>
        </table>
    </form>
    <table width="100%" cellspacing="0" cellpadding="0">
    <td width="100%" valign="top">
    <?php
    if(!getRequestVar('linkman')){
        html_start_box("<strong>主机</strong>","100%",$colors["header"],"3","center",'');
        ?>
        <tr bgcolor="#<?php print $colors["panel"]; ?>">
            <td>
                <form name="form_devices" action="report_network.php">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td nowrap style='white-space: nowrap;' width="50">
                                类型:&nbsp;
                            </td>
                            <td width="1">
                                <select name="host_template_id"
                                        onChange="applyViewDeviceFilterChange(document.form_devices)">
                                    <option
                                        value="-1"<?php if(get_request_var_request("host_template_id")=="-1"){ ?> selected<?php } ?>>
                                        任意
                                    </option>
                                    <option
                                        value="0"<?php if(get_request_var_request("host_template_id")=="0"){ ?> selected<?php } ?>>
                                        无
                                    </option>
                                    <?php
                                    $host_templates=db_fetch_assoc("select id,name from host_template order by name");

                                    if(sizeof($host_templates)>0){
                                        foreach($host_templates as $host_template){
                                            print "<option value='".$host_template["id"]."'";
                                            if(get_request_var_request("host_template_id")==$host_template["id"]){
                                                print " selected";
                                            }
                                            print ">".htmlspecialchars($host_template["name"])."</option>\n";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td nowrap style='white-space: nowrap;' width="50">
                                &nbsp;状态:&nbsp;
                            </td>
                            <td width="1">
                                <select name="host_status"
                                        onChange="applyViewDeviceFilterChange(document.form_devices)">
                                    <option
                                        value="-1"<?php if(get_request_var_request("host_status")=="-1"){ ?> selected<?php } ?>>
                                        任意
                                    </option>
                                    <option
                                        value="-3"<?php if(get_request_var_request("host_status")=="-3"){ ?> selected<?php } ?>>
                                        已启用
                                    </option>
                                    <option
                                        value="-2"<?php if(get_request_var_request("host_status")=="-2"){ ?> selected<?php } ?>>
                                        已禁用
                                    </option>
                                    <option
                                        value="-4"<?php if(get_request_var_request("host_status")=="-4"){ ?> selected<?php } ?>>
                                        不在线
                                    </option>
                                    <option
                                        value="3"<?php if(get_request_var_request("host_status")=="3"){ ?> selected<?php } ?>>
                                        在线
                                    </option>
                                    <option
                                        value="1"<?php if(get_request_var_request("host_status")=="1"){ ?> selected<?php } ?>>
                                        宕机
                                    </option>
                                    <option
                                        value="2"<?php if(get_request_var_request("host_status")=="2"){ ?> selected<?php } ?>>
                                        正在恢复
                                    </option>
                                    <option
                                        value="0"<?php if(get_request_var_request("host_status")=="0"){ ?> selected<?php } ?>>
                                        未知
                                    </option>
                                </select>
                            </td>
                            <td nowrap style='white-space: nowrap;' width="50">
                                &nbsp;搜索:&nbsp;
                            </td>
                            <td width="1">
                                <input type="text" name="filter" size="20"
                                       value="<?php print htmlspecialchars(get_request_var_request("filter")); ?>">
                            </td>
                            <td nowrap style='white-space: nowrap;' width="80">
                                &nbsp;每页行数:&nbsp;
                            </td>
                            <td width="1">
                                <select name="host_rows" onChange="applyViewDeviceFilterChange(document.form_devices)">
                                    <option
                                        value="-1"<?php if(get_request_var_request("host_rows")=="-1"){ ?> selected<?php } ?>>
                                        默认
                                    </option>
                                    <?php
                                    if(sizeof($item_rows)>0){
                                        foreach($item_rows as $key=>$value){
                                            print "<option value='".$key."'";
                                            if(get_request_var_request("host_rows")==$key){
                                                print " selected";
                                            }
                                            print ">".htmlspecialchars($value)."</option>\n";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td nowrap>
                                &nbsp;<input type="submit" value="确定" title="设置/刷新过滤器">
                                <input type="submit" name="clear_x" value="清除" title="清除过滤器">
                            </td>
                        </tr>
                    </table>
                    <div class="show-margin">
                        <label><input type="checkbox" name="show_id" <?php if(getRequestVar('show_id')){
                                echo 'checked';
                            } ?>>编号</label>
                        <label><input type="checkbox" name="show_nosort1" <?php if(getRequestVar('show_nosort1')){
                                echo 'checked';
                            } ?>>图形数量</label>
                        <label><input type="checkbox" name="show_nosort2" <?php if(getRequestVar('show_nosort2')){
                                echo 'checked';
                            } ?>>数据源数量</label>
                        <label><input type="checkbox"
                                      name="show_event_count" <?php if(getRequestVar('show_event_count')){
                                echo 'checked';
                            } ?>>事件数量</label>
                        <label><input type="checkbox" name="show_cur_time" <?php if(getRequestVar('show_cur_time')){
                                echo 'checked';
                            } ?>>当前延时</label>
                    </div>
                    <input type='hidden' name='page' value='1'>
                </form>
            </td>
        </tr>
        <?php
        html_end_box();
    }
/* form the 'where' clause for our main sql query */
if (strlen(get_request_var_request("filter"))) {
    $sql_where = "where (host.hostname like '%%" . get_request_var_request("filter") . "%%' OR host.description like '%%" . get_request_var_request("filter") . "%%')";
}else{
    $sql_where = "";
}

if (get_request_var_request("host_status") == "-1") {
    /* Show all items */
}elseif (get_request_var_request("host_status") == "-2") {
    $sql_where .= (strlen($sql_where) ? " and host.disabled='on'" : "where host.disabled='on'");
}elseif (get_request_var_request("host_status") == "-3") {
    $sql_where .= (strlen($sql_where) ? " and host.disabled=''" : "where host.disabled=''");
}elseif (get_request_var_request("host_status") == "-4") {
    $sql_where .= (strlen($sql_where) ? " and (host.status!='3' or host.disabled='on')" : "where (host.status!='3' or host.disabled='on')");
}else {
    $sql_where .= (strlen($sql_where) ? " and (host.status=" . get_request_var_request("host_status") . " AND host.disabled = '')" : "where (host.status=" . get_request_var_request("host_status") . " AND host.disabled = '')");
}

if (get_request_var_request("host_template_id") == "-1") {
    /* Show all items */
}elseif (get_request_var_request("host_template_id") == "0") {
    $sql_where .= (strlen($sql_where) ? " and host.host_template_id=0" : "where host.host_template_id=0");
}elseif (!empty($_REQUEST["host_template_id"])) {
    $sql_where .= (strlen($sql_where) ? " and host.host_template_id=" . get_request_var_request("host_template_id") : "where host.host_template_id=" . get_request_var_request("host_template_id"));
}

/* print checkbox form for validation */
print "<form name='chk' method='post' action='report_network.php'>\n";

html_start_box("", "100%", $colors["header"], "3", "center", "");

$total_rows = db_fetch_cell("select
		COUNT(host.id)
		from host
		$sql_where");

$sortby = get_request_var_request("sort_column");
if ($sortby=="hostname") {
    $sortby = "INET_ATON(hostname)";
}

$host_graphs       = array_rekey(db_fetch_assoc("SELECT host_id, count(*) as graphs FROM graph_local GROUP BY host_id"), "host_id", "graphs");
$host_data_sources = array_rekey(db_fetch_assoc("SELECT host_id, count(*) as data_sources FROM data_local GROUP BY host_id"), "host_id", "data_sources");

$sql_query = "SELECT *
		FROM host
		$sql_where
		ORDER BY " . $sortby . " " . get_request_var_request("sort_direction") . "
		LIMIT " . (get_request_var_request("host_rows")*(get_request_var_request("page")-1)) . "," . get_request_var_request("host_rows");

$hosts = db_fetch_assoc($sql_query);

/* generate page list */
$url_page_select = get_page_list(get_request_var_request("page"), MAX_DISPLAY_PAGES, get_request_var_request("host_rows"), $total_rows, "report_network.php?filter=" . get_request_var_request("filter") . "&host_template_id=" . get_request_var_request("host_template_id") . "&host_status=" . get_request_var_request("host_status"));

$display_text=array('description'=>array('主机名称','ASC'));
if(getRequestVar('show_id'))$display_text['id']=array('编号','ASC');
if(getRequestVar('show_nosort1'))$display_text['nosort1']=array('图形数量','ASC');
if(getRequestVar('show_nosort2'))$display_text['nosort2']=array('数据源数量','ASC');
$display_text['status']=array('状态','ASC');
if(getRequestVar('show_event_count'))$display_text['status_event_count']=array('事件数量','ASC');
$display_text['hostname']=array('主机名','ASC');
if(getRequestVar('show_cur_time'))$display_text['cur_time']=array('当前延时(毫秒)','DESC');
$display_text['avg_time']=array('平均延时(毫秒)','DESC');
$display_text['availability']=array('可用性(百分比)','ASC');

html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"), false,'',false);

$i = 0;
if (sizeof($hosts) > 0) {
    foreach ($hosts as $host) {
        form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $host["id"]); $i++;
        form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("host.php?action=edit&id=" . $host["id"]) . "'>" .
            (strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", htmlspecialchars($host["description"])) : htmlspecialchars($host["description"])) . "</a>", $host["id"]);
        if(getRequestVar('show_id'))form_selectable_cell(round(($host["id"]), 2), $host["id"]);
        if(getRequestVar('show_nosort1'))form_selectable_cell((isset($host_graphs[$host["id"]]) ? $host_graphs[$host["id"]] : 0), $host["id"]);
        if(getRequestVar('show_nosort2'))form_selectable_cell((isset($host_data_sources[$host["id"]]) ? $host_data_sources[$host["id"]] : 0), $host["id"]);
        form_selectable_cell(get_colored_device_status(($host["disabled"] == "on" ? true : false), $host["status"]), $host["id"]);
        if(getRequestVar('show_event_count'))form_selectable_cell(round(($host["status_event_count"]), 2), $host["id"]);
        form_selectable_cell((strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", htmlspecialchars($host["hostname"])) : htmlspecialchars($host["hostname"])), $host["id"]);
        if(getRequestVar('show_cur_time'))form_selectable_cell(round(($host["cur_time"]), 2), $host["id"]);
        form_selectable_cell(round(($host["avg_time"]), 2), $host["id"]);
        form_selectable_cell(round($host["availability"], 2), $host["id"]);
        form_end_row();
    }

    /* put the nav bar on the bottom as well */
    if(!getRequestVar('linkman')){
        $nav = "<tr bgcolor='#" . $colors["header"] . "'>
			<td colspan='11'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; ";
        if (get_request_var_request("page") > 1){
            $nav .= "<a class='linkOverDark' href='" . htmlspecialchars("report_network.php?filter=" . get_request_var_request("filter") . "&host_template_id=" . get_request_var_request("host_template_id") . "&host_status=" . get_request_var_request("host_status") . "&page=" . (get_request_var_request("page")-1)) . "'>";
        }
        $nav .= "上一页";
        if (get_request_var_request("page") > 1){
            $nav .= "</a>";
        }
        $nav.="</strong></td>\n
    <td align='center' class='textHeaderDark'>" . ((get_request_var_request("host_rows")*(get_request_var_request("page")-1))+1) . " 到 " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (get_request_var_request("host_rows")*get_request_var_request("page")))) ? $total_rows : (get_request_var_request("host_rows")*get_request_var_request("page"))) . " 行,共 $total_rows 行 [ 第 $url_page_select 页 ]</td>\n
    <td align='right' class='textHeaderDark'><strong>";
        if((get_request_var_request("page") * get_request_var_request("host_rows")) < $total_rows) { $nav .= "<a class='linkOverDark' href='" . htmlspecialchars("report_network.php?filter=" . get_request_var_request("filter") . "&host_template_id=" . get_request_var_request("host_template_id") . "&host_status=" . get_request_var_request("host_status") . "&page=" . (get_request_var_request("page")+1)) . "'>"; } $nav .= "下一页"; if ((get_request_var_request("page") * get_request_var_request("host_rows")) < $total_rows){
            $nav.="</a>";
        }
        $nav .= " &gt;&gt;</strong></td>\n</tr></table></td></tr>\n";
        print $nav;
    }
    
}else{
    print "<tr><td><em>无主机</em></td></tr>";
}
html_end_box(false);
print "</form>\n";
include_once("./include/bottom_footer.php");

function getRequestVar($name,$default=false){
    if(isset($_REQUEST[$name])){
        return $_REQUEST[$name];
    }else if($default!==false){
        return $default;
    }else{
        return false;
    }
}
