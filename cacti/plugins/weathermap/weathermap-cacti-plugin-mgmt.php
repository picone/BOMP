<?php

chdir('../../');
include_once("./include/auth.php");
include_once("./include/config.php");

include_once($config["library_path"] . "/database.php");

$weathermap_confdir = realpath(dirname(__FILE__).'/configs');

// include the weathermap class so that we can get the version
include_once(dirname(__FILE__)."/Weathermap.class.php");

$i_understand_file_permissions_and_how_to_fix_them = FALSE;

$action = "";
if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

switch ($action) {

case 'group_update':
	$id = -1;
	$newname = "";
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))  { $id = intval($_REQUEST['id']); }
	if( isset($_REQUEST['gname']) && (strlen($_REQUEST['gname'])>0) )  { $newname = $_REQUEST['gname']; }
	
	if($id >= 0 && $newname != "") weathermap_group_update($id,$newname);
	if($id < 0 && $newname != "") weathermap_group_create($newname);
	header("Location: weathermap-cacti-plugin-mgmt.php?action=groupadmin");	
	
	break;

case 'groupadmin_delete':

	$id = -1;
	
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))  { $id = intval($_REQUEST['id']); }
	
	if($id>=1)
	{
		weathermap_group_delete($id);
	}
	header("Location: weathermap-cacti-plugin-mgmt.php?action=groupadmin");	
	break;
	
case 'group_form':

	$id = -1;

	include_once($config["base_path"]."/include/top_header.php");
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))  { $id = intval($_REQUEST['id']); }
	
	if($id>=0)
	{
		weathermap_group_form($id);
	}
	
	weathermap_footer_links();
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;

case 'groupadmin':
	include_once($config["base_path"]."/include/top_header.php");
	weathermap_group_editor();
	weathermap_footer_links();
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;

case 'chgroup_update':
	$mapid = -1;
	$groupid = -1;

	if( isset($_REQUEST['map_id']) && is_numeric($_REQUEST['map_id']))  { $mapid = intval($_REQUEST['map_id']); }
	if( isset($_REQUEST['new_group']) && is_numeric($_REQUEST['new_group']))  { $groupid = intval($_REQUEST['new_group']); }

	if( ($groupid > 0) && ($mapid >= 0)) { weathermap_set_group($mapid,$groupid); }
	
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;

case 'chgroup':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
	{
		include_once($config["base_path"]."/include/top_header.php");
		weathermap_chgroup( intval($_REQUEST['id']) );
		include_once($config["base_path"]."/include/bottom_footer.php");
	}
	else
	{
		print "Something got lost back there.";
	}
	break;
	 
case 'map_settings_delete':
	$mapid = NULL;
	$settingid = NULL;
	if( isset($_REQUEST['mapid']) && is_numeric($_REQUEST['mapid']))  { $mapid = intval($_REQUEST['mapid']); }
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))  { $settingid = intval($_REQUEST['id']); }
		
	if(! is_null($mapid) && ! is_null($settingid) )
	{
		// create setting
		weathermap_setting_delete($mapid,$settingid);
	}	
	header("Location: weathermap-cacti-plugin-mgmt.php?action=map_settings&id=".$mapid);
	break;

// this is the save option from the map_settings_form
case 'save':
	$mapid = NULL;
	$settingid = NULL;
	$name=''; $value='';

	if( isset($_REQUEST['mapid']) && is_numeric($_REQUEST['mapid']))  { $mapid = intval($_REQUEST['mapid']); }
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))  { $settingid = intval($_REQUEST['id']); }
	
	if( isset($_REQUEST['name']) && $_REQUEST['name'])  { $name = $_REQUEST['name']; }
	if( isset($_REQUEST['value']) && $_REQUEST['value'])  { $value = $_REQUEST['value']; }
	
	if(! is_null($mapid) && $settingid==0 )
	{
		// create setting
		weathermap_setting_save($mapid,$name,$value);
	}
	elseif(! is_null($mapid) && ! is_null($settingid) )
	{
		// update setting
		weathermap_setting_update($mapid,$settingid,$name,$value);
	}	
	header("Location: weathermap-cacti-plugin-mgmt.php?action=map_settings&id=".$mapid);
	break;

case 'map_settings_form':
	if( isset($_REQUEST['mapid']) && is_numeric($_REQUEST['mapid']))
	{
		include_once($config["base_path"]."/include/top_header.php");
		
		if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
		{
			weathermap_map_settings_form(intval($_REQUEST['mapid']), intval($_REQUEST['id']) );
		}
		else
		{
			weathermap_map_settings_form(intval($_REQUEST['mapid']));
		}
				
		weathermap_footer_links();
		include_once($config["base_path"]."/include/bottom_footer.php");
	}
	break;
case 'map_settings':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		include_once($config["base_path"]."/include/top_header.php");
		weathermap_map_settings(intval($_REQUEST['id']));
		weathermap_footer_links();
		include_once($config["base_path"]."/include/bottom_footer.php");
	}
	break;
	
case 'perms_add_user':
	if( isset($_REQUEST['mapid']) && is_numeric($_REQUEST['mapid'])
		&& isset($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])
		)
	{
		perms_add_user(intval($_REQUEST['mapid']),intval($_REQUEST['userid']));
		header("Location: weathermap-cacti-plugin-mgmt.php?action=perms_edit&id=".intval($_REQUEST['mapid']));
	}
	break;
case 'perms_delete_user':
	if( isset($_REQUEST['mapid']) && is_numeric($_REQUEST['mapid'])
		&& isset($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])
		)
	{
		perms_delete_user($_REQUEST['mapid'],$_REQUEST['userid']);
		header("Location: weathermap-cacti-plugin-mgmt.php?action=perms_edit&id=".$_REQUEST['mapid']);
	}
	break;
