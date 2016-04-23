<?php
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/hosts.inc.php';
require_once dirname(__FILE__).'/include/events.inc.php';
require_once dirname(__FILE__).'/include/actions.inc.php';
require_once dirname(__FILE__).'/include/discovery.inc.php';
require_once dirname(__FILE__).'/include/html.inc.php';
if (hasRequest('csv_export')) {
    $csvExport = true;
    $csvRows = array();

    $page['type'] = detect_page_type(PAGE_TYPE_CSV);
    $page['file'] = 'zbx_events_export.csv';

    require_once dirname(__FILE__).'/include/func.inc.php';
}else if(hasRequest('print')){
    $post=array('linkman','department','telphone','description','introduction','problem');
    $cmd='/usr/local/bin/wkhtmltopdf';
    $cmd.=' --cookie zbx_sessionid '.$_COOKIE['zbx_sessionid'];
    foreach($post as &$val){
        $cmd.=' --post '.$val.' \''.getRequest($val).'\'';
    }
    $cmd.=' http://127.0.0.1/bomp/report_event.php /var/www/new_zabbix/output/event.pdf';
    exec($cmd);
    ob_clean();
    if(is_file('/var/www/new_zabbix/output/event.pdf')){
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename="事件响应报告'.date('YmdHi').'.pdf"');
        readfile('/var/www/new_zabbix/output/event.pdf');
    }else{
        echo 'Cannot read file.';
    }
    exit;
}else{
    $csvExport = false;
    $page['title'] ='事件响应报告';
    $page['file'] = 'events.php';
    $page['hist_arg'] = array('groupid', 'hostid');
    $page['scripts'] = array('class.calendar.js', 'gtlc.js','multiselect.js');
    $page['type'] = detect_page_type(PAGE_TYPE_HTML);
}

require_once dirname(__FILE__).'/include/page_header.php';

$allow_discovery = check_right_on_discovery();
$allowed_sources[] = EVENT_SOURCE_TRIGGERS;
if ($allow_discovery) {
    $allowed_sources[] = EVENT_SOURCE_DISCOVERY;
}

/*
 * Permissions
 */
if (getRequest('groupid') && !API::HostGroup()->isReadable(array(getRequest('groupid')))) {
    access_deny();
}
if (getRequest('hostid') && !API::Host()->isReadable(array(getRequest('hostid')))) {
    access_deny();
}
if (getRequest('triggerid') && !API::Trigger()->isReadable(array(getRequest('triggerid')))) {
    access_deny();
}

/*
 * Ajax
 */
if (hasRequest('filterState')) {
    CProfile::update('web.events.filter.state', getRequest('filterState'), PROFILE_TYPE_INT);
}
if (hasRequest('favobj')) {
    // saving fixed/dynamic setting to profile
    if ('timelinefixedperiod' == getRequest('favobj')) {
        if (hasRequest('favid')) {
            CProfile::update('web.events.timelinefixed', getRequest('favid'), PROFILE_TYPE_INT);
        }
    }
}

if ($page['type'] == PAGE_TYPE_JS || $page['type'] == PAGE_TYPE_HTML_BLOCK) {
    require_once dirname(__FILE__).'/include/page_footer.php';
    exit;
}

$source = getRequest('source', CProfile::get('web.events.source', EVENT_SOURCE_TRIGGERS));

/*
 * Filter
 */
if (hasRequest('filter_set')) {
    CProfile::update('web.events.filter.triggerid', getRequest('triggerid', 0), PROFILE_TYPE_ID);
    CProfile::update('web.events.filter.limit',getRequest('limit',50),PROFILE_TYPE_INT);
    CProfile::update('web.events.filter.acknowledge',getRequest('acknowledge',-1),PROFILE_TYPE_INT);
    CProfile::update('web.events.filter.status',getRequest('status',-1),PROFILE_TYPE_INT);
    CProfile::update('web.events.filter.priority',getRequest('priority',0),PROFILE_TYPE_INT);
    CProfile::update('web.events.filter.priority',getRequest('priority',0),PROFILE_TYPE_INT);
    CProfile::updateArray('web.events.filter.hostids',getRequest('hostids',array()),PROFILE_TYPE_STR);
}
elseif (hasRequest('filter_rst')) {
    DBStart();
    CProfile::delete('web.events.filter.triggerid');
    CProfile::delete('web.events.filter.limit');
    CProfile::delete('web.events.filter.acknowledge');
    CProfile::delete('web.events.filter.status');
    CProfile::delete('web.events.filter.priority');
    CProfile::deleteIdx('web.events.filter.groupids');
    CProfile::deleteIdx('web.events.filter.hostids');
    DBend();
}

