<?php


require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�û����ŷ���");

show_nav();

if ($loginok==1) {
?>
<table border="0" width="97%">
<?php
	showUserMailbox();
?>
</table>
<?php
	head_var($userid."�Ŀ������","usermanagemenu.php",0);
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
}

show_footer();

function main() {
	bbs_mailwebmsgs();
	setSucMsg("ѶϢ�����Ѿ��Ļ��������䣡");
	return html_success_quit();
}
?>