case 'perms_edit':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
	{
		include_once($config["base_path"]."/include/top_header.php");
		perms_list($_REQUEST['id']);
		include_once($config["base_path"]."/include/bottom_footer.php");
	}
	else
	{
		print "Something got lost back there.";
	}
	break;



case 'delete_map':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) map_delete($_REQUEST['id']);
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;

case 'deactivate_map':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) map_deactivate($_REQUEST['id']);
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;

case 'activate_map':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) map_activate($_REQUEST['id']);
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;

case 'move_map_up':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
		isset($_REQUEST['order']) && is_numeric($_REQUEST['order']) )
		map_move($_REQUEST['id'],$_REQUEST['order'],-1);
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;
case 'move_map_down':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
		isset($_REQUEST['order']) && is_numeric($_REQUEST['order']) )
		map_move($_REQUEST['id'],$_REQUEST['order'],+1);
	header("Location: weathermap-cacti-plugin-mgmt.php");
	break;

case 'move_group_up':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
		isset($_REQUEST['order']) && is_numeric($_REQUEST['order']) )
		weathermap_group_move(intval($_REQUEST['id']),intval($_REQUEST['order']),-1);
	header("Location: weathermap-cacti-plugin-mgmt.php?action=groupadmin");
	break;
case 'move_group_down':
	if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
		isset($_REQUEST['order']) && is_numeric($_REQUEST['order']) )
		weathermap_group_move(intval($_REQUEST['id']),intval($_REQUEST['order']),1);
	header("Location: weathermap-cacti-plugin-mgmt.php?action=groupadmin");
	break;
	
case 'viewconfig':
	include_once($config["base_path"]."/include/top_graph_header.php");
	if(isset($_REQUEST['file']))
	{
		preview_config($_REQUEST['file']);
	}
	else
	{
		print "未找到文件.";
	}
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;

case 'addmap_picker':
	
	include_once($config["base_path"]."/include/top_header.php");
	if(isset($_REQUEST['show']) && $_REQUEST['show']=='all')
	{
		addmap_picker(true);
	}
	else
	{
		addmap_picker(false);
	}
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;

case 'addmap':
	if(isset($_REQUEST['file']))
	{
		add_config($_REQUEST['file']);
		header("Location: weathermap-cacti-plugin-mgmt.php");
	}
	else
	{
		print "未找到文件.";
	}

	break;

case 'editor':
	// chdir(dirname(__FILE__));
	// include_once('./weathermap-cacti-plugin-editor.php');
	break;

case 'rebuildnow':
	
	include_once($config["base_path"]."/include/top_header.php");

	print "<h3>真的重建所有气象图吗?</h3><strong>说明:因为您的Cacti采集器进程可能没有以apache用户运行,问题可能是文件权限引起,以正常方式运行Cacti采集器便会正常.在某些情况下也有可能是内存限制问题,请在php.ini文件里增加内存.</strong><hr>";

	print "<p>在没有找到问题原因前建议您不要使用该功能.</p>";
	print "<h4><a href=\"weathermap-cacti-plugin-mgmt.php?action=rebuildnow2\">是</a></h4>";
	print "<h1><a href=\"weathermap-cacti-plugin-mgmt.php\">否</a></h1>";
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;

case 'rebuildnow2':
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."Weathermap.class.php");
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."poller-common.php");

		include_once($config["base_path"]."/include/top_header.php");
	print "<h3>重建所有气象图</h3><strong>说明:因为您的Cacti采集器进程可能没有以apache用户运行,问题可能是文件权限引起,以正常方式运行Cacti采集>器便会正常.在某些情况下也有可能是内存限制问题,请在php.ini文件里增加内存.</strong><hr><pre>";
	weathermap_run_maps(dirname(__FILE__));
	print "</pre>";
	print "<hr /><h3>Done.</h3>";
	include_once($config["base_path"]."/include/bottom_footer.php");

		break;

	// by default, just list the map setup
default:
	include_once($config["base_path"]."/include/top_header.php");
	maplist();
	//weathermap_footer_links();
	include_once($config["base_path"]."/include/bottom_footer.php");
	break;
}

///////////////////////////////////////////////////////////////////////////

function weathermap_footer_links()
{
	global $colors;
	global $WEATHERMAP_VERSION;
	print '<br />'; 
    html_start_box("<center><a target=\"_blank\" class=\"linkOverDark\" href=\"docs/\">本地文档(英文)</a> -- <a target=\"_blank\" class=\"linkOverDark\" href=\"http://www.network-weathermap.com/\">气象图网站</a> -- <a target=\"_target\" class=\"linkOverDark\" href=\"editor.php?plug=1\">气象图编辑器</a> -- 当前版本$WEATHERMAP_VERSION</center>", "100%", $colors["header"], "2", "center", "");
	html_end_box(); 
}

// Repair the sort order column (for when something is deleted or inserted, or moved between groups)
// our primary concern is to make the sort order consistent, rather than any special 'correctness'
function map_resort()
{
	$list = db_fetch_assoc("select * from weathermap_maps order by group_id,sortorder;");
	$i = 1;
	$last_group = -1020.5;
	foreach ($list as $map)
	{
		if($last_group != $map['group_id']) 
		{
			$last_group  = $map['group_id'];
			$i=1;
		}
		$sql[] = "update weathermap_maps set sortorder = $i where id = ".$map['id'];
		$i++;
	}
	if (!empty($sql)) {
		for ($a = 0; $a < count($sql); $a++) {
			$result = db_execute($sql[$a]);
		}
	}
}