$triggerId = CProfile::get('web.events.filter.triggerid', 0);
$filter=array(
    'limit'=>intval(CProfile::get('web.events.filter.limit',50)),
    'acknowledge'=>intval(CProfile::get('web.events.filter.acknowledge',-1)),
    'status'=>intval(CProfile::get('web.events.filter.status',-1)),
    'priority'=>intval(CProfile::get('web.events.filter.priority',0)),
    'hostids'=>CProfile::getArray('web.events.filter.hostids'),
);

if(isset($filter['hostids'][0])){
    $filter['groupids']=null;
    CProfile::deleteIdx('web.events.filter.groupids');
}else{
    CProfile::updateArray('web.events.filter.groupids',getRequest('groupids',array()),PROFILE_TYPE_STR);
    $filter['groupids']=CProfile::getArray('web.events.filter.groupids');
}
if($filter['acknowledge']<0)$filter['acknowledge']=null;
if($filter['status']<0)$filter['status']=null;
CWebUser::$data['rows_per_page']=$filter['limit'];
$line_num=0;

CProfile::update('web.events.source', $source, PROFILE_TYPE_INT);

// calculate stime and period
if ($csvExport) {
    $period = getRequest('period', ZBX_PERIOD_DEFAULT);

    if (hasRequest('stime')) {
        $stime = getRequest('stime');

        if ($stime + $period > time()) {
            $stime = date(TIMESTAMP_FORMAT, time() - $period);
        }
    }
    else {
        $stime = date(TIMESTAMP_FORMAT, time() - $period);
    }
}
else {
    $sourceName = ($source == EVENT_OBJECT_TRIGGER) ? 'trigger' : 'discovery';

    if (hasRequest('period')) {
        $_REQUEST['period'] = getRequest('period', ZBX_PERIOD_DEFAULT);
        CProfile::update('web.events.'.$sourceName.'.period', getRequest('period'), PROFILE_TYPE_INT);
    }
    else {
        $_REQUEST['period'] = CProfile::get('web.events.'.$sourceName.'.period');
    }

    $period = navigation_bar_calc();
    $stime = getRequest('stime');
}

$from = zbxDateToTime($stime);
$till = $from + $period;

/*
 * Display
 */
