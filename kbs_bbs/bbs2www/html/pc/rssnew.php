<?php
	/*
	** @id:windinsin nov 29,2003
	*/
	require("rsstool.php");
	
	$link = pc_db_connect();
	$rss = array();
	$rss[channel] = array(
			"siteaddr" => "http://".$pcconfig["SITE"],
			"title" => BBS_FULL_NAME."��ʱBlog�����б�" ,
			"pcaddr" => "http://".$pcconfig["SITE"],
			"desc" => BBS_FULL_NAME."����".$pcconfig["NEWS"]."ƪ����",
			"email" => BBS_FULL_NAME,
			"publisher" => BBS_FULL_NAME,
			"creator" => BBS_FULL_NAME,
			"rights" => BBS_FULL_NAME,
			"date" => date("Y-m-d"),
			"updatePeriod" => "��ʱ����",
			"updateFrequency" => "���µ�".$pcconfig["NEWS"]."ƪBlog����",
			"updateBase" => date("Y-m-d H:i:s"),
			
			);
	
	$query = "SELECT * FROM nodes WHERE `access` = 0 ORDER BY `nid` DESC LIMIT 0 , ".$pcconfig["NEWS"]." ; ";
	$result = mysql_query($query,$link);
	$j = 0;
	$bloguser = array();
	while($rows=mysql_fetch_array($result))
	{
		if(!$bloguser[$rows[uid]])
		{
			$query = "SELECT `corpusname`,`theme`,`username` FROM users WHERE `uid` = '".$rows[uid]."' LIMIT 0 , 1 ;";
			$result0 = mysql_query($query);
			$rows0 = mysql_fetch_array($result0);
			$bloguser[$rows[uid]] = array(
						"USER" => $rows0[username],
						"NAME" => $rows0[corpusname],
						"THEM" => $rows0[theme]
						);
		}
		
		$body = "<br>\n".
			"����: ".html_format($bloguser[$rows[uid]]["NAME"])."<br>\n".
			"����: ".html_format($bloguser[$rows[uid]]["THEM"])."<br>\n".
			"����: ".$bloguser[$rows[uid]]["USER"]."<br>\n".
			"����վ: ".BBS_FULL_NAME."<br>\n".
			"ʱ��: ".time_format($rows[created])."<br>\n".
			"<hr size=1>\n".
			html_format($rows[body],TRUE,$rows[htmltag]).
			"<hr size=1>\n".
			"(<a href=\"http://".$pcconfig["SITE"]."/pc/pccon.php?id=".$rows[uid]."&nid=".$rows[nid]."&tid=".$rows[tid]."&s=all\">���ȫ��</a>\n".
			"<a href=\"http://".$pcconfig["SITE"]."/pc/pccom.php?act=pst&nid=".$rows[nid]."\">��������</a>)<br>\n".
			"<a href=\"http://".$pcconfig["SITE"]."/pc/rss.php?userid=".$bloguser[$rows[uid]]["USER"]."\"><img src=\"http://".$pcconfig["SITE"]."/pc/images/xml.gif\" border=\"0\" align=\"absmiddle\" alt=\"XML\">Blog��ַ��http://".$pcconfig["SITE"]."/pc/rss.php?userid=".$bloguser[$rows[uid]]["USER"]."</a>";
		$rss[useretems][$j] = array(
					"addr" => "http://".$pcconfig["SITE"]."/pc/pccon.php?id=".$rows[uid]."&amp;nid=".$rows[nid]."&amp;tid=".$rows[tid],
					"title" => htmlspecialchars(stripslashes($rows[subject])),
					"desc" => $body,
					"publisher" => BBS_FULL_NAME,
					"creator" => $bloguser[$rows[uid]]["USER"],
					"rights" => $bloguser[$rows[uid]]["USER"].".bbs@".$pcconfig["SITE"]
					);
		$j ++;
	}
	mysql_free_result($result);
	
	pc_db_close($link);
	header("Content-Type: text/xml");
	header("Content-Disposition: inline;filename=userrss.xml");
	@pc_rss_user($rss);
?>