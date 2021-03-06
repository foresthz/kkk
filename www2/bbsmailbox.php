<?php
	require("www2-funcs.php");
	login_init();
	bbs_session_modify_user_mode(BBS_MODE_MAIL);
	assert_login();
	
	mailbox_header("阅读信件");
		
	if (isset($_GET["path"])){
		$mail_path = $_GET["path"];
		$mail_title = $_GET["title"];
	}
	else {
		$mail_path = ".DIR";    //default is .DIR
		$mail_title = "收件箱";
	}
	if (isset($_GET["start"]))
		$start = $_GET["start"];
	else
		$start = 999999;   //default*/

	if (strstr($mail_path,'..'))
		html_error_quit("读取邮件数据失败!");

	$mail_fullpath = bbs_setmailfile($currentuser["userid"],$mail_path);
	$mail_num = bbs_getmailnum2($mail_fullpath);
	if($mail_num < 0 || $mail_num > 30000)
		html_error_quit("Too many mails!");
	$num = 19;
	if ($start > $mail_num - 19)
		$start = $mail_num - 19;
		 if ($start < 0)
	{
		$start = 0;
		if ($num > $mail_num) $num = $mail_num;
	}
	$maildata = bbs_getmails($mail_fullpath,$start,$num);
	if ($maildata == FALSE)
		html_error_quit("读取邮件数据失败!");

	//system mailboxs
	$mail_box = array(".DIR",".SENT",".DELETED");
	$mail_boxtitle = array("收件箱","发件箱","垃圾箱");
	//$mail_boxnums = array(bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],".DIR")),bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],".SENT")),bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],".DELETED")));
	//custom mailboxs
	$mail_cusbox = bbs_loadmaillist($currentuser["userid"]);
	//$totle_mails = $mail_boxnums[0]+$mail_boxnums[1]+$mail_boxnums[2];
	$i = 2;
	if ($mail_cusbox != -1){
		foreach ($mail_cusbox as $mailbox){
			$i++;
			$mail_box[$i] = $mailbox["pathname"];
			$mail_boxtitle[$i] = $mailbox["boxname"];
			//$mail_boxnums[$i] = bbs_getmailnum2(bbs_setmailfile($currentuser["userid"],$mailbox["pathname"]));
			//$totle_mails+= $mail_boxnums[$i];
			}
		}
	$mailboxnum = $i + 1;
	$mail_title_encode = rawurlencode($mail_title);
?>
<script type="text/javascript">
<!--
function checkall(form)  {
  for (var i=0;i<form.elements.length;i++)    {
	var e = form.elements[i];
	if (e.name != 'chkall')       e.checked = form.chkall.checked; 
   }
  }
function bbsconfirm(url,infor){
	if(confirm(infor)){
		window.location.href=url;
		return true;
		}
	return false;
}
<?php
	if($mail_path == ".DIR") {
?>
if(top.window['f4'])
	top.window['f4'].hasMail = 0;
<?php
	}
?>
//-->
</script>
<div class="mail">
<div class="mailH">
<a href="bbspstmail.php">写邮件</a>
<?php
	$current_i = 0;
	for($i=0;$i<$mailboxnum;$i++){
		if($mail_path==$mail_box[$i]&&$mail_title==$mail_boxtitle[$i]){
			$current_i = $i;
?>
<b><?php echo htmlspecialchars($mail_boxtitle[$i]); ?></b>
<?php			
		} else{
?>
<a href="bbsmailbox.php?path=<?php echo $mail_box[$i];?>&title=<?php echo urlencode($mail_boxtitle[$i]);?>"><?php echo htmlspecialchars($mail_boxtitle[$i]); ?></a>
<?php		
		}
	}
?>
</div>
<div class='mailM'>
<p align="center" class="b9">
您的 <font class="b3"><?php echo $mail_title; ?></font> 里共有 <font class="b3"><?php echo $mail_num; ?></font> 封邮件
[<a href="bbsmail.php" class="b9">返回邮箱列表</a>]
</p>
<form action="bbsmailact.php?act=move&<?php echo "dir=".urlencode($mail_path)."&title=".$mail_title_encode; ?>" method="POST">
<table width="95%" cellspacing="1" cellpadding="5" border="0" bgcolor="#666666">
	<tr bgcolor="#00BFFF">
		<td class="mt2" width="30">已读</td>
		<td class="mt2" width="30">选中</td>
		<td class="mt2" width="30">序号</td>
		<td class="mt2" width="30">状态</td>
		<td class="mt2" width="100"><?php echo ($mail_path==".SENT"?"收信者":"发信者"); ?></td>
		<td class="mt2">标题</td>
		<td class="mt2" width="100">时间</td>
		<td class="mt2" width="50">大小</td>
		<td class="mt2" width="40">删除</td>
	</tr>
