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
		html_error_quit("guest ���ܷ�������!\n<br>\n<a href=\"/\" target=\"_top\">���ڵ�¼</a>");
		exit();
	}
	else
	{
		pc_html_init("gb2312",$pcconfig["BBSNAME"]."Blog","","","",TRUE);		
		$nid = (int)($_GET["nid"]);
		$act = $_GET["act"];
		$cid = (int)($_GET["cid"]);
		
		$link =	pc_db_connect();
		$query = "SELECT `access`,`uid` FROM nodes WHERE `nid` = '".$nid."' AND `type` != '1' ;";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		mysql_free_result($result);
		
		if(!$rows)
		{
			html_error_quit("�����۵����²�����!");
			exit();
		}
		
		$uid = $rows[uid];
				
		if($rows[access] != 0)
		{
			$pc = pc_load_infor($link,"",$rows[uid]);
			if(!$pc)   
	                {   
	                	html_error_quit("�Բ�����Ҫ�鿴��Blog������");   
	                	exit();   
	                }
	                
	                $userPermission = pc_get_user_permission($currentuser,$pc);
			$sec = $userPermission["sec"];
			$pur = $userPermission["pur"];
			$tags = $userPermission["tags"];
			if(!$tags[$rows[access]])
			{
				html_error_quit("�Բ��������ܲ鿴������¼!");
				exit();
			}
		}
		
		if($act == "pst")
		{
?>
<br><center>		
<form name="postform" action="pccom.php?act=add&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" width="90%" border="0" class="t1">
<tr>
	<td class="t2">��������</td>
</tr>
<tr>
	<td class="t8">
	����
	<input type="text" name="subject" maxlength="200" size="100" class="f1">
	</td>
</tr>
<tr>
	<td class="t13">�������</td>
</tr>
<tr>
	<td class="t5"><?php @require("emote.html"); ?></td>
</tr>
<tr>
	<td class="t11">����
	<input type="checkbox" name="htmltag" value=1 checked>ʹ��HTML���
	</td>
</tr>
<tr>
	<td class="t8"><textarea name="blogbody" class="f1" cols="100" rows="20" id="blogbody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical">
	<?php echo $pcconfig["EDITORALERT"].$_POST["blogbody"]; ?>
	</textarea></td>
</tr>
<tr>
	<td class="t2">
	<input type="button" name="ins" value="����HTML" class="b1" onclick="return insertHTML();" />
	<input type="button" name="hil" value="����" class="b1" onclick="return highlight();" />
	<input type="submit" value="��������" class="b1">
	<input type="button" value="������ҳ" class="b1" onclick="history.go(-1)">
</tr>
</table>
</form></center>	
<?php			
		}
		elseif($act == "add")
		{
			if(!$_POST["subject"])
			{
				html_error_quit("���������۱���!");
				exit();
			}
			$emote = (int)($_POST["emote"]);
			$useHtmlTag = ($_POST["htmltag"]==1)?1:0;
			$query = "INSERT INTO `comments` ( `cid` , `nid` , `uid` , `emote` , `hostname` , `username` , `subject` , `created` , `changed` , `body`  , `htmltag`)". 
				"VALUES ('', '".$nid."', '".$uid."', '".$emote."' , '".$_SERVER["REMOTE_ADDR"]."', '".$currentuser["userid"]."', '".addslashes($_POST["subject"])."', '".date("YmdHis")."' , '".date("YmdHis")."', '".addslashes(html_editorstr_format($_POST["blogbody"]))."' , '".$useHtmlTag."' );";
			mysql_query($query,$link);
			$query = "UPDATE nodes SET commentcount = commentcount + 1 , changed = changed  WHERE `nid` = '".$nid."' ;";
			mysql_query($query,$link);
?>
<script language="javascript">
window.location.href="pccon.php?id=<?php echo $uid; ?>&nid=<?php echo $nid; ?>";
</script>
<?php
		}
		
		pc_db_close($link);
		html_normal_quit();
	} 
?>