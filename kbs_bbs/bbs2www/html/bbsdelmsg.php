<?php
	/**
	 * This file delete current user's message log.
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
		$filename = bbs_sethomefile($currentuser["userid"],"msgindex");
		if (file_exists($filename))
			unlink($filename);
		$filename = bbs_sethomefile($currentuser["userid"],"msgindex2");
		if (file_exists($filename))
			unlink($filename);
		$filename = bbs_sethomefile($currentuser["userid"], "msgcount");
		if (file_exists($filename))
			unlink($filename);
?>
<body>
�Ѿ�ɾ������ѶϢ����<a href="javascript:history.go(-2)">����</a>
<?php
		html_normal_quit();
	}
?>
