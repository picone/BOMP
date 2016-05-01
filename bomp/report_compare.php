<?php
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/reports.inc.php';

$page['title']=getRequest('title','对比指标报告');
$page['file']='report6.php';
$page['hist_arg']=array('period');
$page['scripts'] = array('class.calendar.js');
if(hasRequest('print')&&getRequest('print')==1){
    $post=array('linkman','department','telphone','description','introduction','problem','picture_src');
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie zbx_sessionid '.$_COOKIE['zbx_sessionid'];
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequest($val).'\'';
    }
    $cmd.=' http://127.0.0.1/bomp/report_compare.php /var/www/new_zabbix/output/compare.pdf';
    exec($cmd);
    ob_clean();
    if(file_exists('/var/www/new_zabbix/output/compare.pdf')){
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename="对比指标报告'.date('YmdHi').'.pdf"');
        readfile('/var/www/new_zabbix/output/compare.pdf');
    }else{
        echo 'Cannot read file.';
    }
    exit;
}
require_once dirname(__FILE__).'/include/page_header.php';
// filter reset
if (hasRequest('report_reset')) {
    // get requests keys
    if (getRequest('config') == BR_DISTRIBUTION_MULTIPLE_PERIODS) {
        $unsetRequests = array('title', 'xlabel', 'ylabel', 'showlegend', 'scaletype', 'items', 'report_timesince',
            'report_timetill', 'report_show'
        );
    }
    elseif (getRequest('config') == BR_DISTRIBUTION_MULTIPLE_ITEMS) {
        $unsetRequests = array('periods', 'items', 'title', 'xlabel', 'ylabel', 'showlegend', 'sorttype',
            'report_show'
        );
    }
    else {
        $unsetRequests = array('report_timesince', 'report_timetill', 'sortorder', 'groupids', 'hostids', 'itemid',
            'title', 'xlabel', 'ylabel', 'showlegend', 'groupid', 'scaletype', 'avgperiod', 'palette', 'palettetype',
            'report_show'
        );
    }

    // requests unseting
    foreach ($unsetRequests as $unsetRequests) {
        unset($_REQUEST[$unsetRequests]);
    }
}

if (hasRequest('new_graph_item')) {
    $_REQUEST['items'] = getRequest('items', array());
    $newItem = getRequest('new_graph_item', array());

    foreach ($_REQUEST['items'] as $item) {
        if ((bccomp($newItem['itemid'], $item['itemid']) == 0)
            && $newItem['calc_fnc'] == $item['calc_fnc']
            && $newItem['caption'] == $item['caption']) {
            $itemExists = true;
            break;
        }
    }

    if (!isset($itemExists)) {
        array_push($_REQUEST['items'], $newItem);
    }
}

// validate permissions
if (getRequest('config') == BR_COMPARE_VALUE_MULTIPLE_PERIODS) {
    if (getRequest('groupid') && !API::HostGroup()->isReadable(array($_REQUEST['groupid']))) {
        access_deny();
    }
    if (getRequest('groupids') && !API::HostGroup()->isReadable($_REQUEST['groupids'])) {
        access_deny();
    }
    if (getRequest('hostids') && !API::Host()->isReadable($_REQUEST['hostids'])) {
        access_deny();
    }
    if (getRequest('itemid')) {
        $items = API::Item()->get(array(
            'itemids' => $_REQUEST['itemid'],
            'webitems' => true,
            'output' => array('itemid')
        ));
        if (!$items) {
            access_deny();
        }
    }
}
else {
    if (getRequest('items') && count($_REQUEST['items']) > 0) {
        $itemIds = zbx_objectValues($_REQUEST['items'], 'itemid');
        $itemsCount = API::Item()->get(array(
            'itemids' => $itemIds,
            'webitems' => true,
            'countOutput' => true
        ));

        if (count($itemIds) != $itemsCount) {
            access_deny();
        }
    }
}

if (hasRequest('filterState')) {
    CProfile::update('web.report6.filter.state', getRequest('filterState'), PROFILE_TYPE_INT);
}

if ((PAGE_TYPE_JS == $page['type']) || (PAGE_TYPE_HTML_BLOCK == $page['type'])) {
    require_once dirname(__FILE__).'/include/page_footer.php';
    exit;
}


