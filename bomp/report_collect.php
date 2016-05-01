<?php
define('ZBX_PAGE_NO_MENU',1);
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/blocks.inc.php';
require_once dirname(__FILE__).'/include/triggers.inc.php';
$page['title']=utf8_decode(getRequest('title','运维汇总报告'));
$page['file']='dashboard.php';
$page['type']=detect_page_type(PAGE_TYPE_HTML);

if(hasRequest('print')&&getRequest('print')==1){
    $post=array('title','linkman','department','telphone','description','introduction','problem');
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie zbx_sessionid '.$_COOKIE['zbx_sessionid'];
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequest($val).'\'';
    }
    $cmd.=' http://127.0.0.1/bomp/report_collect.php /var/www/new_zabbix/output/collect.pdf';
    exec($cmd);
    ob_clean();
    if(file_exists('/var/www/new_zabbix/output/collect.pdf')){
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename="运维汇总报告'.date('YmdHi').'.pdf"');
        readfile('/var/www/new_zabbix/output/collect.pdf');
    }else{
        echo 'Cannot read file.';
    }
    exit;
}
require_once dirname(__FILE__).'/include/page_header.php';
/*
 * Filter
 */
$dashboardConfig = array(
    'groupids' => null,
    'maintenance' => null,
    'severity' => null,
    'extAck' => 0
);
/*
 * Actions
 */
if ($page['type']==PAGE_TYPE_JS||$page['type']==PAGE_TYPE_HTML_BLOCK) {
    require_once dirname(__FILE__).'/include/page_footer.php';
    exit;
}
/*
 * Display
 */
?>
<link href="/public/report.css" rel="stylesheet" type="text/css"/>
<script src="/public/report.js" type="application/javascript"></script>
<form method="post" target="_blank">
    <h1 class="report-title"><?php if(hasRequest('title')){echo $page['title'];}else{?><input type="text" name="title" value="运维汇总报告"><?php }?></h1>
    <input type="hidden" name="print" value="1">
    <table class="table">
        <thead>
        <tr>
            <th colspan="1">生成时间</th>
            <td colspan="5"><?php echo zbx_date2str(DATE_TIME_FORMAT_SECONDS);if(!hasRequest('linkman')){?><button type="submit">导出PDF</button><?php }?></td>
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
$dashboardWidget=new CWidget(null,'dashboard');
$date='已更新: '.date('H:i:s');
//系统状态
$systemStatus=new CUiWidget(WIDGET_SYSTEM_STATUS,new CDiv(make_system_status($dashboardConfig)),'textcolorstyles');
$systemStatus->setHeader(_('System status'));
$systemStatus->setFooter(new CDiv($date,'textwhite',WIDGET_SYSTEM_STATUS.'_footer'));
$dashboardWidget->addItem($systemStatus);
$dashboardWidget->addItem(BR());
//主机状态
$hostStatus=new CUiWidget(WIDGET_HOST_STATUS,new CDiv(make_hoststat_summary($dashboardConfig)),'textcolorstyles');
$hostStatus->setHeader(_('Host status'));
$hostStatus->setFooter(new CDiv($date,'textwhite', WIDGET_HOST_STATUS.'_footer'));
$dashboardWidget->addItem($hostStatus);
$dashboardWidget->addItem(BR());
// 最近20个议题
$lastIssues=new CUiWidget(WIDGET_LAST_ISSUES,new CDiv(make_latest_issues($dashboardConfig)),'textcolorstyles');
$lastIssues->setHeader(_n('Last %1$d issue', 'Last %1$d issues',DEFAULT_LATEST_ISSUES_CNT));
$lastIssues->setFooter(new CDiv($date,'textwhite',WIDGET_LAST_ISSUES.'_footer'));
$dashboardWidget->addItem($lastIssues);
$dashboardWidget->addItem(BR());
//触发器
$table = new CTableInfo(_('No triggers found.'));
$table->setHeader(array(
    _('Host'),
    _('Trigger'),
    _('Severity'),
    _('Number of status changes')
));
$triggersEventCount = array();
// get 100 triggerids with max event count
$sql = 'SELECT e.objectid,count(distinct e.eventid) AS cnt_event'.
    ' FROM triggers t,events e'.
    ' WHERE t.triggerid=e.objectid'.
    ' AND e.source='.EVENT_SOURCE_TRIGGERS.
    ' AND e.object='.EVENT_OBJECT_TRIGGER.
    ' AND e.clock>'.(time()-SEC_PER_WEEK);//每周
