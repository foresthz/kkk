<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("׫д���ʼ�");

requireLoginok("�οͲ���д�š�");

preprocess();

show_nav();

echo "<br>";
head_var($userid."�ķ�����","usermailbox.php?boxname=sendbox",0);
showUserManageMenu();
showmailBoxes();
main();

show_footer();

//showBoardSampleIcons();
function preprocess(){
	global $currentuser;
	global $loginok;
    if (!bbs_can_send_mail()) {
		foundErr("��û��д��Ȩ��!");
	}
	if (strchr($_POST['destid'], '@') || strchr($_POST['destid'], '|')
        || strchr($_POST['destid'], '&') || strchr($_POST['destid'], ';')) {
        foundErr("������������ʺ�");
    }
	return true;
}

function main() {
	global $_POST;
	$ret=bbs_postmail($_POST['destid'],preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['title']),preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['content']),intval($_POST['signature']), isset($_POST['backup'])?1:0);
	switch ($ret) {
		case -1:
			foundErr("�޷�������ʱ�ļ�");
		case -2:
			foundErr("����ʧ��:�޷������ļ���");
		case -3:
			foundErr("�Է���������ʼ���");
		case -4:
			foundErr("�Է���������");
		case -5:
			foundErr("����ʧ�ܡ�");
		case -100:
			foundErr("�������˺Ŵ���");
	}
	setSucMsg("�ż��ѳɹ����ͣ�");
	return html_success_quit('�����ռ���', 'usermailbox.php?boxname=inbox');
}
?>
