<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�л�����״̬");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();
showUserManageMenu();
html_success_quit();
show_footer();

function main() {
	global $currentuser;
	$mode=bbs_alter_yank();
	return setSucMsg("״̬���л�Ϊ".(($mode==0)?'��ʾȫ��':'��ʾֻ��ʾ���İ�'));
}

?>
