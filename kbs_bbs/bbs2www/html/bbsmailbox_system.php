<?php
	/**
	 * This file display system defined mailboxs .
	 */
	require("funcs.php");
login_init();
	if ($loginok != 1)
		html_nologin();
	else
	{
		html_init("gb2312");

		if(!strcmp($currentuser["userid"],"guest"))
			html_error_quit("guest û���Լ�������!");
		$mail_sysbox = array(".DIR",".SENT",".DELETED");
		$mail_sysboxtitle = array("�ռ���","������","������");
?>
<body>
<center><?php echo BBS_FULL_NAME;?> -- �ż��б� - ϵͳԤ�������� [ʹ����: <?php echo $currentuser["userid"]; ?>]<hr color=green>
<table width="200">
<tr><td>���</td><td>��������</td><td>�ʼ�����</td></tr>
<?php
		$i = 0;
		foreach ($mail_sysbox as $sysbox_path)
		{
			$i++;
?>
<tr><td> <?php echo $i; ?></td>
<td><a href="bbsreadmail.php?path=<?php echo $sysbox_path;?>&title=<?php echo $mail_sysboxtitle[$i-1];?>"><?php echo $mail_sysboxtitle[$i-1]; ?></a></td>
<td><?php
echo bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],$sysbox_path));
?></td></tr>
<?php
		}
?>
</table>
</body>
<hr class="default"/>
<?php
	}
?>
