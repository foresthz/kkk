<?php
require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�û��ղذ���");

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

if (isErrFounded())	{
	html_error_quit();
}

show_footer();

function main()	{
	global $currentuser;
	global $_GET; // �Ҽǵ� $_GET �� super-global ����Ӧ�ò���˵������������ wForum ��ͷ����ôд����Ҳ��ôд�ˡ�- atppp
	
	if (isset($_GET["select"]))
		$select	= $_GET["select"];
	else
		$select	= 0;

	if ($select	< 0) {
		foundErr("����Ĳ���");
		return false;
	}
	if (bbs_load_favboard($select)==-1) {
		foundErr("����Ĳ���");
		return false;
	}
	showSecs($select, 0, true, 1); //������������ isFold����ʱ�趨Ϊ��Զչ�������Ҫ���� showSecs() ����ҲҪ�ġ�- atppp
}
?>
