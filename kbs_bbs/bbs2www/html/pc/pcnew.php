<?php
	/*
	** @id:windinsin nov 29,2003
	*/
	require("pcstat.php");

	function display_navigation_bar($type)
	{
?>
<p align=center class=f1>
<?php pc_main_navigation_bar(); ?>
</p>
<?php
	}

	function display_page_tools($pno,$etemno,$type)
	{
		global $pcconfig;
?>
<p align=center class=f1>
<?php
		$loadtype = ($type=="n")?"":"&t=c";
		if($pno > 1)
			echo "[<a href='pcnew.php?pno=".($pno - 1).$loadtype."'>前".$pcconfig["NEWS"]."条记录</a>]\n";	
		else
			echo "[前".$pcconfig["NEWS"]."条记录]\n";
		if($etemno == $pcconfig["NEWS"])
			echo "[<a href='pcnew.php?pno=".($pno + 1).$loadtype."'>后".$pcconfig["NEWS"]."条记录</a>]\n";	
		else
			echo "[后".$pcconfig["NEWS"]."条记录]\n";
?>
</p>
<?php
	}
	
	//20min更新一次
	if(pc_update_cache_header())
		return;
	
	$pno = (int)($_GET["pno"]);
	$pno = ($pno < 1)?1:$pno;
	$type = ($_GET["t"]=="c")?"c":"n";
	$link = pc_db_connect();
	if($type=="n")
	{
		$newBlogs = getNewBlogs($link,$pno);
		$newNum = count($newBlogs[useretems]);
	}
	else
	{
		$newComments = getNewComments($link,$pno);
		$newNum = count($newComments);	
	}
	pc_db_close($link);
	
	
	pc_html_init("gb2312",$pcconfig["BBSNAME"]."Blog");
?>
<br><br>
<p align=center class=f2>
欢迎使用<?php echo $pcconfig["BBSNAME"]; ?>Blog系统
</p>
<?php display_navigation_bar($type); ?>
<hr size=1>
<center>
<p align=center class=f1><strong>
最近更新的<?php echo $pcconfig["NEWS"]; ?>篇<?php echo ($type=="n")?"文章":"评论"; ?>列表
</strong></p>
<?php display_page_tools($pno,$newNum,$type); ?>
<table cellspacing=0 cellpadding=5 width=98% border=0 class=t1>
<?php
	if($type == "n")
	{
?>
	<tr>
		<td class=t2 width=90>用户名</td>
		<td class=t2 width=180>Blog名称</td>
		<td class=t2 width=80>Blog主题</td>
		<td class=t2 width=60>访问量</td>
		<td class=t2 width=60>文章数</td>
		<td class=t2>文章主题</td>
		<td class=t2 width=130>更新时间</td>
	</tr>
<?php
		for($i=0;$i < $newNum;$i++)
		{
			echo "<tr>\n<td class=t4><a href='/bbsqry.php?userid=".$newBlogs[useretems][$i][pc][USER]."'>".$newBlogs[useretems][$i][pc][USER]."</a></td>\n".
				"<td class=t3><span title=\"".$newBlogs[useretems][$i][pc][DESC]."\"><a href=\"index.php?id=".$newBlogs[useretems][$i][pc][USER]."\">".$newBlogs[useretems][$i][pc][NAME]."</a>&nbsp;</span></td>\n".
				"<td class=t4><span title=\"点击查看该分类的其它Blog信息\"><a href=\"pcsec.php?sec=".html_format($newBlogs[useretems][$i][pc][THEM][0])."\">".
				$pcconfig["SECTION"][$newBlogs[useretems][$i][pc][THEM][0]]."</a></span></td>\n".
				"<td class=t3>".$newBlogs[useretems][$i][pc][VISIT]."</td>\n".
				"<td class=t4>".$newBlogs[useretems][$i][pc][NODES]."</td>\n".
				"<td class=t8><a href='pccon.php?id=".$newBlogs[useretems][$i][pc][UID]."&tid=".$newBlogs[useretems][$i][tid]."&nid=".$newBlogs[useretems][$i][nid]."&s=all'>".$newBlogs[useretems][$i][subject]."</a>&nbsp;</td>\n".
				"<td class=t4>".$newBlogs[useretems][$i][created]."</td>\n</tr>\n";
		}
	}
	else
	{
?>
	<tr>
		<td class=t2 width=90>发布者</td>
		<td class=t2>评论主题</td>
		<td class=t2 width=130>评论发布时间</td>
		<td class=t2 width=200>原文主题</td>
		<td class=t2 width=130>原文发布时间</td>
		<td class=t2 width=50>访</td>
		<td class=t2 width=50>评</td>
	</tr>
<?php
		for($i=0;$i < $newNum;$i++)
		{
			echo "<tr>\n<td class=t4><a href='/bbsqry.php?userid=".$newComments[$i][POSTER]."'>".$newComments[$i][POSTER]."</a></td>\n".
				"<td class=t8><a href=\"pcshowcom.php?cid=".$newComments[$i][CID]."\">".$newComments[$i][CSUBJECT]."</a>&nbsp;</td>\n".
				"<td class=t4>".$newComments[$i][CCREATED]."&nbsp;</td>\n".
				"<td class=t8><span title=\"".$newComments[$i][NSUBJECT]."\"><a href=\"pccon.php?id=".$newComments[$i][UID]."&nid=".$newComments[$i][NID]."&s=all\">".substr($newComments[$i][NSUBJECT],0,20)." \n ";
			if(strlen($newComments[$i][NSUBJECT]) > 20) echo " ...";
			echo "</a>&nbsp;</span></td>\n".
				"<td class=t4>".$newComments[$i][NCREATED]."</td>\n".
				"<td class=t3>".$newComments[$i][VISITCOUNT]."</td>\n".
				"<td class=t4>".$newComments[$i][COMMENTCOUNT]."</td>\n</tr>\n";
		}
	}	
?>
</table>
<?php display_page_tools($pno,$newNum,$type); ?>
<hr size=1>
<?php 
	display_navigation_bar($type); 
	if($type=="n")
	{
?>
<p align=center class=f1>
<a href="rssnew.php" target="_blank"><img src="images/xml.gif" border="0" align="absmiddle" alt="XML"></a>
</p>
<?php
	}
?>
</center>
<?php	
	html_normal_quit();
?>