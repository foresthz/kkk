<?php
	/*
	** this file display single comment
	** @id:windinsn nov 27,2003
	*/
	$needlogin=0;
	require("pcfuncs.php");
	
	$cid = (int)($_GET["cid"]);
	
	pc_html_init("gb2312","�����ļ�");
	$link = pc_db_connect();
	$query = "SELECT * FROM comments WHERE `cid` = '".$cid."' LIMIT 0 , 1 ; ";
	$result = mysql_query($query,$link);
	$comment = mysql_fetch_array($result);
	if(!$comment)
	{
		@mysql_free_result($result);
		html_error_quit("�Բ�����Ҫ�鿴�����۲�����");
		exit();
	}
	$query = "SELECT `access`,`uid`,`subject`,`emote`,`tid`,`pid` FROM nodes WHERE `nid` = '".$comment[nid]."' LIMIT 0 , 1 ; ";
	$result = mysql_query($query,$link);
	$node = mysql_fetch_array($result);
	if(!$node)
	{
		@mysql_free_result($result);
		html_error_quit("�Բ�����Ҫ�鿴�����۲�����");
		exit();
	}
	if($node[access] > 0)
	{
		//�ж��Ƿ�Ϊ���ѻ�������	
		if ($loginok != 1 || !strcmp($currentuser["userid"],"guest"))
		{
			html_error_quit("�Բ���guest ���ܲ鿴������¼!");
			exit();
		}
		
		$query = "SELECT `username` FROM users WHERE `uid` = '".$node[uid]."' LIMIT 0 , 1 ; ";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		$pc = array(
				"USER" => $rows[username],
				"UID" => $node[uid]
				);
		mysql_free_result($result);
		if( ( $node[access] == 1 && !pc_is_friend($currentuser["userid"],$pc["USER"])) || ( $node[access] > 1 && strtolower($pc["USER"]) != strtolower($currentuser["userid"]) ) )
		{
			html_error_quit("�Բ��������ܲ鿴������¼!");
			exit();
		}
	}
?>
<br>
<center>
<table cellspacing="0" cellpadding="5" border="0" width="90%" class="t1">
<tr>
	<td class="t2">
	<img src="icon/<?php echo $node[emote]; ?>.gif" border="0" alt="�������" align="absmiddle">
	��
	<a href="pccon.php?<?php echo "id=".$node[uid]."&nid=".$comment[nid]."&pid=".$node[pid]."&tid=".$node[tid]."&tag=".$node[access]; ?>" class="t2">
	<?php echo html_format($node[subject]); ?>
	</a>
	��
	������
	</td>
</tr>
<tr>
	<td class="t8">
	<?php
		echo "<a href=\"/bbsqry.php?userid=".$comment[username]."\">".$comment[username]."</a>\n".
			"�� ".time_format($comment[created])." �ᵽ:\n";
	?>
	</td>
</tr>
<tr>
	<td class="t13">
	<img src="icon/<?php echo $comment[emote]; ?>.gif" border="0" alt="�������" align="absmiddle">
	<strong>
	<?php echo html_format($comment[subject]); ?>
	</strong>
	</td>
</tr>
<tr>
	<td class="t13" height="200" align="left" valign="top">
	<?php echo html_format($comment[body],TRUE); ?>
	</td>
</tr>
<tr>
	<td class="t5" align="right">
	[FROM:
	<?php echo $comment[hostname]; ?>
	]
	&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
</tr>
<tr>
	<td class="t3">
<?php
	$query = "SELECT `cid` FROM comments WHERE `nid` = '".$comment[nid]."' AND `cid` < '".$cid."' ORDER BY `cid` DESC LIMIT 0 , 1 ; ";
	$result = mysql_query($query,$link);
	if($rows = mysql_fetch_array($result))
		echo "<a href=\"pcshowcom.php?cid=".$rows[cid]."\">��һƪ</a> \n";
	else
		echo "��һƪ \n";
	$query = "SELECT `cid` FROM comments WHERE `nid` = '".$comment[nid]."' AND `cid` > '".$cid."' ORDER BY `cid` ASC LIMIT 0 , 1 ; ";
	$result = mysql_query($query,$link);
	if($rows = mysql_fetch_array($result))
		echo "<a href=\"pcshowcom.php?cid=".$rows[cid]."\">��һƪ</a> \n";
	else
		echo "��һƪ \n";
	mysql_free_result($result);
?>	
	<a href="pccon.php?<?php echo "id=".$node[uid]."&nid=".$comment[nid]."&pid=".$node[pid]."&tid=".$node[tid]."&tag=".$node[access]; ?>">����ԭ��</a>
	<a href="pccom.php?act=pst&nid=<?php echo $comment[nid]; ?>">��������</a>
	<a href="/bbspstmail.php?userid=<?php echo $comment[username]; ?>&title=�ʺ�">���Ÿ�<?php echo $comment[username]; ?></a>
	</td>
</tr>
</table>
</center>
<?php	
?>