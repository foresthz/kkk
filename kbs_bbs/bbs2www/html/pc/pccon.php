<?php
	/*
	** this file display article  in personal corp.
	** @id:windinsn  Nov 19,2003
	*/
	@session_start();
	$needlogin=0;
	require("pcfuncs.php");
	
	function pc_add_new_comment($pc,$nid,$alert)
	{
?>
<center>
<form name="postform" action="pccom.php?act=add&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" width="500" border="0" class="t1">
<tr>
	<td class="t5"><strong>�������� </strong>
	<?php if($alert){ ?>
	<font class=f4>
	ע�⣺���б�վ��¼�û����ܷ�������[<a href="/">�����¼</a>]��
	</font>
	<?php } ?>
	</td>
</tr>
<tr>
	<td class="t8">
	����
	<input type="text" name="subject" maxlength="200" size="60" class="f1">
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
	<input type="hidden" name="htmltag" value=0>
	</td>
</tr>
<tr>
	<td class="t8"><textarea name="blogbody" class="f1" cols="60" rows="10" id="blogbody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical"></textarea></td>
</tr>
<tr>
	<td class="t5">
	<input type="submit" value="��������" class="f1">
	<input type="button" value="������ҳ" class="f1" onclick="history.go(-1)">
	<input type="button" value="ʹ��HTML�༭��" class="f1" onclick="window.location.href='pccom.php?act=pst&nid=<?php echo $nid; ?>';">
</tr>
</table>
</form></center>
<?php		
	}
	
	function pc_node_counter($link,$nid)
	{
		$query = "UPDATE nodes SET visitcount = visitcount + 1 , changed  = changed  WHERE `nid` = '".$nid."' ;";
		mysql_query($query,$link);
	}
	
	function display_navigation_bar($link,$pc,$nid,$pid,$tag,$spr,$order,$comment,$tid=0,$pur,$trackback , $subject)
	{
		$query = "SELECT `nid` FROM nodes WHERE `nid` < ".$nid." AND `uid` = '".$pc["UID"]."' AND `pid` = '".$pid."' AND `access` = '".$tag."' AND `tid` = '".$tid."' AND `type` != '1' ORDER BY `nid` DESC LIMIT 0 , 1 ;";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		if($rows)
			echo " <a href=\"pccon.php?id=".$pc["UID"]."&nid=".$rows[nid]."&pid=".$pid."&tag=".$tag."&tid=".$tid."\">��һƪ</a>\n";
		else
			echo " ��һƪ\n";
		mysql_free_result($result);
		$query = "SELECT `nid` FROM nodes WHERE `nid` > ".$nid." AND `uid` = '".$pc["UID"]."' AND `pid` = '".$pid."' AND `access` = '".$tag."' AND `tid` = '".$tid."' AND `type` != '1' ORDER BY `nid` ASC LIMIT 0 , 1 ;";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		if($rows)
			echo " <a href=\"pccon.php?id=".$pc["UID"]."&nid=".$rows[nid]."&pid=".$pid."&tag=".$tag."&tid=".$tid."\">��һƪ</a>\n";
		else
			echo " ��һƪ\n";
		mysql_free_result($result);
		
		if($comment != 0)
		{
			if($spr)
				echo "<a href=\"pccon.php?id=".$pc["UID"]."&nid=".$nid."\">����ʾ��������</a>\n";
			else
				echo "<a href=\"pccon.php?id=".$pc["UID"]."&nid=".$nid."&s=all\">չ����������</a>\n";
			echo "<a href=\"pccom.php?act=pst&nid=".$nid."\">��������</a>\n";
		}
		if($pur == 3)
			echo "<a href=\"pcmanage.php?act=edit&nid=".$nid."\">�޸�</a>\n";
		if($trackback)
			echo 	"<a href=\"javascript:openScript('pctb.php?nid=".$nid."&uid=".$pc["UID"]."&subject=".base64_encode($subject)."',460 , 480)\">����</a>\n";
		echo 	"<a href=\"/bbspstmail.php?userid=".$pc["USER"]."&title=�ʺ�\">д���ʺ�</a>\n".
			//"<a href=\"pccon.php?id=".$id."&nid=".$nid."\">ת��</a>\n".
			//"<a href=\"pccon.php?id=".$id."&nid=".$nid."\">ת��</a>\n".
			"<a href=\"pcdoc.php?userid=".$pc["USER"]."&pid=".$pid."&tag=".$tag."&order=".$order."&tid=".$tid."\">����Ŀ¼</a>\n".
			"<a href=\"javascript:history.go(-1);\">���ٷ���</a>\n";
	}
	
	function display_pc_comments($link,$uid,$nid,$spr)
	{
		global $pc;
		global $currentuser;
		
		if(strtolower($pc["USER"]) == strtolower($currentuser["userid"]))
			$perm = TRUE;
		else
			$perm = FALSE;
		
		if($spr)
			$query = "SELECT * FROM comments WHERE `nid` = '".$nid."' AND `uid` = '".$uid."' ORDER BY `cid` ASC ;";
		else
			$query = "SELECT `username` , `emote` , `subject` , `created`,`cid` FROM comments WHERE `nid` = '".$nid."' AND `uid` = '".$uid."' ORDER BY `cid` ASC ;";
		
		$result = mysql_query($query,$link);
		$re_num = mysql_num_rows($result);
?>
<table cellspacing="0" cellpadding="3" border="0" width="90%" class="t1">
<tr>
	<td class="t9" colspan="2">���� <?php echo $re_num; ?> ������</td>
</tr>
<?php
		for($i = 0;$i < $re_num ;$i++)
		{
			$contentcss = ($rows[htmltag])?"contentwithhtml":"content";
			if($i%2==0)
				$tdclass= array("t8","t10","t11");
			else
				$tdclass= array("t5","t12","t13");
			$rows = mysql_fetch_array($result);
			echo "<tr>\n<td class=\"".$tdclass[1]."\">&nbsp;".
				"<img src=\"icon/".$rows[emote].".gif\" border=\"0\" align=\"absmiddle\">\n".
				"<a href=\"pcshowcom.php?cid=".$rows[cid]."\">".
				html_format($rows[subject]).
				"</a>".
				"[<a href=\"/bbsqry.php?userid=".$rows[username]."\">".$rows[username]."</a> �� ".time_format($rows[created])." �ᵽ]\n";
			if($perm || strtolower($rows[username]) == strtolower($currentuser["userid"]))
				echo "[<a href=\"#\" onclick=\"bbsconfirm('pceditcom.php?act=del&cid=".$rows[cid]."','ȷ��ɾ��?')\">ɾ</a>]\n";
			if(strtolower($rows[username]) == strtolower($currentuser["userid"]))
				echo "[<a href=\"pceditcom.php?act=edit&cid=".$rows[cid]."\">��</a>]\n";
			echo "</td><td width=\"100\" align=\"right\" class=\"".$tdclass[0]."\"><font class=\"f4\">".($i+1)."</font>&nbsp;&nbsp;</td>\n</tr>\n";
			if($spr)
			{
				echo "<tr>\n<td colspan='2' class=\"".$tdclass[2]."\"><font class='".$contentcss."'>".
					html_format($rows[body],TRUE,$rows[htmltag])."</font></td>\n</tr>\n".
					"<tr>\n<td colspan='2' align='right' class=\"".$tdclass[0]."\">[FROM: ".$rows[hostname]."]".
					"</td>\n</tr>\n";
			}	
		}
?>
</table>
<?php		
		mysql_free_result($result);
		return $re_num;
	}
	
	$id = (int)($_GET["id"]);
	$nid = (int)($_GET["nid"]);
	$pid = (int)($_GET["pid"]);
	$tag = (int)($_GET["tag"]);
	if($_GET["s"]=="all")
		$spr = TRUE;
	else
		$spr = FALSE;
	
	$link = pc_db_connect();
	$pc = pc_load_infor($link,"",$id);
	if(!$pc)
	{
		pc_db_close($link);
		html_init("gb2312","Blog");		
		html_error_quit("�Բ�����Ҫ�鿴��Blog������");
		exit();
	}
	
	pc_html_init("gb2312",$pc["NAME"],"","",$pc["BKIMG"]);
	
	if(pc_is_admin($currentuser,$pc) && $loginok == 1)
		$pur = 3;
	elseif(pc_is_friend($currentuser["userid"],$pc["USER"]) || bbs_is_bm($pcconfig["BRDNUM"], $currentuser["index"]))
		$pur = 1;
	else
		$pur = 0;
		
	$query = "SELECT * FROM nodes WHERE `nid` = '".$nid."' AND `uid` = '".$id."' LIMIT 0 , 1 ;";
	$result = mysql_query($query,$link);
	$rows = mysql_fetch_array($result);
	mysql_free_result($result);
	
	if(!$rows)
	{
		html_error_quit("�Բ�����Ҫ�鿴�ĸ������²�����");
		exit();
	}
	if( $rows[access] == 1 && $pur < 1)
	{
		html_error_quit("�Բ���ֻ�к����б��е��û����ܲ鿴����������!");
		exit();
	}
	if( $rows[access] > 1 && $pur < 2 )
	{
		html_error_quit("�Բ���Blog�����߲��ܲ鿴������!");
		exit();
	}
	$nid = $rows[nid];
	$tid = $rows[tid];
	
	if(!session_is_registered("readnodes"))
	{
		$readnodes = ",".$nid.",";
		session_register("readnodes");
		pc_node_counter($link,$nid);
		$rows[visitcount] ++;
	}
	elseif(!stristr($_SESSION["readnodes"],",".$nid.","))
	{
		$_SESSION["readnodes"] .= $nid.",";
		pc_node_counter($link,$nid);
		$rows[visitcount] ++;
	}
