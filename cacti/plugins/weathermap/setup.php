<?php
/*******************************************************************************

	Author ......... Howard Jones
	Contact ........ howie@thingy.com
	Home Site ...... http://wotsit.thingy.com/haj/
	Program ........ Network Weathermap for Cacti
	Version ........ See code below
	Purpose ........ Network Usage Overview

*******************************************************************************/

// who knows why this changed... not me.
function disabled_plugin_weathermap_version()
{
	return(weathermap_version());
}

function weathermap_version () {
	return array( 	'name'    	=> 'weathermap',
		'version'       => '0.97a',
		'longname'      => 'PHP Network Weathermap',
		'author'        => 'Howard Jones',
		'homepage'      => 'http://www.network-weathermap.com/',
		'webpage'      => 'http://www.network-weathermap.com/',
		'email' 	=> 'howie@thingy.com',
		'url'           => 'http://www.network-weathermap.com/versions.php'
	);
}

function disabled_plugin_weathermap_uninstall() {
	// doesn't really do anything. Here to remind me.
	
	// not sure what it should really do.
}

function disabled_plugin_weathermap_check_config() {
	// doesn't really do anything either because it's not implemented yet in PIA. Would be handy if it was.
	
	if(!function_exists("imagecreate")) return FALSE;
	if(!function_exists("preg_match")) return FALSE;
	if(!function_exists("imagecreatetruecolor")) return FALSE;
	if(!function_exists("imagecreatefrompng")) return FALSE;
	if(!function_exists("imagepng")) return FALSE;
	if(!function_exists("imageSaveAlpha")) return FALSE;
	if(!function_exists("imagealphablending")) return FALSE;
	
//	if(!function_exists("imagecopyresampled")) return FALSE;
	
	return TRUE;
}

function disabled_plugin_weathermap_install () {

	api_plugin_register_hook('weathermap', 'config_arrays',         'weathermap_config_arrays',        'setup.php');
	api_plugin_register_hook('weathermap', 'draw_navigation_text',  'weathermap_draw_navigation_text', 'setup.php');
	api_plugin_register_hook('weathermap', 'config_settings',       'weathermap_config_settings',      'setup.php');
	api_plugin_register_hook('weathermap', 'top_header_tabs',       'weathermap_show_tab',             'setup.php');
	api_plugin_register_hook('weathermap', 'top_graph_header_tabs', 'weathermap_show_tab',             'setup.php');

	api_plugin_register_hook('weathermap', 'poller_bottom', 'weathermap_poller_bottom',             'setup.php');
	api_plugin_register_hook('weathermap', 'poller_top', 'weathermap_poller_top',             'setup.php');
	api_plugin_register_hook('weathermap', 'poller_output', 'weathermap_poller_output',             'setup.php');
	
	api_plugin_register_hook('weathermap', 'top_graph_refresh', 'weathermap_top_graph_refresh',             'setup.php');
	api_plugin_register_hook('weathermap', 'page_title', 'weathermap_page_title',             'setup.php');
	api_plugin_register_hook('weathermap', 'page_head', 'weathermap_page_head',             'setup.php');

	weathermap_setup_table ();
}


function plugin_init_weathermap() {
	global $plugin_hooks;
	$plugin_hooks['top_header_tabs']['weathermap'] = 'weathermap_show_tab';
	$plugin_hooks['top_graph_header_tabs']['weathermap'] = 'weathermap_show_tab';
	$plugin_hooks['config_arrays']['weathermap'] = 'weathermap_config_arrays';
	$plugin_hooks['draw_navigation_text']['weathermap'] = 'weathermap_draw_navigation_text';
	$plugin_hooks['config_settings']['weathermap'] = 'weathermap_config_settings';
	
	$plugin_hooks['poller_bottom']['weathermap'] = 'weathermap_poller_bottom';
	$plugin_hooks['poller_top']['weathermap'] = 'weathermap_poller_top';
	$plugin_hooks['poller_output']['weathermap'] = 'weathermap_poller_output';
	
	$plugin_hooks['top_graph_refresh']['weathermap'] = 'weathermap_top_graph_refresh';
	$plugin_hooks['page_title']['weathermap'] = 'weathermap_page_title';
	$plugin_hooks['page_head']['weathermap'] = 'weathermap_page_head';
}

