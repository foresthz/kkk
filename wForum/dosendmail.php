<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("׫д���ʼ�");

preprocess();

show_nav();

echo "<br>";

if (!isErrFounded()) {
	head_var($userid."�ķ�����","usermailbox.php?boxname=sendbox",0);
}

if ($loginok==1) {
	showUserManageMenu();
	showmailBoxes();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}


if (isErrFounded()) {
		html_error_quit();
} 
show_footer();

//showBoardSampleIcons();
function preprocess(){
	global $currentuser;
	global $loginok;
	if ($loginok!=1) {
		foundErr("�οͲ���д�š�");
		return false;
	}
    if (!bbs_can_send_mail()) {
		foundErr("��û��д��Ȩ��!");
		return false;
	}
	if (strchr($_POST['destid'], '@') || strchr($_POST['destid'], '|')
        || strchr($_POST['destid'], '&') || strchr($_POST['destid'], ';')) {
        foundErr("������������ʺ�");
		return false;
    }
	return true;
}

function 	main(){
	global $_POST;
	$ret=bbs_postmail($_POST['destid'],preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['title']),preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['content']),intval($_POST['signature']), isset($_POST['backup'])?1:0);
	switch ($ret) {
		case -1:
			foundErr("�޷�������ʱ�ļ�");
			return false;
		case -2:
			foundErr("����ʧ��:�޷������ļ���");
			return false;
		case -3:
			foundErr("�Է���������ʼ���");
			return false;
		case -4:
			foundErr("�Է���������");
			return false;
		case -5:
			foundErr("����ʧ�ܡ�");
			return false;
		case -100:
			foundErr("�������˺Ŵ���");
			return false;
	}
	setSucMsg("�ż��ѳɹ����ͣ�");
	return html_success_quit('�����ռ���', 'usermailbox.php?boxname=inbox');
}
?>
