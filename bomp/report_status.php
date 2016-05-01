<?php
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/triggers.inc.php';
require_once dirname(__FILE__).'/include/services.inc.php';
$page['title']=utf8_decode(getRequest('title','服务可用性报告'));
$page['file']='srv_status.php';
$page['hist_arg']=array();
if(hasRequest('print')&&getRequest('print')==1){
    $post=array('title','linkman','department','telphone','description','introduction','problem');
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie zbx_sessionid '.$_COOKIE['zbx_sessionid'].' --cookie tree_admin_service_status_tree 1';
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequest($val).'\'';
    }
    $cmd.=' http://127.0.0.1/bomp/report_status.php /var/www/new_zabbix/output/status.pdf';
    exec($cmd);
    ob_clean();
    if(file_exists('/var/www/new_zabbix/output/status.pdf')){
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename="服务可用性报告'.date('YmdHi').'.pdf"');
        readfile('/var/www/new_zabbix/output/status.pdf');
    }else{
        echo 'Cannot read file.';
    }
    exit;
}
require_once dirname(__FILE__).'/include/page_header.php';
$periods = array(
    'today' => _('Today'),
    'week' => _('This week'),
    'month' => _('This month'),
    'year' => _('This year'),
    24 => _('Last 24 hours'),
    24 * 7 => _('Last 7 days'),
    24 * 30 => _('Last 30 days'),
    24 * DAY_IN_YEAR => _('Last 365 days')
);
$period = getRequest('period', 7 * 24);
$period_end = time();

switch ($period) {
    case 'today':
        $period_start = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        break;
    case 'week':
        $period_start = strtotime('last sunday');
        break;
    case 'month':
        $period_start = mktime(0, 0, 0, date('n'), 1, date('Y'));
        break;
    case 'year':
        $period_start = mktime(0, 0, 0, 1, 1, date('Y'));
        break;
    case 24:
    case 24 * 7:
    case 24 * 30:
    case 24 * DAY_IN_YEAR:
        $period_start = $period_end - ($period * 3600);
        break;
}

// fetch services
$services = API::Service()->get(array(
    'output' => array('name', 'serviceid', 'showsla', 'goodsla', 'algorithm'),
    'selectParent' => array('serviceid'),
    'selectDependencies' => array('servicedownid', 'soft', 'linkid'),
    'selectTrigger' => array('description', 'triggerid', 'expression'),
    'preservekeys' => true,
    'sortfield' => 'sortorder',
    'sortorder' => ZBX_SORT_UP
));

// expand trigger descriptions
$triggers = zbx_objectValues($services, 'trigger');
$triggers = CMacrosResolverHelper::resolveTriggerNames($triggers);

foreach ($services as &$service) {
    if ($service['trigger']) {
        $service['trigger'] = $triggers[$service['trigger']['triggerid']];
    }
}
unset($service);

// fetch sla
$slaData = API::Service()->getSla(array(
    'intervals' => array(array(
        'from' => $period_start,
        'to' => $period_end
    ))
));
// expand problem trigger descriptions
foreach ($slaData as &$serviceSla) {
    foreach ($serviceSla['problems'] as &$problemTrigger) {
        $problemTrigger['description'] = $triggers[$problemTrigger['triggerid']]['description'];
    }
    unset($problemTrigger);
}
unset($serviceSla);

$treeData = array();
createServiceMonitoringTree($services, $slaData, $period, $treeData);
$tree = new CServiceTree('service_status_tree',
    $treeData,
    array(
        'caption' => _('Service'),
        'status' => _('Status'),
        'reason' => _('Reason'),
        'sla' => _('Problem time'),
        'sla2' => nbsp(_('SLA').' / '._('Acceptable SLA'))
    )
);

if ($tree) {
    ?>
    <link href="/public/report.css" rel="stylesheet" type="text/css"/>
    <script src="/public/report.js" type="application/javascript"></script>
    <form method="post" target="_blank">
        <h1 class="report-title"><?php if(hasRequest('title')){echo $page['title'];}else{?><input type="text" name="title" value="服务可用性报告"><?php }?></h1>
        <input type="hidden" name="print" value="1">
        <table class="table">
            <thead>
            <tr>
                <th colspan="1">生成时间</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_TIME_FORMAT_SECONDS);?></td>
                <th colspan="1">统计时段</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_FORMAT,$period_start),'到',zbx_date2str(DATE_FORMAT,$period_end);if(!hasRequest('linkman')){?><button type="submit">导出PDF</button><?php }?></td>
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
    $srv_wdgt = new CWidget('hat_services', 'service-list service-mon');
    if(!hasRequest('linkman')){
        $srv_wdgt->addPageHeader(_('IT SERVICES'));
        // creates form for choosing a preset interval
        $r_form = new CForm();
        $r_form->setAttribute('action','report_status.php');
        $r_form->setAttribute('class', 'nowrap');
        $r_form->setMethod('get');
        $r_form->setAttribute('name', 'period_choice');
        $r_form->addVar('fullscreen', $_REQUEST['fullscreen']);

        $period_combo = new CComboBox('period', $period, 'javascript: submit();');
        foreach ($periods as $key => $val) {
            $period_combo->addItem($key, $val);
        }
        $r_form->addItem(array(_('Period').SPACE, $period_combo));
        $srv_wdgt->addHeader(_('IT services'), $r_form);
    }
    $srv_wdgt->addItem(BR());
    $srv_wdgt->addItem($tree->getHTML());
    $srv_wdgt->show();
    $table = new CTable(null, 'chart');
    foreach($services as &$val){
        $table->addItem(BR());
        $div=new CDiv(new CImg('chart5.php?serviceid='.$val['serviceid'].url_param('path')));
        $div->addStyle('text-align:center');
        $table->addItem($div);
    }
    $table->show();
}
else {
    error(_('Cannot format Tree. Check logic structure in service links.'));
}
require_once dirname(__FILE__).'/include/page_footer.php';