<?php

#
# Change the uncommented line to point to your Cacti installation
#
$cacti_base = "/var/www/new_zabbix/cacti";

// check if the goalposts have moved
if( is_dir($cacti_base) && file_exists($cacti_base."/include/global.php") )
{
        // include the cacti-config, so we know about the database
        require_once($cacti_base."/include/global.php");
}
elseif( is_dir($cacti_base) && file_exists($cacti_base."/include/config.php") )
{
        // include the cacti-config, so we know about the database
        require_once($cacti_base."/include/config.php");
}
else
{
	print "Couldn't find a usable Cacti config";
}

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."setup.php");
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."poller-common.php");

weathermap_setup_table();

weathermap_run_maps(dirname(__FILE__) );

// vim:ts=4:sw=4:
?>
