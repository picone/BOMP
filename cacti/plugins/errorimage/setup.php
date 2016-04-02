<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@sqmail.org
    Home Site ...... http://cactiusers.org
    Program ........ Error Images
    Purpose ........ Displays a real error instead of a broken graph image

*******************************************************************************/

function plugin_init_errorimage() {
	global $plugin_hooks;
	$plugin_hooks['graph_image']['errorimage'] = 'errorimage_check_graphs';
}

function errorimage_check_graphs () {
	global $config;

	$local_graph_id = $_GET['local_graph_id'];
	$graph_items = db_fetch_assoc("select
		data_template_rrd.local_data_id
		from graph_templates_item
		left join data_template_rrd on (graph_templates_item.task_item_id=data_template_rrd.id)
		where graph_templates_item.local_graph_id=$local_graph_id
		order by graph_templates_item.sequence");

	$ids = array();
	foreach($graph_items as $graph) {
		if ($graph['local_data_id'] != '')
			$ids[] = $graph['local_data_id'];
	}
	$ids = array_unique($ids);

	foreach ($ids as $id => $local_data_id) {
		$data_source_path = get_data_source_path($local_data_id, true);
		if (!file_exists($data_source_path)) {
			$filename = $config['base_path'] . '/plugins/errorimage/images/no-datasource.png';
			$file = fopen($filename, 'rb');
			echo fread($file, filesize($filename));
			fclose($file);
			exit;
		}
	}
}

function errorimage_version () {
	return array(	'name' 		=> 'errorimage',
			'version' 	=> '0.1',
			'longname'	=> 'Error Images',
			'author'	=> 'Jimmy Conner',
			'homepage'	=> 'http://cactiusers.org',
			'email'		=> 'jimmy@sqmail.org',
			'url'		=> 'http://cactiusers.org/cacti/versions.php'
			);
}

?>