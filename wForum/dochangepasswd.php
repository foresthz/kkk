<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�޸��ǳƻ�����");

requireLoginok();

show_nav(false);

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();
showUserManageMenu();
html_success_quit();

show_footer();

function main() {
	global $currentuser;
	if (isset($_POST["nick"])) {
		if (bbs_modify_nick($_POST["nick"], intval($_POST["chkTmp"])) == 0) {
			setSucMsg("�����ǳƳɹ���");
		} else {
			foundErr("�ǳ��޸�ʧ�ܣ�");
		}
		return;
	}
	$pw1=trim($_POST['oldpsw']);
	$pw2=trim($_POST['psw']);
	$pw3=trim($_POST['psw2']);
    if (strcmp($pw2, $pw3)) {
		foundErr("�������벻��ͬ");
	}
    if (strlen($pw2) < 2) {
        foundErr("������̫��");
	}
    if (bbs_checkuserpasswd($currentuser['userid'], $pw1)) {
        foundErr("�����벻��ȷ");
	}
	$ret=bbs_setuserpasswd($currentuser['userid'], $pw2);
	if ($ret!=0) {
		foundErr("��������ʧ�ܣ�");
	}
	return setSucMsg("��������ɹ���");
}
?>