// Repair the sort order column (for when something is deleted or inserted)
function weathermap_group_resort()
{
	$list = db_fetch_assoc("select * from weathermap_groups order by sortorder;");
	$i = 1;
	foreach ($list as $group)
	{
		$sql[] = "update weathermap_groups set sortorder = $i where id = ".$group['id'];
		$i++;
	}
	if (!empty($sql)) {
		for ($a = 0; $a < count($sql); $a++) {
			$result = db_execute($sql[$a]);
		}
	}
}

function map_move($mapid,$junk,$direction)
{
	$source = db_fetch_assoc("select * from weathermap_maps where id=$mapid");
	$oldorder = $source[0]['sortorder'];
	$group = $source[0]['group_id'];

	$neworder = $oldorder + $direction;
	$target = db_fetch_assoc("select * from weathermap_maps where group_id=$group and sortorder = $neworder");

	if(!empty($target[0]['id']))
	{
		$otherid = $target[0]['id'];
		// move $mapid in direction $direction
		$sql[] = "update weathermap_maps set sortorder = $neworder where id=$mapid";
		// then find the other one with the same sortorder and move that in the opposite direction
		$sql[] = "update weathermap_maps set sortorder = $oldorder where id=$otherid";
	}
	if (!empty($sql)) {
		for ($a = 0; $a < count($sql); $a++) {
			$result = db_execute($sql[$a]);
		}
	}
}

function weathermap_group_move($id,$junk,$direction)
{
	$source = db_fetch_assoc("select * from weathermap_groups where id=$id");
	$oldorder = $source[0]['sortorder'];

	$neworder = $oldorder + $direction;
	$target = db_fetch_assoc("select * from weathermap_groups where sortorder = $neworder");

	if(!empty($target[0]['id']))
	{
		$otherid = $target[0]['id'];
		// move $mapid in direction $direction
		$sql[] = "update weathermap_groups set sortorder = $neworder where id=$id";
		// then find the other one with the same sortorder and move that in the opposite direction
		$sql[] = "update weathermap_groups set sortorder = $oldorder where id=$otherid";
	}
	if (!empty($sql)) {
		for ($a = 0; $a < count($sql); $a++) {
			$result = db_execute($sql[$a]);
		}
	}
}