// figure out if this poller run is hitting the 'cron' entry for any maps.
function weathermap_poller_top()
{
	global $weathermap_poller_start_time;
	
	$n = time();
	
	// round to the nearest minute, since that's all we need for the crontab-style stuff
	$weathermap_poller_start_time = $n - ($n%60);

}

function weathermap_page_head()
{
	global $config;
	
	// Add in a Media RSS link on the thumbnail view
	// - format isn't quite right, so it's disabled for now.
	//	if(preg_match('/plugins\/weathermap\/weathermap\-cacti\-plugin\.php/',$_SERVER['REQUEST_URI'] ,$matches))
	//	{
	//		print '<link id="media-rss" title="My Network Weathermaps" rel="alternate" href="?action=mrss" type="application/rss+xml">';
	//	}
	if(preg_match('/plugins\/weathermap\//',$_SERVER['REQUEST_URI'] ,$matches))
    {
		print '<LINK rel="stylesheet" type="text/css" media="screen" href="weathermap-cacti-plugin.css">';
	}
}

function weathermap_page_title( $t )
{
        if(preg_match('/plugins\/weathermap\//',$_SERVER['REQUEST_URI'] ,$matches))
        {
                $t .= " - Weathermap";

		if(preg_match('/plugins\/weathermap\/weathermap-cacti-plugin.php\?action=viewmap&id=([^&]+)/',$_SERVER['REQUEST_URI'],$matches))
                {
                        $mapid = $matches[1];
						if(preg_match("/^\d+$/",$mapid))
						{
							$title = db_fetch_cell("SELECT titlecache from weathermap_maps where ID=".intval($mapid));
						}
						else
						{
							$title = db_fetch_cell("SELECT titlecache from weathermap_maps where filehash='".mysql_real_escape_string($mapid)."'");
						}
                        if(isset($title)) $t .= " - $title";
                }

        }
        return($t);
}



function weathermap_top_graph_refresh($refresh)
{
	if (basename($_SERVER["PHP_SELF"]) != "weathermap-cacti-plugin.php")
		return $refresh;

	// if we're cycling maps, then we want to handle reloads ourselves, thanks
	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == 'viewmapcycle')
	{
		return(86400);
	}
	return ($refresh);
}

//function weathermap_config_settings () {
//	global $tabs, $settings;
//	$tabs["misc"] = "杂项";
//
//	$temp = array(
//		"weathermap_header" => array(
//			"friendly_name" => "网络气象图",
//			"method" => "spacer",
//		),
//		"weathermap_pagestyle" => array(
//			"friendly_name" => "页面样式",
//			"description" => "如何显示多个气象图.",
//			"method" => "drop_array",
//			"array" => array(0 => "缩略图预览", 1 => "完整图片", 2 => "只显示第一张")
//		),
//		"weathermap_thumbsize" => array(
//			"friendly_name" => "缩略图最大尺寸",
//			"description" => "显示缩略图的最大宽度或高度,单位:像素.将在下一次运行采集器时生效.",
//			"method" => "textbox",
//			"max_length" => 5,
//		),
//		"weathermap_cycle_refresh" => array(
//			"friendly_name" => "刷新时间",
//			"description" => "循环模式时隔多久刷新一次.选择'自动'将每5分钟刷新一次所有气象图.",
//			"method" => "drop_array",
//			"array" => array(0 => "自动", 5 => "5秒",
//			15 => '15秒',
//			30 => '30秒',
//			60 => '1分钟',
//			120 => '2分钟',
//			300 => '5分钟',
//		)
//	),
//	"weathermap_output_format" => array(
//		"friendly_name" => "输出格式",
//		"description" => "您希望产生哪种格式的图片和缩略图?",
//		"method" => "drop_array",
//		"array" => array('png' => "PNG (默认)",
//		'jpg' => "JPEG",
//		'gif' => 'GIF'
//	)
//),
//"weathermap_render_period" => array(
//	"friendly_name" => "气象图生成周期",
//	"description" => "气象图每隔多久重新生成一次?当使用非标准采集器时才需要用到此功能,否则请不要作任何修改.",
//	"method" => "drop_array",
//	"array" => array(-1 => "永不 (手动生成)",
//		0 => "每次采集器周期 (默认)",
//		2 => '每2次采集器周期',
//		3 => '每3次采集器周期',
//		4 => '每4次采集器周期',
//		5 => '每5次采集器周期',
//		10 => '每10次采集器周期',
//		12 => '每12次采集器周期',
//		24 => '每24次采集器周期',
//		36 => '每36次采集器周期',
//		48 => '每48次采集器周期',
//		72 => '每72次采集器周期',
//		288 => '每288次采集器周期',
//		),
//	),
//
//	"weathermap_all_tab" => array(
//		"friendly_name" => "显示'全部'标签",
//		"description" => "当使用分组,添加一个'全部气象图'标签到标签栏.",
//		"method" => "drop_array",
//		"array" => array(0=>"否 (默认)",1=>"是")
//		),
//	"weathermap_map_selector" => array(
//		"friendly_name" => "显示气象图选择器",
//		"description" => "在全屏气象图查看时显示一个组合框气象图选择器.",
//		"method" => "drop_array",
//		"array" => array(0=>"否",1=>"是 (默认)")
//		),
//	"weathermap_quiet_logging" => array(
//		"friendly_name" => "安静日志",
//		"description" => "默认情况下,处于低级别日志,这将会记录很多气象图日志.这个功能将在低级别日志时只记录错误日志.",
//		"method" => "drop_array",
//		"array" => array(0=>"详细 (默认)",1=>"安静")
//		)
//	);
//	if (isset($settings["misc"]))
//		$settings["misc"] = array_merge($settings["misc"], $temp);
//	else
//		$settings["misc"]=$temp;
//}


