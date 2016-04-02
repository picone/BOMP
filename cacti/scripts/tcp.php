<?php
$no_http_headers = true;

/* display No errors */
error_reporting(E_ERROR);

include_once(dirname(__FILE__) . "/../include/global.php");

include_once(dirname(__FILE__) . "/../lib/snmp.php");

if (!isset($called_by_script_server)) {
	array_shift($_SERVER["argv"]);
//print_r($_SERVER["argv"]);
	print call_user_func_array("TCP", $_SERVER["argv"]);
}

function TCP($hostname, $host_id, $cmd, $rien, $arg2="", $arg3="") {
//print "xxx".$cmd;
  global $config;
  $filename = $config["base_path"].'/scripts/ports.inc';
  
  $host_method=db_fetch_cell("SELECT availability_method FROM `host` where id='" . $host_id . "'");
  
  if ($host_method == "3") {     //ping enabled
    $val_limite=db_fetch_cell("SELECT ping_timeout FROM `host` where id='" . $host_id . "'");
  }
	
  if ( ($host_method == "1") || ($host_method == "2") ) {     //snmp enabled
    $val_limite=db_fetch_cell("SELECT snmp_timeout FROM `host` where id='" . $host_id . "'");
  }  

  $val_limite=floor($val_limite/1000);
  if ($val_limite == "0") {
    $val_limite = "1";
  }
  
  if ($cmd == "query") {
    if (is_readable($filename)) {
      $lines = file ($filename);
      foreach ($lines as $line) {
        $v = explode("#",$line);
        $socket = @fsockopen(strtolower($hostname), trim($v[0]), $error_number, $error, (float) $val_limite);
        if ($socket) { 
          print trim($v[0]). "!" . trim($v[0]) . "\n";
          fclose ($socket);
        }
	  }
    }
  }
		
  if ($cmd == "get") {
    $socket = @fsockopen(strtolower($hostname), $arg2, $error_number, $error, (float) $val_limite);
    if (!$socket) { 
      print "0";
    } else {
      print "1";
      fclose ($socket);
    }
  }

}


?>