<?php
/*
** @id:windinsin nov 29,2003
*/
require_once("pcfuncs.php");

function getNewBlogs($link,$pno=1,$etemnum=0)
{
	global $pcconfig;
	if($pno < 1)
		$pno = 1;
	
	$etemnum = intval( $etemnum );
	if($etemnum <= 0 )
		$etemnum = $pcconfig["NEWS"];
	
	$newBlogs = array();
	$newBlogs[channel] = array(
			"siteaddr" => "http://".$pcconfig["SITE"],
			"title" => $pcconfig["BBSNAME"]."��ʱBlog�����б�" ,
			"pcaddr" => "http://".$pcconfig["SITE"],
			"desc" => $pcconfig["BBSNAME"]."����".$etemnum."ƪ����",
			"email" => $pcconfig["BBSNAME"],
			"publisher" => $pcconfig["BBSNAME"],
			"creator" => $pcconfig["BBSNAME"],
			"rights" => $pcconfig["BBSNAME"],
			"date" => date("Y-m-d"),
			"updatePeriod" => "��ʱ����",
			"updateFrequency" => "���µ�".$etemnum."ƪBlog����",
			"updateBase" => date("Y-m-d H:i:s"),
			
			);
	
	$query = "SELECT * FROM nodes WHERE `access` = 0 ORDER BY `nid` DESC LIMIT ".(($pno - 1) * $etemnum)." , ".$etemnum." ;";
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

function getNewComments($link,$pno=1,$etemnum=0)
{
	global $pcconfig;
	if($pno < 1)	$pno = 1;
	$etemnum = intval( $etemnum );
	if($etemnum <= 0 )
		$etemnum = $pcconfig["NEWS"];
	
	$newComments = array();
	$query = "SELECT cid , comments.uid , comments.subject , comments.created , comments.username , nodes.subject , nodes.created , visitcount , commentcount , nodes.nid FROM comments, nodes WHERE comments.nid = nodes.nid AND access = 0  AND comment = 1 ORDER BY cid DESC LIMIT ".(($pno - 1) * $etemnum)." , ".$etemnum." ;";
	$result = mysql_query($query,$link);
	for($i = 0; $i < mysql_num_rows($result) ; $i ++ )
	{
		$rows = mysql_fetch_array($result);
		$newComments[$i] = array(
					"CID" => $rows[cid],
					"UID" => $rows[uid],
					"CCREATED" => time_format($rows[3]),
					"NCREATED" => time_format($rows[created]),
					"VISITCOUNT" => $rows[visitcount],
					"COMMENTCOUNT" => $rows[commentcount],
					"CSUBJECT" => html_format($rows[2]),
					"NSUBJECT" => html_format($rows[subject]),
					"POSTER" => $rows[username],
					"NID" => $rows[nid]
					);	
	}
	mysql_free_result($result);
	return $newComments;
}


?>