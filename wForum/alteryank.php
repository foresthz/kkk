<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�л�����״̬");

show_nav();

if ($loginok==1) {
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showUserMailbox();
?>
</table>
<?php
}

head_var($userid."�Ŀ������","usermanagemenu.php",0);

if ($loginok==1) {
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
} else {
		showUserManageMenu();
		html_success_quit();
}

show_footer();

function main() {
	global $currentuser;
	$mode=bbs_alter_yank();
?>

<?php

	return setSucMsg("״̬���л�Ϊ".(($mode==0)?'��ʾȫ��':'��ʾֻ��ʾ���İ�'));
}

?>
