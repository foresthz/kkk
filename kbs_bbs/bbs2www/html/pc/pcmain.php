<?php
require("pcfuncs.php");
$link = pc_db_connect();

function pcmain_blog_statistics_list()
{
	global $pcconfig;
	$query = "SELECT COUNT(*) FROM users;";
	$result = mysql_query($query);
	$rows = mysql_fetch_row($result);
	mysql_free_result($result);
	$totaluser = $rows[0];
	
	$query = "SELECT COUNT(*) FROM nodes WHERE type != 1;";
	$result = mysql_query($query);
	$rows = mysql_fetch_row($result);
	mysql_free_result($result);
	$totalnode = $rows[0];
	
	$query = "SELECT COUNT(*) FROM comments;";
	$result = mysql_query($query);
	$rows = mysql_fetch_row($result);
	mysql_free_result($result);
	$totalcomment = $rows[0];
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7>&gt;&gt; ��վBlogͳ�ƣ�</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t8 style="line-height=20px">
					ע���û���<font color="#FF0000"><strong><?php echo $totaluser; ?></strong></font> ��<br />
					Blog���⣺<font color="#FF0000"><strong><?php echo $totalnode; ?></strong></font> ƪ<br />
					Blog�ظ���<font color="#FF0000"><strong><?php echo $totalcomment; ?></strong></font> ƪ<br />
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php
}

function pcmain_blog_new_user()
{
	global $pcconfig,$link;
	$query = "SELECT username,corpusname,description FROM users ORDER BY createtime DESC LIMIT 0,10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7>&gt;&gt; ���������û���</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t8 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i<$num;$i++)
	{
		$rows = mysql_fetch_array($result);
		echo "<li><a href=\"index.php?id=".$rows[username]."\"><span title=\"".html_format($rows[description])."\">".html_format($rows[corpusname])."</span></a><br/>&nbsp;&lt;<a href=\"/bbsqry.php?userid=".$rows[username]."\">".$rows[username]."</a>&gt;</li>";	
	}
?>				
				</ul>
				<p align="right"><a href="pc.php?order=createtime&order1=DESC">&gt;&gt;����</a></p>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php
}

function pcmain_blog_top_ten()
{
	global $pcconfig,$link;
	$query = "SELECT username , corpusname , description FROM users ORDER BY visitcount DESC LIMIT 0,10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7>&gt;&gt; ��������ʮ��Blog��</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t8 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i<$num;$i++)
	{
		$rows = mysql_fetch_array($result);
		echo "<li><a href=\"index.php?id=".$rows[username]."\"><span title=\"".html_format($rows[description])."\">".html_format($rows[corpusname])."</span></a><br/>&nbsp;&lt;<a href=\"/bbsqry.php?userid=".$rows[username]."\">".$rows[username]."</a>&gt;</li>";	
	}
?>				
				</ul>
				<p align="right"><a href="pc.php?order=visitcount&order1=DESC">&gt;&gt;����</a></p>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php
}

function pcmain_blog_last_update()
{
	global $pcconfig,$link;
	$query = "SELECT username , corpusname , description FROM users WHERE createtime != modifytime ORDER BY modifytime DESC LIMIT 0,10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7>&gt;&gt; ������µ�ʮ��Blog��</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t8 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i<$num;$i++)
	{
		$rows = mysql_fetch_array($result);
		echo "<li><a href=\"index.php?id=".$rows[username]."\"><span title=\"".html_format($rows[description])."\">".html_format($rows[corpusname])."</span></a><br/>&nbsp;&lt;<a href=\"/bbsqry.php?userid=".$rows[username]."\">".$rows[username]."</a>&gt;</li>";	
	}
?>				
				</ul>
				<p align="right"><a href="pc.php?order=modifytime&order1=DESC">&gt;&gt;����</a></p>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php
}

function pcmain_blog_sections()
{
	global $pcconfig;
	$keys = array_keys($pcconfig["SECTION"]);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7>&gt;&gt; Blog���ࣺ</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t8 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i<count($keys);$i++)
	{
		echo "<li><a href=\"pcsec.php?sec=".$keys[$i]."\">".html_format($pcconfig["SECTION"][$keys[$i]])."</a></li>";	
	}
?>				
				</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php
}

function  pcmain_blog_recommend_nodes()
{
	global $pcconfig,$link;
	$query = "SELECT nid , subject , htmltag , body , uid FROM recommend ORDER BY rid DESC LIMIT 0 , 10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7> �Ƽ����⣺</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t5 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i < $num ;$i ++)
	{
		$rows = mysql_fetch_array($result);
		$pcinfor = pc_load_infor($link,"",$rows[uid]);
		echo "<li><a href=\"pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&s=all\">".html_format($rows[subject])."</a>\n".
			"[����<a href=\"/bbsqry.php?userid=".$pcinfor[USER]."\">".$pcinfor[USER]."</a>��Blog:<a href=\"index.php?id=".$pcinfor[USER]."\">".$pcinfor[NAME]."</a>]\n<br/>";
		if($rows[htmltag])
			$body = undo_html_format(strip_tags($rows[body]));
		else
			$body = $rows[body];
		echo html_format(substr($body,0,500)." \n");
		if(strlen($body)>500) echo " ... ...\n";
		echo "</li>\n";	
	}
?>				
				</ul>
				<p align="right"><a href="pcreclist.php">�����Ƽ�����</a></p>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php		
}

