<?php
	/*
	** @id:windinsin nov 29,2003
	*/
	require("pcstat.php");

	function display_navigation_bar()
	{
?>
<p align=center class=f1>
[<a href="javascript:location=location">ˢ��</a>]
[<a href="pc.php">�û��б�</a>]
[<a href="pcsearch2.php">�ļ�����</a>]
[<a href="pcnsearch.php">��������</a>]
</p>
<?php
	}

	function display_page_tools($pno,$etemno)
	{
		global $pcconfig;
?>
<p align=center class=f1>
<?php
		if($pno > 1)
			echo "[<a href='pcnew.php?pno=".($pno - 1)."'>ǰ".$pcconfig["NEWS"]."����¼</a>]\n";	
		else
			echo "[ǰ".$pcconfig["NEWS"]."����¼]\n";
		if($etemno == $pcconfig["NEWS"])
			echo "[<a href='pcnew.php?pno=".($pno + 1)."'>��".$pcconfig["NEWS"]."����¼</a>]\n";	
		else
			echo "[��".$pcconfig["NEWS"]."����¼]\n";
?>
</p>
<?php
	}
	
	$pno = (int)($_GET["pno"]);
	$pno = ($pno < 1)?1:$pno;
	$link = pc_db_connect();
	$newBlogs = getNewBlogs($link,$pno);
	$newBlogsNum = count($newBlogs[useretems]);
	pc_db_close($link);
	
	
	pc_html_init("gb2312","�����ļ�");
?>
<br><br>
<p align=center class=f2>
��ӭʹ��<?php echo BBS_FULL_NAME; ?>Blogϵͳ
</p>
<?php display_navigation_bar(); ?>
<hr size=1>
<center>
<p align=center class=f1><strong>
������µ�<?php echo $pcconfig["NEWS"]; ?>ƪ�����б�
</strong></p>
<?php display_page_tools($pno,$newBlogsNum); ?>
<?php
	
?>
<table cellspacing=0 cellpadding=5 width=98% border=0 class=t1>
	<tr>
		<td class=t2 width=80>�û���</td>
		<td class=t2 width=160>Blog����</td>
		<td class=t2 width=80>Blog����</td>
		<td class=t2 width=40>������</td>
		<td class=t2 width=40>������</td>
		<td class=t2>��������</td>
		<td class=t2 width=120>����ʱ��</td>
	</tr>
	<?php
		for($i=0;$i < $newBlogsNum;$i++)
			echo "<tr>\n<td class=t4><a href='/bbsqry.php?userid=".$newBlogs[useretems][$i][pc][USER]."'>".$newBlogs[useretems][$i][pc][USER]."</a></td>\n".
				"<td class=t3><span title=\"".$newBlogs[useretems][$i][pc][DESC]."\"><a href=\"index.php?id=".$newBlogs[useretems][$i][pc][USER]."\">".$newBlogs[useretems][$i][pc][NAME]."</a>&nbsp;</span></td>\n".
				"<td class=t4>".$newBlogs[useretems][$i][pc][THEM]."&nbsp;</td>\n".
				"<td class=t3>".$newBlogs[useretems][$i][pc][VISIT]."</td>\n".
				"<td class=t4>".$newBlogs[useretems][$i][pc][NODES]."</td>\n".
				"<td class=t8><a href='pccon.php?id=".$newBlogs[useretems][$i][pc][UID]."&tid=".$newBlogs[useretems][$i][tid]."&nid=".$newBlogs[useretems][$i][nid]."&s=all'>".$newBlogs[useretems][$i][title]."</a>&nbsp;</td>\n".
				"<td class=t4>".$newBlogs[useretems][$i][created]."</td>\n</tr>\n";
	?>
</table>
<?php display_page_tools($pno,$newBlogsNum); ?>
<hr size=1>
<?php display_navigation_bar(); ?>
<p align=center class=f1>
<a href="rssnew.php" target="_blank"><img src="images/xml.gif" border="0" align="absmiddle" alt="XML"></a>
</p>
</center>
<?php	
	html_normal_quit();
?>