function maplist()
{
	global $colors, $menu;
	global $i_understand_file_permissions_and_how_to_fix_them;

	#print "<pre>";
	#print_r($menu);
	#print "</pre>";

	$last_started = read_config_option("weathermap_last_started_file",true);
	$last_finished = read_config_option("weathermap_last_finished_file",true);
	$last_start_time = intval(read_config_option("weathermap_last_start_time",true));
	$last_finish_time = intval(read_config_option("weathermap_last_finish_time",true));
	$poller_interval = intval(read_config_option("poller_interval"));

	if( ($last_finish_time - $last_start_time) > $poller_interval ) {

	if( ($last_started != $last_finished) && ($last_started != "") ) {
		print '<div align="center" class="wm_warning"><p>';
		print "上次运行,气象图运行未完成.当处理'$last_started'时失败. ";
		print "这<strong>或许</strong>是因为受到其它插件的影响. </p><p>";
		print "您应该禁用该气象图,然后查找错误.也可能是内存限制问题.Cacti日志里有更多的可用信息.";
		print '</p></div>';
	}
	}
	echo '<div style="padding-top:5px;"><a href="editor.php"><button>创建新的流量图</button></a></div>';
	echo '<div style="padding-top:5px;"><a href="weathermap-cacti-plugin-mgmt.php?action=addmap_picker"><button>添加流量图到运行</button></a></div>';
	html_start_box("<strong>正在运行的流量图</strong>", "100%", $colors["header"], "3", "center",'');

	html_header(array("配置文件", "标题", "分组", "使用", "设置", "排序", "可访问用户",""));

	$query = db_fetch_assoc("select id,username from user_auth");
	$users[0] = '任何人';

	foreach ($query as $user)
	{
		$users[$user['id']] = $user['username'];
	}

	$i = 0;
	$queryrows = db_fetch_assoc("select weathermap_maps.*, weathermap_groups.name as groupname from weathermap_maps, weathermap_groups where weathermap_maps.group_id=weathermap_groups.id order by weathermap_groups.sortorder,sortorder");
	// or die (mysql_error("Could not connect to database") )

	$previous_id = -2;
	$had_warnings = 0;
	if( is_array($queryrows) )
	{
		form_alternate_row_color($colors["alternate"],$colors["light"],$i);
		print "<td>全部流量图</td><td>(指定设置给所有流量图)</td><td></td><td></td>";
		
		print "<td><a href='?action=map_settings&id=0'>";
		$setting_count = db_fetch_cell("select count(*) from weathermap_settings where mapid=0 and groupid=0");
		if($setting_count > 0)
		{
			print $setting_count." special";
			if($setting_count>1) print "s";
		}
		else
		{
			print "standard";
		}
		print "</a>";
		
		print "</td>";
		print "<td></td>";
		print "<td></td>";
		print "<td></td>";
		print "</tr>";
		$i++;
		
		foreach ($queryrows as $map)
		{
			form_alternate_row_color($colors["alternate"],$colors["light"],$i);

			print '<td><a title="使用编辑器编辑该文件" href="editor.php?plug=1&mapname='.htmlspecialchars($map['configfile']).'">'.htmlspecialchars($map['configfile']).'</a>';
			if($map['warncount']>0)
			{
				$had_warnings++;
				
				print '<a href="../../utilities.php?tail_lines=500&message_type=2&action=view_logfile&filter='.urlencode($map['configfile']).'" title="请检查Cacti日志中这个气象图"><img border=0 src="images/exclamation.png" title="'.$map['warncount'].'该气象图上次运行时发出警告.检查您的日志.">'.$map['warncount']."</a>";
			}
			print "</td>";
			
			#		print '<a href="?action=editor&plug=1&mapname='.htmlspecialchars($map['configfile']).'">[edit]</a></td>';
			print '<td>'.htmlspecialchars($map['titlecache']).'</td>';
			print '<td><a title="点击更改分组" href="?action=chgroup&id='.$map['id'].'">'.htmlspecialchars($map['groupname']).'</a></td>';
						
			if($map['active'] == 'on')
			{
				print '<td class="wm_enabled"><a title="点击不激活" href="?action=deactivate_map&id='.$map['id'].'"><font color="green">是</font></a>';
			}
			else
			{
				print '<td class="wm_disabled"><a title="点击激活" href="?action=activate_map&id='.$map['id'].'"><font color="red">否</font></a>';
			}			
			print "<td>";
			
			print "<a href='?action=map_settings&id=".$map['id']."'>";
			$setting_count = db_fetch_cell("select count(*) from weathermap_settings where mapid=".$map['id']);
			if($setting_count > 0)
			{
				print $setting_count." special";
				if($setting_count>1) print "s";
			}
			else
			{
				print "standard";
			}
			print "</a>";
			
			print "</td>";
			
			print '</td>';

			print '<td>';

			print '<a href="?action=move_map_up&order='.$map['sortorder'].'&id='.$map['id'].'"><img src="../../images/move_up.gif" width="14" height="10" border="0" alt="Move Map Up" title="上移"></a>';
			print '<a href="?action=move_map_down&order='.$map['sortorder'].'&id='.$map['id'].'"><img src="../../images/move_down.gif" width="14" height="10" border="0" alt="Move Map Down" title="下移"></a>';
// print $map['sortorder'];

			print "</td>";

			print '<td>';
			$UserSQL = 'select * from weathermap_auth where mapid='.$map['id'].' order by userid';
			$userlist = db_fetch_assoc($UserSQL);

			$mapusers = array();
			foreach ($userlist as $user)
			{
				if(array_key_exists($user['userid'],$users))
				{
					$mapusers[] = $users[$user['userid']];
				}
			}

			print '<a title="点击编辑权限" href="?action=perms_edit&id='.$map['id'].'">';
			if(count($mapusers) == 0)
			{
				print "(无用户)";
			}
			else
			{
				print join(", ",$mapusers);
			}
			print '</a>';

			print '</td>';
			//  print '<td><a href="?action=editor&mapname='.urlencode($map['configfile']).'">Edit Map</a></td>';
			print '<td>';
			print '<a href="?action=delete_map&id='.$map['id'].'"><img src="../../images/delete_icon.gif" width="10" height="10" border="0" alt="Delete Map" title="删除气象图"></a>';
			print '</td>';

			print '</tr>';
			$i++;
		}
	}

	if($i==0)
	{
		print "<tr><td><em>无已配置的气象图</em></td></tr>\n";
	}

	html_end_box();
/*
        $last_stats = read_config_option("weathermap_last_stats", true);

	if($last_stats != "") {
		print "<div align='center'><strong>上次完整运行:</strong> $last_stats</div>";
	} else {
		
	}
	
	if($had_warnings>0)
	{
		print '<div align="center" class="wm_warning">'.$had_warnings.' 上次运行,您的气象图产生了警告.您可以尝试查找Cacti日志中的错误</div>';
	}
	
	print "<div align='center'>";	
	print "<a href='weathermap-cacti-plugin-mgmt.php?action=groupadmin'><img src='images/button_editgroups.png' border=0 alt='Edit Groups' /></a>";
	print "&nbsp;<a href='../../settings.php?tab=misc'><img src='images/button_settings.gif' border=0 alt='Settings' /></a>";
	if($i>0 && $i_understand_file_permissions_and_how_to_fix_them)
	{
		print '<br /><a href="?action=rebuildnow"><img src="images/btn_recalc.png" border="0" alt="Rebuild All Maps Right Now"><br />(正常情况下您不需要使用该功能)</a><br />';
	}
	print "</div>";
*/
}

