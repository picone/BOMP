<?php
/*
** Zabbix
** Copyright (C) 2001-2015 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Softsvnware Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/
define('ZBX_PAGE_NO_AUTHORIZATION', true);
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$page['title'] = _('ZABBIX');
$page['file'] = 'index.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
    'name' =>		array(T_ZBX_STR, O_NO,	null,	NOT_EMPTY,		'isset({enter})', _('Username')),
    'password' =>	array(T_ZBX_STR, O_OPT, null,	null,			'isset({enter})'),
    'sessionid' =>	array(T_ZBX_STR, O_OPT, null,	null,			null),
    'reconnect' =>	array(T_ZBX_INT, O_OPT, P_SYS|P_ACT,	BETWEEN(0, 65535), null),
    'enter' =>		array(T_ZBX_STR, O_OPT, P_SYS,	null,			null),
    'autologin' =>	array(T_ZBX_INT, O_OPT, null,	null,			null),
    'request' =>	array(T_ZBX_STR, O_OPT, null,	null,			null)
);
check_fields($fields);

// logout
if (isset($_REQUEST['reconnect'])) {
    DBstart();
    add_audit(AUDIT_ACTION_LOGOUT, AUDIT_RESOURCE_USER, _('Manual Logout'));
    DBend(true);
    CWebUser::logout();
    redirect('index.php');
}

$config = select_config();

if ($config['authentication_type'] == ZBX_AUTH_HTTP) {
    if (!empty($_SERVER['PHP_AUTH_USER'])) {
        $_REQUEST['enter'] = _('Sign in');
        $_REQUEST['name'] = $_SERVER['PHP_AUTH_USER'];
    }
    else {
        access_deny(ACCESS_DENY_PAGE);
    }
}

// login via form
if (isset($_REQUEST['enter']) && $_REQUEST['enter'] == _('Sign in')) {
    // try to login
    $autoLogin = getRequest('autologin', 0);

    DBstart();
    $loginSuccess = CWebUser::login(getRequest('name', ''), getRequest('password', ''));
    DBend(true);

    if ($loginSuccess) {
        // save remember login preference
        $user = array('autologin' => $autoLogin);

        if (CWebUser::$data['autologin'] != $autoLogin) {
            API::User()->updateProfile($user);
        }
        /*除去原来的URL跳转
        $request = getRequest('request');
        $url = zbx_empty($request) ? CWebUser::$data['url'] : $request;
        if (zbx_empty($url) || $url == $page['file']) {
            $url = 'dashboard.php';
        }*/
        //并增加Cacti登录
        session_name('Cacti');
        session_start();
        $_SESSION['sess_user_id']=1;
        header('Location:/');
        //redirect($url);
        //End
        exit;
    }
    // login failed, fall back to a guest account
    else {
        CWebUser::checkAuthentication(null);
    }
}
else {
    // login the user from the session, if the session id is empty - login as a guest
    CWebUser::checkAuthentication(CWebUser::getSessionCookie());
}
if(isset($_REQUEST['enter'])&&$_REQUEST['enter']=='relogin'){CWebUser::$data['alias']=false;}
// the user is not logged in, display the login form
if (!CWebUser::$data['alias'] || CWebUser::$data['alias'] == ZBX_GUEST_USER) {
    switch ($config['authentication_type']) {
        case ZBX_AUTH_HTTP:
            echo _('User name does not match with DB');
            break;
        case ZBX_AUTH_LDAP:
        case ZBX_AUTH_INTERNAL:
            if ($messages = clear_messages()) {
                $messages = array_pop($messages);
				if($_REQUEST['enter']!='relogin')$_REQUEST['message'] = $messages['message'];
            }
            $loginForm = new CView('general.login');
            $loginForm->render();
    }
}
else {
    redirect(zbx_empty(CWebUser::$data['url']) ? 'dashboard.php' : CWebUser::$data['url']);
}
