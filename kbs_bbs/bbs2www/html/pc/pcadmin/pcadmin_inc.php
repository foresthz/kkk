<?php
require("pcfuncs.php");
function pc_admin_check_permission()
{
	global $loginok , $currentuser , $pcconfig ;
	if ($loginok != 1)
		html_nologin();
	elseif(!strcmp($currentuser["userid"],"guest"))
	{
		html_init("gb2312");
		html_error_quit("�Բ������ȵ�¼");
		exit();
	}
	elseif(!pc_is_manager($currentuser))
	{
		html_init("gb2312");
		html_error_quit("�Բ�������Ȩ���ʸ�ҳ");
		exit();
	}
	else
		return;
}

function pc_admin_navigation_bar()
{
?>
<p align="center">
[
Blog����Ա����:
<a href="pcmain.php">������ҳ</a>
<a href="pcadmin_rec.php">�Ƽ����¹���</a>
<a href="pcadmin_bla.php">����������</a>
<a href="pcadmin_app.php">��������</a>
<a href="pcadmin_log.php">��¼����Ա</a>
]
</p>
<?php
}

?>