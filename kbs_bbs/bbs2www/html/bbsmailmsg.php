<?php
	/**
	 * This file mail message log to current user.
	 * $Id$
	 */
	require("funcs.php");
	if ($loginok != 1)
		html_nologin();
	else
	{
		html_init("gb2312");
        if ($currentuser["userid"]=="guest")
			html_error_quit("�Ҵҹ��Ͳ��ܴ���ѶϢ�����ȵ�¼");
		if (bbs_mailwebmsgs() == false)
		{
			html_error_quit("ѶϢ���ݼĻ�����ʧ��");
		}
?>
<body>
ѶϢ�����Ѿ��Ļ���������<a href="javascript:history.go(-2)">����</a>
<?php
		html_normal_quit();
	}
?>
