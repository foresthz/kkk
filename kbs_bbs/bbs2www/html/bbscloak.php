<?php
	/**
	 * This file lists boards to user.
	 * $Id$
	 */
	require("funcs.php");
login_init();
	if ($loginok !=1 )
		html_nologin();
	else
	{
		html_init("gb2312");
        if (!strcasecmp($currentuser["userid"],"guest")) {
			html_error_quit("guest����ʹ������");
        }
        if (!($currentuser["userlevel"]&BBS_PERM_CLOAK)) {
			html_error_quit("����Ĳ���");
        }
?>
<body>
<?php
		bbs_update_uinfo("invisible", !$currentuinfo["invisible"]);
        if (!$currentuinfo["invisible"])
            echo("��ʼ����!");
        else
            echo("�����Ѿ�ֹͣ!");
?>
<br/>
<a href="javascript:history.go(-1)">���ٷ���</a>
<?php
		html_normal_quit();
	}
?>