if ($csvExport) {
    if (!hasRequest('hostid')) {
        $_REQUEST['hostid'] = 0;
    }
    if (!hasRequest('groupid')) {
        $_REQUEST['groupid'] = 0;
    }
}
else {
    if ($source == EVENT_SOURCE_TRIGGERS) {

        // try to find matching trigger when host is changed
        // use the host ID from the page filter since it may not be present in the request
        // if all hosts are selected, preserve the selected trigger
        if ($triggerId != 0 && isset($filter['hostids'][0])) {

            $oldTriggers = API::Trigger()->get(array(
                'output' => array('triggerid', 'description', 'expression'),
                'selectHosts' => array('hostid', 'host'),
                'selectItems' => array('itemid', 'hostid', 'key_', 'type', 'flags', 'status'),
                'selectFunctions' => API_OUTPUT_EXTEND,
                'triggerids' => $triggerId
            ));
            $oldTrigger = reset($oldTriggers);

            $oldTrigger['hosts'] = zbx_toHash($oldTrigger['hosts'], 'hostid');

            // if the trigger doesn't belong to the selected host - find a new one on that host
            foreach($filter['hostids'] as &$val){
                if (!isset($oldTrigger['hosts'][$val])) {
                    $triggerId = 0;

                    $oldTrigger['items'] = zbx_toHash($oldTrigger['items'], 'itemid');
                    $oldTrigger['functions'] = zbx_toHash($oldTrigger['functions'], 'functionid');
                    $oldExpression = triggerExpression($oldTrigger);

                    $newTriggers = API::Trigger()->get(array(
                        'output' => array('triggerid', 'description', 'expression'),
                        'selectHosts' => array('hostid', 'host'),
                        'selectItems' => array('itemid', 'key_'),
                        'selectFunctions' => API_OUTPUT_EXTEND,
                        'filter' => array('description' => $oldTrigger['description']),
                        'hostids' => $filter['hostids']
                    ));

                    foreach ($newTriggers as $newTrigger) {
                        if (count($oldTrigger['items']) != count($newTrigger['items'])) {
                            continue;
                        }

                        $newTrigger['items'] = zbx_toHash($newTrigger['items'], 'itemid');
                        $newTrigger['hosts'] = zbx_toHash($newTrigger['hosts'], 'hostid');
                        $newTrigger['functions'] = zbx_toHash($newTrigger['functions'], 'functionid');

                        $found = false;
                        foreach ($newTrigger['functions'] as $fnum => $function) {
                            foreach ($oldTrigger['functions'] as $ofnum => $oldFunction) {
                                // compare functions
                                if (($function['function'] != $oldFunction['function']) || ($function['parameter'] != $oldFunction['parameter'])) {
                                    continue;
                                }
                                // compare that functions uses same item keys
                                if ($newTrigger['items'][$function['itemid']]['key_'] != $oldTrigger['items'][$oldFunction['itemid']]['key_']) {
                                    continue;
                                }
                                // rewrite itemid so we could compare expressions
                                // of two triggers form different hosts
                                $newTrigger['functions'][$fnum]['itemid'] = $oldFunction['itemid'];
                                $found = true;

                                unset($oldTrigger['functions'][$ofnum]);
                                break;
                            }
                            if (!$found) {
                                break;
                            }
                        }
                        if (!$found) {
                            continue;
                        }

                        // if we found same trigger we overwriting it's hosts and items for expression compare
                        $newTrigger['hosts'] = $oldTrigger['hosts'];
                        $newTrigger['items'] = $oldTrigger['items'];

                        $newExpression = triggerExpression($newTrigger);

                        if (strcmp($oldExpression, $newExpression) == 0) {
                            CProfile::update('web.events.filter.triggerid', $newTrigger['triggerid'], PROFILE_TYPE_ID);
                            $triggerId = $newTrigger['triggerid'];
                            break;
                        }
                    }
                }
            }
        }
    }
    $eventsWidget = new CWidget();
    if(!hasRequest('linkman')){
        $csvDisabled = true;
        $eventsWidget->addPageHeader(_('HISTORY OF EVENTS').SPACE.'['.zbx_date2str(DATE_TIME_FORMAT_SECONDS).']');

        $headerForm = new CForm('get');
        $headerForm->addVar('fullscreen', getRequest('fullscreen'));
        $headerForm->addVar('stime', $stime);
        $headerForm->addVar('period', $period);

        // add host and group filters to the form
        if ($source == EVENT_SOURCE_TRIGGERS) {
            if (getRequest('triggerid') != 0) {
                $headerForm->addVar('triggerid', getRequest('triggerid'), 'triggerid_filter');
            }
        }

        if ($allow_discovery) {
            $cmbSource = new CComboBox('source', $source, 'submit()');
            $cmbSource->addItem(EVENT_SOURCE_TRIGGERS, _('Trigger'));
            $cmbSource->addItem(EVENT_SOURCE_DISCOVERY, _('Discovery'));
            $headerForm->addItem(array(SPACE._('Source').SPACE, $cmbSource));
        }
        $headerForm->setAttribute('action','report_event.php');
        $eventsWidget->addHeader(_('Events'), $headerForm);
        $eventsWidget->addHeaderRowNumber();

        $filterForm = null;

        if ($source == EVENT_SOURCE_TRIGGERS) {
            $filterForm = new CFormTable(null,'report_event.php','get');
            $filterForm->setTableClass('formtable old-filter');
            $filterForm->setAttribute('name', 'zbx_filter');
            $filterForm->setAttribute('id', 'zbx_filter');
            $filterForm->addVar('triggerid', $triggerId);
            $filterForm->addVar('stime', $stime);
            $filterForm->addVar('period', $period);

            if ($triggerId > 0) {
                $dbTrigger = API::Trigger()->get(array(
                    'triggerids' => $triggerId,
                    'output' => array('description', 'expression'),
                    'selectHosts' => array('name'),
                    'preservekeys' => true,
                    'expandDescription' => true
                ));
                if ($dbTrigger) {
                    $dbTrigger = reset($dbTrigger);
                    $host = reset($dbTrigger['hosts']);

                    $trigger = $host['name'].NAME_DELIMITER.$dbTrigger['description'];
                }
                else {
                    $triggerId = 0;
                }
            }
            if (!isset($trigger)) {
                $trigger = '';
            }
            //行数
            $filterForm->addRow(new CRow(array(
                new CCol('显示行数','form_row_c'),
                new CCol(new CTextBox('limit',$filter['limit'],50))
            )));
            //群组
            $multiSelectHostGroupData=array();
            if ($filter['groupids']!==null){
                $filterGroups=API::HostGroup()->get(array(
                    'output'=>array('groupid','name'),
                    'groupids'=>$filter['groupids']
                ));
                foreach ($filterGroups as $group){
                    $multiSelectHostGroupData[]=array(
                        'id'=>$group['groupid'],
                        'name'=>$group['name']
                    );
                }
            }
            $filterForm->addRow(new CRow(array(
                new CCol(_('Host groups'),'form_row_c'),
                new CCol(new CMultiSelect(array(
                    'name'=>'groupids[]',
                    'objectName'=>'hostGroup',
                    'data'=>$multiSelectHostGroupData,
                    'popup'=>array(
                        'parameters'=>'srctbl=host_groups&dstfrm='.$filterForm->getName().'&dstfld1=groupids_&srcfld1=groupid&multiselect=1',
                        'width'=>450,
                        'height'=>450,
                        'buttonClass'=>'input filter-multiselect-select-button'
                    )
                )))
            )));
            //主机
            $multiSelectHostData = array();
            if ($filter['hostids']){
                $filterHosts=API::Host()->get(array(
                    'output'=>array('hostid','name'),
                    'hostids'=>$filter['hostids']
                ));
                foreach ($filterHosts as $host) {
                    $multiSelectHostData[]=array(
                        'id'=>$host['hostid'],
                        'name'=>$host['name']
                    );
                }
            }
            $filterForm->addRow(new CRow(array(
                new CCol(_('Hosts'),'form_row_c'),
                new CCol(new CMultiSelect(array(
                    'name'=>'hostids[]',
                    'objectName'=>'hosts',
                    'data'=>$multiSelectHostData,
                    'popup'=>array(
                        'parameters'=>'srctbl=hosts&dstfrm='.$filterForm->getName().'&dstfld1=hostids_&srcfld1=hostid&real_hosts=1&multiselect=1',
                        'width'=>450,
                        'height'=>450,
                        'buttonClass'=>'input filter-multiselect-select-button'
                    )
                )))
            )));
            //触发器
            $filterForm->addRow(new CRow(array(
                new CCol(_('Trigger'), 'form_row_c'),
                new CCol(array(
                    new CTextBox('trigger', $trigger,50,true),
                    new CButton('btn1', _('Select'),
                        'return PopUp("popup.php?'.
                        'dstfrm='.$filterForm->getName().
                        '&dstfld1=triggerid'.
                        '&dstfld2=trigger'.
                        '&srctbl=triggers'.
                        '&srcfld1=triggerid'.
                        '&srcfld2=description'.
                        '&real_hosts=1'.
                        '&monitored_hosts=1'.
                        '&with_monitored_triggers=1'.
                        //($pageFilter->hostid ? '&only_hostid='.$pageFilter->hostid : '').
                        '");',
                        'T'
                    )
                ), 'form_row_r')
            )));
            //状态
            $statusComboBox=new CComboBox('status',getRequest('status'));
            $statusComboBox->addItem(-1,_('All'));
            $statusComboBox->addItem(0,_('Normal'));
            $statusComboBox->addItem(1,_('Problem'));
            $filterForm->addRow(new CRow(array(
                new CCOl(_('Status'),'form_row_c'),
                new CCol($statusComboBox)
            )));
            //严重性
            $priorityComboBox=new CComboBox('priority',$filter['priority']);
            $data=getSeverityCaption();
            foreach($data as $key=>&$val){
                $priorityComboBox->addItem($key,_($val));
            }
            $filterForm->addRow(new CRow(array(
                new CCOl(_('Severity'),'form_row_c'),
                new CCol($priorityComboBox)
            )));
            //知悉
            $ackComboBox=new CComboBox('acknowledge',getRequest('acknowledge'));
            $ackComboBox->addItem(-1,_('All'));
            $ackComboBox->addItem(0,'未知悉');
            $ackComboBox->addItem(1,_('Acknowledged'));
            $filterForm->addRow(new CRow(array(
                new CCOl(_('Acknowledges'),'form_row_c'),
                new CCol($ackComboBox)
            )));
            $filterForm->addItemToBottomRow(new CSubmit('filter_set', _('Filter')));
            $filterForm->addItemToBottomRow(new CSubmit('filter_rst', _('Reset')));
        }

        $eventsWidget->addFlicker($filterForm, CProfile::get('web.events.filter.state', 0));

        $scroll = new CDiv();
        $scroll->setAttribute('id', 'scrollbar_cntr');
        $eventsWidget->addFlicker($scroll, CProfile::get('web.events.filter.state', 0));
    }
    $table = new CTableInfo(_('No events found.'));
}