function  pcmain_blog_most_hot()
{
	global $pcconfig,$link;
	$query = "SELECT nid , subject , htmltag , body , uid FROM nodes WHERE access = 0 AND type = 0 AND recommend != 2 AND created > ".date("YmdHis",time()-604800)." AND commentcount != 0 ORDER BY commentcount DESC LIMIT 0 , 10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7> �����������Ż��⣺</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t5 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i < $num ;$i ++)
	{
		$rows = mysql_fetch_array($result);
		$pcinfor = pc_load_infor($link,"",$rows[uid]);
		echo "<li><a href=\"pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&s=all\">".html_format($rows[subject])."</a>\n".
			"[����<a href=\"/bbsqry.php?userid=".$pcinfor[USER]."\">".$pcinfor[USER]."</a>��Blog:<a href=\"index.php?id=".$pcinfor[USER]."\">".$pcinfor[NAME]."</a>]\n<br/>";
		if($rows[htmltag])
			$body = undo_html_format(strip_tags($rows[body]));
		else
			$body = $rows[body];
		echo html_format(substr($body,0,300)." \n");
		if(strlen($body)>300) echo " ... ...\n";
		echo "</li>\n";	
	}
?>				
				</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php		
}

function  pcmain_blog_most_trackback()
{
	global $pcconfig,$link;
	$query = "SELECT nid , subject , htmltag , body , uid FROM nodes WHERE access = 0 AND type = 0 AND recommend != 2 AND created > ".date("YmdHis",time()-2592000)." AND trackbackcount != 0 ORDER BY trackbackcount DESC LIMIT 0 , 10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7> ��ʮ�����������Ļ��⣺</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t5 style="line-height=20px">
				<ul>
<?php
	for($i = 0;$i < $num ;$i ++)
	{
		$rows = mysql_fetch_array($result);
		$pcinfor = pc_load_infor($link,"",$rows[uid]);
		echo "<li><a href=\"pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&s=all\">".html_format($rows[subject])."</a>\n".
			"[����<a href=\"/bbsqry.php?userid=".$pcinfor[USER]."\">".$pcinfor[USER]."</a>��Blog:<a href=\"index.php?id=".$pcinfor[USER]."\">".$pcinfor[NAME]."</a>]\n<br/>";
		if($rows[htmltag])
			$body = undo_html_format(strip_tags($rows[body]));
		else
			$body = $rows[body];
		echo html_format(substr($body,0,300)." \n");
		if(strlen($body)>300) echo " ... ...\n";
		echo "</li>\n";	
	}
?>				
				</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php		
}

function  pcmain_blog_most_view()
{
	global $pcconfig,$link;
	$query = "SELECT nid , subject , uid FROM nodes WHERE access = 0 AND type = 0 AND recommend != 2 AND created > ".date("YmdHis",time()-604800)." AND visitcount != 0 ORDER BY visitcount DESC LIMIT 0 , 10;";
	$result = mysql_query($query,$link);
	$num = mysql_num_rows($result);
?>
<table cellspacing=0 cellpadding=3 width=98% class=f1>
<tr>
	<td class=t7> �������Ķ����Ļ��⣺</td>
</tr>
<tr>
	<td>
		<table cellspacing=0 cellpadding=10 width=100% class=t1>
			<tr>
				<td class=t5 style="line-height=24px">
				<ul>
<?php
	for($i = 0;$i < $num ;$i ++)
	{
		$rows = mysql_fetch_array($result);
		$pcinfor = pc_load_infor($link,"",$rows[uid]);
		echo "<li><a href=\"pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&s=all\">".html_format($rows[subject])."</a>\n".
			"[����<a href=\"/bbsqry.php?userid=".$pcinfor[USER]."\">".$pcinfor[USER]."</a>��Blog:<a href=\"index.php?id=".$pcinfor[USER]."\">".$pcinfor[NAME]."</a>]\n<br/>";
		/*if($rows[htmltag])
			$body = undo_html_format(strip_tags($rows[body]));
		else
			$body = $rows[body];
		echo html_format(substr($body,0,600)." \n");
		if(strlen($body)>500) echo " ... ...\n";
		*/echo "</li>\n";	
	}
?>				
				</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php		
}

//20min����һ��
if(pc_update_cache_header())
	return;

pc_html_init("gb2312" , $pcconfig["BBSNAME"]."Blog");
?>
<table cellspacing=0 cellpadding=5 width=100% border=0 class=f1>
<tr>
	<td colspan="2" class=t2>
	<a class="t2" href="pc.php">�û��б�</a>
	<a class="t2" href="pcreclist.php">�Ƽ�����</a>
	<a class="t2" href="pcsec.php">����Ŀ¼</a>
	<a class="t2" href="pcnew.php">��������</a>
	<a class="t2" href="pcnew.php?t=c">��������</a>
	<a class="t2" href="pcsearch2.php">Blog����</a>
	<a class="t2" href="pcnsearch.php">��������</a>
	<a class="t2" href="/bbsdoc.php?board=<?php echo $pcconfig["BOARD"]; ?>">Blog��̳</a>
	</td>
</tr>
<tr>
	<td width="75%" class=t13 align="left" valign="top">
<?php
pcmain_blog_recommend_nodes();
pcmain_blog_most_hot();
pcmain_blog_most_view();
pcmain_blog_most_trackback();
?>
	</td>
	<td width="25%" align="left" valign="top">
<?php
pcmain_blog_statistics_list();
pcmain_blog_new_user();
pcmain_blog_top_ten();
pcmain_blog_last_update();
pcmain_blog_sections();
?>	
	</td>
</tr>
<tr>
	<td colspan=2 height=1 bgcolor=#999999> </td>
</tr>
<tr>
	<td colspan=2 class=f1>
	<center>��Ȩ���� &copy; <?php echo $pcconfig["BBSNAME"]; ?></center>
	</td>
</tr>
<?php
pc_db_close($link);
html_normal_quit();
?>