<?php
	/*
	**manage personal corp.
	**@id: windinsn Nov 19,2003	
	*/
	@session_start();
	/*
	**	���ղؼеļ��С����Ʋ�����Ҫ session ֧�� windinsn nov 25,2003
	*/
	require("pcfuncs.php");
	if ($loginok != 1)
		html_nologin();
	elseif(!strcmp($currentuser["userid"],"guest"))
	{
		html_init("gb2312");
		html_error_quit("guest û�и����ļ�!");
		exit();
	}
	else
	{
		$link = pc_db_connect();
		$pc = pc_load_infor($link,$currentuser["userid"]);
		if(!$pc || !pc_is_admin($currentuser,$pc))
		{
			pc_db_close($link);
			html_error_quit("�Բ�����Ҫ�鿴�ĸ����ļ�������");
			exit();
		}
		
		$act = $_GET["act"]?$_GET["act"]:$_POST["act"];
		
		if(($act == "post" || $act == "edit") && !$_POST["subject"])
			pc_html_init("gb2312",stripslashes($pc["NAME"]),"","","",TRUE);
		else
			pc_html_init("gb2312",stripslashes($pc["NAME"]));
		
		if($act == "cut" || $act == "copy")
		{
			$target = (int)($_POST["target"]);
			$access = (int)($_POST["access"]);
			if($target < 0 || $target > 4 )
				$target = 2;//�����������������˽����
			if($target == 3)
			{
				$query = "SELECT `nid` FROM nodes WHERE `access` = '3' AND  `uid` = '".$pc["UID"]."' AND `pid` = '0' AND `type` = '1' LIMIT 0 , 1 ;";
				$result = mysql_query($query,$link);
				if($rows = mysql_fetch_array($result))
				{
					$rootpid = $rows[nid];
					mysql_free_result($result);
				}
				else
				{
					html_error_quit("�ղؼи�Ŀ¼����!");
					exit();
				}
			}
			
			if($act == "cut" && $target == 3)
				$query = "UPDATE nodes SET `access` = '".$target."' , `changed` = '".date("YmdHis")."' , `pid` = '".$rootpid."', `tid` = 0 WHERE `uid` = '".$pc["UID"]."' AND ( `nid` = '0' ";
			elseif($act == "cut")
				$query = "UPDATE nodes SET `access` = '".$target."' , `changed` = '".date("YmdHis")."' , `pid` = '0' , `tid` = 0 WHERE `uid` = '".$pc["UID"]."' AND `type` = 0  AND ( `nid` = '0' ";
			else
				$query = "SELECT * FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `type` = 0 AND ( `nid` = '0' ";
			
			$j = 0;
			for($i = 1 ;$i < $pc["NLIM"]+1 ; $i ++)
			{
				if($_POST["art".$i])
				{
					$query .= " OR `nid` = '".(int)($_POST["art".$i])."' ";
					$j ++;
				}
			}
			$query .= " ) ;";
			if($act == "cut")
			{
				if(pc_used_space($link,$pc["UID"],$target)+$j > $pc["NLIM"])
				{
					html_error_quit("Ŀ�������������������� (".$pc["NLIM"]." ƪ)!");
					exit();
				}
				else
				{
					mysql_query($query,$link);
				}
			}
			else
			{
				$result = mysql_query($query,$link);
				$num_rows = mysql_num_rows($result);
				$j = $num_rows;
				
				if(pc_used_space($link,$pc["UID"],$target)+$num_rows > $pc["NLIM"])
				{
					html_error_quit("Ŀ�������������������� (".$pc["NLIM"]." ƪ)!");
					exit();
				}
				for($i = 0;$i < $num_rows ; $i ++)
				{
					/*	Ŀǰ�������µ�ʱ�����۲�ͬ������	*/
					$rows = mysql_fetch_array($result);
					$query = "INSERT INTO `nodes` ( `pid` , `tid` , `type` , `source` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` ,`htmltag`)  ".
						" VALUES ('0','0' , '0', '".$rows[source]."', '".$rows[hostname]."','".date("YmdHis")."' , '".$rows[created]."', '".$pc["UID"]."', '".$rows[comment]."', '0', '".$rows[subject]."', '".$rows[body]."', '".$target."', '0','".$rows[htmltag]."');";
					mysql_query($query,$link);
				}
				
			}
			if($access == 0 && $act == "cut")
				pc_update_record($link,$pc["UID"]," - ".$j);
			if($target == 0)
				pc_update_record($link,$pc["UID"]," + ".$j);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php
		}
		elseif($act == "post")
		{
			$tag =(int)($_GET["tag"]);
			if($tag < 0 || $tag > 4 )
				$tag = 2;//���������������˽��������
			if($tag == 3)
			{
				
				$pid = (int)($_GET["pid"]);
				$query = "SELECT `nid` FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `access` = 3 AND `nid` = '".$pid."'; ";
				$result = mysql_query($query,$link);
				if($rows = mysql_fetch_array($result))
				{
					mysql_free_result($result);
					if(pc_used_space($link,$pc["UID"],3,$pid) >= $pc["NLIM"])
					{
						html_error_quit("Ŀ�������������������� (".$pc["NLIM"]." ƪ)!");
						exit();
					}
				}
				else
				{
					mysql_free_result($result);
					html_error_quit("��Ŀ¼������!");
					exit();
				}
					
			}
			else
			{
				$pid = 0;
				if(pc_used_space($link,$pc["UID"],$tag) >= $pc["NLIM"])
				{
					html_error_quit("Ŀ�������������������� (".$pc["NLIM"]." ƪ)!");
					exit();
				}
			}
			if($_POST["subject"])
			{
				if($_POST["comment"]==1)
					$c = 0;
				else
					$c = 1;
				$emote = (int)($_POST["emote"]);
				$useHtmlTag = ($_POST["htmltag"]==1)?1:0;
				$query = "INSERT INTO `nodes` (  `pid` , `tid` , `type` , `source` , `emote` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` , `htmltag`) ".
					"VALUES ( '".$pid."', '".(int)($_POST["tid"])."' , '0', '', '".$emote."' ,  '".$_SERVER["REMOTE_ADDR"]."','".date("YmdHis")."' , '".date("YmdHis")."', '".$pc["UID"]."', '".$c."', '0', '".addslashes($_POST["subject"])."', '".addslashes(html_editorstr_format($_POST["blogbody"]))."', '".$tag."', '0' , '".$useHtmlTag."' );";
				mysql_query($query,$link);
				if($tag == 0)
					pc_update_record($link,$pc["UID"]," + 1");
?>
<script language="javascript">
window.location.href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=<?php echo $tag; ?>&tid=<?php echo $_POST["tid"]; ?>&pid=<?php echo $pid; ?>";
</script>
<?php
			}
			else
			{
?>
<br><center>
<form name="postform" action="pcmanage.php?act=post&<?php echo "tag=".$tag."&pid=".$pid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" border="0" width="90%" class="t1">
<tr>
	<td class="t2">��������</td>
</tr>
<tr>
	<td class="t8">����
	<input type="text" size="100" maxlength="200" name="subject" class="f1">
	</td>
</tr>
<tr>
	<td class="t5">
	����
	<input type="radio" name="comment" value="0" checked class="f1">����
	<input type="radio" name="comment" value="1" class="f1">������
	</td>
</tr>
<tr>
	<td class="t8">
	�ļ�
	<select name="tid" class="f1">
<?php
		$blogs = pc_blog_menu($link,$pc["UID"],$tag);
		for($i = 0 ; $i < count($blogs) ; $i ++)
			echo "<option value=\"".$blogs[$i]["TID"]."\">".html_format($blogs[$i]["NAME"])."</option>";
?>
	</select>
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
	<td class="t8"><textarea name="blogbody" class="f1" cols="120" rows="30" id="blogbody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical">
	<?php echo $pcconfig["EDITORALERT"].$_POST["blogbody"]; ?>
	</textarea></td>
</tr>
<!--
<tr>
	<td class="t13">
	����ͨ��
	</td>
</tr>
<tr>
	<td class="t5">
	<textarea name="tb" class="f1" cols="100" rows="3" id="tb"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical"></textarea>
	</td>
</tr>
-->
<tr>
	<td class="t2">
		<input type="button" name="ins" value="����HTML" class="b1" onclick="return insertHTML();" />
		<input type="button" name="hil" value="����" class="b1" onclick="return highlight();" />
		<input type="submit" value="������" class="b1">
		<input type="button" value="������ҳ" onclick="history.go(-1)" class="b1">
	</td>
</tr>
</table>
</form></center>
<?php				
			}
		}
		elseif($act == "edit")
		{
			$nid = (int)($_GET["nid"]);
			$query = "SELECT `subject` , `body` ,`comment`,`type`,`tid`,`access`,`htmltag` FROM nodes WHERE `nid` = '".$nid."' AND `uid` = '".$pc["UID"]."' LIMIT 0 , 1 ; ";
			$result = mysql_query($query,$link);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if(!$rows)
			{
				html_error_quit("���²�����!");
				exit();
			}
			if($_POST["subject"])
			{
				if($_POST["comment"]==1)
					$c = 0;
				else
					$c = 1;
				$useHtmlTag = ($_POST["htmltag"]==1)?1:0;
				$emote = (int)($_POST["emote"]);
				$query = "UPDATE nodes SET `subject` = '".addslashes($_POST["subject"])."' , `body` = '".addslashes(html_editorstr_format($_POST["blogbody"]))."' , `changed` = '".date("YmdHis")."' , `comment` = '".$c."' , `tid` = '".(int)($_POST["tid"])."' , `emote` = '".$emote."' , `htmltag` = '".$useHtmlTag."'  WHERE `nid` = '".$nid."' ; ";
				mysql_query($query,$link);
				pc_update_record($link,$pc["UID"]);
?>
<p align="center">
<a href="javascript:history.go(-2);">�����ɹ�,�������</a>
</p>
<?php
			}
			else
			{
?>
<br><center>			
<form name="postform" action="pcmanage.php?act=edit&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" border="0" width="90%" class="t1">
<?php
		if($rows[type]==1)
		{
?>
<tr>
	<td class="t2">�޸�Ŀ¼</td>
</tr>
<tr>
	<td class="t8">
	����
	<input type="text" size="100" class="f1" maxlength="200" name="subject" value="<?php echo htmlspecialchars(stripslashes($rows[subject])); ?>">
	</td>
</tr>
<tr>
	<td class="t2">
		<input type="submit" value="�޸�Ŀ¼" class="b1">
		<input type="button" value="������ҳ" class="b1" onclick="history.go(-1)">
	</td>
</tr>
<?php
		}
		else
		{
?>
<tr>
	<td class="t2">�޸�����</td>
</tr>
<tr>
	<td class="t8">����
	<input type="text" size="100" class="f1" name="subject" value="<?php echo htmlspecialchars(stripslashes($rows[subject])); ?>">
	</td>
</tr>
<tr>
	<td class="t5">
	����
	<input type="radio" name="comment" class="f1" value="0" <?php if($rows[comment]!=0) echo "checked"; ?>>����
	<input type="radio" name="comment" class="f1" value="1" <?php if($rows[comment]==0) echo "checked"; ?>>������
	</td>
</tr>
<tr>
	<td class="t8">
	�ļ�
	<select name="tid" class="f1">
<?php
		$blogs = pc_blog_menu($link,$pc["UID"],$rows[access]);
		for($i = 0 ; $i < count($blogs) ; $i ++)
		{
			if($blogs[$i]["TID"] == $rows[tid])
				echo "<option value=\"".$blogs[$i]["TID"]."\" selected>".html_format($blogs[$i]["NAME"])."</option>";
			else
				echo "<option value=\"".$blogs[$i]["TID"]."\" >".html_format($blogs[$i]["NAME"])."</option>";
		}
?>
	</select>
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
	<input type="checkbox" name="htmltag" value=1 <?php if(strstr($rows[body],$pcconfig["NOWRAPSTR"]) || $rows[htmltag] == 1) echo "checked"; ?> >ʹ��HTML���
	</td>
</tr>
<tr>
	<td class="t8">
	<textarea name="blogbody" class="f1" cols="120" rows="30" id="blogbody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical">
	<?php echo $pcconfig["EDITORALERT"]; ?>
	<?php echo htmlspecialchars(stripslashes($rows[body]." ")); ?>
	</textarea></td>
</tr>
<tr>
	<td class="t2">
		<input type="button" name="ins" value="����HTML" class="b1" onclick="return insertHTML();" />
		<input type="button" name="hil" value="����" class="b1" onclick="return highlight();" />
		<input type="submit" value="�޸ı���" class="b1">
		<input type="button" value="������ҳ" onclick="history.go(-1)" class="b1">
	</td>
</tr>
<?php
		}
?>
</table>
</form></center>
<?php				
			}
		}
		elseif($act == "del")
		{
			$nid = (int)($_GET["nid"]);	
			$query = "SELECT `access`,`type` FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `nid` = '".$nid."' ; ";
			$result = mysql_query($query,$link);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if(!$rows)
			{
				html_error_quit("���²�����!");
				exit();
			}
			if($rows[access] == 4)
			{
				//����ɾ��	
				$query = "DELETE FROM nodes WHERE `nid` = '".$nid."' ";
				mysql_query($query,$link);
				$query = "DELETE FROM comments WHERE `nid` = '".$nid."' ";
				mysql_query($query,$link);
			}
			else
			{
				if($rows[type] == 1)
				{
					$query = "SELECT `nid` FROM nodes WHERE `pid` = '".$nid."' LIMIT 0, 1 ;";
					$result = mysql_query($query);
					if($rows0 = mysql_fetch_array($result))
					{
						mysql_free_result($result);
						html_error_quit("����ɾ����Ŀ¼�µ�����!");
						exit();
					}
					mysql_free_result($result);
					$query = "DELETE FROM nodes WHERE `nid` = '".$nid."' ;";
					mysql_query($query,$link);
				}
				else
				{
					$query = "UPDATE nodes SET `access` = '4' , `changed` = '".date("YmdHis")."' , `tid` = '0' WHERE `nid` = '".$nid."' ;";
					mysql_query($query,$link);
					if($rows[access] == 0)
						pc_update_record($link,$pc["UID"]," - 1");
				}
			}
			pc_update_record($link,$pc["UID"]);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php		
		}
		elseif($act == "clear")
		{
			$query = "SELECT `nid` FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `access` = '4' ; ";	
			$result = mysql_query($query,$link);
			$query = "DELETE FROM comments WHERE `nid` = '0' ";
			while($rows = mysql_fetch_array($result))
			{
				$query.= "  OR `nid` = '".$rows[nid]."' ";	
			}
			mysql_query($query,$link);
			$query = "DELETE FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `access` = '4' ; ";
			mysql_query($query,$link);
			pc_update_record($link,$pc["UID"]);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php			
		}
		elseif($act == "tedit")
		{
			$query = "SELECT * FROM topics WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".(int)($_GET["tid"])."' ; ";	
			$result = mysql_query($query,$link);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if(!$rows)
			{
				html_error_quit("�ļ�������!");
				exit();
			}
			if($_POST["topicname"])
			{
				/*
				if($_POST["access"] != $rows[access])
				{
					$query = "UPDATE nodes SET `access` = '".$_POST["access"]."' , `changed` = '".date("YmdHis")."' WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$rows[tid]."' ; ";
					mysql_query($query,$link);
				}
				*/
				//$query = "UPDATE topics SET `topicname` = '".$_POST["topicname"]."' , `access` = '".$_POST["access"]."' WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$rows[tid]."' ; ";
				$query = "UPDATE topics SET `topicname` = '".addslashes($_POST["topicname"])."' WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$rows[tid]."' ; ";
				mysql_query($query,$link);
				pc_update_record($link,$pc["UID"]);
				
?>
<p align="center">
<a href="javascript:history.go(-2);">�����ɹ�,�������</a>
</p>
<?php			
			}
			else
			{
				$sec = array("������","������","˽����");
?>
<br>
<center>
<form action="pcmanage.php?act=tedit&tid=<?php echo $rows[tid]; ?>" method="post" onsubmit="if(this.topicname.value==''){alert('�������ļ�����!');return false;}">
<table cellspacing="0" cellpadding="5" border="0" width="90%" class="t1">
<tr>
	<td class="t2">�޸��ļ�</td>
</tr>
<?php /*
<tr>
	<td>
	���ڷ���
	<select name="access">
<?php
		for($i =0 ;$i < 3 ;$i++ )
		{
			if($i == $rows[access])
				echo "<option value=\"".$i."\" selected>".$sec[$i]."</option>\n";
			else
				echo "<option value=\"".$i."\">".$sec[$i]."</option>\n";
		}
?>	
	</select>
	</td>
</tr>

*/
?>
<tr>
	<td class="t8">
	�ļ���
	<input type="text" class="f1" name="topicname" value="<?php echo htmlspecialchars(stripslashes($rows[topicname])); ?>">
	</td>
</tr>
<tr>
	<td class="t2">
	<input type="submit" value="�޸��ļ�" class="b1">
	<input type="button" value="������ҳ" class="b1" onclick="history.go(-1)">
	</td>
</tr>
</table>
</form></center>
<?php
			}
		}
		elseif($act == "tdel")
		{
			$query = "SELECT `nid` FROM nodes WHERE `tid` = '".(int)($_GET["tid"])."' ; ";
			$result = mysql_query($query,$link);
			$rows = mysql_fetch_array($result);
			mysql_free_result($result);
			if($rows)
			{
				html_error_quit("����ɾ���ļ��е�����!");
				exit();
			}
			else
			{
				$query = "DELETE FROM topics WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".(int)($_GET["tid"])."' ; ";
				mysql_query($query,$link);
				pc_update_record($link,$pc["UID"]);
?>
<p align="center">
<a href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=6">�����ɹ�,�������</a>
</p>
<?php				
			}
		}
		elseif($act == "tadd" && $_POST["topicname"])
		{
			$access = (int)($_POST["access"]);
			if($access < 0 || $access > 2)
				$access = 0;
			$query = "INSERT INTO `topics` (`uid` , `access` , `topicname` , `sequen` ) ".
					"VALUES ( '".$pc["UID"]."', '".$access."', '".addslashes($_POST["topicname"])."', '0');";
			mysql_query($query,$link);
			pc_update_record($link,$pc["UID"]);
?>
<p align="center">
<a href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=6">�����ɹ�,�������</a>
</p>
<?php
		}
		elseif($act == "sedit" && $_POST["pcname"])
		{
			$query = "UPDATE `users` SET `createtime` = `createtime` , `corpusname` = '".addslashes(undo_html_format($_POST["pcname"]))."',`description` = '".addslashes(undo_html_format($_POST["pcdesc"]))."',`theme` = '".addslashes(undo_html_format($_POST["pcthem"]))."' , `backimage` = '".addslashes(undo_html_format($_POST["pcbkimg"]))."' , `logoimage` = '".addslashes(undo_html_format($_POST["pclogo"]))."' , `modifytime` = '".date("YmdHis")."' WHERE `uid` = '".$pc["UID"]."' LIMIT 1 ;";	
			mysql_query($query,$link);
			
?>
<p align="center">
<a href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>">�����ɹ�,�������</a>
</p>
<?php
		}
		elseif($act == "adddir" && $_POST["dir"])
		{
			$pid = (int)($_POST["pid"]);
			if(pc_dir_num($link,$pc["UID"],$pid)+1 > $pc["DLIM"])
			{
				html_error_quit("Ŀ���ļ����е�Ŀ¼���Ѵ����� ".$pc["DLIM"]. " ��!");
				exit();
			}
			$query = "INSERT INTO `nodes` ( `nid` , `pid` , `type` , `source` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` , `tid` , `emote` ) ".
				"VALUES ('', '".$pid."', '1', '', '".$_SERVER["REMOTE_ADDR"]."','".date("YmdHis")."', '".date("YmdHis")."', '".$pc["UID"]."', '0', '0', '".addslashes($_POST["dir"])."', NULL , '3', '0', '0', '0');";
			mysql_query($query,$link);
			pc_update_record($link,$pc["UID"]);
?>
<script language="javascript">
window.location.href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=3&pid=<?php echo $pid; ?>";
</script>
<?php
		}
		elseif($act == "favcut" || $act == "favcopy")
		{
			//Ŀǰ��֧��Ŀ¼�ļ��к͸���
			$query = "SELECT `nid`,`type`,`pid`,`subject`,`tid` FROM nodes WHERE `nid` = '".(int)($_GET["nid"])."' AND `uid` = '".$pc["UID"]."' AND `access` = 3  AND `type` = 0 LIMIT 0 , 1;";
			$result = mysql_query($query,$link);
			$rows = mysql_fetch_array($result);
			if(!$rows)
			{
				html_error_quit("���²�����!");
				exit();
			}
			$favaction = array(
					"ACT" => $act,
					"NID" => $rows[nid],
					"TYPE" => $rows[type],
					"PID" => $rows[pid]
					);
			mysql_free_result($result);
			session_register("favaction");
?>
<br>
<p align="center">
<a href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=3&tid=<?php echo $rows[tid]; ?>&pid=<?php echo $rows[pid]; ?>">�����ɹ�,�ѽ� <font class=f2><?php echo $rows[subject]; ?></font> ��������壬�������</a>
</p>
<?php			
		}
		elseif($act == "favpaste")
		{
			$favaction = $_SESSION["favaction"];
			if(!session_is_registered("favaction") || !$favaction)
			{
				html_error_quit("���ļ������ǿյģ����ȼ��л��߸���һ���ļ�!");
				exit();
			}
			$pid = (int)($_GET["pid"]);
			$query = "SELECT `nid` FROM nodes WHERE `nid` = '".$pid."' AND `uid` = '".$pc["UID"]."' AND `type` = 1 AND `access` = 3 LIMIT 0 , 1 ;";
			$result = mysql_query($query,$link);
			if(!$rows=mysql_fetch_array($result))
			{
				mysql_free_result($result);
				html_error_quit("Ŀ���ļ��в�����!");
				exit();
			}
			mysql_free_result($result);
			
			if(pc_file_num($link,$pc["UID"],$pid)+1 > $pc["NLIM"])
			{
				html_error_quit("Ŀ���ļ����е��ļ����Ѵ����� ".$pc["NLIM"]. " ��!");
				exit();
			}
			
			if($favaction["ACT"] == "favcut")
			{
				$query = "UPDATE nodes SET `pid` = '".$pid."' WHERE `nid` = '".$favaction["NID"]."'; ";
			}
			else
			{
				$query = "SELECT * FROM nodes WHERE `nid` = '".$favaction["NID"]."' LIMIT 0 , 1 ; ";
				$result = mysql_query($query,$link);
				$rows = mysql_fetch_array($result);
				mysql_free_result($result);
				$query = "INSERT INTO `nodes` ( `nid` , `pid` , `type` , `source` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` , `tid` , `emote` ,`htmltag`) ".
					"VALUES ('', '".$pid."', '0', '".$rows[source]."', '".$rows[hostname]."', '".date("YmdHis")."' , '".$rows[created]."', '".$pc["UID"]."', '".$rows[comment]."', '".$rows[commentcount]."', '".$rows[subject]."', '".$rows[body]."', '3', '".$rows[visitcount]."', '".$rows[tid]."', '".$rows[emote]."','".$rows[htmltag]."');";
			}
			mysql_query($query,$link);
			unset($favaction);
			session_unregister("favaction");
			pc_update_record($link,$pc["UID"]);
?>
<script language="javascript">
window.location.href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=3&pid=<?php echo $pid; ?>";
</script>
<?php		
		}
		
		html_normal_quit();
	}
	
?>