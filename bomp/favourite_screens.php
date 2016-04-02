<?php
/*
** Zabbix
** Copyright (C) 2001-2015 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/blocks.inc.php';

$page['title'] = _('Dashboard');
$page['file'] = 'dashboard.php';
$page['hist_arg'] = array();
$page['scripts'] = array('class.pmaster.js');
$page['type'] = detect_page_type(PAGE_TYPE_HTML);

require_once dirname(__FILE__).'/include/page_header.php';

//	VAR		TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
$fields = array(
	'groupid' =>		array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID,		null),
	'view_style' =>		array(T_ZBX_INT, O_OPT, P_SYS,	IN('0,1'),	null),
	'type' =>			array(T_ZBX_INT, O_OPT, P_SYS,	IN('0,1'),	null),
	'output' =>			array(T_ZBX_STR, O_OPT, P_SYS,	null,		null),
	'jsscriptid' =>		array(T_ZBX_STR, O_OPT, P_SYS,	null,		null),
	'fullscreen' =>		array(T_ZBX_INT, O_OPT, P_SYS,	IN('0,1'),	null),
	// ajax
	'widgetName' =>		array(T_ZBX_STR, O_OPT, P_ACT,	null,		null),
	'widgetRefresh' =>	array(T_ZBX_STR, O_OPT, null,	null,		null),
	'widgetRefreshRate' => array(T_ZBX_STR, O_OPT, P_ACT, null,		null),
	'widgetSort' =>		array(T_ZBX_STR, O_OPT, P_ACT,	null,		null),
	'widgetState' =>	array(T_ZBX_STR, O_OPT, P_ACT,	null,		null),
	'favobj' =>			array(T_ZBX_STR, O_OPT, P_ACT,	null,		null),
	'favaction' =>		array(T_ZBX_STR, O_OPT, P_ACT,	IN('"add","remove"'), null),
	'favid' =>			array(T_ZBX_STR, O_OPT, P_ACT,	null,		null)
);
check_fields($fields);

/*
 * Filter
 */
$dashboardConfig = array(
	'groupids' => null,
	'maintenance' => null,
	'severity' => null,
	'extAck' => 0,
	'filterEnable' => CProfile::get('web.dashconf.filter.enable', 0)
);

if ($dashboardConfig['filterEnable'] == 1) {
	// groups
	$dashboardConfig['grpswitch'] = CProfile::get('web.dashconf.groups.grpswitch', 0);

	if ($dashboardConfig['grpswitch'] == 0) {
		// null mean all groups
		$dashboardConfig['groupids'] = null;
	}
	else {
		$dashboardConfig['groupids'] = zbx_objectValues(CFavorite::get('web.dashconf.groups.groupids'), 'value');
		$hideHostGroupIds = zbx_objectValues(CFavorite::get('web.dashconf.groups.hide.groupids'), 'value');

		if ($hideHostGroupIds) {
			// get all groups if no selected groups defined
			if (!$dashboardConfig['groupids']) {
				$dbHostGroups = API::HostGroup()->get(array(
					'output' => array('groupid')
				));
				$dashboardConfig['groupids'] = zbx_objectValues($dbHostGroups, 'groupid');
			}

			$dashboardConfig['groupids'] = array_diff($dashboardConfig['groupids'], $hideHostGroupIds);

			// get available hosts
			$dbAvailableHosts = API::Host()->get(array(
				'groupids' => $dashboardConfig['groupids'],
				'output' => array('hostid')
			));
			$availableHostIds = zbx_objectValues($dbAvailableHosts, 'hostid');

			$dbDisabledHosts = API::Host()->get(array(
				'groupids' => $hideHostGroupIds,
				'output' => array('hostid')
			));
			$disabledHostIds = zbx_objectValues($dbDisabledHosts, 'hostid');

			$dashboardConfig['hostids'] = array_diff($availableHostIds, $disabledHostIds);
		}
		else {
			if (!$dashboardConfig['groupids']) {
				// null mean all groups
				$dashboardConfig['groupids'] = null;
			}
		}
	}

	// hosts
	$maintenance = CProfile::get('web.dashconf.hosts.maintenance', 1);
	$dashboardConfig['maintenance'] = ($maintenance == 0) ? 0 : null;

	// triggers
	$severity = CProfile::get('web.dashconf.triggers.severity', null);
	$dashboardConfig['severity'] = zbx_empty($severity) ? null : explode(';', $severity);
	$dashboardConfig['severity'] = zbx_toHash($dashboardConfig['severity']);

	$config = select_config();
	$dashboardConfig['extAck'] = $config['event_ack_enable'] ? CProfile::get('web.dashconf.events.extAck', 0) : 0;
}

/*
 * Actions
 */