function addmap_picker($show_all=false)
{
	global $weathermap_confdir;
	global $colors;

	$loaded=array();
	$flags=array();
	// find out what maps are already in the database, so we can skip those
	$queryrows = db_fetch_assoc("select * from weathermap_maps");
	if( is_array($queryrows) )
	{
		foreach ($queryrows as $map)
		{
			$loaded[]=$map['configfile'];
			
		}
	}
	$loaded[]='index.php';

	html_start_box("<strong>可用流量图配置文件</strong>", "100%", $colors["header"], "1", "center", "");

	if( is_dir($weathermap_confdir))
	{
		$n=0;
		$dh = opendir($weathermap_confdir);
		if($dh)
		{
			$i = 0; $skipped = 0;
			html_header(array("","","配置文件", "标题",""),2);

			while($file = readdir($dh))
			{
				$realfile = $weathermap_confdir.'/'.$file;
				
				$used = in_array($file,$loaded);
				$flags[$file] = '';
				if($used) $flags[$file] = 'USED';
				
				if( is_file($realfile) )
				{
					if( $used && !$show_all)
					{
						$skipped++;
					}
					else
					{
						$title = wmap_get_title($realfile);
						$titles[$file] = $title;
						$i++;
					}
				}
			}
			closedir($dh);
			
			if($i>0)
			{
				ksort($titles);
			
				$i=0;
				foreach ($titles as $file=>$title)
				{
					$title = $titles[$file];
					form_alternate_row_color($colors["alternate"],$colors["light"],$i);
					print '<td><a href="?action=addmap&amp;file='.$file.'" title="添加配置文件">添加</a></td>';
					print '<td><a href="?action=viewconfig&amp;file='.$file.'" title="在新窗口查看配置文件" target="_blank">查看</a></td>';
					print '<td>'.htmlspecialchars($file);
					if($flags[$file] == 'USED') print ' <b>(正在使用)</b>';
					print '</td>';
					print '<td><em>'.htmlspecialchars($title).'</em></td>';
					print '</tr>';
					$i++;
				}
			}
			
			if( ($i + $skipped) == 0 )
			{
				print "<tr><td>在配置文件目录里未找到文件.</td></tr>";
			}		

			if( ($i == 0) && $skipped>0)
			{
				print "<tr><td>($skipped 文件将不显示,因为它们已经在数据库中</td></tr>";
			}
		}
		else
		{
			print "<tr><td>无法访问 $weathermap_confdir - 您应该设置为让apache可读该目录.</td></tr>";
		}
	}
	else
	{
		print "<tr><td>$weathermap_confdir不是一个目录 - 您需要创建它,然后设置它为apache可读.如果您希望在Cacti里修改它,它应该为apache <i>可写</i>.</td></tr>";
	}

	html_end_box();

	if($skipped>0)
	{
		print "<p align=center>某些文件未显示,因为它们正在使用.您可以 <a href='?action=addmap_picker&show=all'> 显示它们</a>.</p>";
	}
	if($show_all)
	{
		print "<p align=center>某些文件已经在使用,您可以 <a href='?action=addmap_picker'>隐藏它们</a>.</p>";
	}
	
}

function preview_config($file)
{
	global $weathermap_confdir;
	global $colors;

	chdir($weathermap_confdir);

	$path_parts = pathinfo($file);
	$file_dir = realpath($path_parts['dirname']);

	if($file_dir != $weathermap_confdir)
	{
		// someone is trying to read arbitrary files?
		// print "$file_dir != $weathermap_confdir";
		print "<h3>路径不匹配</h3>";
	}
	else
	{
		html_start_box("<strong>预览文件 $file</strong>", "98%", $colors["header"], "3", "center", "");

		print '<tr><td valign="top" bgcolor="#'.$colors["light"].'" class="textArea">';
		print '<pre>';
		$realfile = $weathermap_confdir.'/'.$file;
		if( is_file($realfile) )
		{
			$fd = fopen($realfile,"r");
			while (!feof($fd))
			{
				$buffer = fgets($fd,4096);
				print $buffer;
			}
			fclose($fd);
		}
		print '</pre>';
		print '</td></tr>';
		html_end_box();
	}
}

function add_config($file)
{
	global $weathermap_confdir;
	global $colors;

	chdir($weathermap_confdir);

	$path_parts = pathinfo($file);
	$file_dir = realpath($path_parts['dirname']);

	if($file_dir != $weathermap_confdir)
	{
		// someone is trying to read arbitrary files?
		// print "$file_dir != $weathermap_confdir";
		print "<h3>路径不匹配</h3>";
	}
	else
	{
		$realfile = $weathermap_confdir.DIRECTORY_SEPARATOR.$file;
		$title = wmap_get_title($realfile);

		$file = mysql_real_escape_string($file);
		$title = mysql_real_escape_string($title);
		$SQL = "insert into weathermap_maps (configfile,titlecache,active,imagefile,htmlfile,filehash,config) VALUES ('$file','$title','on','','','','')";
		db_execute($SQL);

		// add auth for 'admin'
		$last_id = mysql_insert_id();
		// $myuid = (int)$_SESSION["sess_user_id"];
		$myuid = (isset($_SESSION["sess_user_id"]) ? intval($_SESSION["sess_user_id"]) : 1);
		$SQL = "insert into weathermap_auth (mapid,userid) VALUES ($last_id,$myuid)";
		db_execute($SQL);
		
		db_execute("update weathermap_maps set filehash=LEFT(MD5(concat(id,configfile,rand())),20) where id=$last_id");

		map_resort();
	}
}

function wmap_get_title($filename)
{
	$title = "(no title)";
	$fd = fopen($filename,"r");
	while (!feof($fd))
	{
		$buffer = fgets($fd,4096);
		if(preg_match("/^\s*TITLE\s+(.*)/i",$buffer, $matches))
		{
			$title = $matches[1];
		}
		// this regexp is tweaked from the ReadConfig version, to only match TITLEPOS lines *with* a title appended
		if(preg_match("/^\s*TITLEPOS\s+\d+\s+\d+\s+(.+)/i",$buffer, $matches))
		{
			$title = $matches[1];
		}
		// strip out any DOS line endings that got through
		$title=str_replace("\r", "", $title);
	}
	fclose($fd);

	return($title);
}

function map_deactivate($id)
{
	$SQL = "update weathermap_maps set active='off' where id=".$id;
	db_execute($SQL);
}

function map_activate($id)
{
	$SQL = "update weathermap_maps set active='on' where id=".$id;
	db_execute($SQL);
}

function map_delete($id)
{
	$SQL = "delete from weathermap_maps where id=".$id;
	db_execute($SQL);

	$SQL = "delete from weathermap_auth where mapid=".$id;
	db_execute($SQL);

	$SQL = "delete from weathermap_settings where mapid=".$id;
	db_execute($SQL);
	
	map_resort();
}

function weathermap_set_group($mapid,$groupid)
{
	# print "UPDATING";
	$SQL = sprintf("update weathermap_maps set group_id=%d where id=%d", $groupid, $mapid);
	db_execute($SQL);
	map_resort();
}

