<?php
	/**
	 * This file display user custom mailboxs .
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
		
		$mailboxs = bbs_loadmaillist($currentuser["userid"]);
		if ($mailboxs == FALSE)html_error_quit("��ȡ�Զ�����������ʧ��!");

		if (isset($_GET["delete"]))//delete
		{
			$delete =$_GET["delete"];  //1-based
			unset($mailboxs[$delete -1]);
			if(!bbs_changemaillist(FALSE,$currentuser["userid"],"",$delete-1))
				html_error_quit("�洢�Զ�����������ʧ��!");
			$mailboxs = bbs_loadmaillist($currentuser["userid"]);
		}
		else
			$delete = 0;

		if (isset($_GET["boxname"]))//add
		{
			$boxname = $_GET["boxname"];
			$ret = bbs_changemaillist(TRUE,$currentuser["userid"],$boxname,0);
			if (!$ret)html_error_quit("�洢�Զ�����������ʧ��!");
			if ($ret > 0)  //��Ŀ������
			{
?>
<SCRIPT language="javascript">
	alert("�Զ����������ѵ�����!������" + <?php echo "\"$ret\"";?>);
	history.go(-1);
</SCRIPT>
<?php
			}
			$mailboxs = bbs_loadmaillist($currentuser["userid"]);
		}

?>
<body>
<center><?php echo BBS_FULL_NAME;?> -- �ż��б� - �Զ������� [ʹ����: <?php echo $currentuser["userid"]; ?>]<hr color=green>

<?php
		if($mailboxs == -1)
		{
			echo "���Զ�������";
		}
		else
		{
?>
<table width="250">
<tr><td>���</td><td>��������</td><td>�ʼ�����</td></tr>
<?php
			$i = 0;
			foreach ($mailboxs as $mailbox)
			{
				$i++;
?>
<tr><td> <?php echo $i; ?></td>
<td><a href="bbsreadmail.php?path=<?php echo $mailbox["pathname"];?>&title=<?php echo $mailbox["boxname"];?>"><?php echo $mailbox["boxname"]; ?></a></td>
<td><?php
echo bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],$mailbox["pathname"]));
?></td>
<td><a href=bbsmailbox_custom.php?delete=<?php echo $i;?>>ɾ��</a>
</td></tr>
<?php
			}
		}
?>
</table>
</body>
<form>�����Զ�������:<input name=boxname size=24 maxlength=20 type=text value=''><input type=submit value=ȷ��></form>
<hr class="default"/>
<?php
	}

?>
