<?php
/*
** @id:windinsin nov 29,2003
*/
$needlogin=0;
require_once("pcfuncs.php");

function getNewBlogs($link,$pno=1)
{
	global $pcconfig;
	if($pno < 1)
		$pno = 1;
		
	$newBlogs = array();
	$newBlogs[channel] = array(
			"siteaddr" => "http://".$pcconfig["SITE"],
			"title" => $pcconfig["BBSNAME"]."��ʱBlog�����б�" ,
			"pcaddr" => "http://".$pcconfig["SITE"],
			"desc" => $pcconfig["BBSNAME"]."����".$pcconfig["NEWS"]."ƪ����",
			"email" => $pcconfig["BBSNAME"],
			"publisher" => $pcconfig["BBSNAME"],
			"creator" => $pcconfig["BBSNAME"],
			"rights" => $pcconfig["BBSNAME"],
			"date" => date("Y-m-d"),
			"updatePeriod" => "��ʱ����",
			"updateFrequency" => "���µ�".$pcconfig["NEWS"]."ƪBlog����",
			"updateBase" => date("Y-m-d H:i:s"),
			
			);
	
	$query = "SELECT * FROM nodes WHERE `access` = 0 ORDER BY `nid` DESC LIMIT ".(($pno - 1) * $pcconfig["NEWS"])." , ".$pcconfig["NEWS"]." ; ";
	$result = mysql_query($query,$link);
	$j = 0;
	$bloguser = array();
	while($rows=mysql_fetch_array($result))
	{
		if(!$bloguser[$rows[uid]])
			$bloguser[$rows[uid]] = pc_load_infor($link,FALSE,$rows[uid]);
			
		$body = "<br>\n".
			"����: ".$bloguser[$rows[uid]]["NAME"]."<br>\n".
			"����: ".$bloguser[$rows[uid]]["THEM"]."<br>\n".
			"����: ".$bloguser[$rows[uid]]["USER"]."<br>\n".
			"����վ: ".$pcconfig["BBSNAME"]."<br>\n".
			"ʱ��: ".time_format($rows[created])."<br>\n".
			"<hr size=1>\n".
			html_format($rows[body],TRUE,$rows[htmltag]).
			"<hr size=1>\n".
			"(<a href=\"http://".$pcconfig["SITE"]."/pc/pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&tid=".$rows[tid]."&s=all\">���ȫ��</a>\n".
			"<a href=\"http://".$pcconfig["SITE"]."/pc/pccom.php?act=pst&nid=".$rows[nid]."\">��������</a>)<br>\n".
			"<a href=\"http://".$pcconfig["SITE"]."/pc/rss.php?userid=".$bloguser[$rows[uid]]["USER"]."\"><img src=\"http://".$pcconfig["SITE"]."/pc/images/xml.gif\" border=\"0\" align=\"absmiddle\" alt=\"XML\">Blog��ַ��http://".$pcconfig["SITE"]."/pc/rss.php?userid=".$bloguser[$rows[uid]]["USER"]."</a>";
		$newBlogs[useretems][$j] = array(
					"addr" => "http://".$pcconfig["SITE"]."/pc/pccon.php?id=".$rows[uid]."&amp;nid=".$rows[nid]."&amp;tid=".$rows[tid],
					"title" => htmlspecialchars(stripslashes($rows[subject])),
					"desc" => $body,
					"tid" => $rows[tid],
					"nid" => $rows[nid],
					"publisher" => $pcconfig["BBSNAME"],
					"creator" => $bloguser[$rows[uid]]["USER"],
					"pc" => $bloguser[$rows[uid]],
					"created" => time_format($rows[created]),
					"rights" => $bloguser[$rows[uid]]["USER"].".bbs@".$pcconfig["SITE"]
					);
		$j ++;
	}
	mysql_free_result($result);
	
	return $newBlogs;
}

?>