function perms_add_user($mapid,$userid)
{
	$SQL = "insert into weathermap_auth (mapid,userid) values($mapid,$userid)";
	db_execute($SQL);
}

function perms_delete_user($mapid,$userid)
{
	$SQL = "delete from weathermap_auth where mapid=$mapid and userid=$userid";
	db_execute($SQL);
}

function perms_list($id)
{
	global $colors;

	// $title_sql = "select titlecache from weathermap_maps where id=$id";
	$title = db_fetch_cell("select titlecache from weathermap_maps where id=".intval($id));
	// $title = $results[0]['titlecache'];

	$auth_sql = "select * from weathermap_auth where mapid=$id order by userid";

	$query = db_fetch_assoc("select id,username from user_auth order by username");
	$users[0] = '任何人';
	foreach ($query as $user)
	{
		$users[$user['id']] = $user['username'];
	}

	$auth_results = db_fetch_assoc($auth_sql);
	$mapusers = array();
	$mapuserids = array();
	foreach ($auth_results as $user)
	{
		if(isset($users[$user['userid']]))
		{
			$mapusers[] = $users[$user['userid']];
			$mapuserids[] = $user['userid'];
		}
	}

	$userselect="";
	foreach ($users as $uid => $name)
	{
		if(! in_array($uid,$mapuserids))    $userselect .= "<option value=\"$uid\">$name</option>\n";
	}

	html_start_box("<strong>编辑权限 $id: $title</strong>", "100%", $colors["header"], "2", "center", "");
	html_header(array("用户名", ""));

	$n = 0;
	foreach($mapuserids as $user)
	{
		form_alternate_row_color($colors["alternate"],$colors["light"],$n);
		print "<td>".$users[$user]."</td>";
		print '<td><a href="?action=perms_delete_user&mapid='.$id.'&userid='.$user.'"><img src="../../images/delete_icon.gif" width="10" height="10" border="0" alt="Remove permissions for this user to see this map"></a></td>';

		print "</tr>";
		$n++;
	}
	if($n==0)
	{
		print "<tr><td><em><strong>没有人</strong>可以查看这个气象图</em></td></tr>";
	}
	html_end_box();

	html_start_box("", "100%", $colors["header"], "3", "center", "");
	print "<tr>";
	if($userselect == '')
	{
		print "<td><em>没有任何可以添加的用户!</em></td></tr>";
	}
	else
	{
		print "<td><form action=\"\">允许 <input type=\"hidden\" name=\"action\" value=\"perms_add_user\"><input type=\"hidden\" name=\"mapid\" value=\"$id\"><select name=\"userid\">";
		print $userselect;
		print "</select> 查看这个气象图 <input type=\"submit\" value=\"确定\"></form></td>";
		print "</tr>";
	}
	html_end_box();
}

function weathermap_map_settings($id)
{
	global $colors, $config;
	
	if($id==0)
	{
		$title = "给所有气象图添加设置";
		$nonemsg = "目前没有给所有气象图添加的设置.您可以添加一些,或从管理页面选择一个单独的气象图添加设置.";
		$type = "global";
		$settingrows = db_fetch_assoc("select * from weathermap_settings where mapid=0 and groupid=0");
		 
	}
	elseif($id<0)
	{
		$group_id = -intval($id);
		$groupname = db_fetch_cell("select name from weathermap_groups where id=".$group_id);		
		$title = "根据分组设置各气象图 ". $group_id . ": " . $groupname;
		$nonemsg = "这个分组未设置.您可以添加一些设置.";
		$type="group";
		$settingrows = db_fetch_assoc("select * from weathermap_settings where groupid=".$group_id);
	}
	else
	{
		// print "Per-map settings for map $id";
		$map = db_fetch_row("select * from weathermap_maps where id=".intval($id));
		
		$groupname = db_fetch_cell("select name from weathermap_groups where id=".intval($map['group_id']));	
		$title = "根据气象图设置各图 $id: " . $map['titlecache'];
		$nonemsg = "这个气象图未设置.您可以添加一些设置.";
		$type = "map";
		$settingrows = db_fetch_assoc("select * from weathermap_settings where mapid=".intval($id));
	}

	if($type == "group")
	{
		print "<p>这个分组里所有的气象图都会受到全局设置的影响(分组优于全局,气象图优于分组,但两个都优于气象图的配置文件):</p>";
		weathermap_readonly_settings(0, "全局设置");
		
	}
	
	if($type == "map")
	{
		print "<p>该气象图受以下全局和分组设置影响(分组优于全局,气象图优于分组,但两个都优于气象图配置文件):</p>";
		
		weathermap_readonly_settings(0, "全局设置");
		
		weathermap_readonly_settings(-$map['group_id'], "全局设置 (".htmlspecialchars($groupname).")");
		
	}
	
	html_start_box("<strong>$title</strong>", "100%", $colors["header"], "2", "center", "weathermap-cacti-plugin-mgmt.php?action=map_settings_form&mapid=".intval($id));
	html_header(array("","名称", "值",""));
	
	$n=0;

	
	
	if( is_array($settingrows) )
	{
		if(sizeof($settingrows)>0)
		{
			foreach( $settingrows as $setting)
			{
				form_alternate_row_color($colors["alternate"],$colors["light"],$n);
				print '<td><a href="?action=map_settings_form&mapid='.$id.'&id='.intval($setting['id']).'"><img src="../../images/graph_properties.gif" width="16" height="16" border="0" alt="Edit this definition">编辑</a></td>';
				print "<td>".htmlspecialchars($setting['optname'])."</td>";
				print "<td>".htmlspecialchars($setting['optvalue'])."</td>";
				print '<td><a href="?action=map_settings_delete&mapid='.$id.'&id='.intval($setting['id']).'"><img src="../../images/delete_icon_large.gif" width="12" height="12" border="0" alt="Remove this definition from this map"></a></td>';
				print "</tr>";
				$n++;
			}
		}
		else
		{
			print "<tr>";
			print "<td colspan=2>$nonemsg</td>";
			print "</tr>";
		}
	}
	
	html_end_box();
	
	print "<div align=center>";
	if($type == "group") print "<a href='?action=groupadmin'>返回分组管理</a>";
	if($type == "global") print "<a href='?action='>返回气象图管理</a>";
	print "</div>";
}

