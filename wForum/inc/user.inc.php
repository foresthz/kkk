<?php
function showUserMailbox(){
	global $currentuser;
?>
<tr><td width=65% >
</td><td width=35% align=right>
<?php   
	bbs_getmailnum($currentuser["userid"],$total,$unread);
	if ($unread>0)  {
?>
<bgsound src="sound/msg.wav" border=0>
<img src="pic/msg_new_bar.gif" /> <a href="usermailbox.php?boxname=inbox">�ҵ��ռ���</a> (<a href="usermail.php?boxname=inbox&num=<?php echo $total-1;?>" target=_blank><font color="#FF0000"><?php  echo $unread; ?> ��</font></a>)
<?php   }
    else
  {
?>
<img src="pic/msg_no_new_bar.gif" /> <a href="usermailbox.php?boxname=inbox">�ҵ��ռ���</a> (<font color=gray>0 ��</font>)
<?php
  }
?>
</td></tr>
<?php
}

function showAllSecs(){
	GLOBAL $sectionCount;
	GLOBAL $_COOKIE;
	GLOBAL $_GET;

	for ($i=0;$i<$sectionCount;$i++){
		if ($_COOKIE['ShowSecBoards'.$i]=='Y') {
			showSecs($i,true);
		} else {
			showSecs($i,false);
		}
	}
	return false;
}

function showSecs($secNum=0,$isFold) {
	extract($GLOBALS);
	if ( ($secNum<0)  || ($secNum>=$sectionCount)) {
		foundErr("�����������");
		return false;
	}
?>
<table cellspacing=1 cellpadding=0 align=center width="97%" class=tableBorder1>
<TR><Th colSpan=2 height=25 align=left id=TableTitleLink>&nbsp;
<?php
	if ($isFold) {
?>
<a href="<?php echo $_SERVER['PHP_SELF'] ; ?>?sec=<?php echo $secNum; ?>&ShowBoards=N" title="�ر���̳�б�"><img src="pic/nofollow.gif" border=0></a><a href="section.php?sec=<?php echo $secNum ; ?>" title=���뱾������̳><?php echo $section_names[$secNum][0]; ?></a>
</th></tr>
<?php
	} else {
?>
<a href="<?php echo $_SERVER['PHP_SELF'] ; ?>?sec=<?php echo $secNum; ?>&ShowBoards=Y" title="չ����̳�б�"><img src="pic/plus.gif" border=0></a><a href="section.php?sec=<?php echo $secNum ; ?>" title=���뱾������̳><?php echo $section_names[$secNum][0]; ?></a>
<?php
	}
	$boards = bbs_getboards($section_nums[$secNum], 0, bbs_is_yank()?0:1);
	if ($boards == FALSE) {
?>
		<TR><TD colspan="2" class=tablebody1>&nbsp;���������ް���</td></tr>
<?php
	} else {
		$brd_name = $boards["NAME"]; // Ӣ����
		$brd_desc = $boards["DESC"]; // ��������
		$brd_class = $boards["CLASS"]; // �������
		$brd_bm = $boards["BM"]; // ����
		$brd_artcnt = $boards["ARTCNT"]; // ������
		$brd_unread = $boards["UNREAD"]; // δ�����
		$brd_zapped = $boards["ZAPPED"]; // �Ƿ� z ��
		$brd_flag = $boards["FLAG"]; //flag
		$brd_bid = $boards["BID"]; //flag
		$rows = sizeof($brd_name);
		$isFirst=false;
		for ($i = 0; $i < $rows; $i++)	{
			$isFirst=!$isFirst;
			if ($isFold){
?>
			<TR><TD align=middle width="100%" class=tablebody1>
		<table width="100%" cellspacing=0 cellpadding=0><TR><TD align=middle width=46 class=tablebody1>
<?php	
				if ( $brd_unread[$i] == 1) {
					echo "<img src=pic/forum_isnews.gif alt=��������>";
				} else  {
					echo "<img src=pic/forum_nonews.gif alt=��������>";
				}
?>
		</TD>
		<TD width=1 bgcolor=#7a437a>
		<TD vAlign=top width=* class=tablebody1>
		
		<TABLE cellSpacing=0 cellPadding=2 width=100% border=0>
		<tr><td class=tablebody1 width=*><a href="board.php?name=<?php echo $brd_name[$i]; ?> ">
		<font color=#000066><?php echo $brd_name[$i] ?> </font></a>
				</td>
		<td width=40 rowspan=2 align=center class=tablebody1></td><td width=200 rowspan=2 class=tablebody1><?php
				if ($brd_flag[$i] & BBS_BOARD_GROUP) {
		?>
				<B>����Ϊ����Ŀ¼��</B>
		<?php
				} else {
					if ($brd_artcnt[$i] <= 0) {
		?>
				<B>������������</B>
		<?php
					} else {
						$articles = bbs_getthreads($brd_name[$i], 0, 1,0 ); //$brd_artcnt[$i], 1, $default_dir_mode);
						if ($articles == FALSE) {
		?>
				<B>������������</B>
		<?php
						} else {
						//	$threads = bbs_getthreads($brd_name[$i],0,1); 
						//	$start = bbs_get_thread_article_num( $brd_name[$i], $articles[0]['GROUPID']); 
		?>
				���⣺<a href="disparticle.php?boardName=<?php echo $brd_name[$i]; ?>&ID=0"><?php echo htmlspecialchars($articles[0]['TITLE'],ENT_QUOTES); ?></a><BR>���ߣ�<a href="userinfo.php?id=<?php echo $articles[0]['OWNER']; ?>" target=_blank><?php echo $articles[0]['OWNER']; ?></a><BR>���ڣ�<?php echo strftime('%Y-%m-%d %H:%M:%S', intval($articles[0]['POSTTIME'])) ; ?>&nbsp;<a href="disparticle.php?boardName=<?php echo $brd_name[$i]; ?>&ID=0&start=<?php echo $start?>"><IMG border=0 src="pic/lastpost.gif" title="ת����<?php echo $articles[0]['TITLE']; ?>"></a>
	<?php
						}
					}
				}
	?>
</TD></TR><TR><TD width=*><FONT face=Arial><img src=pic/forum_readme.gif align=middle> <?php echo $brd_desc[$i] ?></FONT>
</TD></TR><TR><TD class=tablebody2 height=20 width=*>������<?php echo $brd_bm[$i]==''?'����':$brd_bm[$i] ; ?> </TD><td width=40 align=center class=tablebody2>&nbsp;</td><TD vAlign=middle class=tablebody2 width=200>
		<table width=100% border=0><tr><td width=25% vAlign=middle><img src=pic/forum_today.gif alt=������ align=absmiddle>&nbsp;<font color=#FF0000><?php echo bbs_get_today_article_num($brd_name[$i]) ?></font></td><td width=30% vAlign=middle><img src=pic/forum_topic.gif alt=���� border=0  align=absmiddle>&nbsp;<?php echo bbs_getthreadnum($brd_bid[$i]) ?></td><td width=45% vAlign=middle><img src=pic/forum_post.gif alt=���� border=0 align=absmiddle>&nbsp;<?php echo $brd_artcnt[$i]; ?></td></tr>
		</table></TD></TR></TBODY></TABLE></td></tr></table></td></tr>
<?php
			} else {
				if ($isFirst) {
					echo "<tr>";
				}
?>
<td class=tablebody1 width="50%"><TABLE cellSpacing=2 cellPadding=2 width=100% border=0><tr><td width="100%" title="<?php echo $brd_desc[$i] ; ?>" colspan=2><a href="board.php?name=<?php echo $brd_name[$i]; ?>"><font color=#000066><?php echo $brd_name[$i] ; ?></font></a></td></tr><tr>
<?php
				if ($brd_flag[$i] & BBS_BOARD_GROUP) {
?>
<td> <b>����Ϊ����Ŀ¼��</b></td>
<?php
				} else {
?>
<td width="50%">���գ�<font color=#FF0000><?php echo bbs_get_today_article_num($brd_name[$i])?></font></td><td width="50%">������<?php echo $brd_artcnt[$i] ; ?></td>
<?php
				}
?>
</tr></table></td>
<?php
				if (!$isFirst) {
					echo "</tr>";
				}
			}
		}
		if ($isFirst) {
?>
<td class=tablebody1 width="50%"></td></tr>
<?php
		}
	}

?>
</table><br>
<?php
}

