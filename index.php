<?php
define('FRAME_PATH',dirname(__FILE__).'/');
require FRAME_PATH.'cacti/include/global.php';
if(!isset($_SESSION["sess_user_id"])||$_SESSION['sess_user_id']!=1){
	header('Location: bomp/index.php?enter=relogin');
	exit;
}
require FRAME_PATH.'main.html';