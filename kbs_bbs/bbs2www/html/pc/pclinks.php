<?php
	/*
	** @id:windinsn dec 3,2003
	*/
	require("pcfuncs.php");
	function pc_edit_link($link,$favlinks,$uid)
	{
		$links = "";
		foreach($favlinks as $favlink)
		{
			if($links == "")
				$links .= base64_encode($favlink["LINK"])."#".base64_encode($favlink["URL"])."#".(int)($favlink["IMAGE"]);
			else
				$links .= "|".base64_encode($favlink["LINK"])."#".base64_encode($favlink["URL"])."#".(int)($favlink["IMAGE"]);
		}
			
		$query = "UPDATE users SET `createtime` = `createtime` , `modifytime` = '".date("YmdHis")."' , `links` = '".addslashes($links)."' WHERE `uid` = '".$uid."' ";
		mysql_query($query,$link);
	}
	
	if ($loginok != 1)
		html_nologin();
	elseif(!strcmp($currentuser["userid"],"guest"))
	{
		html_init("gb2312");
		html_error_quit("guest û��Blog!");
		exit();
	}
	else
	{
		$link = pc_db_connect();
		$pc = pc_load_infor($link,$currentuser["userid"]);
		if(!$pc || !pc_is_admin($currentuser,$pc))
		{
			pc_db_close($link);
			html_error_quit("�Բ�����Ҫ�鿴��Blog������");
			exit();
		}
		$favlinks = $pc["LINKS"];
		
		if($_GET["act"] == "edit")
		{
			$favlinks = array();
			$favlinksnum = count($pc["LINKS"]);
			for($i = 0;$i < $favlinksnum ; $i ++ )
			{
				if(!$_POST["link".$i] || !$_POST["url".$i]) continue;
				$favlinks[$i] = array("LINK" => $_POST["link".$i] , "URL" => $_POST["url".$i] , "IMAGE" => (int)($_POST["image".$i]));
			}
			if($_POST["link".$favlinksnum] && $_POST["url".$favlinksnum])
				$favlinks[$favlinksnum] =array("LINK" => $_POST["link".$favlinksnum] , "URL" => $_POST["url".$favlinksnum] , "IMAGE" => (int)($_POST["image".$favlinksnum]));
			pc_edit_link($link,$favlinks,$pc["UID"]);
		}
		if($_GET["act"] == "del" && $_GET["linkid"])
		{
			$favlinks = array();
			for($i = 0;$i < count($pc["LINKS"]);$i ++)
			{
				if($i != $_GET["linkid"] - 1)
				{
					$isImage = $pc["LINKS"][$i]["IMAGE"]?1:0;
					$favlinks[] = array("LINK" => $pc["LINKS"][$i]["LINK"] ,"URL" => $pc["LINKS"][$i]["URL"] , "IMAGE" => $isImage );
				}
			}
			pc_edit_link($link,$favlinks,$pc["UID"]);
		}
		$favlinksnum = count($favlinks) + 1;
		$favlinks[] = array("URL" => NULL,"LINK" => NULL);
		
		pc_html_init("gb2312",$pc["NAME"]);
?>
<br><br><p align=center class=f2>�������ӹ���</p>
<hr size=1>
<center>
<form action="pclinks.php?act=edit" method="post">
<table cellspacing=0 cellpadding=5 width=98% border=0 class=t1>
	<tr>
		<td class=t2 width=30>���</td>
		<td class=t2 width=120>����</td>
		<td class=t2>����</td>
		<td class=t2 width=30>ͼƬ</td>
		<td class=t2 width=80>ɾ��</td>
	</tr>
<?php
	
	for($i = 0 ; $i < $favlinksnum ; $i ++)
	{
		echo "<tr>\n<td class=t3><strong>".($i + 1)."</strong></td>".
			"<td class=t8><input size=30 name='link".$i."' value='".$favlinks[$i]["LINK"]."' class=f1></td>\n".
			"<td class=t5>http://<input size=50 name='url".$i."' value='".$favlinks[$i]["URL"]."' class=f1></td>\n".
			"<td class=t3><input type=checkbox name='image".$i."' value=1 ";
		if($favlinks[$i]["IMAGE"]) echo " checked ";
		echo "></td>\n";
		if( $i != $favlinksnum - 1 )
			echo "<td class=t4><a href='pclinks.php?act=del&linkid=".($i+1)."'>ɾ��</a>\n<a href='http://".$favlinks[$i]["URL"]."'>����</a></td>";
		else
			echo "<td class=t4>-</td>";
		echo "</tr>\n";
	}
?>
<tr>
	<td class=t4 colspan=5><input type=submit value="�޸�" class=b1></a>
</tr>
</table>
</form>
˵������ΪͼƬ���ӣ����ڡ����ơ�һ��������ͼƬ��URL��ַ��
</center>
<hr size=1>
<p class=f1 align=center>
[<a href="index.php?id=<?php echo $pc["USER"]; ?>">Blog��ҳ</a>]
[<a href="pcdoc.php?userid=<?php echo $pc["USER"]; ?>&tag=7">Blog�����趨</a>]
[<a href="javascript:history.go(-1)">���ٷ���</a>]
</p>
<?php
		pc_db_close($link);
		html_normal_quit();
	}
?>