function showAnnounce(){
	global $AnnounceBoard;
	global $SiteName;
?>
<tr>
<td align=center width=100% valign=middle colspan=2>
<SCRIPT LANGUAGE='JavaScript' SRC='inc/fader.js' TYPE='text/javascript'></script>
<SCRIPT LANGUAGE='JavaScript' TYPE='text/javascript'>
prefix="";
arNews = [<?php 
		$brdarr = array();
		$brdnum = bbs_getboard($AnnounceBoard, $brdarr);
		if ( ($brdnum==0) || ($brdarr["FLAG"] & BBS_BOARD_GROUP) ) {
			echo '"��ǰû�й���","",';
		} else {
			$total = bbs_getThreadNum($brdnum);
			if ($total <= 0) {
				echo '"��ǰû�й���","",';
			} else {
				$articles = bbs_getthreads($brdarr['NAME'], 0, ANNOUNCENUMBER,1); 
				if ($articles == FALSE) {
					echo '"��ǰû�й���2","",';
				} else {
					$num=count($articles);
					for ($i=0;$i<$num;$i++) {
					echo '"<b><a href=\"disparticle.php?boardName='.$brdarr['NAME'].'&ID='.($i).'\">' .htmlspecialchars($articles[$i]['TITLE'],ENT_QUOTES) . '</a></b> ('.strftime('%Y-%m-%d %H:%M:%S', intval($articles[$i]['POSTTIME'])).')","",';
					}
				}
			}
		}
?>"��ӭ����<?php echo $SiteName; ?>",""
];
</SCRIPT>
<div id="elFader" style="position:relative;visibility:hidden; height:16" ></div>
</td>
</tr>
<?php
}