?>
<a name="top"></a>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td>
	<table cellspacing="0" cellpadding="3" border="0" width="100%" class="tt1">
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "<a href=\"/\" class=f1>".BBS_FULL_NAME."</a> - <a href='pc.php' class=f1>Blog</a> - <a href=\"pcdoc.php?userid=".$pc["USER"]."&tag=".$rows[access]."&pid=".$pid."\" class=f1>".$pc["NAME"]."</a>"; ?></td>
			<td align="right">http://<?php echo $pc["USER"].$pcconfig["DOMAIN"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>
	</table>
	</td>
</tr>
<tr>
	<td class="f2" align="center" height="40" valign="middle">
	<?php echo $pc["USER"]; ?> ��Blog
	-
	<?php echo $pc["NAME"]; ?>
	</td>
</tr>
<tr>
	<td>
	<table cellspacing="0" cellpadding="10" border="0" width="100%" class="tt2">
	<tr>
<?php
	if($pc["LOGO"])
		echo "<td><img src=\"".$pc["LOGO"]."\" border=\"0\" alt=\"".$pc["DESC"]."\"></td>\n";

?>	
		<td align="left">&nbsp;<?php echo $pc["DESC"]; ?></td>
		<td align="right">[����:<?php echo $pc["THEM"]; ?>]&nbsp;</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td align="center">
	<table cellspacing="0" cellpadding="5" border="0" width="90%" class="t1">
	<tr>
		<td colspan="2" class="t9">
		<img src="icon/<?php echo $rows[emote]; ?>.gif" border="0" align="absmiddle">
		<?php echo html_format($rows[subject]); ?></td>
	</tr>
	<tr>
		<td width="20%" align="left" valign="top" class="t8">
		���ߣ�<?php echo "<a href=\"/bbsqry.php?userid=".$pc["USER"]."\">".$pc["USER"]."</a>"; ?><br/>
		����ʱ�䣺<br/>
		<?php echo time_format($rows[created]); ?><br/>
		����ʱ�䣺<br/>
		<?php echo time_format($rows[changed]); ?><br/>
		�����<?php echo $rows[visitcount]; ?>��<br>
		<?php
			if($rows[comment]==0)
				echo "��������<br>";
			else
				echo "���ۣ�".$rows[commentcount]."ƪ<br>";
			
			if($rows[trackback])
				echo "���ã�".$rows[trackbackcount]."��<br/>";
		?>
		��ַ��<?php echo $rows[hostname]; ?>
		</td>
		<td width="80%" height="300" align="left" valign="top" class="t5">
		<font class="<?php echo ($rows[htmltag])?"contentwithhtml":"content"; ?>">
		<?php echo html_format($rows[body],TRUE,$rows[htmltag]); ?>&nbsp;
		</font>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="right" class="t8">
		<?php display_navigation_bar($link,$pc,$nid,$rows[pid],$rows[access],$spr,addslashes($_GET["order"]),$rows[comment],$tid,$pur,$rows[trackback],$rows[subject]); ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