// trigger events
if ($source == EVENT_OBJECT_TRIGGER) {
    $firstEvent = API::Event()->get(array(
        'source' => EVENT_SOURCE_TRIGGERS,
        'object' => EVENT_OBJECT_TRIGGER,
        'output' => API_OUTPUT_EXTEND,
        'objectids' => $triggerId ? $triggerId : null,
        'sortfield' => array('clock'),
        'sortorder' => ZBX_SORT_UP,
        'limit' => 1
    ));
    $firstEvent = reset($firstEvent);
}

// discovery events
else {
    $firstEvent = API::Event()->get(array(
        'output' => API_OUTPUT_EXTEND,
        'source' => EVENT_SOURCE_DISCOVERY,
        'object' => EVENT_OBJECT_DHOST,
        'sortfield' => array('clock'),
        'sortorder' => ZBX_SORT_UP,
        'limit' => 1
    ));
    $firstEvent = reset($firstEvent);

    $firstDServiceEvent = API::Event()->get(array(
        'output' => API_OUTPUT_EXTEND,
        'source' => EVENT_SOURCE_DISCOVERY,
        'object' => EVENT_OBJECT_DSERVICE,
        'sortfield' => array('clock'),
        'sortorder' => ZBX_SORT_UP,
        'limit' => 1
    ));
    $firstDServiceEvent = reset($firstDServiceEvent);

    if ($firstDServiceEvent && (!$firstEvent || $firstDServiceEvent['eventid'] < $firstEvent['eventid'])) {
        $firstEvent = $firstDServiceEvent;
    }
}