function weathermap_setup_table () {
	global $config, $database_default;
	global $WEATHERMAP_VERSION;
	include_once($config["library_path"] . DIRECTORY_SEPARATOR . "database.php");

	$dbversion = read_config_option("weathermap_db_version");

	$myversioninfo = weathermap_version();
	$myversion = $myversioninfo['version'];
	
	// only bother with all this if it's a new install, a new version, or we're in a development version
	// - saves a handful of db hits per request!
	if( ($dbversion=="") || (preg_match("/dev$/",$myversion)) || ($dbversion != $myversion) )
	{
		# cacti_log("Doing setup_table() \n",true,"WEATHERMAP");
		$sql = "show tables";
		$result = db_fetch_assoc($sql) or die (mysql_error());

		$tables = array();
		$sql = array();

		foreach($result as $index => $arr) {
			foreach ($arr as $t) {
				$tables[] = $t;
			}
		}

		$sql[] = "update weathermap_maps set sortorder=id where sortorder is null;";
		
		if (!in_array('weathermap_maps', $tables)) {
			$sql[] = "CREATE TABLE weathermap_maps (
				id int(11) NOT NULL auto_increment,
				sortorder int(11) NOT NULL default 0,
				group_id int(11) NOT NULL default 1,
				active set('on','off') NOT NULL default 'on',
				configfile text NOT NULL,
				imagefile text NOT NULL,
				htmlfile text NOT NULL,
				titlecache text NOT NULL,
				filehash varchar (40) NOT NULL default '',
				warncount int(11) NOT NULL default 0,
				config text NOT NULL default '',
				thumb_width int(11) NOT NULL default 0,
				thumb_height int(11) NOT NULL default 0,
				schedule varchar(32) NOT NULL default '*',
				archiving set('on','off') NOT NULL default 'off',
				PRIMARY KEY  (id)
			) TYPE=MyISAM;";
		}
		else
		{
			$colsql = "show columns from weathermap_maps from " . $database_default;
			$result = mysql_query($colsql) or die (mysql_error());
			$found_so = false;	$found_fh = false;
			$found_wc = false;	$found_cf = false;
			$found_96changes = false;
			$found_96bchanges = false;
			
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if ($row['Field'] == 'sortorder') $found_so = true;
				if ($row['Field'] == 'filehash') $found_fh = true;
				if ($row['Field'] == 'warncount') $found_wc = true;
				if ($row['Field'] == 'config') $found_cf = true;
				
				if ($row['Field'] == 'thumb_width') $found_96changes = true;
				if ($row['Field'] == 'group_id') $found_96bchanges = true;
			}
			if (!$found_so) $sql[] = "alter table weathermap_maps add sortorder int(11) NOT NULL default 0 after id";
			if (!$found_fh) $sql[] = "alter table weathermap_maps add filehash varchar(40) NOT NULL default '' after titlecache";		
			if (!$found_wc) $sql[] = "alter table weathermap_maps add warncount int(11) NOT NULL default 0 after filehash";		
			if (!$found_cf) $sql[] = "alter table weathermap_maps add config text NOT NULL  default '' after warncount";
			if (!$found_96changes)
			{
				$sql[] = "alter table weathermap_maps add thumb_width int(11) NOT NULL default 0 after config";
				$sql[] = "alter table weathermap_maps add thumb_height int(11) NOT NULL default 0 after thumb_width";
				$sql[] = "alter table weathermap_maps add schedule varchar(32) NOT NULL default '*' after thumb_height";
				$sql[] = "alter table weathermap_maps add archiving set('on','off') NOT NULL default 'off' after schedule";
			}
			if (!$found_96bchanges)
			{
				$sql[] = "alter table weathermap_maps add group_id int(11) NOT NULL default 1 after sortorder";
				$sql[] = "ALTER TABLE `weathermap_settings` ADD `groupid` INT NOT NULL DEFAULT '0' AFTER `mapid`";
			}
		}

		$sql[] = "update weathermap_maps set filehash=LEFT(MD5(concat(id,configfile,rand())),20) where filehash = '';";
		
		if (!in_array('weathermap_auth', $tables)) {
			$sql[] = "CREATE TABLE weathermap_auth (
				userid mediumint(9) NOT NULL default '0',
				mapid int(11) NOT NULL default '0'
			) TYPE=MyISAM;";
		}

		
		if (!in_array('weathermap_groups', $tables)) {
			$sql[] = "CREATE TABLE  weathermap_groups (
				`id` INT(11) NOT NULL auto_increment,
				`name` VARCHAR( 128 ) NOT NULL default '',
				`sortorder` INT(11) NOT NULL default 0,
				PRIMARY KEY (id)
				) TYPE=MyISAM;";
			$sql[] = "INSERT INTO weathermap_groups (id,name,sortorder) VALUES (1,'Weathermaps',1)";
		}
		
		if (!in_array('weathermap_settings', $tables)) {
			$sql[] = "CREATE TABLE weathermap_settings (
				id int(11) NOT NULL auto_increment,
				mapid int(11) NOT NULL default '0',
				groupid int(11) NOT NULL default '0',
				optname varchar(128) NOT NULL default '',
				optvalue varchar(128) NOT NULL default '',
				PRIMARY KEY  (id)
			) TYPE=MyISAM;";
		}
		
		if (!in_array('weathermap_data', $tables)) {
			$sql[] = "CREATE TABLE IF NOT EXISTS weathermap_data (id int(11) NOT NULL auto_increment,
				rrdfile varchar(255) NOT NULL,data_source_name varchar(19) NOT NULL,
				  last_time int(11) NOT NULL,last_value varchar(255) NOT NULL,
				last_calc varchar(255) NOT NULL, sequence int(11) NOT NULL, local_data_id int(11) NOT NULL DEFAULT 0, PRIMARY KEY  (id), KEY rrdfile (rrdfile),
				  KEY local_data_id (local_data_id), KEY data_source_name (data_source_name) ) TYPE=MyISAM";
		}
		else
		{
			$colsql = "show columns from weathermap_data from " . $database_default;
			$result = mysql_query($colsql) or die (mysql_error());
			$found_ldi = false;
			
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if ($row['Field'] == 'local_data_id') $found_ldi = true;
			}
			if (!$found_ldi) 
			{
				$sql[] = "alter table weathermap_data add local_data_id int(11) NOT NULL default 0 after sequence";
				$sql[] = "alter table weathermap_data add index ( `local_data_id` )";
				# if there is existing data without a local_data_id, ditch it
				$sql[] = "delete from weathermap_data";
			}
		}

		// create the settings entries, if necessary

		$pagestyle = read_config_option("weathermap_pagestyle");
		if($pagestyle == '' or $pagestyle < 0 or $pagestyle >2)
		{
			$sql[] = "replace into settings values('weathermap_pagestyle',0)";
		}

		$cycledelay = read_config_option("weathermap_cycle_refresh");  
		if($cycledelay == '' or intval($cycledelay < 0) )
		{
			$sql[] = "replace into settings values('weathermap_cycle_refresh',0)";
		}

		$renderperiod = read_config_option("weathermap_render_period");  
		if($renderperiod == '' or intval($renderperiod < -1) )
		{
			$sql[] = "replace into settings values('weathermap_render_period',0)";
		}
		
		$quietlogging = read_config_option("weathermap_quiet_logging");  
		if($quietlogging == '' or intval($quietlogging < -1) )
		{
			$sql[] = "replace into settings values('weathermap_quiet_logging',0)";
		}

		$rendercounter = read_config_option("weathermap_render_counter");  
		if($rendercounter == '' or intval($rendercounter < 0) )
		{
			$sql[] = "replace into settings values('weathermap_render_counter',0)";
		}

		$outputformat = read_config_option("weathermap_output_format");  
		if($outputformat == '' )
		{
			$sql[] = "replace into settings values('weathermap_output_format','png')";
		}

		$tsize = read_config_option("weathermap_thumbsize");
		if($tsize == '' or $tsize < 1)
		{
			$sql[] = "replace into settings values('weathermap_thumbsize',250)";
		}

		$ms = read_config_option("weathermap_map_selector");
		if($ms == '' or intval($ms) < 0 or intval($ms) > 1)
		{
			$sql[] = "replace into settings values('weathermap_map_selector',1)";
		}

		$at = read_config_option("weathermap_all_tab");
		if($at == '' or intval($at) < 0 or intval($at) > 1)
		{
			$sql[] = "replace into settings values('weathermap_all_tab',0)";
		}

		// update the version, so we can skip this next time
		$sql[] = "replace into settings values('weathermap_db_version','$myversion')";
		
		// patch up the sortorder for any maps that don't have one.
		$sql[] = "update weathermap_maps set sortorder=id where sortorder is null or sortorder=0;";

		if (!empty($sql)) {
			for ($a = 0; $a < count($sql); $a++) {
				# cacti_log("Executing SQL: ".$sql[$a]."\n",true,"WEATHERMAP");
				$result = db_execute($sql[$a]);
			}
		}
	}
	else
	{
		# cacti_log("Skipping SQL updates\n",true,"WEATHERMAP");
	}
}