function weathermap_readonly_settings($id,$title="设置")
{
	global $colors, $config;

	if($id == 0) $query = "select * from weathermap_settings where mapid=0 and groupid=0";
	if($id < 0) $query = "select * from weathermap_settings where mapid=0 and groupid=".(-intval($id));
	if($id > 0) $query = "select * from weathermap_settings where mapid=".intval($id);
	
	$settings = db_fetch_assoc($query);
		
	html_start_box("<strong>$title</strong>", "100%", $colors["header"], "2", "center", "");
	html_header(array("","名称", "值",""));
	
	$n=0;
	
	if(sizeof($settings)>0)
	{
		foreach($settings as $setting)
		{
			form_alternate_row_color($colors["alternate"],$colors["light"],$n);
			print "<td></td>";
			print "<td>".htmlspecialchars($setting['optname'])."</td><td>".htmlspecialchars($setting['optvalue'])."</td>";
			print "<td></td>";
			print "</tr>";
			$n++;
		}
	}
	else
	{
		form_alternate_row_color($colors["alternate"],$colors["light"],$n);
		print "<td colspan=4><em>无设置</em></td>";
		print "</tr>";
	}
		
	html_end_box();
	
}

function weathermap_map_settings_form($mapid=0,$settingid=0)
{
	global $colors, $config;
	
	// print "Per-map settings for map $id";
	
	if($mapid > 0)	$title = db_fetch_cell("select titlecache from weathermap_maps where id=".intval( $mapid ));		
	if($mapid < 0)	$title = db_fetch_cell("select name from weathermap_groups where id=".intval( -$mapid ));		
	// print "Settings edit/add form.";
	
	$name = "";
	$value = "";
	
	if($settingid != 0)
	{
		
		$result = db_fetch_assoc("select * from weathermap_settings where id=".intval($settingid));
		
		if(is_array($result) && sizeof($result)>0)
		{
			$name = $result[0]['optname'];
			$value = $result[0]['optvalue'];
		}
	}
	
	# print "$mapid $settingid |$name| |$value|";
			
	$values_ar = array();
	
	$field_ar = array(
		"mapid" => array("friendly_name" => "气象图ID", "method" => "hidden_zero", "value" => $mapid ) ,
		"id" => array("friendly_name" => "设置ID", "method" => "hidden_zero", "value" => $settingid ) ,
		"name" => array("friendly_name" => "名称", "method" => "textbox", "max_length"=>128,"description"=>"气象图全局设置变更的名称", "value"=>$name),
		"value" => array("friendly_name" => "值", "method" => "textbox", "max_length"=>128, "description"=>"设置为什么", "value"=>$value)		
	);

	$action = "Edit";
	if($settingid == 0) $action ="添加";
	
	if($mapid == 0)
	{
		$title = "设置所有气象图";
	}
	elseif($mapid < 0)
	{
		$grpid = -$mapid;
		$title = "按分组设置各图形 $grpid: $title";
	}
	else
	{
		$title = "按气象图设置各图形 $mapid: $title";
	}
	
	html_start_box("<strong>$action $title</strong>", "98%", $colors["header"], "3", "center", "");
	draw_edit_form( array("config"=>$values_ar, "fields"=>$field_ar) );
	html_end_box();

	form_save_button("weathermap-cacti-plugin-mgmt.php?action=map_settings&id=".$mapid);	
	
}

function weathermap_setting_save($mapid,$name,$value) 
{
	if($mapid >0)
	{
		db_execute("insert into weathermap_settings (mapid, optname, optvalue) values ($mapid,'".mysql_real_escape_string($name)."','".mysql_real_escape_string($value)."')");
	}
	elseif($mapid <0)
	{
		db_execute("insert into weathermap_settings (mapid, groupid, optname, optvalue) values (0, -$mapid,'".mysql_real_escape_string($name)."','".mysql_real_escape_string($value)."')");
	}
	else
	{
		db_execute("insert into weathermap_settings (mapid, groupid, optname, optvalue) values (0, 0,'".mysql_real_escape_string($name)."','".mysql_real_escape_string($value)."')");
	}
} 
function weathermap_setting_update($mapid,$settingid,$name,$value) 
{	
	db_execute("update weathermap_settings set optname='".mysql_real_escape_string($name)."', optvalue='".mysql_real_escape_string($value)."' where id=".intval($settingid));
} 

function weathermap_setting_delete($mapid,$settingid) 
{
	db_execute("delete from weathermap_settings where id=".intval($settingid)." and mapid=".intval($mapid));
} 

