<?php
	/*
	** some comments actions in personal corp.
	** @id:windinsn  Nov 19,2003
	*/
	require("pcfuncs.php");
	
	if ($loginok != 1)
		html_nologin();
	elseif(!strcmp($currentuser["userid"],"guest"))
	{
		html_init("gb2312");
		html_error_quit("guest ���ܷ�������!");
		exit();
	}
	else
	{
		html_init("gb2312","�����ļ�");		
		$act = $_GET["act"];
		$cid = $_GET["cid"];
		
		$link =	pc_db_connect();
		if($act == "del")
		{
			$query = "SELECT `username` , `uid` ,`nid` FROM comments WHERE `cid` = '".$cid."' LIMIT 0 , 1 ; ";
			$result = mysql_query($query);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if(strtolower($rows[username])==strtolower($currentuser["userid"]))
			{
				$query = "DELETE FROM comments WHERE `cid` = '".$cid."' ";
				mysql_query($query,$link);
				$query = "UPDATE nodes SET commentcount = commentcount - 1 WHERE `nid` = '".$rows[nid]."' ; ";
				mysql_query($query,$link);
			}
			else
			{
				$query = "SELECT `uid` FROM users WHERE `username` = '".$currentuser["userid"]."' AND `uid` = '".$rows[uid]."' LIMIT 0 , 1; ";
				$result = mysql_query($query,$link);
				if($rows1 = mysql_fetch_array($result))
				{
					$query = "DELETE FROM comments WHERE `cid` = '".$cid."' ";
					mysql_query($query,$link);
					$query = "UPDATE nodes SET commentcount = commentcount - 1 WHERE `nid` = '".$rows[nid]."' ; ";
					mysql_query($query,$link);
				}
				@mysql_free_result($result);
			}
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php		
		}
		elseif($act == "edit")
		{
			$query = "SELECT `subject`,`body` FROM comments WHERE `cid` = '".$cid."' AND `username` = '".$currentuser["userid"]."' LIMIT 0 , 1 ; ";
			$result = mysql_query($query);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if(!$rows)
			{
				html_error_quit("��ѡ������۲�����!");
				exit();
			}
?>
<form action="pceditcom.php?act=edit2&cid=<?php echo $cid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" width="100%" border="1">
<tr>
	<td>�޸�����</td>
</tr>
<tr>
	<td align="left"><hr width="90%" align="left"></td>
</tr>
<tr>
	<td>
	����
	<input type="text" name="subject" size="100" value="<?php echo stripslashes($rows[subject]); ?>">
	</td>
</tr>
<tr>
	<td>�������</td>
</tr>
<tr>
	<td><?php @require("emote.html"); ?></td>
</tr>
<tr>
	<td>����</td>
</tr>
<tr>
	<td><textarea name="body" cols="100" rows="20" id="body"><?php echo stripslashes($rows[body]); ?></textarea></td>
</tr>
<tr>
	<td>
	<input type="submit" value="�޸�����">
	<input type="button" value="������ҳ" onclick="history.go(-1)">
</tr>
</table>
</form>			
<?php			
		}
		elseif($act == "edit2")
		{
			$emote = (int)($_POST["emote"]);
			$query = "UPDATE `comments` SET `subject` = '".addslashes($_POST["subject"])."',`changed` = '".date("YmdHis")."',`body` = '".addslashes($_POST["body"])."' , `emote` = '".$emote."' WHERE `cid` = '".$cid."' AND `username` = '".$currentuser["userid"]."' LIMIT 1 ;";
			mysql_query($query,$link);
?>
<p align="center">
<a href="javascript:history.go(-2);">�����ɹ�,�������</a>
</p>
<?php
		}
		
		pc_db_close($link);
		html_normal_quit();
	} 
?>