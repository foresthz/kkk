<?php
	/*
	**manage personal corp.
	**@id: windinsn Nov 19,2003	
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
		$query = "SELECT `uid`,`nodelimit`,`dirlimit`,`corpusname`,`username`  FROM users WHERE username = '".$currentuser["userid"]."' LIMIT 0 , 1 ;";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		if(!$rows)
		{
			mysql_free_result($result);
			pc_db_close($link);
			html_error_quit("�Բ�����Ҫ�鿴�ĸ����ļ�������");
			exit();
		}
		$pc = array(
				"UID" => $rows[uid],
				"NLIM" => $rows[nodelimit],
				"DLIM" => $rows[dirlimit],
				"USER" => $rows[username],
				"NAME" => $rows[corpusname]
				);
		mysql_free_result($result);
		pc_html_init("gb2312",stripslashes($pc["NAME"]));
		
		$act = $_GET["act"]?$_GET["act"]:$_POST["act"];
		
		if($act == "cut" || $act == "copy")
		{
			$target = $_POST["target"];
			if($target < 0 || $target > 4 )
				$target = 2;//�����������������˽����
			if($target == 3)
			{
				$query = "SELECT `nid` FROM nodes WHERE `access` = '3' AND  `uid` = '".$pc["UID"]."' AND `pid` = '0' AND `type` = '1' LIMIT 0 , 1 ; ";
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
				$query = "SELECT `source`,`hostname`,`created`,`comment`,`commentcount`,`subject`,`body`,`visitcount` FROM nodes WHERE `uid` = '".$pc["UID"]."' AND `type` = 0 AND ( `nid` = '0' ";
			
			$j = 0;
			for($i = 1 ;$i < $pc["NLIM"]+1 ; $i ++)
			{
				if($_POST["art".$i])
				{
					$query .= " OR `nid` = '".$_POST["art".$i]."' ";
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
					mysql_query($query,$link);
			}
			else
			{
				$result = mysql_query($query,$link);
				$num_rows = mysql_num_rows($result);
				if(pc_used_space($link,$pc["UID"],$target)+$num_rows > $pc["NLIM"])
				{
					html_error_quit("Ŀ�������������������� (".$pc["NLIM"]." ƪ)!");
					exit();
				}
				for($i = 0;$i < $num_rows ; $i ++)
				{
					/*	Ŀǰ�������µ�ʱ�����۲�ͬ������	*/
					$rows = mysql_fetch_array($result);
					$query = "INSERT INTO `nodes` ( `pid` , `tid` , `type` , `source` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` )  ".
						" VALUES ('0','0' , '0', '".$rows[source]."', '".$rows[hostname]."','".date("YmdHis")."' , '".$rows[created]."', '".$pc["UID"]."', '".$rows[comment]."', '".$rows[commentcount]."', '".$rows[subject]."', '".$rows[body]."', '".$target."', '".$rows[visitcount]."');";
					mysql_query($query,$link);
				}
				
			}
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php
		}
		elseif($act == "post")
		{
			$tag =$_GET["tag"];
			if($tag < 0 || $tag > 4 )
				$tag = 2;//���������������˽��������
			if($tag == 3)
			{
				
				$pid = $_GET["pid"];
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
				$query = "INSERT INTO `nodes` (  `pid` , `tid` , `type` , `source` , `emote` , `hostname` , `changed` , `created` , `uid` , `comment` , `commentcount` , `subject` , `body` , `access` , `visitcount` ) ".
					"VALUES ( '".$pid."', '".$_POST["tid"]."' , '0', '', '".$emote."' ,  '".$_SERVER["REMOTE_ADDR"]."','".date("YmdHis")."' , '".date("YmdHis")."', '".$pc["UID"]."', '".$c."', '0', '".addslashes($_POST["subject"])."', '".addslashes($_POST["body"])."', '".$tag."', '0');";
				mysql_query($query,$link);
?>
<script language="javascript">
window.location.href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=<?php echo $tag; ?>&tid=<?php echo $_POST["tid"]; ?>";
</script>
<?php
			}
			else
			{
?>
<form action="pcmanage.php?act=post&<?php echo "tag=".$tag."&pid=".$pid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" border="1" width="100%">
<tr>
	<td>��������</td>
</tr>
<tr>
	<td>����
	<input type="text" size="100" name="subject">
	</td>
</tr>
<tr>
	<td>
	����
	<input type="radio" name="comment" value="0" checked>����
	<input type="radio" name="comment" value="1">������
	</td>
</tr>
<tr>
	<td>
	�ļ�
	<select name="tid">
<?php
		$blogs = pc_blog_menu($link,$pc["UID"],$tag);
		for($i = 0 ; $i < count($blogs) ; $i ++)
			echo "<option value=\"".$blogs[$i]["TID"]."\">".stripslashes($blogs[$i]["NAME"])."</option>";
?>
	</select>
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
	<td><textarea name="body" cols="100" rows="20" id="body"></textarea></td>
</tr>
<tr>
	<td>
		<input type="submit" value="������">
		<input type="button" value="������ҳ" onclick="history.go(-1)">
	</td>
</tr>
</table>
</form>
<?php				
			}
		}
		elseif($act == "edit")
		{
			$nid = $_GET["nid"];
			$query = "SELECT `subject` , `body` ,`comment`,`type`,`tid`,`access` FROM nodes WHERE `nid` = '".$nid."' AND `uid` = '".$pc["UID"]."' LIMIT 0 , 1 ; ";
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
				$emote = (int)($_POST["emote"]);
				$query = "UPDATE nodes SET `subject` = '".addslashes($_POST["subject"])."' , `body` = '".addslashes($_POST["body"])."' , `changed` = '".date("YmdHis")."' , `comment` = '".$c."' , `tid` = '".$_POST["tid"]."' , `emote` = '".$emote."' WHERE `nid` = '".$nid."' ; ";
				mysql_query($query,$link);
?>
<p align="center">
<a href="javascript:history.go(-2);">�����ɹ�,�������</a>
</p>
<?php
			}
			else
			{
?>			
<form action="pcmanage.php?act=edit&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" border="1" width="100%">
<?php
		if($rows[type]==1)
		{
?>
<tr>
	<td>�޸�Ŀ¼</td>
</tr>
<tr>
	<td>
	����
	<input type="text" size="100" name="subject" value="<?php echo stripslashes($rows[subject]); ?>">
	</td>
</tr>
<tr>
	<td>
		<input type="submit" value="�޸�Ŀ¼">
		<input type="button" value="������ҳ" onclick="history.go(-1)">
	</td>
</tr>
<?php
		}
		else
		{
?>
<tr>
	<td>�޸�����</td>
</tr>
<tr>
	<td>����
	<input type="text" size="100" name="subject" value="<?php echo stripslashes($rows[subject]); ?>">
	</td>
</tr>
<tr>
	<td>
	����
	<input type="radio" name="comment" value="0" <?php if($rows[comment]!=0) echo "checked"; ?>>����
	<input type="radio" name="comment" value="1" <?php if($rows[comment]==0) echo "checked"; ?>>������
	</td>
</tr>
<tr>
	<td>
	�ļ�
	<select name="tid">
<?php
		$blogs = pc_blog_menu($link,$pc["UID"],$rows[access]);
		for($i = 0 ; $i < count($blogs) ; $i ++)
		{
			if($blogs[$i]["TID"] == $rows[tid])
				echo "<option value=\"".$blogs[$i]["TID"]."\" selected>".stripslashes($blogs[$i]["NAME"])."</option>";
			else
				echo "<option value=\"".$blogs[$i]["TID"]."\" >".stripslashes($blogs[$i]["NAME"])."</option>";
		}
?>
	</select>
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
	<td><textarea name="body" cols="100" rows="20" id="body"><?php echo stripslashes($rows[body]." "); ?></textarea></td>
</tr>
<tr>
	<td>
		<input type="submit" value="�޸ı���">
		<input type="button" value="������ҳ" onclick="history.go(-1)">
	</td>
</tr>
<?php
		}
?>
</table>
</form>
<?php				
			}
		}
		elseif($act == "del")
		{
			$nid = $_GET["nid"];	
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
				}
			}
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
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php			
		}
		elseif($act == "tedit")
		{
			$query = "SELECT * FROM topics WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$_GET["tid"]."' ; ";	
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
				$query = "UPDATE topics SET `topicname` = '".$_POST["topicname"]."' WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$rows[tid]."' ; ";
				mysql_query($query,$link);
				
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
<form action="pcmanage.php?act=tedit&tid=<?php echo $rows[tid]; ?>" method="post" onsubmit="if(this.topicname.value==''){alert('�������ļ�����!');return false;}">
<table cellspacing="0" cellpadding="5" border="1" width="100%">
<tr>
	<td>�޸��ļ�</td>
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
	<td>
	�ļ���
	<input type="text" name="topicname" value="<?php echo $rows[topicname]; ?>">
	</td>
</tr>
<tr>
	<td>
	<input type="submit" value="�޸��ļ�">
	<input type="button" value="������ҳ" onclick="history.go(-1)">
	</td>
</tr>
</table>
</form>
<?php
			}
		}
		elseif($act == "tdel")
		{
			$query = "SELECT `nid` FROM nodes WHERE `tid` = '".$_GET["tid"]."' ; ";
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
				$query = "DELETE FROM topics WHERE `uid` = '".$pc["UID"]."' AND `tid` = '".$_GET["tid"]."' ; ";
				mysql_query($query,$link);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php				
			}
		}
		elseif($act == "tadd" && $_POST["topicname"])
		{
			$access = $_POST["access"];
			if($access < 0 || $access > 2)
				$access = 0;
			$query = "INSERT INTO `topics` (`uid` , `access` , `topicname` , `sequen` ) ".
					"VALUES ( '".$pc["UID"]."', '".$access."', '".$_POST["topicname"]."', '0');";
			mysql_query($query,$link);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php
		}
		elseif($act == "sedit" && $_POST["pcname"])
		{
			$query = "UPDATE `users` SET `corpusname` = '".addslashes($_POST["pcname"])."',`description` = '".addslashes($_POST["pcdesc"])."',`theme` = '".addslashes($_POST["pcthem"])."' WHERE `uid` = '".$pc["UID"]."' LIMIT 1 ;";	
			mysql_query($query,$link);
?>
<p align="center">
<a href="javascript:history.go(-1);">�����ɹ�,�������</a>
</p>
<?php
		}
		
		html_normal_quit();
	}
	
?>