$config = select_config();

// headers
if ($source == EVENT_SOURCE_DISCOVERY) {
    $header = array(
        _('Time'),
        _('IP'),
        _('DNS'),
        _('Description'),
        _('Status')
    );
    $table->setHeader($header);
}else{
    $header = array(
        _('Time'),
        (getRequest('hostid', 0) == 0) ? _('Host') : null,
        _('Description'),
        _('Status'),
        _('Severity'),
        _('Duration'),
        $config['event_ack_enable'] ? _('Ack') : null,
        _('Actions')
    );
    $table->setHeader($header);
}

if (!$firstEvent) {
    $starttime = null;

    if (!$csvExport) {
        $events = array();
        $paging = getPagingLine($events);
    }
}
else {
    $starttime = $firstEvent['clock'];

    if ($source == EVENT_SOURCE_DISCOVERY) {
        // fetch discovered service and discovered host events separately
        $dHostEvents = API::Event()->get(array(
            'source' => EVENT_SOURCE_DISCOVERY,
            'object' => EVENT_OBJECT_DHOST,
            'time_from' => $from,
            'time_till' => $till,
            'output' => array('eventid', 'object', 'objectid', 'clock', 'value'),
            'sortfield' => array('clock', 'eventid'),
            'sortorder' => ZBX_SORT_DOWN,
            'limit' => $config['search_limit'] + 1
        ));
        $dServiceEvents = API::Event()->get(array(
            'source' => EVENT_SOURCE_DISCOVERY,
            'object' => EVENT_OBJECT_DSERVICE,
            'time_from' => $from,
            'time_till' => $till,
            'output' => array('eventid', 'object', 'objectid', 'clock', 'value'),
            'sortfield' => array('clock', 'eventid'),
            'sortorder' => ZBX_SORT_DOWN,
            'limit' => $config['search_limit'] + 1
        ));
        $dsc_events = array_merge($dHostEvents, $dServiceEvents);
        CArrayHelper::sort($dsc_events, array(
            array('field' => 'clock', 'order' => ZBX_SORT_DOWN),
            array('field' => 'eventid', 'order' => ZBX_SORT_DOWN)
        ));
        $dsc_events = array_slice($dsc_events, 0, $config['search_limit'] + 1);

        $paging = getPagingLine($dsc_events);

        if (!$csvExport) {
            $csvDisabled = zbx_empty($dsc_events);
        }

        $objectids = array();
        foreach ($dsc_events as $event_data) {
            $objectids[$event_data['objectid']] = $event_data['objectid'];
        }

        // object dhost
        $dhosts = array();
        $res = DBselect(
            'SELECT s.dserviceid,s.dhostid,s.ip,s.dns'.
            ' FROM dservices s'.
            ' WHERE '.dbConditionInt('s.dhostid', $objectids)
        );
        while ($dservices = DBfetch($res)) {
            $dhosts[$dservices['dhostid']] = $dservices;
        }

        // object dservice
        $dservices = array();
        $res = DBselect(
            'SELECT s.dserviceid,s.ip,s.dns,s.type,s.port'.
            ' FROM dservices s'.
            ' WHERE '.dbConditionInt('s.dserviceid', $objectids)
        );
        while ($dservice = DBfetch($res)) {
            $dservices[$dservice['dserviceid']] = $dservice;
        }

        foreach ($dsc_events as $event_data) {
            switch ($event_data['object']) {
                case EVENT_OBJECT_DHOST:
                    if (isset($dhosts[$event_data['objectid']])) {
                        $event_data['object_data'] = $dhosts[$event_data['objectid']];
                    }
                    else {
                        $event_data['object_data']['ip'] = _('Unknown');
                        $event_data['object_data']['dns'] = _('Unknown');
                    }
                    $event_data['description'] = _('Host');
                    break;

                case EVENT_OBJECT_DSERVICE:
                    if (isset($dservices[$event_data['objectid']])) {
                        $event_data['object_data'] = $dservices[$event_data['objectid']];
                    }
                    else {
                        $event_data['object_data']['ip'] = _('Unknown');
                        $event_data['object_data']['dns'] = _('Unknown');
                        $event_data['object_data']['type'] = _('Unknown');
                        $event_data['object_data']['port'] = _('Unknown');
                    }

                    $event_data['description'] = _('Service').NAME_DELIMITER.
                        discovery_check_type2str($event_data['object_data']['type']).
                        discovery_port2str($event_data['object_data']['type'], $event_data['object_data']['port']);
                    break;

                default:
                    continue;
            }

            if (!isset($event_data['object_data'])) {
                continue;
            }

            if ($csvExport) {
                $csvRows[] = array(
                    zbx_date2str(DATE_TIME_FORMAT_SECONDS, $event_data['clock']),
                    $event_data['object_data']['ip'],
                    $event_data['object_data']['dns'],
                    $event_data['description'],
                    discovery_value($event_data['value'])
                );
            }
            else {
                $table->addRow(array(
                    zbx_date2str(DATE_TIME_FORMAT_SECONDS, $event_data['clock']),
                    $event_data['object_data']['ip'],
                    zbx_empty($event_data['object_data']['dns']) ? SPACE : $event_data['object_data']['dns'],
                    $event_data['description'],
                    new CCol(discovery_value($event_data['value']), discovery_value_style($event_data['value']))
                ));
            }
        }
    }

    // source not discovery i.e. trigger
    else{
        $knownTriggerIds = array();
        $validTriggerIds = array();

        $triggerOptions = array(
            'output' => array('triggerid'),
            'preservekeys' => true,
            'monitored' => true
        );

        $allEventsSliceLimit = $config['search_limit'];

        //严重性过滤
        if($filter['priority']>0){
            $object_id=API::Trigger()->get(array(
                'min_severity'=>$filter['priority'],
                'output'=>array('objectid')
            ));
            $object_id=zbx_objectValues($object_id,'triggerid');
        }else{
            $object_id=null;
        }
        
        $eventOptions = array(
            'source' => EVENT_SOURCE_TRIGGERS,
            'object' => EVENT_OBJECT_TRIGGER,
            'objectids'=>$object_id,
            'time_from' => $from,
            'time_till' => $till,
            'output' => array('eventid', 'objectid'),
            'sortfield' => array('clock', 'eventid'),
            'sortorder' => ZBX_SORT_DOWN,
            'acknowledged'=>$filter['acknowledge'],
            'value'=>$filter['status'],
            'limit' => $allEventsSliceLimit + 1
        );

        if($triggerId){
            $knownTriggerIds = array($triggerId => $triggerId);
            $validTriggerIds = $knownTriggerIds;
            $eventOptions['objectids'] = array($triggerId);
        }else if(isset($filter['hostids'][0])){
            $hostTriggers = API::Trigger()->get(array(
                'output' => array('triggerid'),
                'hostids' =>$filter['hostids'],
                'monitored' => true,
                'preservekeys' => true
            ));
            $filterTriggerIds = array_map('strval', array_keys($hostTriggers));
            $knownTriggerIds = array_combine($filterTriggerIds, $filterTriggerIds);
            $validTriggerIds = $knownTriggerIds;

            $eventOptions['hostids'] =$filter['hostids'];
            $eventOptions['objectids'] = $validTriggerIds;
        }else if(isset($filter['groupids'][0])){
            $eventOptions['groupids']=$triggerOptions['groupids']=$filter['groupids'];
        }
        
        $events = array();

        while (true) {
            $allEventsSlice = API::Event()->get($eventOptions);

            $triggerIdsFromSlice = array_keys(array_flip(zbx_objectValues($allEventsSlice, 'objectid')));

            $unknownTriggerIds = array_diff($triggerIdsFromSlice, $knownTriggerIds);

            if ($unknownTriggerIds) {
                $triggerOptions['triggerids'] = $unknownTriggerIds;
                $validTriggersFromSlice = API::Trigger()->get($triggerOptions);

                foreach ($validTriggersFromSlice as $trigger) {
                    $validTriggerIds[$trigger['triggerid']] = $trigger['triggerid'];
                }

                foreach ($unknownTriggerIds as $id) {
                    $id = strval($id);
                    $knownTriggerIds[$id] = $id;
                }
            }

            foreach ($allEventsSlice as $event) {
                if (isset($validTriggerIds[$event['objectid']])) {
                    $events[] = array('eventid' => $event['eventid']);
                }
            }

            // break loop when either enough events have been retrieved, or last slice was not full
            if (count($events) >= $config['search_limit'] || count($allEventsSlice) <= $allEventsSliceLimit) {
                break;
            }
            /*
             * Because events in slices are sorted descending by eventid (i.e. bigger eventid),
             * first event in next slice must have eventid that is previous to last eventid in current slice.
             */
            $lastEvent = end($allEventsSlice);
            $eventOptions['eventid_till'] = $lastEvent['eventid'] - 1;
        }

        /*
         * At this point it is possible that more than $config['search_limit'] events are selected,
         * therefore at most only first $config['search_limit'] + 1 events will be used for pagination.
         */
        $events = array_slice($events, 0, $config['search_limit'] + 1);

        // get paging
        $paging = getPagingLine($events);
        $line_num=count($events);

        if (!$csvExport) {
            $csvDisabled = zbx_empty($events);
        }

        // query event with extend data
        $events = API::Event()->get(array(
            'source' => EVENT_SOURCE_TRIGGERS,
            'object' => EVENT_OBJECT_TRIGGER,
            'eventids' => zbx_objectValues($events, 'eventid'),
            'output' => API_OUTPUT_EXTEND,
            'select_acknowledges' => API_OUTPUT_COUNT,
            'acknowledged'=>$filter['acknowledge'],
            'objectids'=>$object_id,
            'value'=>$filter['status'],
            'sortfield' => array('clock', 'eventid'),
            'sortorder' => ZBX_SORT_DOWN,
            'nopermissions' => true,
            'limit'=>$filter['limit']
        ));

        $triggers = API::Trigger()->get(array(
            'output' => array('triggerid', 'description', 'expression', 'priority', 'flags', 'url'),
            'selectHosts' => array('hostid', 'name', 'status'),
            'selectItems' => array('itemid', 'hostid', 'name', 'key_', 'value_type'),
            'triggerids' => zbx_objectValues($events, 'objectid')
        ));
        $triggers = zbx_toHash($triggers, 'triggerid');

        // fetch hosts
        $hosts = array();
        foreach ($triggers as $trigger) {
            $hosts[] = reset($trigger['hosts']);
        }
        $hostids = zbx_objectValues($hosts, 'hostid');

        $hosts = API::Host()->get(array(
            'output' => array('name', 'hostid', 'status'),
            'hostids' => $hostids,
            'selectGraphs' => API_OUTPUT_COUNT,
            'selectScreens' => API_OUTPUT_COUNT,
            'preservekeys' => true
        ));

        // fetch scripts for the host JS menu
        if (!$csvExport && getRequest('hostid', 0) == 0) {
            $scripts = API::Script()->getScriptsByHosts($hostids);
        }

        // actions
        $actions = getEventActionsStatus(zbx_objectValues($events, 'eventid'));

        // events
        foreach ($events as $event) {
            $trigger = $triggers[$event['objectid']];

            $host = reset($trigger['hosts']);
            $host = $hosts[$host['hostid']];

            $description = CMacrosResolverHelper::resolveEventDescription(zbx_array_merge($trigger, array(
                'clock' => $event['clock'],
                'ns' => $event['ns']
            )));

            // duration
            $event['duration'] = ($nextEvent = get_next_event($event, $events))
                ? zbx_date2age($event['clock'], $nextEvent['clock'])
                : zbx_date2age($event['clock']);

            // action
            $action = isset($actions[$event['eventid']]) ? $actions[$event['eventid']] : ' - ';

            if ($csvExport) {
                $csvRows[] = array(
                    zbx_date2str(DATE_TIME_FORMAT_SECONDS, $event['clock']),
                    (getRequest('hostid', 0) == 0) ? $host['name'] : null,
                    $description,
                    trigger_value2str($event['value']),
                    getSeverityCaption($trigger['priority']),
                    $event['duration'],
                    $config['event_ack_enable'] ? ($event['acknowledges'] ? _('Yes') : _('No')) : null,
                    strip_tags((string) $action)
                );
            }
            else {
                $triggerDescription = new CSpan($description, 'pointer link_menu');
                $triggerDescription->setMenuPopup(
                    CMenuPopupHelper::getTrigger($trigger, null, $event['clock'])
                );

                // acknowledge
                $ack = getEventAckState($event,'report_event.php');

                // add colors and blinking to span depending on configuration and trigger parameters
                $statusSpan = new CSpan(trigger_value2str($event['value']));

                addTriggerValueStyle(
                    $statusSpan,
                    $event['value'],
                    $event['clock'],
                    $event['acknowledged']
                );

                // host JS menu link
                $hostName = null;

                if (getRequest('hostid', 0) == 0) {
                    $hostName = new CSpan($host['name'], 'link_menu');
                    $hostName->setMenuPopup(CMenuPopupHelper::getHost($host, $scripts[$host['hostid']]));
                }

                $table->addRow(array(
                    new CLink(zbx_date2str(DATE_TIME_FORMAT_SECONDS, $event['clock']),
                        'tr_events.php?triggerid='.$event['objectid'].'&eventid='.$event['eventid'],
                        'action'
                    ),
                    $hostName,
                    $triggerDescription,
                    $statusSpan,
                    getSeverityCell($trigger['priority'], null, !$event['value']),
                    $event['duration'],
                    $config['event_ack_enable'] ? $ack : null,
                    $action
                ));
                if($event['acknowledged']>0){
                    $e= DBselect(
                        'SELECT a.*,u.alias,u.name,u.surname'.
                        ' FROM acknowledges a'.
                        ' LEFT JOIN users u ON u.userid=a.userid'.
                        ' WHERE a.eventid='.zbx_dbstr($event['eventid'])
                    );
                    while ($acknowledge = DBfetch($e)){
                        $table->addRow(array(
                            SPACE,
                            zbx_date2str(DATE_TIME_FORMAT_SECONDS,$acknowledge['clock']),
                            new CCol($acknowledge['alias'].'('.$acknowledge['name'].' '.$acknowledge['surname'].')：'.$acknowledge['message'],null,7)
                        ));
                    }
                }
            }
        }
    }
}

