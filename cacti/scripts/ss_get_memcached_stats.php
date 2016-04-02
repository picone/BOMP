<?php
//
// +----------------------------------------------------------------------+
// | MemcachedStats for cacti script server, v 1.0                         |
// +----------------------------------------------------------------------+
// | License: GNU LESSER GENERAL PUBLIC LICENSE                           |
// +----------------------------------------------------------------------+
// | Authors: hqlulu <hqlulu@gmail.com>                             |
// +----------------------------------------------------------------------+

//+----------------------------------------------------------------------+
//| Cacti template Part: memcached Cacti Template
//modify@1122		<input_string>php &lt;path_cacti&gt;/scripts/ss_get_memcached_stats.php &lt;hostname&gt;</input_string>
//| visit http://dealnews.com/developers/cacti/memcached.html
//+----------------------------------------------------------------------+
//| Socket Part: MEMCACHE INFO
//| Author:  Harun Yayli <harunyayli at gmail.com>                       |
//+----------------------------------------------------------------------+
//
//  为了不改变后面socket部分的原作者的代码，配置部分和一些信息进行了调整
//
//	Usage:
//
//	From the command line:
//		memcached.php <hostname>
//

$no_http_headers = true;

/* display No errors */
error_reporting(E_ERROR);
$MEMCACHE_SERVERS = array();

//include_once(dirname(__FILE__) . "/../include/config.php");
//include_once(dirname(__FILE__) . "/../lib/snmp.php");
if (!isset($called_by_script_server)) {
	array_shift($_SERVER["argv"]);
	print call_user_func_array("ss_get_memcached_stats", $_SERVER["argv"]);
}

function ss_get_memcached_stats( $host ){
	global $MEMCACHE_SERVERS;

	$port = 11211;

	//2010-11-1 support other port, such as 192.168.1.2:11311
	$host_port = explode(':', $host);
	if(isset($host_port[1])) $port = $host_port[1];

	//default value
	$stats = array('total_items'=>0, 'bytes_written'=>0, 'uptime'=>0, 'bytes'=>0, 
			 'cmd_get'=>0, 'curr_items'=>0, 'curr_connections'=>0, 'connection_structures'=>0, 
			 'limit_maxbytes'=>0, 'rusage_user'=>0.0, 'total_connections'=>0, 'cmd_set'=>0, 
			 'time'=>0, 'get_misses'=>0, 'bytes_read'=>0, 'rusage_system'=>0.0, 'get_hits'=>0);

	if(!$host) exit;

	$MEMCACHE_SERVERS[] = $host.':'.$port;
	$memstats = getMemcacheStats();

	foreach($memstats as $k=>$v){
		if(is_array($v)){
			$memstats[$k] = array_sum($v);
		}
	}
	//print_r($memstats);

	$return = '';
	foreach($stats as $k => $v){
		if(isset($memstats[$k])){
			$return .= sprintf('%s:%s ', $k, $memstats[$k]);
		}else{
			$return .= sprintf('%s:%s ', $k, $v);
		}
	}

	return rtrim($return);
}

///////////////////////////////// 

function sendMemcacheCommands($command){
    global $MEMCACHE_SERVERS;
	$result = array();

	foreach($MEMCACHE_SERVERS as $server){
		$strs = explode(':',$server);
		$host = $strs[0];
		$port = $strs[1];
		$result[$server] = sendMemcacheCommand($host,$port,$command);
	}
	return $result;
}

function sendMemcacheCommand($server,$port,$command){

	$s = @fsockopen($server,$port);
	if (!$s){
		die("Cant connect to:".$server.':'.$port);
	}

	fwrite($s, $command."\r\n");

	$buf='';
	while ((!feof($s))) {
		$buf .= fgets($s, 256);
		if (strpos($buf,"END\r\n")!==false){ // stat says end
		    break;
		}
		if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
		    break;
		}
	}
    fclose($s);
    return parseMemcacheResults($buf);
}

function parseMemcacheResults($str){

	$res = array();
	$lines = explode("\r\n",$str);
	$cnt = count($lines);
	for($i=0; $i< $cnt; $i++){
	    $line = $lines[$i];
		$l = explode(' ',$line,3);
		if (count($l)==3){
			$res[$l[0]][$l[1]]=$l[2];
			if ($l[0]=='VALUE'){ // next line is the value
			    $res[$l[0]][$l[1]] = array();
			    list ($flag,$size)=explode(' ',$l[2]);
			    $res[$l[0]][$l[1]]['stat']=array('flag'=>$flag,'size'=>$size);
			    $res[$l[0]][$l[1]]['value']=$lines[++$i];
			}
		}elseif($line=='DELETED' || $line=='NOT_FOUND'){
		    return $line;
		}
	}
	return $res;

}

function getMemcacheStats($total=true){
	$resp = sendMemcacheCommands('stats');
	if ($total){
		$res = array();
		foreach($resp as $server=>$r){
			foreach($r['STAT'] as $key=>$row){
				if (!isset($res[$key])){
					$res[$key]=null;
				}
				switch ($key){
					case 'pid':
						$res['pid'][$server]=$row;
						break;
					case 'uptime':
						$res['uptime'][$server]=$row;
						break;
					case 'time':
						$res['time'][$server]=$row;
						break;
					case 'version':
						$res['version'][$server]=$row;
						break;
					case 'pointer_size':
						$res['pointer_size'][$server]=$row;
						break;
					case 'rusage_user':
						$res['rusage_user'][$server]=$row;
						break;
					case 'rusage_system':
						$res['rusage_system'][$server]=$row;
						break;
					case 'curr_items':
						$res['curr_items']+=$row;
						break;
					case 'total_items':
						$res['total_items']+=$row;
						break;
					case 'bytes':
						$res['bytes']+=$row;
						break;
					case 'curr_connections':
						$res['curr_connections']+=$row;
						break;
					case 'total_connections':
						$res['total_connections']+=$row;
						break;
					case 'connection_structures':
						$res['connection_structures']+=$row;
						break;
					case 'cmd_get':
						$res['cmd_get']+=$row;
						break;
					case 'cmd_set':
						$res['cmd_set']+=$row;
						break;
					case 'get_hits':
						$res['get_hits']+=$row;
						break;
					case 'get_misses':
						$res['get_misses']+=$row;
						break;
					case 'evictions':
						$res['evictions']+=$row;
						break;
					case 'bytes_read':
						$res['bytes_read']+=$row;
						break;
					case 'bytes_written':
						$res['bytes_written']+=$row;
						break;
					case 'limit_maxbytes':
						$res['limit_maxbytes']+=$row;
						break;
					case 'threads':
						$res['rusage_system'][$server]=$row;
						break;
				}
			}
		}
		return $res;
	}
	return $resp;
}

?>