if (isset($_REQUEST['delete_item']) && isset($_REQUEST['group_gid'])) {
    foreach ($_REQUEST['items'] as $gid => $item) {
        if (!isset($_REQUEST['group_gid'][$gid])) {
            continue;
        }
        unset($_REQUEST['items'][$gid]);
    }
    unset($_REQUEST['delete_item'], $_REQUEST['group_gid']);
}
elseif (isset($_REQUEST['new_period'])) {
    $_REQUEST['periods'] = getRequest('periods', array());
    $newPeriod = getRequest('new_period', array());

    foreach ($_REQUEST['periods'] as $period) {
        $period['report_timesince'] = zbxDateToTime($period['report_timesince']);
        $period['report_timetill'] = zbxDateToTime($period['report_timetill']);

        if ($newPeriod['report_timesince'] == $period['report_timesince']
            && $newPeriod['report_timetill'] == $period['report_timetill']) {
            $periodExists = true;
            break;
        }
    }

    if (!isset($periodExists)) {
        array_push($_REQUEST['periods'], $newPeriod);
    }
}
elseif (isset($_REQUEST['delete_period']) && isset($_REQUEST['group_pid'])) {
    foreach ($_REQUEST['periods'] as $pid => $period) {
        if (!isset($_REQUEST['group_pid'][$pid])) {
            continue;
        }
        unset($_REQUEST['periods'][$pid]);
    }
    unset($_REQUEST['delete_period'], $_REQUEST['group_pid']);
}

// item validation
$config = $_REQUEST['config'] = getRequest('config', BR_DISTRIBUTION_MULTIPLE_PERIODS);

// items array validation
if ($config != BR_COMPARE_VALUE_MULTIPLE_PERIODS) {
    $items = getRequest('items');
    $validItems = validateBarReportItems($items);
    if ($validItems) {
        $validItems = CMacrosResolverHelper::resolveItemNames($validItems);

        foreach ($validItems as &$item) {
            if ($item['caption'] === $item['name']) {
                $item['caption'] = $item['name_expanded'];
            }
        }

        unset($item);
    }

    if ($config == BR_DISTRIBUTION_MULTIPLE_ITEMS) {
        $validPeriods = validateBarReportPeriods(getRequest('periods'));
    }
}

$_REQUEST['report_timesince'] = zbxDateToTime(getRequest('report_timesince',
    date(TIMESTAMP_FORMAT_ZERO_TIME, time() - SEC_PER_DAY)));
$_REQUEST['report_timetill'] = zbxDateToTime(getRequest('report_timetill',
    date(TIMESTAMP_FORMAT_ZERO_TIME, time())));

