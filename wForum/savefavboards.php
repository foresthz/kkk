<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�����ղذ���");

show_nav();

if ($loginok==1) {
	showUserMailbox();
	head_var($userid."�Ŀ������","usermanagemenu.php",0);
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
/*
	����ط��������ª�Ĵ��룬��Ҫ����PHP����n�Σ�����fav�ļ�n�Ρ�
	������������뻯�Ĵ��룬����PHP��������array���÷�̫��������д... - atppp
	$boards = array();
	foreach($_POST as $board => $value) {
		if ($value == 1) $boards[] = $board;
	}
	bbs_set_favboards($boards);
*/
	$select = 0;
	if (bbs_load_favboard($select) == -1) {
		foundErr("�޷���ȡ�ղؼ�");
		return false;
	}
	$boards = bbs_fav_boards($select, 1);
	if ($boards == FALSE) {
		foundErr("�޷���ȡ�ղؼ�");
		return false;
	}
	$brd_flag= $boards["FLAG"];
	$brd_npos= $boards["NPOS"];
	$rows = sizeof($brd_flag);
	for ($i = 0; $i < $rows; $i++) {
		if ($brd_flag[$i] == -1 ) continue;
		bbs_del_favboard($select,$brd_npos[$i]);
	}
	foreach($_POST as $board => $value) {
		if ($value == 1) bbs_add_favboard($board);
	}
	setSucMsg("�޸ĳɹ���");
	return html_success_quit('���ؿ������', 'usermanagemenu.php');
}
?>