function weathermap_config_arrays () {
	global $user_auth_realms, $user_auth_realm_filenames, $menu;
	global $tree_item_types, $tree_item_handlers;

	if (function_exists('api_plugin_register_realm')) {
		api_plugin_register_realm('weathermap', 'weathermap-cacti-plugin.php', 'Plugin -> Weathermap: View', 1);
		api_plugin_register_realm('weathermap', 'weathermap-cacti-plugin-mgmt.php', 'Plugin -> Weathermap: Configure/Manage', 1);
	} else {		
		$user_auth_realms[42]='Plugin -> Weathermap: Configure/Manage';
		$user_auth_realms[43]='Plugin -> Weathermap: View';
		$user_auth_realm_filenames['weathermap-cacti-plugin.php'] = 43;
		$user_auth_realm_filenames['weathermap-cacti-plugin-mgmt.php'] = 42;
#		$user_auth_realm_filenames['weathermap-cacti-plugin-mgmt-groups.php'] = 42;
	}
	// if there is support for custom graph tree types, then register ourselves
	if(isset($tree_item_handlers))
	{
		$tree_item_types[10] = "Weathermap";
		$tree_item_handlers[10] = array("render" => "weathermap_tree_item_render",
					"name" => "weathermap_tree_item_name",
					"edit" => "weathermap_tree_item_edit");
	}

	$wm_menu = array(
		'plugins/weathermap/weathermap-cacti-plugin-mgmt.php' => "气象图",
		'plugins/weathermap/weathermap-cacti-plugin-mgmt-groups.php' => "分组"
	);
	
	$menu["管理"]['plugins/weathermap/weathermap-cacti-plugin-mgmt.php'] = $wm_menu;
	
}

