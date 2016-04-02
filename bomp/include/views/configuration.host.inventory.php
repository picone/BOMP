<?php
require_once dirname(__FILE__).'/js/configuration.host.edit.js.php';
$frmHost = new CForm();
$frmHost->addVar('inventory',1);
$inventoryFormList = new CFormList('inventorylist');
$hostInventoryTable = DB::getSchema('host_inventory');
$hostInventoryFields = getHostInventories();

$inventoryMode=HOST_INVENTORY_MANUAL;

$input=new CTextBox('host','');
$input->setAttribute('maxlength',16);
$input->addStyle('width:16em;');
$inventoryFormList->addRow('主机名',array($input,new CSpan('','populating_item')));
//var_dump($data);
unset($input);

$frmHost->addItem(makeFormFooter(
	new CSubmit('add','添加'),
	new CButtonCancel(null,'window.location="hosts.php?action=host.list";')
));

foreach ($hostInventoryFields as $inventoryNo => $inventoryInfo) {
	if (!isset($hostInventory[$inventoryInfo['db_field']])) {
		$hostInventory[$inventoryInfo['db_field']] = '';
	}

	if ($hostInventoryTable['fields'][$inventoryInfo['db_field']]['type'] == DB::FIELD_TYPE_TEXT) {
		$input = new CTextArea('host_inventory['.$inventoryInfo['db_field'].']', $data[0][$inventoryInfo['db_field']]);
		$input->addStyle('width: 64em;');
	}
	else {
		$fieldLength = $hostInventoryTable['fields'][$inventoryInfo['db_field']]['length'];
		$input = new CTextBox('host_inventory['.$inventoryInfo['db_field'].']', $data[0][$inventoryInfo['db_field']]);
		$input->setAttribute('maxlength', $fieldLength);
		$input->addStyle('width: '.($fieldLength > 64 ? 64 : $fieldLength).'em;');
	}
	// link to populating item at the right side (if any)
	if (isset($hostItemsToInventory[$inventoryNo])) {
		$itemName = $hostItemsToInventory[$inventoryNo]['name_expanded'];

		$populatingLink = new CLink($itemName, 'items.php?form=update&itemid='.$hostItemsToInventory[$inventoryNo]['itemid']);
		$populatingLink->setAttribute('title', _s('This field is automatically populated by item "%s".', $itemName));
		$populatingItemCell = array(' &larr; ', $populatingLink);

		$input->addClass('linked_to_item'); // this will be used for disabling fields via jquery
	}
	else {
		$populatingItemCell = '';
	}
	$input->addStyle('float: left;');

	$populatingItem = new CSpan($populatingItemCell, 'populating_item');

	$inventoryFormList->addRow($inventoryInfo['title'], array($input, $populatingItem));
}
$clearFixDiv = new CDiv();
$clearFixDiv->addStyle('clear: both;');
$inventoryFormList->addRow('', $clearFixDiv);
$frmHost->addItem( $inventoryFormList);
$frmHost->addItem(makeFormFooter(
	new CSubmit('add','添加'),
	new CButtonCancel(null,'window.location="hosts.php?action=host.list";')
));
return $frmHost;