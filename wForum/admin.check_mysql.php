<?php

require("inc/funcs.php");
require("inc/user.inc.php");

setStat("���ݿ����ü��");

requireLoginok("��ҳ�����Ҫ����Ա��¼����ʹ�á�");

preprocess();

show_nav();

showUserMailBox();
head_var("վ������", 'admin.site_defines.php');

check_mysql();

show_footer();

function preprocess() {
	global $currentuser;
	if (!($currentuser["userlevel"] & BBS_PERM_SYSOP)) {
		foundErr("��ҳ�����Ҫ����Ա��¼����ʹ�á�");
	}
}

function check_mysql() {
    global $dbhost, $dbuser, $dbpasswd, $dbname;
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 width="97%"><tr><th>���ݿ����ü��</td></tr>
<?php
    if (!DB_ENABLED) {
?>
<tr><td class="TableBody1">���ݿ�֧�ֱ����á����� site.php �ж��壺define("DB_ENABLED", true);</td></tr>
<?php
    } else {
        if (!function_exists('mysql_connect'))  {
?>
<tr><td class="TableBody1">mysql_connect PHP ����û�ж��壬PHP û�м��� mysql ֧�֡������� configure PHP ���밲װ���������ϵͳ�� redhat 9���밲װ php-mysql rpm��</td></tr>
<?php
		} else {
    		@$pig = @mysql_connect($dbhost, $dbuser, $dbpasswd) or $pig = true;
    		if ($pig === true) {
?>
<tr><td class="TableBody1">mysql ����ʧ�ܣ����� site.php ���ã�$dbhost, $dbuser, $dbpasswd��</td></tr>
<?php
    		} else {
    		    @mysql_select_db($dbname) or $pig = true;
    		    if ($pig === true) {
?>
<tr><td class="TableBody1">���ݿ��ѡȡʧ�ܡ����� site.php ���ã�$dbname��</td></tr>
<?php
    		    } else {
?>
<tr><td class="TableBody1">���ݿ�����û�����⣡</td></tr>
<?php
    		    }
    		}
    	}
    }
?>
</table>
<?php
}
?>