<?php
	if($mail_num == 0)
	{
?>
<tr><td colspan="8" align="center"><font color="#999999">文件夹中目前没有邮件</font></td></tr>
<?php
	}
	else
	{
		$bgcs = false;
		for ($i = 0; $i < $num; $i++)
		{
			$bgcs = !$bgcs;
			if(stristr($maildata[$i]["FLAGS"],"N"))
				$newmail = true;
			else
				$newmail = false;
?>
<tr bgcolor="<?php echo $newmail?"#98FB98":($bgcs?"#F0FFFF":"E0FFFF"); ?>">
	<td class="mt3">
	<?php 
		if($newmail)
			echo "<img src='images/nmail.gif' alt='未读邮件' border='0'>";
		else
			echo "<img src='images/omail.gif' alt='已读邮件' border='0'>";
	?>
	</td>
	<td class="mt4">
	<input type="checkbox" name="file<?php echo $i; ?>" value="<?php echo $maildata[$i]["FILENAME"]	?>">
	</td>
	<td class="mt3"><?php echo $start+$i+1;?></td>
	<td class="mt4"><nobr>&nbsp;<?php echo $maildata[$i]["FLAGS"]; if ($maildata[$i]["ATTACHPOS"]>0) echo "<font color='red'>@</font>"; ?>&nbsp;</nobr></td>
	<td class="mt3"><a href="bbsqry.php?userid=<?php echo $maildata[$i]["OWNER"];?>"><?php echo $maildata[$i]["OWNER"];?></a></td>
	<td class="mt5">&nbsp;<a href="bbsmailcon.php?dir=<?php echo $mail_path;?>&num=<?php echo $i+$start;?>&title=<?php echo $mail_title_encode;?>"><?php
if(strncmp($maildata[$i]["TITLE"],"Re: ",4))
	echo "★" .  htmlspecialchars($maildata[$i]["TITLE"]);
else
	echo htmlspecialchars($maildata[$i]["TITLE"]);
?> </a></td>
	<td class="mt3"><?php echo strftime("%b&nbsp;%e&nbsp;%H&nbsp;:%M",$maildata[$i]["POSTTIME"]);?></td>
	  <td class="mt3" style="text-align:right;padding-right:10pt;"><?php echo sizestring($maildata[$i]['EFFSIZE']); ?></td>
	<td class="mt4"><input type="button" name="del" value="删除" class="bt1" onclick="bbsconfirm('bbsmailact.php?act=del&<?php echo "dir=".urlencode($mail_path)."&file=".urlencode($maildata[$i]["FILENAME"])."&title=".$mail_title_encode; ?>','确认删除该邮件吗?')"></td>
</tr>
<?php
		}
	}
?>
</table>
<table cellpadding="3" cellspacing="0" width="95%" border="0" class="b9">
<tr><td class="b9">
<input type="button" value="发送邮件" class="bt1" onclick="window.location.href='bbspstmail.php'">
<input onclick="checkall(this.form)" type="checkbox" value="on" name="chkall" align="absmiddle">
选中本页所有邮件
<?php
/*
<input type="submit" value="移到" class="bt1">
<select name="object" id="object" class="bt2">
<?php
	for($i=0;$i<$mailboxnum;$i++){
		if($mail_path==$mail_box[$i]&&$mail_title==$mail_boxtitle[$i])
			continue;
		echo "<option value='".urlencode($mail_boxtitle[$i])."'>".htmlspecialchars($mail_boxtitle[$i])."</option>";
		}
?>
</select>
*/
?>
<input type="hidden" name="act2" value="delarea">
<input type="submit" value="删除所选邮件" class="bt1" onclick="if(confirm('删除选中的邮件吗?')){submit();return true;}return false;">
<?php
	if ($mail_path == ".DELETED") {
?>
<input type="button" value="清空垃圾箱" class="bt1" onclick="bbsconfirm('bbsmailact.php?act=clear', '确定清空垃圾箱?');">
<?php
	}
?>
</td>
<td class="b9" align="right">
<?php
		if ($start > 0)
		{
			$i = $start - 19;
			if ($i < 0)$i = 0;
			echo "<a href=\"bbsmailbox.php?path=$mail_path&start=0&title=$mail_title_encode\">第一页</a> ";
			echo "<a href=\"bbsmailbox.php?path=$mail_path&start=$i&title=$mail_title_encode\">上一页</a> ";
		}
		if ($start < $mail_num - 19)
		{
			$i = $start + 19;
			if ($i > $mail_num -1)$i = $mail_num -1;
			echo "<a href=\"bbsmailbox.php?path=$mail_path&start=$i&title=$mail_title_encode\">下一页</a> ";
			echo "<a href=\"bbsmailbox.php?path=$mail_path&title=$mail_title_encode\">最后一页</a> ";
		}
?>
[<a href="bbsmail.php" class="b9">返回邮箱列表</a>]
&nbsp;&nbsp;</td></tr></table></form>
<table cellpadding="3" cellspacing="0" width="95%" border="0" class="b9">
<form action="bbsdelmail.php?<?php echo "dir=".urlencode($mail_path)."&title=".$mail_title_encode; ?>" method="POST">
<input type="hidden" name="dir" value="<?php echo $mail_path; ?>">
<input type="hidden" name="title" value="<?php echo $mail_title_encode; ?>">
<tr><td class="b9">
区段删除：
起始序号
<input type="text" size="3" class="b9" name="dstart">
结束序号
<input type="text" size="3" class="b9" name="dend">
删除类型：
<input type="radio" class="b9" name="dtype" value="0" checked>普通
<input type="radio" class="b9" name="dtype" value="1">强制&nbsp;&nbsp;
<input type="submit" value="区段删除邮件" class="bt1"/>
</td></tr>
</form></table>
</div>
</div>
<?php
	page_footer();
?>