<?php
		if($rows[comment]!=0)
		{
?>
<tr>
	<td align="center"><?php $re_num = display_pc_comments($link,$rows[uid],$rows[nid],$spr); ?></td>
</tr>
<?php
		}
?>
<tr>
	<td align="middle" class="f1" height="40" valign="middle">
	<?php
		if($re_num != 0)
			display_navigation_bar($link,$pc,$nid,$rows[pid],$rows[access],$spr,addslashes($_GET["order"]),$rows[comment],$tid,$pur,$rows[trackback],$rows[subject]); 
	?>
	&nbsp;</td>
</tr>
<tr>
	<td>
	<?php 
		if($rows[comment] && $rows[type] == 0)
		{
			$alert = ($loginok != 1 || !strcmp($currentuser["userid"],"guest"))?TRUE:FALSE;
			pc_add_new_comment($pc,$nid,$alert); 
		}
	?>
	</td>
</tr>
<tr>
	<td align="center" class="tt3" valign="middle" height="25">
	[<a href="#top" class=f1>���ض���</a>]
	[<a href='javascript:location=location' class=f1>ˢ��</a>]
	[<?php echo "<a href=\"/bbspstmail.php?userid=".$pc["USER"]."&title=�ʺ�\" class=f1>��".$pc["USER"]."д��</a>"; ?>]
	[<a href="index.php?id=<?php echo $pc["USER"]; ?>" class=f1><?php echo $pc["NAME"]; ?>��ҳ</a>]
	[<a href="pc.php" class=f1>Blog��ҳ</a>]
	[<a href="
<?php
	if(!strcmp($currentuser["userid"],"guest"))
		echo "/guest-frames.html";
	else
		echo "/frames.html";
?>	
	" class=f1 target="_top"><?php echo BBS_FULL_NAME; ?>��ҳ</a>]
	</td>
</tr>
</table>
<?php
	pc_db_close($link);
	html_normal_quit();
?>