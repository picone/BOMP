<?php
/*
** Zabbix
** Copyright (C) 2001-2015 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
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


define('ZBX_PAGE_NO_HEADER', 1);
define('ZBX_PAGE_NO_FOOTER', 1);

$request = CHtml::encode(getRequest('request', ''));
$message = CHtml::encode(getRequest('message', '')) ;
// remove debug code for login form message, trimming not in regex to relay only on [ ] in debug message.
$message = trim(preg_replace('/\[.*\]/', '', $message));
$page['title']='用户登录';
require_once dirname(__FILE__).'/../page_header.php';
?>
    <div class="login">
        <div id="glow">
            <div class="loginForm">
                <div style="position: relative; color: #FFF; height: 100%;">
                    <div style="float: left; width: 250px; height: 100%;">
                        <div style="position:absolute;top:32%;left:36px;" class="loginLogo"></div>
                    </div>

                    <!-- Login Form -->
                    <div style="height: 100%; padding-top: 58px; padding-right: 40px; margin-left: 275px;">
                        <div style="float: right;">
                            <form action="index.php" method="post">
                                <input type="hidden" name="request" class="input hidden" value="<?php echo $request; ?>" />
                                <ul style="list-style-type: none;">
                                    <li style="padding-right: 6px; height: 22px;">
                                        <div class="ui-corner-all textwhite bold" style="padding: 2px 4px; float: right; background-color: #D60900; visibility: <?php echo zbx_empty($message) ? 'hidden' : 'visible'; ?>" >
                                            <span class="nowrap"><?php echo $message; ?></span>
                                        </div>
                                    </li>
                                    <li style="margin-top: 10px; padding-top: 1px; height: 22px; width: 265px; white-space: nowrap;" >
                                        <div class="label"><?php echo _('Username'); ?></div><input type="text" id="name" name="name" class="input" />
                                    </li>
                                    <li style="margin-top: 10px; padding-top: 1px; height: 22px; width: 265px; white-space: nowrap;" >
                                        <div class="label"><?php echo _('Password'); ?></div><input type="password" id="password" name="password" class="input"/>
                                    </li>
                                    <li style="margin-top: 8px; text-align: center;">
                                        <div style="height: 8px;"></div>
                                        <input type="submit" class="input jqueryinput" name="enter" id="enter" value="<?php echo _('Sign in'); ?>" />
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('body').css('background-color', '#E8EAEF');
            jQuery('#enter').button();
            jQuery('#name').focus();
        });
    </script>
<?php
require_once dirname(__FILE__).'/../page_footer.php';