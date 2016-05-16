<?php
require_once dirname(__FILE__).'/include/config.inc.php';
$page['title']=utf8_decode(getRequest('title','设备运行情况前'.getRequest('limit',10).'排名报告'));
$page['file'] = 'latest.php';
$page['type'] = detect_page_type(PAGE_TYPE_HTML);
$page['scripts'] = array('multiselect.js');
if(hasRequest('print')&&getRequest('print')==1){
    $post=array('title','linkman','department','telphone','description','introduction','problem');
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie zbx_sessionid '.$_COOKIE['zbx_sessionid'];
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequest($val).'\'';
    }
    $cmd.=' http://127.0.0.1/bomp/report_top.php /var/www/new_zabbix/output/top.pdf';
    exec($cmd);
    ob_clean();
    header('Content-type:application/pdf');
    header('Content-Disposition:attachment;filename="设备运行情况报告'.date('YmdHi').'.pdf"');
    readfile('/var/www/new_zabbix/output/top.pdf');
    exit;
}
require_once dirname(__FILE__).'/include/page_header.php';
if (hasRequest('filter_set')) {
    CProfile::updateArray('web.top.filter.groupids', getRequest('groupids', array()), PROFILE_TYPE_STR);
    CProfile::update('web.top.filter.limit',getRequest('limit',10),PROFILE_TYPE_INT);
}elseif (hasRequest('filter_rst')){
    CProfile::deleteIdx('web.top.filter.groupids');
    CProfile::delete('web.top.filter.limit');
}
$filter=array(
    'groupids' => CProfile::getArray('web.top.filter.groupids'),
    'limit' => CProfile::get('web.top.filter.limit',10)
);
$multiSelectHostGroupData = array();
if ($filter['groupids'] !== null) {
    $filterGroups = API::HostGroup()->get(array(
        'output' => array('groupid', 'name'),
        'groupids' => $filter['groupids']
    ));
    foreach ($filterGroups as $group) {
        $multiSelectHostGroupData[] = array(
            'id' => $group['groupid'],
            'name' => $group['name']
        );
    }
}
?>
<link href="/public/report.css" rel="stylesheet" type="text/css"/>
<script src="/public/report.js" type="application/javascript"></script>
<form method="post" target="_blank">
    <h1 class="report-title"><?php if(hasRequest('title')){echo $page['title'];}else{?><input type="text" name="title" value="设备运行情况前<?php echo $filter['limit']?>排名报告"><?php }?></h1>
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
$widget=new CWidget(null,'latest-mon');
if(!hasRequest('linkman')){
    $widget->addPageHeader('设备运行情况排名');
    $widget->addHeader(BR());
    $filterForm = new CForm('get','report_top.php');
    $filterForm->setAttribute('name','zbx_filter');
    $filterForm->setAttribute('id', 'zbx_filter');
    $filterTable=new CTable(null,'filter');
    $filterTable->setCellPadding(0);
    $filterTable->setCellSpacing(0);
    $filterTable->setAlign("left");
    $filterTable->addRow(
        array(
            new CCol(bold(_('Host groups').':')),
            new CCol(new CMultiSelect(
                array(
                    'name' => 'groupids[]',
                    'objectName' => 'hostGroup',
                    'data' => $multiSelectHostGroupData,
                    'popup' => array(
                        'parameters' => 'srctbl=host_groups&dstfrm='.$filterForm->getName().'&dstfld1=groupids_&srcfld1=groupid&multiselect=1',
                        'width' => 450,
                        'height' => 450,
                        'buttonClass' => 'input filter-multiselect-select-button'
                    )
                ))
            ),
            new CCol(bold('筛选数量(建议不超过20):')),
            new CCol(new CTextBox('limit',$filter['limit'],40))
        )
    );
    $filterButton = new CSubmit('filter_set', _('Filter'), 'chkbxRange.clearSelectedOnFilterChange();');
    $filterButton->useJQueryStyle();
    $resetButton = new CSubmit('filter_rst', _('Reset'), 'chkbxRange.clearSelectedOnFilterChange();');
    $resetButton->useJQueryStyle();
    $divButtons = new CDiv(array($filterButton,SPACE,$resetButton));
    $divButtons->setAttribute('style', 'padding: 4px 0px;');
    $filterTable->addRow(new CCol($divButtons, 'controls', 6));
    $filterForm->addItem($filterTable);
    $widget->addFlicker($filterForm,true);
    $widget->addItem(BR());
}
if(count($filter['groupids'])>0){
    //CPU排名
    $CPUTable=new CTableInfo('暂无监控主机');
    $CPUTable->setHeader(array(
        SPACE,
        '服务器名称',
        '项目',
        '时间',
        'CPU占用'
    ));
    $items=DBfetchArray(DBselect('SELECT host,history.hostid,history.itemid,history.name,value,clock,key_ FROM (
SELECT itemid,CONCAT(\'1-\',items.name) AS name,hostid,TRUNCATE(100-value,2) AS value,clock,key_ FROM cache_history JOIN items USING (itemid) WHERE key_ IN (\'system.stat[cpu,id]\',\'system.cpu.util[,idle]\')
UNION ALL SELECT itemid,items.name,hostid,TRUNCATE(value,2),clock,key_ FROM cache_history JOIN items USING (itemid) WHERE key_=\'perf_counter[\\\\Processor(_Total)\\\\% Processor Time]\') AS history
JOIN hosts USING (hostid) JOIN hosts_groups USING (hostid) WHERE groupid IN ('.implode(',',$filter['groupids']).') ORDER BY value DESC LIMIT '.$filter['limit']));
    $items=CMacrosResolverHelper::resolveItemNames($items);
    foreach($items as $key=>&$val){
        $CPUTable->addRow(array(
            $key+1,
            new CLink($val['host'],'latest.php?filter_set=1&hostids[]='.$val['hostid'],'action'),
            $val['name_expanded'],
            zbx_date2str(DATE_TIME_FORMAT_SECONDS,$val['clock']),
            $val['value'].'%'
        ));
    }
    $CPUWidget=new CUiWidget('cpu_widget',$CPUTable);
    $CPUWidget->setHeader('CPU占用排名');
    $widget->addItem($CPUWidget);
    $widget->addItem(BR());
    //内存排名
    $MemoryTable=new CTableInfo('暂无监控主机');
    $MemoryTable->setHeader(array(
        SPACE,
        '服务器名称',
        '项目',
        '时间',
        '内存占用'
    ));
    //内存排名
    if($filter['limit']>0){
        $items=DBfetchArray(DBselect('SELECT host,hostid,cache_history_uint.itemid,TRUNCATE((1-cache_history_uint.value/mem_total.value)*100,2) AS value,cache_history_uint.clock FROM cache_history_uint JOIN items USING (itemid) JOIN (SELECT hostid,value FROM cache_history_uint JOIN items USING (itemid) WHERE key_=\'vm.memory.size[total]\') AS mem_total USING (hostid) JOIN hosts USING (hostid) JOIN hosts_groups USING (hostid) WHERE key_ IN (\'vm.memory.size[available]\',\'vm.memory.size[free]\') AND groupid IN ('.implode(',',$filter['groupids']).') ORDER BY value DESC LIMIT '.$filter['limit']));
        foreach($items as $key=>&$val){
            $MemoryTable->addRow(array(
                $key+1,
                new CLink($val['host'],'latest.php?filter_set=1&hostids[]='.$val['hostid'],'action'),
                'Available memory/Total memory',
                zbx_date2str(DATE_TIME_FORMAT_SECONDS,$val['clock']),
                $val['value'].'%'
            ));
        }
    }
    $MemoryWidget=new CUiWidget('memory_widget',$MemoryTable);
    $MemoryWidget->setHeader('内存占用排名');
    $widget->addItem($MemoryWidget);
    $widget->addItem(BR());
    $DiskTable=new CTableInfo('暂无监控主机');
    $DiskTable->setHeader(array(
        SPACE,
        '服务器名称',
        '项目',
        '时间',
        '磁盘占用'
    ));
    $items=DBfetchArray(DBselect('SELECT host,history.hostid,history.itemid,value,clock FROM (
SELECT itemid,hostid,TRUNCATE((1-SUM(cache_history_uint.value)/disk_total.value)*100,2) AS value,cache_history_uint.clock FROM cache_history_uint JOIN items USING (itemid) JOIN (SELECT hostid,SUM(value) AS value FROM cache_history_uint JOIN items USING (itemid) WHERE key_ LIKE \'vfs.fs.size[%,total]\' GROUP BY hostid) AS disk_total USING (hostid) WHERE key_ LIKE \'vfs.fs.size[%,free]\' GROUP BY hostid
) AS history JOIN hosts USING (hostid) JOIN hosts_groups USING (hostid) WHERE groupid IN ('.implode(',',$filter['groupids']).') ORDER BY value DESC LIMIT '.$filter['limit']));
    foreach($items as $key=>&$val){
        $DiskTable->addRow(array(
            $key+1,
            new CLink($val['host'],'latest.php?filter_set=1&hostids[]='.$val['hostid'],'action'),
            '1-Free disk space on server',
            zbx_date2str(DATE_TIME_FORMAT_SECONDS,$val['clock']),
            $val['value'].'%'
        ));
    }
    $DiskWidget=new CUiWidget('cpu_widget',$DiskTable);
    $DiskWidget->setHeader('磁盘占用排名');
    $widget->addItem($DiskWidget);
}
$widget->show();
require_once dirname(__FILE__).'/include/page_footer.php';
function compare($a,$b){
    if($a['value']==$b['value']){
        return 0;
    }else{
        return $a['value']<$b['value']?-1:1;
    }
}