function weathermap_tree_item_render($leaf)
{
	global $colors;

        $outdir = dirname(__FILE__).'/output/';
        $confdir = dirname(__FILE__).'/configs/';

	$map = db_fetch_assoc("select weathermap_maps.* from weathermap_auth,weathermap_maps where weathermap_maps.id=weathermap_auth.mapid and active='on' and (userid=".$_SESSION["sess_user_id"]." or userid=0) and weathermap_maps.id=".$leaf['item_id']);

	if(sizeof($map))
        {
                $htmlfile = $outdir."weathermap_".$map[0]['id'].".html";
                $maptitle = $map[0]['titlecache'];
                if($maptitle == '') $maptitle= "Map for config file: ".$map[0]['configfile'];
	
                html_graph_start_box(1,true);
?>
<tr bgcolor="<?php print $colors["panel"];?>">
  <td>
          <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                   <td class="textHeader" nowrap><?php print $maptitle; ?></td>
                </tr>
          </table>
  </td>
</tr>
<?php
                print "<tr><td>";
	
		if(file_exists($htmlfile))
		{
			include($htmlfile);
		}
		print "</td></tr>";
		html_graph_end_box();

	}
}

// calculate the name that cacti will use for this item in the tree views
function weathermap_tree_item_name($item_id)
{
        $description = db_fetch_cell("select titlecache from weathermap_maps where id=".intval($item_id));
	if($description == '')
	{
        	$configfile = db_fetch_cell("select configfile from weathermap_maps where id=".intval($item_id));
 		$description = "Map for config file: ".$configfile;
	}
	

	return $description;
}

