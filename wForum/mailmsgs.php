<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�û����ŷ���");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();

show_footer();

function main() {
	bbs_mailwebmsgs();
	setSucMsg("ѶϢ�����Ѿ��Ļ��������䣡");
	return html_success_quit();
}
?>