<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("���˲����޸�");

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
	showUserManageMenu();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
} 

show_footer();

function main(){
	global $currentuser;
	global $user_define_num;
	$flags=0;
	for ($i=0;$i<$user_define_num;$i++) {
		if ($_POST['param'.$i]==1) {
			$flags|= (1<<$i);
		}
	}
	bbs_setuserparam($flags);
	setSucMsg("�޸ĳɹ���");
	return html_success_quit('���ؿ������', 'usermanagemenu.php');
}
?>