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
		html_error_quit("guest 不能发表评论!\n<br>\n<a href=\"/\">现在登录</a>");
		exit();
	}
	else
	{
		pc_html_init("gb2312","个人文集","","","",TRUE);		
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
			html_error_quit("所评论的文章不存在!");
			exit();
		}
		
		$uid = $rows[uid];
				
		if($rows[access] != 0)
		{
			$pc = pc_load_infor($link,"",$rows[uid]);
			
			 if(!$pc)   
                         {   
                                 html_error_quit("对不起，您要查看的个人文集不存在");   
                                 exit();   
                         } 

			if(strtolower($currentuser["userid"]) == strtolower($pc["USER"]))
				$pur = 2;
			elseif(pc_is_friend($currentuser["userid"],$pc["USER"]))
				$pur = 1;
			else
				$pur = 0;
			
			if( $rows[access] == 1 && $pur < 1)
			{
				html_error_quit("对不起，只有好友列表中的用户才能查看好友区文章!");
				exit();
			}
			if( $rows[access] > 1 && $pur < 2 )
			{
				html_error_quit("对不起，文集所有者才能查看该文章!");
				exit();
			}
		}
		
		if($act == "pst")
		{
?>
<br><center>		
<form name="postform" action="pccom.php?act=add&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('请输入评论主题!');return false;}">
<table cellspacing="0" cellpadding="5" width="90%" border="0" class="t1">
<tr>
	<td class="t2">发表评论</td>
</tr>
<tr>
	<td class="t8">
	主题
	<input type="text" name="subject" size="100" class="f1">
	</td>
</tr>
<tr>
	<td class="t13">心情符号</td>
</tr>
<tr>
	<td class="t5"><?php @require("emote.html"); ?></td>
</tr>
<tr>
	<td class="t11">内容</td>
</tr>
<tr>
	<td class="t8"><textarea name="blogbody" class="f1" cols="100" rows="20" id="blogbody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical">
	<!--NoWrap-->
	<!--
		Loading HTMLArea Editor , Please Wait ... ...
		正在加载 HTML编辑器 ， 请稍候 ……
	-->
	</textarea></td>
</tr>
<tr>
	<td class="t2">
	<input type="button" name="ins" value="插入HTML" class="b1" onclick="return insertHTML();" />
	<input type="button" name="hil" value="高亮" class="b1" onclick="return highlight();" />
	<input type="submit" value="发表评论" class="b1">
	<input type="button" value="返回上页" class="b1" onclick="history.go(-1)">
</tr>
</table>
</form></center>	
<?php			
		}
		elseif($act == "add")
		{
			if(!$_POST["subject"])
			{
				html_error_quit("请输入评论标题!");
				exit();
			}
			$emote = (int)($_POST["emote"]);
			$query = "INSERT INTO `comments` ( `cid` , `nid` , `uid` , `emote` , `hostname` , `username` , `subject` , `created` , `changed` , `body` )". 
				"VALUES ('', '".$nid."', '".$uid."', '".$emote."' , '".$_SERVER["REMOTE_ADDR"]."', '".$currentuser["userid"]."', '".addslashes($_POST["subject"])."', '".date("YmdHis")."' , '".date("YmdHis")."', '".addslashes($_POST["blogbody"])."');";
			mysql_query($query,$link);
			$query = "UPDATE nodes SET commentcount = commentcount + 1 WHERE `nid` = '".$nid."' ; ";
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