$rep_tab = new CTable();
$rep_tab->setCellPadding(3);
$rep_tab->setCellSpacing(3);
$rep_tab->setAttribute('border', 0);
if(hasRequest('report_show')||hasRequest('linkman')) {
    $items = ($config == BR_COMPARE_VALUE_MULTIPLE_PERIODS)
        ? array(array('itemid' => getRequest('itemid')))
        : $validItems;

    if ((($config != BR_COMPARE_VALUE_MULTIPLE_PERIODS) ? $validItems : true)
        && (($config == BR_DISTRIBUTION_MULTIPLE_ITEMS) ? $validPeriods : true)) {
        $src = 'chart_bar.php?'.
            'config='.$config.
            url_param('title').
            url_param('xlabel').
            url_param('ylabel').
            url_param('scaletype').
            url_param('avgperiod').
            url_param('showlegend').
            url_param('sorttype').
            url_param('report_timesince').
            url_param('report_timetill').
            url_param('periods').
            url_param($items, false, 'items').
            url_param('hostids').
            url_param('groupids').
            url_param('palette').
            url_param('palettetype');

        $rep_tab->addRow(new CImg($src, 'report'));
    }
    ?>
    <link href="/public/report.css" rel="stylesheet" type="text/css"/>
    <script src="/public/report.js" type="application/javascript"></script>
    <h1 class="report-title"><?php echo $page['title'];?></h1>
    <form method="post" target="_blank">
        <input type="hidden" name="print" value="1">
        <input type="hidden" name="picture_src" value="<?php if(isset($src)){echo $src;}?>">
        <table class="table">
            <thead>
            <tr>
                <th colspan="1">生成时间</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_TIME_FORMAT_SECONDS);?></td>
                <th colspan="1">统计时段</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_FORMAT, $_REQUEST['report_timesince']), '到', zbx_date2str(DATE_FORMAT, $_REQUEST['report_timetill']);if (!hasRequest('linkman')){?><button type="submit">导出PDF</button><?php }?></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th><label for="linkman">联系人</label></th>
                <td><input type="text" name="linkman" id="linkman" value="<?php echo utf8_decode(getRequest('linkman'));?>"></td>
                <th><label for="department">相关部门</label></th>
                <td><input type="text" name="department" id="department" value="<?php echo utf8_decode(getRequest('department'));?>"></td>
                <th><label for="telphone">电话</label></th>
                <td><input type="text" name="telphone" id="telphone" value="<?php echo utf8_decode(getRequest('telphone'));?>"></td>
            </tr>
            <tr>
                <th>总体健康评价</th>
                <td colspan="5">
                    <table>
                        <tr>
                            <td data-role="color" data-class="report-strong"><input type="checkbox" name="description" id="strong" value="1"<?php if(getRequest('description')==1||!hasRequest('description')){echo 'checked';}?>><label for="strong">健壮</label></td>
                            <td data-role="color" data-class="report-increase"><input type="checkbox" name="description" id="increase" value="2"<?php if(getRequest('description')==2){echo 'checked';}?>><label for="increase">待提高</label></td>
                            <td data-role="color" data-class="report-unhealthy"><input type="checkbox" name="description" id="unhealthy" value="3"<?php if(getRequest('description')==3){echo 'checked';}?>><label for="unhealthy">不健康</label></td>
                            <td data-role="color" data-class="report-serious"><input type="checkbox" name="description" id="serious" value="4"<?php if(getRequest('description')==4){echo 'checked';}?>><label for="serious">严重问题</label></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th><label for="introduction">报告说明</label></th>
                <td colspan="5"><?php if(hasRequest('introduction')){?><pre><?php echo utf8_decode(getRequest('introduction'));?></pre><?php }else{?><textarea name="introduction" id="introduction" rows="10" onclick="javascript:ResizeTextarea(this,10);" onkeyup="javascript:ResizeTextarea(this,10);"></textarea><?php }?></td>
            </tr>
            <tr>
                <th><label for="problem">关键问题</label></th>
                <td colspan="5"><?php if(hasRequest('problem')){?><pre><?php echo utf8_decode(getRequest('problem'));?></pre><?php }else{?><textarea name="problem" id="problem" rows="1" onclick="javascript:ResizeTextarea(this,1);" onkeyup="javascript:ResizeTextarea(this,1);"></textarea><?php }?></td>
            </tr>
            </tbody>
        </table>
    </form>
<?php
}
$rep6_wdgt = new CWidget();
if(hasRequest('linkman')){
    $rep_tab->addRow(new CImg(utf8_decode(getRequest('picture_src')), 'report'));
}else{
    $r_form = new CForm();
    $cnfCmb = new CComboBox('config', $config, 'submit();');
    $cnfCmb->addItem(BR_DISTRIBUTION_MULTIPLE_PERIODS, _('Distribution of values for multiple periods'));
    $cnfCmb->addItem(BR_DISTRIBUTION_MULTIPLE_ITEMS, _('Distribution of values for multiple items'));
    $cnfCmb->addItem(BR_COMPARE_VALUE_MULTIPLE_PERIODS, _('Compare values for multiple periods'));
    $r_form->setAttribute('action','report_compare.php');
    $r_form->addItem(array(_('Reports').SPACE, $cnfCmb));

    $rep6_wdgt->addPageHeader(_('Bar reports'));
    $rep6_wdgt->addHeader(_('Report'), $r_form);
    $rep6_wdgt->addItem(BR());
    switch ($config) {
        default:
        case BR_DISTRIBUTION_MULTIPLE_PERIODS:
            $rep_form = valueDistributionFormForMultiplePeriods($validItems);
            break;
        case BR_DISTRIBUTION_MULTIPLE_ITEMS:
            $rep_form = valueDistributionFormForMultipleItems($validItems, $validPeriods);
            break;
        case BR_COMPARE_VALUE_MULTIPLE_PERIODS:
            $rep_form = valueComparisonFormForMultiplePeriods();
            break;
    }
    $rep_form->setAttribute('action','report_compare.php');
    $rep6_wdgt->addFlicker($rep_form, CProfile::get('web.report6.filter.state', BR_DISTRIBUTION_MULTIPLE_PERIODS));
}

$outer_table = new CTable();

$outer_table->setAttribute('border', 0);
$outer_table->setAttribute('width', '100%');

$outer_table->setCellPadding(1);
$outer_table->setCellSpacing(1);

$tmp_row = new CRow($rep_tab);
$tmp_row->setAttribute('align', 'center');

$outer_table->addRow($tmp_row);

$rep6_wdgt->addItem($outer_table);
$rep6_wdgt->show();

require_once dirname(__FILE__).'/include/page_footer.php';