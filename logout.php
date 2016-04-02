<?php
/**
 * Created by PhpStorm.
 * User: Jian
 * Date: 2015/5/17
 * Time: 14:14
 */
require 'cacti/include/global.php';
setcookie(session_name(),"",time() - 3600,"/");
session_destroy();
?>
<script type="text/javascript">window.parent.location.href='index.php';</script>