function FastLogin()
{
extract($GLOBALS);
?>
<table cellspacing=1 cellpadding=3 align=center class=tableBorder1>
<form action="logon.php" method=post>
<input type="hidden" name="action" value="doLogon">
<tr>
<th align=left id=tabletitlelink height=25 style="font-weight:normal">
<b>-=> ���ٵ�¼���</b>
[<a href=register.php>ע���û�</a>]��[<a href=lostpass.php style="CURSOR: help">��������</a>]
</th>
</tr>
<tr>
<td class=tablebody1 height=40 width="100%">
&nbsp;�û�����<input maxLength=16 name=id size=12>�������룺<input maxLength=20 name=password size=12 type=password>����<select name=CookieDate><option selected value=0>������</option><option value=1>����һ��</option><option value=2>����һ��</option><option value=3>����һ��</option></select><input type=hidden name=comeurl value="<?php echo $_SERVER['PHP_SELF']; ?>"><input type=submit name=submit value="�� ½">
</td>
</tr>
</form>
</table><br>
<?php 
} 

function usersysinfo($info){
   if (strpos($info,';')!==false)  {
	  $usersys=explode(';',$info);
	  if (count($usersys)>=2)  {
		  $usersys[1]=str_replace("MSIE","Internet Explorer",$usersys[1]);
		  $usersys[2]=str_replace(")","",$usersys[2]);
		  $usersys[2]=str_replace("NT 5.1","XP",$usersys[2]);
		  $usersys[2]=str_replace("NT 5.0","2000",$usersys[2]);
		  $usersys[2]=str_replace("9x","Me",$usersys[2]);
		  $usersys[1]="� �� ����".trim($usersys[1]);
		  $usersys[2]="����ϵͳ��".trim($usersys[2]);
		  $function_ret=$usersys[1].'��'.$usersys[2];
	  }  else  {
		  	  $function_ret='� �� ����δ֪������ϵͳ��δ֪';
	  }
  }  else {
    if ($getinfo==1)  {
      $function_ret="δ֪��δ֪";
    } 
  } 
  return $function_ret;
} 

function showUserInfo(){
?>
<table cellpadding=5 cellspacing=1 class=tableborder1 align=center style="word-break:break-all;">
<TR><Th align=left colSpan=2 height=25>-=> �û�������Ϣ</Th></TR>
<TR><TD vAlign=top class=tablebody1 height=25 width=100% >
<?php
$userip = $_SERVER["HTTP_X_FORWARDED_FOR"];
$userip2 = $_SERVER["REMOTE_ADDR"];
if  ($userip=='') {
	echo '������ʵ�ɣ� �ǣ�'. $userip2. '��';
} else {
	echo '������ʵ�ɣ� �ǣ�'. $userip .'��';
}
echo usersysinfo($_SERVER["HTTP_USER_AGENT"]);

?>
</TD></TR></table><br>
<?php

}

function showOnlineUsers(){
?>
<table cellpadding=5 cellspacing=1 class=tableborder1 align=center style="word-break:break-all;">
<TR><Th colSpan=2 align=left id=tabletitlelink height=25 style="font-weight:normal"><b>-=> ��̳����ͳ��</b>&nbsp;[<a href=showOnlineUsers.php?action=show>��ʾ��ϸ�б�</a>] [<a href=boardstat.php?reaction=online>�鿴�����û�λ��</a>]</Th></TR>
<TR><TD width=100% vAlign=top class=tablebody1>  Ŀǰ��̳���ܹ��� <b><?php echo bbs_getonlinenumber() ; ?></b> �����ߣ�����ע���û� <b><?php echo bbs_getonlineusernumber(); ?></b> �ˣ��ÿ� <b><?php echo bbs_getwwwguestnumber() ; ?></b> �ˡ�<br>
��ʷ������߼�¼�� <b><?php echo  $Maxonline ?></b> ��ͬʱ����
</td></tr>
</table><br>
<?php
}

function showSample(){
?>
<table cellspacing=1 cellpadding=3 width="97%" border=0 align=center>
<tr><td align=center><img src="pic/forum_nonews.gif" align="absmiddle">&nbsp;û���µ�����&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="pic/forum_isnews.gif" align="absmiddle">&nbsp;���µ�����&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="pic/forum_lock.gif" align="absmiddle">&nbsp;����������̳</td></tr>
</table><br>
<?php
}

function showMailSampleIcon(){
?>
<table cellspacing=1 cellpadding=3 width="97%" border=0 align=center>
<tr><td align=center><img src="pic/m_news.gif" align="absmiddle">&nbsp;δ���ʼ�&nbsp<img src="pic/m_olds.gif" align="absmiddle">&nbsp;�Ѷ��ʼ�&nbsp<img src="pic/m_replys.gif" align="absmiddle">&nbsp;�ѻظ��ʼ�&nbsp;<img src="pic/m_newlocks.gif" align="absmiddle">&nbsp;������δ���ʼ�&nbsp;<img src="pic/m_oldlocks.gif" align="absmiddle">&nbsp;�������Ѷ��ʼ�&nbsp;<img src="pic/m_lockreplys.gif" align="absmiddle">&nbsp;�������ѻظ��ʼ�</td></tr>
</table><br>
<?php
}


?>
