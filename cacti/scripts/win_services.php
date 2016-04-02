<?php
$no_http_headers = true;

/* display No errors */
error_reporting(E_ERROR);

include_once(dirname(__FILE__) . "/../include/global.php");
include_once(dirname(__FILE__) . "/../lib/snmp.php");

if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . "/../include/global.php");
	array_shift($_SERVER["argv"]);
	print call_user_func_array("win_services", $_SERVER["argv"]);
}

function win_services($hostname, $snmp_auth, $cmd, $arg1 = "", $arg2 = "") {
# 1.3.6.1.4.1.77.1.2.3.1  Windows services table
# 1.3.6.1.4.1.77.1.2.3.1.1 Name of the service
# 1.3.6.1.4.1.77.1.2.3.1.2 Install state: 
#    uninstalled(1), install-pending(2), uninstall-pending(3), installed(4)
# 1.3.6.1.4.1.77.1.2.3.1.3 Operating state:
#    active(1),  continue-pending(2),  pause-pending(3),  paused(4)
# 1.3.6.1.4.1.77.1.2.3.1.4 Can be uninstalled:
#    cannot-be-uninstalled(1), can-be-uninstalled(2)

	$oids = array(
		"index" => ".1.3.6.1.4.1.77.1.2.3.1.1",
		"servstate" => ".1.3.6.1.4.1.77.1.2.3.1.3"
		);

	$snmp = explode(":", $snmp_auth);
	$snmp_version = $snmp[0];
	$snmp_port    = $snmp[1];
	$snmp_timeout = $snmp[2];

	$snmp_auth_username   = "";
	$snmp_auth_password   = "";
	$snmp_auth_protocol   = "";
	$snmp_priv_passphrase = "";
	$snmp_priv_protocol   = "";
	$snmp_context         = "";
	$snmp_community = "";

	if ($snmp_version == 3) {
		$snmp_auth_username   = $snmp[4];
		$snmp_auth_password   = $snmp[5];
		$snmp_auth_protocol   = $snmp[6];
		$snmp_priv_passphrase = $snmp[7];
		$snmp_priv_protocol   = $snmp[8];
		$snmp_context         = $snmp[9];
	}else{
		$snmp_community = $snmp[3];
	}

	if ((func_num_args() <= "5") && (func_num_args() >= "3")) {
		if ($cmd == "index") {
			/* this is where it is pulling the index */
			$return_arr = cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, read_config_option("snmp_retries"), SNMP_POLLER);

			for ($i=0; $i < sizeof($return_arr); $i++) {
				if (substr($return_arr[$i]['oid'],0,4) == "SNMP")
					print substr($return_arr[$i]['oid'],35) . "\n";
				else
					print substr($return_arr[$i]['oid'],25) . "\n";
			}
		}elseif ($cmd == "query") {
			$arg = $arg1;
			$arr_index2 = array();
			$arr_index = cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, read_config_option("snmp_retries"), SNMP_POLLER);
			for ($i = 0; $i < sizeof($arr_index); $i++) {
				if (substr($arr_index[$i]['oid'],0,4) == "SNMP")
					$arr_index2[$i] =  substr($arr_index[$i]['oid'],35);
				else
					$arr_index2[$i] =  substr($arr_index[$i]['oid'],25);
			}

			$arr = win_services_reindex(cacti_snmp_walk($hostname, $snmp_community, $oids[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, read_config_option("snmp_retries"), SNMP_POLLER));
			for ($i = 0; $i < sizeof($arr_index2); $i++) {
				print $arr_index2[$i] . " !" . $arr[$i] . "\n";
			}
		}elseif ($cmd == "get") {
			$arg = $arg1;
			$index = trim($arg2);
			if ($arg == "servstate") {
				$x = trim(cacti_snmp_get($hostname, $snmp_community, $oids[$arg] . '.' . $index, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, read_config_option("snmp_retries"), SNMP_POLLER));
				if ($x== '') $x=0; 
#				if (trim($x) == '') $x = 0;
#				if ($x < 4) $x = 1;
#				if ($x == 4) $x = 0;
				return $x;
			}
		}
	} else {
		return "ERROR: Invalid Parameters\n";
	}
	return '';
}

function win_services_reindex($arr) {
	$return_arr = array();

	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
