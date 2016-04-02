<?php
$hostsWidget = new CWidget();

$pageFilter = new CPageFilter(array(
    'groups' => array(
        'real_hosts' => true,
        'editable' => true
    ),
    'groupid' => getRequest('groupid')
));

$_REQUEST['groupid'] = $pageFilter->groupid;
$_REQUEST['hostid'] = getRequest('hostid', 0);

if (hasRequest('action') && getRequest('action') == 'host.massupdateform' && hasRequest('hosts')) {
    $hostsWidget->addPageHeader(_('CONFIGURATION OF Inventories'));

    $data = array(
        'hosts' => getRequest('hosts'),
        'visible' => getRequest('visible', array()),
        'mass_replace_tpls' => getRequest('mass_replace_tpls'),
        'mass_clear_tpls' => getRequest('mass_clear_tpls'),
        'groups' => getRequest('groups', array()),
        'newgroup' => getRequest('newgroup', ''),
        'status' => getRequest('status', HOST_STATUS_MONITORED),
        'description' => getRequest('description'),
        'proxy_hostid' => getRequest('proxy_hostid', ''),
        'ipmi_authtype' => getRequest('ipmi_authtype', -1),
        'ipmi_privilege' => getRequest('ipmi_privilege', 2),
        'ipmi_username' => getRequest('ipmi_username', ''),
        'ipmi_password' => getRequest('ipmi_password', ''),
        'inventory_mode' => getRequest('inventory_mode', HOST_INVENTORY_DISABLED),
        'host_inventory' => getRequest('host_inventory', array()),
        'templates' => getRequest('templates', array())
    );

    // sort templates
    natsort($data['templates']);

    // get groups
    $data['all_groups'] = API::HostGroup()->get(array(
        'output' => API_OUTPUT_EXTEND,
        'editable' => true
    ));
    order_result($data['all_groups'], 'name');

    // get proxies
    $data['proxies'] = DBfetchArray(DBselect(
        'SELECT h.hostid,h.host'.
        ' FROM hosts h'.
        ' WHERE h.status IN ('.HOST_STATUS_PROXY_ACTIVE.','.HOST_STATUS_PROXY_PASSIVE.')'
    ));
    order_result($data['proxies'], 'host');

    // get inventories
    if ($data['inventory_mode'] != HOST_INVENTORY_DISABLED) {
        $data['inventories'] = getHostInventories();
        $data['inventories'] = zbx_toHash($data['inventories'], 'db_field');
    }

    // get templates data
    $data['linkedTemplates'] = null;
    if (!empty($data['templates'])) {
        $getLinkedTemplates = API::Template()->get(array(
            'templateids' => $data['templates'],
            'output' => array('templateid', 'name')
        ));

        foreach ($getLinkedTemplates as $getLinkedTemplate) {
            $data['linkedTemplates'][] = array(
                'id' => $getLinkedTemplate['templateid'],
                'name' => $getLinkedTemplate['name']
            );
        }
    }

    $hostForm = new CView('configuration.host.massupdate', $data);
    $hostsWidget->addItem($hostForm->render());
}
elseif (isset($_REQUEST['form'])) {
    $hostsWidget->addPageHeader(_('CONFIGURATION OF HOSTS'));

    $data = array();
    if ($hostId = getRequest('hostid', 0)) {
        $hostsWidget->addItem(get_header_host_table('', $_REQUEST['hostid']));

        $dbHosts = API::Host()->get(array(
            'hostids' => $hostId,
            'selectGroups' => API_OUTPUT_EXTEND,
            'selectParentTemplates' => array('templateid', 'name'),
            'selectMacros' => API_OUTPUT_EXTEND,
            'selectInventory' => true,
            'selectDiscoveryRule' => array('name', 'itemid'),
            'output' => API_OUTPUT_EXTEND
        ));
        $dbHost = reset($dbHosts);
        order_result($dbHost['groups'], 'name');

        $dbHost['interfaces'] = API::HostInterface()->get(array(
            'hostids' => $hostId,
            'output' => API_OUTPUT_EXTEND,
            'selectItems' => array('type'),
            'sortfield' => 'interfaceid',
            'preservekeys' => true
        ));

        $data['dbHost'] = $dbHost;
    }

    $hostForm = new CView('configuration.host.edit', $data);
    $hostsWidget->addItem($hostForm->render());

    $rootClass = 'host-edit';
    if (getRequest('hostid') && $dbHost['flags'] == ZBX_FLAG_DISCOVERY_CREATED) {
        $rootClass .= ' host-edit-discovered';
    }
    $hostsWidget->setRootClass($rootClass);
}
else {
    $sortField = getRequest('sort', CProfile::get('web.'.$page['file'].'.sort', 'name'));
    $sortOrder = getRequest('sortorder', CProfile::get('web.'.$page['file'].'.sortorder', ZBX_SORT_UP));

    CProfile::update('web.'.$page['file'].'.sort', $sortField, PROFILE_TYPE_STR);
    CProfile::update('web.'.$page['file'].'.sortorder', $sortOrder, PROFILE_TYPE_STR);

    $frmForm = new CForm();
    $frmForm->cleanItems();
    $frmForm->addItem(new CDiv(array(
        new CSubmit('form', _('Create host')),
        new CButton('form', _('Import'), 'redirect("conf.import.php?rules_preset=host")')
    )));
    $frmForm->addItem(new CVar('groupid', $_REQUEST['groupid'], 'filter_groupid_id'));

    $hostsWidget->addPageHeader(_('CONFIGURATION OF HOSTS'), $frmForm);

    $frmGroup = new CForm('get');
    $frmGroup->addItem(array(_('Group').SPACE, $pageFilter->getGroupsCB()));

    $hostsWidget->addHeader(_('Hosts'), $frmGroup);
    $hostsWidget->addHeaderRowNumber();
    $hostsWidget->setRootClass('host-list');

    // filter
    $filterTable = new CTable('', 'filter filter-center');
    $filterTable->addRow(array(
        array(array(bold(_('Name')), SPACE._('like').NAME_DELIMITER), new CTextBox('filter_host', $filter['host'], 20)),
        array(array(bold(_('DNS')), SPACE._('like').NAME_DELIMITER), new CTextBox('filter_dns', $filter['dns'], 20)),
        array(array(bold(_('IP')), SPACE._('like').NAME_DELIMITER), new CTextBox('filter_ip', $filter['ip'], 20)),
        array(bold(_('Port').NAME_DELIMITER), new CTextBox('filter_port', $filter['port'], 20))
    ));

    $filterButton = new CSubmit('filter_set', _('Filter'), 'chkbxRange.clearSelectedOnFilterChange();');
    $filterButton->useJQueryStyle('main');

    $resetButton = new CSubmit('filter_rst', _('Reset'), 'chkbxRange.clearSelectedOnFilterChange();');
    $resetButton->useJQueryStyle();

    $divButtons = new CDiv(array($filterButton, SPACE, $resetButton));
    $divButtons->setAttribute('style', 'padding: 4px 0;');

    $filterTable->addRow(new CCol($divButtons, 'controls', 4));

    $filterForm = new CForm('get');
    $filterForm->setAttribute('name', 'zbx_filter');
    $filterForm->setAttribute('id', 'zbx_filter');
    $filterForm->addItem($filterTable);

    $hostsWidget->addFlicker($filterForm, CProfile::get('web.hosts.filter.state', 0));

    // table hosts
    $form = new CForm();
    $form->setName('hosts');

    $table = new CTableInfo(_('No hosts found.'));
    $table->setHeader(array(
        new CCheckBox('all_hosts', null, "checkAll('".$form->getName()."', 'all_hosts', 'hosts');"),
        make_sorting_header(_('Name'), 'name', $sortField, $sortOrder),
        _('Applications'),
        _('Items'),
        _('Triggers'),
        _('Graphs'),
        _('Discovery'),
        _('Web'),
        _('Interface'),
        _('Templates'),
        make_sorting_header(_('Status'), 'status', $sortField, $sortOrder),
        _('Availability')
    ));

    // get Hosts
    $hosts = array();

    if ($pageFilter->groupsSelected) {
        $hosts = API::Host()->get(array(
            'output' => array('hostid', 'name', 'status'),
            'groupids' => ($pageFilter->groupid > 0) ? $pageFilter->groupid : null,
            'editable' => true,
            'sortfield' => $sortField,
            'sortorder' => $sortOrder,
            'limit' => $config['search_limit'] + 1,
            'search' => array(
                'name' => ($filter['host'] === '') ? null : $filter['host'],
                'ip' => ($filter['ip'] === '') ? null : $filter['ip'],
                'dns' => ($filter['dns'] === '') ? null : $filter['dns']
            ),
            'filter' => array(
                'port' => ($filter['port'] === '') ? null : $filter['port']
            )
        ));
    }
    else {
        $hosts = array();
    }

    // sorting && paging
    order_result($hosts, $sortField, $sortOrder);
    $paging = getPagingLine($hosts);

    $hosts = API::Host()->get(array(
        'hostids' => zbx_objectValues($hosts, 'hostid'),
        'output' => API_OUTPUT_EXTEND,
        'selectParentTemplates' => array('hostid', 'name'),
        'selectInterfaces' => API_OUTPUT_EXTEND,
        'selectItems' => API_OUTPUT_COUNT,
        'selectDiscoveries' => API_OUTPUT_COUNT,
        'selectTriggers' => API_OUTPUT_COUNT,
        'selectGraphs' => API_OUTPUT_COUNT,
        'selectApplications' => API_OUTPUT_COUNT,
        'selectHttpTests' => API_OUTPUT_COUNT,
        'selectDiscoveryRule' => array('itemid', 'name'),
        'selectHostDiscovery' => array('ts_delete')
    ));
    order_result($hosts, $sortField, $sortOrder);

    // selecting linked templates to templates linked to hosts
    $templateIds = array();
    foreach ($hosts as $host) {
        $templateIds = array_merge($templateIds, zbx_objectValues($host['parentTemplates'], 'templateid'));
    }
    $templateIds = array_unique($templateIds);

    $templates = API::Template()->get(array(
        'output' => array('templateid', 'name'),
        'templateids' => $templateIds,
        'selectParentTemplates' => array('hostid', 'name')
    ));
    $templates = zbx_toHash($templates, 'templateid');

    // get proxy host IDs that that are not 0
    $proxyHostIds = array();
    foreach ($hosts as $host) {
        if ($host['proxy_hostid']) {
            $proxyHostIds[$host['proxy_hostid']] = $host['proxy_hostid'];
        }
    }
    if ($proxyHostIds) {
        $proxies = API::Proxy()->get(array(
            'proxyids' => $proxyHostIds,
            'output' => array('host'),
            'preservekeys' => true
        ));
    }

    foreach ($hosts as $host) {
        $interface = reset($host['interfaces']);

        $applications = array(new CLink(_('Applications'), 'applications.php?groupid='.$_REQUEST['groupid'].'&hostid='.$host['hostid']),
            ' ('.$host['applications'].')');
        $items = array(new CLink(_('Items'), 'items.php?filter_set=1&hostid='.$host['hostid']),
            ' ('.$host['items'].')');
        $triggers = array(new CLink(_('Triggers'), 'triggers.php?groupid='.$_REQUEST['groupid'].'&hostid='.$host['hostid']),
            ' ('.$host['triggers'].')');
        $graphs = array(new CLink(_('Graphs'), 'graphs.php?groupid='.$_REQUEST['groupid'].'&hostid='.$host['hostid']),
            ' ('.$host['graphs'].')');
        $discoveries = array(new CLink(_('Discovery'), 'host_discovery.php?&hostid='.$host['hostid']),
            ' ('.$host['discoveries'].')');
        $httpTests = array(new CLink(_('Web'), 'httpconf.php?&hostid='.$host['hostid']),
            ' ('.$host['httpTests'].')');

        $description = array();

        if (isset($proxies[$host['proxy_hostid']])) {
            $description[] = $proxies[$host['proxy_hostid']]['host'].NAME_DELIMITER;
        }
        if ($host['discoveryRule']) {
            $description[] = new CLink($host['discoveryRule']['name'], 'host_prototypes.php?parent_discoveryid='.$host['discoveryRule']['itemid'], 'parent-discovery');
            $description[] = NAME_DELIMITER;
        }

        $description[] = new CLink(CHtml::encode($host['name']), 'hosts.php?form=update&hostid='.$host['hostid'].url_param('groupid'));

        $hostInterface = ($interface['useip'] == INTERFACE_USE_IP) ? $interface['ip'] : $interface['dns'];
        $hostInterface .= empty($interface['port']) ? '' : NAME_DELIMITER.$interface['port'];

        $statusScript = null;

        if ($host['status'] == HOST_STATUS_MONITORED) {
            if ($host['maintenance_status'] == HOST_MAINTENANCE_STATUS_ON) {
                $statusCaption = _('In maintenance');
                $statusClass = 'orange';
            }
            else {
                $statusCaption = _('Enabled');
                $statusClass = 'enabled';
            }

            $statusScript = 'return Confirm('.zbx_jsvalue(_('Disable host?')).');';
            $statusUrl = 'hosts.php?hosts[]='.$host['hostid'].'&action=host.massdisable'.url_param('groupid');
        }
        else {
            $statusCaption = _('Disabled');
            $statusUrl = 'hosts.php?hosts[]='.$host['hostid'].'&action=host.massenable'.url_param('groupid');
            $statusScript = 'return Confirm('.zbx_jsvalue(_('Enable host?')).');';
            $statusClass = 'disabled';
        }

        $status = new CLink($statusCaption, $statusUrl, $statusClass, $statusScript);

        if (empty($host['parentTemplates'])) {
            $hostTemplates = '-';
        }
        else {
            order_result($host['parentTemplates'], 'name');

            $hostTemplates = array();
            $i = 0;

            foreach ($host['parentTemplates'] as $template) {
                $i++;

                if ($i > $config['max_in_table']) {
                    $hostTemplates[] = ' &hellip;';

                    break;
                }

                $caption = array(new CLink(
                    CHtml::encode($template['name']),
                    'templates.php?form=update&templateid='.$template['templateid'],
                    'unknown'
                ));

                if (!empty($templates[$template['templateid']]['parentTemplates'])) {
                    order_result($templates[$template['templateid']]['parentTemplates'], 'name');

                    $caption[] = ' (';
                    foreach ($templates[$template['templateid']]['parentTemplates'] as $tpl) {
                        $caption[] = new CLink(CHtml::encode($tpl['name']),'templates.php?form=update&templateid='.$tpl['templateid'], 'unknown');
                        $caption[] = ', ';
                    }
                    array_pop($caption);

                    $caption[] = ')';
                }

                if ($hostTemplates) {
                    $hostTemplates[] = ', ';
                }

                $hostTemplates[] = $caption;
            }
        }

        $table->addRow(array(
            new CCheckBox('hosts['.$host['hostid'].']', null, null, $host['hostid']),
            $description,
            $applications,
            $items,
            $triggers,
            $graphs,
            $discoveries,
            $httpTests,
            $hostInterface,
            new CCol($hostTemplates, 'wraptext'),
            $status,
            getAvailabilityTable($host)
        ));
    }

    $goBox = new CComboBox('action');

    $goBox->addItem('host.export', _('Export selected'));

    $goBox->addItem('host.massupdateform', _('Mass update'));
    $goOption = new CComboItem('host.massenable', _('Enable selected'));
    $goOption->setAttribute('confirm', _('Enable selected hosts?'));
    $goBox->addItem($goOption);

    $goOption = new CComboItem('host.massdisable', _('Disable selected'));
    $goOption->setAttribute('confirm', _('Disable selected hosts?'));
    $goBox->addItem($goOption);

    $goOption = new CComboItem('host.massdelete', _('Delete selected'));
    $goOption->setAttribute('confirm', _('Delete selected hosts?'));
    $goBox->addItem($goOption);

    $goButton = new CSubmit('goButton', _('Go').' (0)');
    $goButton->setAttribute('id', 'goButton');

    zbx_add_post_js('chkbxRange.pageGoName = "hosts";');

    $form->addItem(array($paging, $table, $paging, get_table_header(array($goBox, $goButton))));
    $hostsWidget->addItem($form);
}

$hostsWidget->show();

require_once dirname(__FILE__).'/include/page_footer.php';