function weathermap_chgroup($id)
{
	global $colors;

	$title = db_fetch_cell("select titlecache from weathermap_maps where id=".intval($id));
	$curgroup = db_fetch_cell("select group_id from weathermap_maps where id=".intval($id));

	$n=0;
	
	print "<form>";
	print "<input type=hidden name='map_id' value='".$id."'>";
	print "<input type=hidden name='action' value='chgroup_update'>";
	html_start_box("<strong>编辑气象图分组: $id: $title</strong>", "100%", $colors["header"], "2", "center", "");

	# html_header(array("Group Name", ""));
	form_alternate_row_color($colors["alternate"],$colors["light"],$n++);
	print "<td><strong>选择一个已存在的分组:</strong><select name='new_group'>";
	$SQL = "select * from weathermap_groups order by sortorder";
	$results = db_fetch_assoc($SQL);	
	
	foreach ($results as $grp)
	{
		print "<option ";
		if($grp['id'] == $curgroup) print " SELECTED ";
		print "value=".$grp['id'].">".htmlspecialchars($grp['name'])."</option>";
	}
	
	print "</select>";
	print '<input type="image" src="../../images/button_save.gif"  border="0" alt="Change Group" title="更改分组" />';
	print "</td>";
	print "</tr>\n";
	print "<tr><td></td></tr>";
	
	print "<tr><td><p>或在<strong><a href='?action=groupadmin'>分组管理页面</a></strong>添加一个分组</p></td></tr>";

	html_end_box();
	print "</form>\n";
}

function weathermap_group_form($id=0)
{
	global $colors, $config;

	$grouptext = "";
	// if id==0, it's an Add, otherwise it's an editor.
	if($id == 0)
	{
		print "添加一个分组...";
	}
	else
	{
		print "编辑分组 $id\n";
		$grouptext = db_fetch_cell("select name from weathermap_groups where id=".$id);
	}
	
	print "<form action=weathermap-cacti-plugin-mgmt.php>\n<input type=hidden name=action value=group_update />\n";
	
	print "分组名称: <input name=gname value='".htmlspecialchars($grouptext)."'/>\n";
	if($id>0)
	{
		print "<input type=hidden name=id value=$id />\n";
		print "分组名称: <input type=submit value='更新' />\n";
	}
	else
	{
		# print "<input type=hidden name=id value=$id />\n";
		print "分组名称: <input type=submit value='添加' />\n";
	}
	
	print "</form>\n";
	
}

function weathermap_group_editor()
{
	global $colors, $config;

	html_start_box("<strong>编辑气象图分组</strong>", "100%", $colors["header"], "2", "center", "weathermap-cacti-plugin-mgmt.php?action=group_form&id=0");
	html_header(array("", "分组名称", "设置", "排序方法", ""));
		
	$groups = db_fetch_assoc("select * from weathermap_groups order by sortorder");

	$n = 0;
	
	if( is_array($groups) )
	{
		if(sizeof($groups)>0)
		{
			foreach( $groups as $group)
			{
				form_alternate_row_color($colors["alternate"],$colors["light"],$n);
				print '<td><a href="weathermap-cacti-plugin-mgmt.php?action=group_form&id='.intval($group['id']).'"><img src="../../images/graph_properties.gif" width="16" height="16" border="0" alt="Rename This Group" title="重命名该分组">重命名</a></td>';
				print "<td>".htmlspecialchars($group['name'])."</td>";

				print "<td>";
			
			print "<a href='?action=map_settings&id=-".$group['id']."'>";
			$setting_count = db_fetch_cell("select count(*) from weathermap_settings where mapid=0 and groupid=".$group['id']);
			if($setting_count > 0)
			{
				print $setting_count." special";
				if($setting_count>1) print "s";
			}
			else
			{
				print "standard";
			}
			print "</a>";
			
			print "</td>";
				
				
				print '<td>';

			print '<a href="weathermap-cacti-plugin-mgmt.php?action=move_group_up&order='.$group['sortorder'].'&id='.$group['id'].'"><img src="../../images/move_up.gif" width="14" height="10" border="0" alt="Move Group Up" title="上移"></a>';
			print '<a href="weathermap-cacti-plugin-mgmt.php?action=move_group_down&order='.$group['sortorder'].'&id='.$group['id'].'"><img src="../../images/move_down.gif" width="14" height="10" border="0" alt="Move Group Down" title="下移"></a>';
// print $map['sortorder'];

			print "</td>";
			
				print '<td>';
				if($group['id']>1)
				{
					print '<a href="weathermap-cacti-plugin-mgmt.php?action=groupadmin_delete&id='.intval($group['id']).'"><img src="../../images/delete_icon.gif" width="10" height="10" border="0" alt="Remove this definition from this map"></a>';
				}
				print '</td>';
			
				print "</tr>";
				$n++;
			}
		}
		else
		{
			print "<tr>";
			print "<td colspan=2>未定义分组.</td>";
			print "</tr>";
		}
	}
	
	html_end_box();
}

function weathermap_group_create($newname)
{
	$sortorder = db_fetch_cell("select max(sortorder)+1 from weathermap_groups");
	$SQL = sprintf("insert into weathermap_groups (name, sortorder) values ('%s',%d)", mysql_escape_string($newname), $sortorder);
#	print $SQL;
	db_execute($SQL);
}

function weathermap_group_update($id, $newname)
{
	
	$SQL = sprintf("update weathermap_groups set name='%s' where id=%d", mysql_escape_string($newname), $id);
#	print $SQL;
	db_execute($SQL);
}

function weathermap_group_delete($id)
{
	$SQL1 = "SELECT MIN(id) from weathermap_groups where id <> ". $id;
	$newid = db_fetch_cell($SQL1);
	# move any maps out of this group into a still-existing one
	$SQL2 = "UPDATE weathermap_maps set group_id=$newid where group_id=".$id;
	# then delete the group
	$SQL3 = "DELETE from weathermap_groups where id=".$id;
	db_execute($SQL2);
	db_execute($SQL3);
}

// vim:ts=4:sw=4:
?>