// the edit form, for when you add or edit a map in a graph tree
function weathermap_tree_item_edit($tree_item)
{
	global $colors; 

	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0);
	print "<td width='50%'><font class='textEditTitle'>Map</font><br />Choose which weathermap to add to the tree.</td><td>";
	form_dropdown("item_id", db_fetch_assoc("select id,CONCAT_WS('',titlecache,' (',configfile,')') as name from weathermap_maps where active='on' order by titlecache, configfile"), "name", "id", $tree_item['item_id'], "", "0");
	print "</td></tr>";
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1);
	print "<td width='50%'><font class='textEditTitle'>Style</font><br />How should the map be displayed?</td><td>";
	print "<select name='item_options'><option value=1>Thumbnail</option><option value=2>Full Size</option></select>";
	print "</td></tr>";
}


function weathermap_show_tab () {
	global $config, $user_auth_realms, $user_auth_realm_filenames;
	$realm_id2 = 0;

	if (isset($user_auth_realm_filenames[basename('weathermap-cacti-plugin.php')])) {
		$realm_id2 = $user_auth_realm_filenames[basename('weathermap-cacti-plugin.php')];
	}

	$tabstyle = intval(read_config_option("superlinks_tabstyle"));
	$userid = (isset($_SESSION["sess_user_id"]) ? intval($_SESSION["sess_user_id"]) : 1);
	
	if ((db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id='" . $userid . "' and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2))) {

		if($tabstyle>0)
		{
			$prefix="s_";
		}
		else
		{
			$prefix="";
		}

		print '<a href="' . $config['url_path'] . 'plugins/weathermap/weathermap-cacti-plugin.php"><img src="' . $config['url_path'] . 'plugins/weathermap/images/'.$prefix.'tab_weathermap';
		// if we're ON a weathermap page, print '_red'
		if(preg_match('/plugins\/weathermap\/weathermap-cacti-plugin.php/',$_SERVER['REQUEST_URI'] ,$matches))
		{
			print "_red";
		}
		print '.gif" alt="weathermap" align="absmiddle" border="0"></a>';

	}

	weathermap_setup_table();
}

