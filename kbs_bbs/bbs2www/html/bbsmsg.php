<?php
	require("funcs.php");
login_init();
	if ($loginok != 1)
		html_nologin();
	else
	{
		html_init("gb2312");
		if( $currentuser["userid"]=="guest" )
				html_error_quit("û����Ϣ");

		$msgs = bbs_getwebmsgs();

		if( $msgs <= 0 )
				html_error_quit("ϵͳ����");

		$i=0;
		cache_header("nocache");
?>
<html>
<table border=1>
<tr><td>���</td><td>ʱ��</td><td>����</td><td>����</td><td>����</td></tr>
<?php
		foreach( $msgs as $msg ){
			$i++;
?>
<tr><td><?php echo $i;?></td>
<td><?php echo date("D M j H:i:s Y", $msg["TIME"]);?></td>
<td><?php if($msg["SENT"]) echo "<a href=\"bbssendmsg.php?destid=".$msg["ID"]."\">��</a>"; else echo "��";?></td>
<td><?php echo $msg["ID"];?></td>
<td><?php echo htmlspecialchars($msg["content"]);?></td>
</tr>
<?php
		}
?>
</table>
<a onclick="return confirm('�����Ҫ�������ѶϢ��?')" href="/bbsdelmsg.php">�������ѶϢ</a> <a href="/bbsmailmsg.php">�Ļ�������Ϣ</a>
</html>
<?php
	}
?>