if ($csvExport) {
    echo zbx_toCSV($csvRows);
}
else {

    ?>
    <link href="/public/report.css" rel="stylesheet" type="text/css"/>
    <script src="/public/report.js" type="application/javascript"></script>
    <h1><center>事件响应报告</center></h1>
    <form method="post" target="_blank">
        <input type="hidden" name="print" value="1">
        <table class="table">
            <thead>
            <tr>
                <th colspan="1">生成时间</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_TIME_FORMAT_SECONDS);?></td>
                <th colspan="1">统计时段</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_FORMAT,$from),'到',zbx_date2str(DATE_FORMAT,$till);if(!hasRequest('linkman')){?><button type="submit">导出PDF</button><?php }?></td>
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
    $eventsWidget->addItem(BR());
    $eventsWidget->addItem(new CDiv(bold('共显示了'.$line_num.'行')));
    $eventsWidget->addItem($table);

    $timeline = array(
        'period' => $period,
        'starttime' => date(TIMESTAMP_FORMAT, $starttime),
        'usertime' => date(TIMESTAMP_FORMAT, $till)
    );

    $objData = array(
        'id' => 'timeline_1',
        'loadSBox' => 0,
        'loadImage' => 0,
        'loadScroll' => 1,
        'dynamic' => 0,
        'mainObject' => 1,
        'periodFixed' => CProfile::get('web.events.timelinefixed', 1),
        'sliderMaximumTimePeriod' => ZBX_MAX_PERIOD
    );

    zbx_add_post_js('jqBlink.blink();');
    zbx_add_post_js('timeControl.addObject("scroll_events_id", '.zbx_jsvalue($timeline).', '.zbx_jsvalue($objData).');');
    zbx_add_post_js('timeControl.processObjects();');

    $eventsWidget->show();

    if ($csvDisabled) {
        zbx_add_post_js('document.getElementById("csv_export").disabled = true;');
    }

    require_once dirname(__FILE__).'/include/page_footer.php';
}