function weathermap_draw_navigation_text ($nav) {
	$nav["weathermap-cacti-plugin.php:"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:viewmap"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:liveview"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:liveviewimage"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:viewmapcycle"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:mrss"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:viewimage"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");
	$nav["weathermap-cacti-plugin.php:viewthumb"] = array("title" => "气象图", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin.php", "level" => "1");

	$nav["weathermap-cacti-plugin-mgmt.php:"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	//   $nav["weathermap-cacti-plugin-mgmt.php:addmap_picker"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:viewconfig"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:addmap"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:editmap"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:editor"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");

	//  "graphs.php:graph_edit" => array("title" => "(Edit)", "mapping" => "index.php:,graphs.php:", "url" => "", "level" => "2"),

	$nav["weathermap-cacti-plugin-mgmt.php:perms_edit"] = array("title" => "编辑权限", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:addmap_picker"] = array("title" => "添加气象图", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:map_settings"] = array("title" => "气象图设置", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:map_settings_form"] = array("title" => "气象图设置", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:map_settings_delete"] = array("title" => "气象图设置", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:map_settings_update"] = array("title" => "气象图设置", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");
	$nav["weathermap-cacti-plugin-mgmt.php:map_settings_add"] = array("title" => "气象图设置", "mapping" => "index.php:,weathermap-cacti-plugin-mgmt.php:", "url" => "", "level" => "2");


	// $nav["weathermap-cacti-plugin-mgmt.php:perms_edit"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:perms_add_user"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:perms_delete_user"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:delete_map"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:move_map_down"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:move_map_up"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:move_group_down"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:move_group_up"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:group_form"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:group_update"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:activate_map"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:deactivate_map"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:rebuildnow"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:rebuildnow2"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");

	$nav["weathermap-cacti-plugin-mgmt.php:chgroup"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:chgroup_update"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:groupadmin"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
	$nav["weathermap-cacti-plugin-mgmt.php:groupadmin_delete"] = array("title" => "气象图管理", "mapping" => "index.php:", "url" => "weathermap-cacti-plugin-mgmt.php", "level" => "1");
		
	return $nav;
}

function weathermap_poller_output($rrd_update_array) {
	global $config;
	// global $weathermap_debugging;

	$logging = read_config_option("log_verbosity");

	if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("WM poller_output: STARTING\n",true,"WEATHERMAP");

	// partially borrowed from Jimmy Conner's THold plugin.
	// (although I do things slightly differently - I go from filenames, and don't use the poller_interval)

	
	// $requiredlist = db_fetch_assoc("select distinct weathermap_data.*, data_template_data.local_data_id, data_template_rrd.data_source_type_id from weathermap_data, data_template_data, data_template_rrd where weathermap_data.rrdfile=data_template_data.data_source_path and data_template_rrd.local_data_id=data_template_data.local_data_id");
	// new version works with *either* a local_data_id or rrdfile in the weathermap_data table, and returns BOTH
	$requiredlist = db_fetch_assoc("select distinct weathermap_data.id, weathermap_data.last_value, weathermap_data.last_time, weathermap_data.data_source_name, data_template_data.data_source_path, data_template_data.local_data_id, data_template_rrd.data_source_type_id from weathermap_data, data_template_data, data_template_rrd where weathermap_data.local_data_id=data_template_data.local_data_id and data_template_rrd.local_data_id=data_template_data.local_data_id and weathermap_data.local_data_id<>0;");
	
	$path_rra = $config["rra_path"];
	
	# especially on Windows, it seems that filenames are not reliable (sometimes \ and sometimes / even though path_rra is always /) .
	# let's make an index from local_data_id to filename, and then use local_data_id as the key...
	
	foreach (array_keys($rrd_update_array) as $key)
	{
		if(isset( $rrd_update_array[$key]['times']) && is_array($rrd_update_array[$key]['times']) )
		{
			# if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("WM poller_output: Adding $key",true,"WEATHERMAP");
			$knownfiles[ $rrd_update_array[$key]["local_data_id"] ] = $key;
			
		}
	}
	
	foreach ($requiredlist as $required)
	{
		$file = str_replace("<path_rra>", $path_rra, $required['data_source_path']);
		$dsname = $required['data_source_name'];
		$local_data_id = $required['local_data_id'];
		
		if(isset($knownfiles[$local_data_id]))
		{
			$file2 = $knownfiles[$local_data_id];			
			if($file2 != '') $file = $file2;
		}
				
	    if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("WM poller_output: Looking for $file ($local_data_id) (".$required['data_source_path'].")\n",true,"WEATHERMAP");
		
		if( isset($rrd_update_array[$file]) && is_array($rrd_update_array[$file]) && isset($rrd_update_array[$file]['times']) && is_array($rrd_update_array[$file]['times']) && isset( $rrd_update_array{$file}['times'][key($rrd_update_array[$file]['times'])]{$dsname} ) )
		{
			$value = $rrd_update_array{$file}['times'][key($rrd_update_array[$file]['times'])]{$dsname};
			$time = key($rrd_update_array[$file]['times']);
			if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) 
				cacti_log("WM poller_output: Got one! $file:$dsname -> $time $value\n",true,"WEATHERMAP");
			
			$period = $time - $required['last_time'];
			$lastval = $required['last_value'];
			
			// if the new value is a NaN, we'll give 0 instead, and pretend it didn't happen from the point
			// of view of the counter etc. That way, we don't get those enormous spikes. Still doesn't deal with
			// reboots very well, but it should improve it for drops.
			if($value == 'U')
			{
				$newvalue = 0;
				$newlastvalue = $lastval;
				$newtime = $required['last_time'];
			}
			else
			{
				$newlastvalue = $value;
				$newtime = $time;
				
				switch($required['data_source_type_id'])
				{
					case 1: //GAUGE
						$newvalue = $value;
						break;
					
					case 2: //COUNTER
						if ($value >= $lastval) {
							// Everything is normal
							$newvalue = $value - $lastval;
						} else {
							// Possible overflow, see if its 32bit or 64bit
							if ($lastval > 4294967295) {
								$newvalue = (18446744073709551615 - $lastval) + $value;
							} else {
								$newvalue = (4294967295 - $lastval) + $value;
							}
						}
						$newvalue = $newvalue / $period;
						break;
					
					case 3: //DERIVE
						$newvalue = ($value-$lastval) / $period;
						break;
					
					case 4: //ABSOLUTE
						$newvalue = $value / $period;
						break;
					
					default: // do something somewhat sensible in case something odd happens
						$newvalue = $value;
						warn("poller_output found an unknown data_source_type_id for $file:$dsname");
						break;
				}
			}
			db_execute("UPDATE weathermap_data SET last_time=$newtime, last_calc='$newvalue', last_value='$newlastvalue',sequence=sequence+1  where id = " . $required['id']);
	        	if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("WM poller_output: Final value is $newvalue (was $lastval, period was $period)\n",true,"WEATHERMAP");
		}
		else
		{
			if(1==0 && $logging >= POLLER_VERBOSITY_DEBUG)
			{
			#	cacti_log("WM poller_output: ENDING\n",true,"WEATHERMAP");
				cacti_log("WM poller_output: Didn't find it.\n",true,"WEATHERMAP");
				cacti_log("WM poller_output: DID find these:\n",true,"WEATHERMAP");
				
				foreach (array_keys($rrd_update_array) as $key)
				{
					$local_data_id = $rrd_update_array[$key]["local_data_id"];
					cacti_log("WM poller_output:    $key ($local_data_id)\n",true,"WEATHERMAP");
				}			
			}
		}
	}

	if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("WM poller_output: ENDING\n",true,"WEATHERMAP");
	
	return $rrd_update_array;
}

function weathermap_poller_bottom() {
	global $config;
	global $weathermap_debugging, $WEATHERMAP_VERSION;

	include_once($config["library_path"] . DIRECTORY_SEPARATOR."database.php");
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."poller-common.php");

	weathermap_setup_table();

	$renderperiod = read_config_option("weathermap_render_period");  
	$rendercounter = read_config_option("weathermap_render_counter");  
	$quietlogging = read_config_option("weathermap_quiet_logging");  

	if($renderperiod<0)
	{
		// manual updates only
		if($quietlogging==0) cacti_log("Weathermap $WEATHERMAP_VERSION - no updates ever",true,"WEATHERMAP");
		return;
	}
	else
	{
		// if we're due, run the render updates
		if( ( $renderperiod == 0) || ( ($rendercounter % $renderperiod) == 0) )
		{
			weathermap_run_maps(dirname(__FILE__) );
		}
		else
		{
			if($quietlogging==0) cacti_log("Weathermap $WEATHERMAP_VERSION - no update in this cycle ($rendercounter)",true,"WEATHERMAP");
		}
		# cacti_log("Weathermap counter is $rendercounter. period is $renderperiod.", true, "WEATHERMAP");
		// increment the counter
		$newcount = ($rendercounter+1)%1000;
		db_execute("replace into settings values('weathermap_render_counter',".$newcount.")");
	}
}

// vim:ts=4:sw=4:
?>