// add permission filter
if (CWebUser::getType() != USER_TYPE_SUPER_ADMIN) {
    $userid = CWebUser::$data['userid'];
    $userGroups = getUserGroupsByUserId($userid);
    $sql .= ' AND EXISTS ('.
        'SELECT NULL'.
        ' FROM functions f,items i,hosts_groups hgg'.
        ' JOIN rights r'.
        ' ON r.id=hgg.groupid'.
        ' AND '.dbConditionInt('r.groupid', $userGroups).
        ' WHERE t.triggerid=f.triggerid'.
        ' AND f.itemid=i.itemid'.
        ' AND i.hostid=hgg.hostid'.
        ' GROUP BY f.triggerid'.
        ' HAVING MIN(r.permission)>'.PERM_DENY.')';
}
$sql .= ' AND '.dbConditionInt('t.flags', array(ZBX_FLAG_DISCOVERY_NORMAL, ZBX_FLAG_DISCOVERY_CREATED)).
    ' GROUP BY e.objectid'.
    ' ORDER BY cnt_event desc';
$result = DBselect($sql, 100);
while ($row = DBfetch($result)) {
    $triggersEventCount[$row['objectid']] = $row['cnt_event'];
}
$triggers = API::Trigger()->get(array(
    'triggerids' => array_keys($triggersEventCount),
    'output' => array('triggerid', 'description', 'expression', 'priority', 'flags', 'url', 'lastchange'),
    'selectHosts' => array('hostid', 'status', 'name'),
    'selectItems' => array('itemid', 'hostid', 'name', 'key_', 'value_type'),
    'expandDescription' => true,
    'preservekeys' => true,
    'nopermissions' => true
));
$hostIds = array();
foreach ($triggers as $triggerId => $trigger) {
    $hostIds[$trigger['hosts'][0]['hostid']] = $trigger['hosts'][0]['hostid'];
    $triggers[$triggerId]['cnt_event'] = $triggersEventCount[$triggerId];
}
CArrayHelper::sort($triggers, array(
    array('field' => 'cnt_event', 'order' => ZBX_SORT_DOWN),
    'host', 'description', 'priority'
));
$hosts = API::Host()->get(array(
    'output' => array('hostid', 'status'),
    'hostids' => $hostIds,
    'selectGraphs' => API_OUTPUT_COUNT,
    'selectScreens' => API_OUTPUT_COUNT,
    'preservekeys' => true
));
$scripts = API::Script()->getScriptsByHosts($hostIds);
foreach ($triggers as $trigger) {
    $hostId = $trigger['hosts'][0]['hostid'];
    $hostName = new CSpan($trigger['hosts'][0]['name'],
        'link_menu menu-host'.(($hosts[$hostId]['status'] == HOST_STATUS_NOT_MONITORED) ? ' not-monitored' : '')
    );
    $hostName->setMenuPopup(CMenuPopupHelper::getHost($hosts[$hostId], $scripts[$hostId]));
    $triggerDescription = new CSpan($trigger['description'], 'link_menu');
    $triggerDescription->setMenuPopup(CMenuPopupHelper::getTrigger($trigger));
    $table->addRow(array(
        $hostName,
        $triggerDescription,
        getSeverityCell($trigger['priority']),
        $trigger['cnt_event']
    ));
}
$rprt_wdgt=new CUiWidget('tigger',$table,'textcolorstyles');
$rprt_wdgt->setHeader(_('MOST BUSY TRIGGERS TOP 100'));
$rprt_wdgt->setFooter(new CDiv($date,'textwhite', WIDGET_HOST_STATUS.'_footer'));
$dashboardWidget->addItem($rprt_wdgt);
$dashboardWidget->show();
require_once dirname(__FILE__).'/include/page_footer.php';