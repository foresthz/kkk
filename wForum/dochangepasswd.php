<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�޸�����");

show_nav();

if ($loginok==1) {
?>
<table border="0" width="97%">
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
	$pw1=trim($_POST['oldpsw']);
	$pw2=trim($_POST['psw']);
	$pw3=trim($_POST['psw2']);
    if (strcmp($pw2, $pw3)) {
		foundErr("�������벻��ͬ");
		return false;
	}
    if (strlen($pw2) < 2) {
        foundErr("������̫��");
		return false;
	}
    if (bbs_checkuserpasswd($currentuser['userid'], $pw1)) {
        foundErr("�����벻��ȷ");
		return false;
	}
	$ret=bbs_setuserpasswd($currentuser['userid'], $pw2);
	if ($ret!=0) {
		foundErr("��������ʧ�ܣ�");
		return 0;
	}
?>

<?php

	return setSucMsg("��������ɹ���");
}

?>