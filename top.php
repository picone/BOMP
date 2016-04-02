<?php
define('FRAME_PATH',dirname(__FILE__).'/');
require FRAME_PATH.'bomp/include/classes/db/DB.php';
require FRAME_PATH.'bomp/include/classes/core/Z.php';
require FRAME_PATH.'bomp/include/classes/core/CRegistryFactory.php';
require FRAME_PATH.'bomp/include/classes/core/CConfigFile.php';
require FRAME_PATH.'bomp/include/classes/api/CApiService.php';
require FRAME_PATH.'bomp/include/classes/api/APIException.php';
require FRAME_PATH.'bomp/include/classes/api/API.php';
require FRAME_PATH.'bomp/include/classes/api/services/CUser.php';
require FRAME_PATH.'bomp/include/classes/api/CApiServiceFactory.php';
Z::getInstance()->run();
DBstart();
API::setApiServiceFactory(new CApiServiceFactory());
API::getWrapper()->auth=CWebUser::checkAuthentication(CWebUser::getSessionCookie());
DBend(true);
require FRAME_PATH.'top.html';