// favourites
if (hasRequest('favobj') && hasRequest('favaction')) {
	$favouriteObject = getRequest('favobj');
	$favouriteAction = getRequest('favaction');
	$favouriteId = getRequest('favid');

	$result = true;

	DBstart();

	switch ($favouriteObject) {
		// favourite graphs
		case 'itemid':
		case 'graphid':
			if ($favouriteAction === 'add') {
				zbx_value2array($favouriteId);

				foreach ($favouriteId as $id) {
					$result &= CFavorite::add('web.favorite.graphids', $id, $favouriteObject);
				}
			}
			elseif ($favouriteAction == 'remove') {
				$result &= CFavorite::remove('web.favorite.graphids', $favouriteId, $favouriteObject);
			}

			$data = getFavouriteGraphs();
			$data = $data->toString();

			echo '
				jQuery("#'.WIDGET_FAVOURITE_GRAPHS.'").html('.CJs::encodeJson($data).');
				jQuery(".menuPopup").remove();
				jQuery("#favouriteGraphs").data("menu-popup", '.CJs::encodeJson(CMenuPopupHelper::getFavouriteGraphs()).');';
			break;

		// favourite maps
		case 'sysmapid':
			if ($favouriteAction == 'add') {
				zbx_value2array($favouriteId);

				foreach ($favouriteId as $id) {
					$result &= CFavorite::add('web.favorite.sysmapids', $id, $favouriteObject);
				}
			}
			elseif ($favouriteAction == 'remove') {
				$result &= CFavorite::remove('web.favorite.sysmapids', $favouriteId, $favouriteObject);
			}

			$data = getFavouriteMaps();
			$data = $data->toString();

			echo '
				jQuery("#'.WIDGET_FAVOURITE_MAPS.'").html('.CJs::encodeJson($data).');
				jQuery(".menuPopup").remove();
				jQuery("#favouriteMaps").data("menu-popup", '.CJs::encodeJson(CMenuPopupHelper::getFavouriteMaps()).');';
			break;

		// favourite screens, slideshows
		case 'screenid':
		case 'slideshowid':
			if ($favouriteAction == 'add') {
				zbx_value2array($favouriteId);

				foreach ($favouriteId as $id) {
					$result &= CFavorite::add('web.favorite.screenids', $id, $favouriteObject);
				}
			}
			elseif ($favouriteAction == 'remove') {
				$result &= CFavorite::remove('web.favorite.screenids', $favouriteId, $favouriteObject);
			}

			$data = getFavouriteScreens();
			$data = $data->toString();

			echo '
				jQuery("#'.WIDGET_FAVOURITE_SCREENS.'").html('.CJs::encodeJson($data).');
				jQuery(".menuPopup").remove();
				jQuery("#favouriteScreens").data("menu-popup", '.CJs::encodeJson(CMenuPopupHelper::getFavouriteScreens()).');';
			break;
	}

	DBend($result);
}

if ($page['type'] == PAGE_TYPE_JS || $page['type'] == PAGE_TYPE_HTML_BLOCK) {
	require_once dirname(__FILE__).'/include/page_footer.php';
	exit;
}

/*
 * Display
 */
$dashboardWidget = new CWidget(null, 'dashboard');
$dashboardWidget->setClass('header');
$dashboardWidget->addHeader(_('PERSONAL DASHBOARD'), array(
	new CIcon(
		_s('Configure (Filter %s)', $dashboardConfig['filterEnable'] ? _('Enabled') : _('Disabled')),
		$dashboardConfig['filterEnable'] ? 'iconconfig_hl' : 'iconconfig',
		'document.location = "dashconf.php";'
	),
	SPACE,
	get_icon('fullscreen', array('fullscreen' => getRequest('fullscreen'))))
);

/*
 * Dashboard grid
 */
$dashboardGrid = array(array(), array(), array());
$widgetRefreshParams = array();

// favourite screens
$icon = new CIcon(_('Menu'), 'iconmenu');
$icon->setAttribute('id', 'favouriteScreens');
$icon->setMenuPopup(CMenuPopupHelper::getFavouriteScreens());

$favouriteScreens = new CCollapsibleUiWidget(WIDGET_FAVOURITE_SCREENS, getFavouriteScreens());
$favouriteScreens->open = (bool) CProfile::get('web.dashboard.widget.'.WIDGET_FAVOURITE_SCREENS.'.state', true);
$favouriteScreens->setHeader(_('Favourite screens'), $icon);
$link1=new CLink(_('Screens').' &raquo;', 'screens.php', 'highlight');
$link1->setTarget('_blank');
$link2=new CLink(_('Slide shows').' &raquo;', 'slides.php', 'highlight');
$link2->setTarget('_blank');
$favouriteScreens->setFooter(
	array(
		$link1,
		SPACE,
		SPACE,
		SPACE,
		$link2
	),
	true
);

$col = CProfile::get('web.dashboard.widget.'.WIDGET_FAVOURITE_SCREENS.'.col', 0);
$row = CProfile::get('web.dashboard.widget.'.WIDGET_FAVOURITE_SCREENS.'.row', 1);
$dashboardGrid[$col][$row] = $favouriteScreens;

$dashboardTable = new CTable();
$dashboardTable->addRow(
	 array(
		 new CDiv($dashboardGrid[0], 'column'),
		 new CDiv($dashboardGrid[1], 'column'),
		 new CDiv($dashboardGrid[2], 'column')
	 ),
	 'top'
 );

$dashboardWidget->addItem($dashboardTable);
$dashboardWidget->show();

/*
 * Javascript
 */
// start refresh process
zbx_add_post_js('initPMaster("dashboard", '.CJs::encodeJson($widgetRefreshParams).');');

// activating blinking
zbx_add_post_js('jqBlink.blink();');

?>
<script type="text/javascript">
	/**
	 * @see init.js add.popup event
	 */
	function addPopupValues(list) {
		var favourites = {graphid: 1, itemid: 1, screenid: 1, slideshowid: 1, sysmapid: 1};

		if (isset(list.object, favourites)) {
			var favouriteIds = [];

			for (var i = 0; i < list.values.length; i++) {
				favouriteIds.push(list.values[i][list.object]);
			}

			sendAjaxData({
				data: {
					favobj: list.object,
					'favid[]': favouriteIds,
					favaction: 'add'
				}
			});
		}
	}
</script>
<?php
require_once dirname(__FILE__).'/include/page_footer.php';
