<?php

function showUserMailBoxOrBR() { //������ͨ�û��Ϳ��Է��ʵ�ҳ��
	global $loginok;
	if ($loginok==1) {
		showUserMailBox();
	} else {
		echo "<br>";
	}
}

function showUserMailbox(){ //�������ֱ�ӵ��ñ��뱣֤ $loginok==1
	global $currentuser;
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<tr><td width=65% >
</td><td width=35% align=right>
<?php   
	bbs_getmailnum($currentuser["userid"],$total,$unread,0,0);
	if ($unread>0)  {
?>
<bgsound src="sound/newmail.wav" border=0>
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
</table>
<?php
}

/* $secNum == -1 means fav */
function setSecFoldCookie($secNum, $flag, $isShow) {
	if ($isShow) $cn = "ShowSecBoards";
	else $cn = "HideSecBoards";
	if (isset($_COOKIE[$cn])) {
		$ssb = $_COOKIE[$cn];
		settype($ssb, "integer");
	}
	else $ssb = 0;
	if ($flag) {
		$ssb = $ssb | (1 << ($secNum+1));
	} else {
		$ssb = $ssb & ~(1 << ($secNum+1));
	}
	setcookie($cn, $ssb ,time() + 604800);
	$_COOKIE[$cn] = $ssb;
	return 0;
}

function getSecFoldCookie($secNum, $isShow = true) {
	if ($isShow) $cn = "ShowSecBoards";
	else $cn = "HideSecBoards";
	if (isset($_COOKIE[$cn])) {
		$ssb = $_COOKIE[$cn];
		settype($ssb, "integer");
	} else {
		if (SECTION_DEF_CLOSE && !$isShow) $ssb = ~0;
		else $ssb = 0;
	}
	return (($ssb & (1 << ($secNum+1))) != 0);
}

function showSecsJS($secNum,$group,$isFold,$isFav,$isHide) {
	global $yank;
	global $section_nums;
?>
<script language="JavaScript">
<!--
	boards = new Array();
<?php
	if ($isFav) {
		$select = $secNum; //���������� :D
		echo "j_select = $select;\n";
	}

	if (!$isHide) {
		if (!$isFav) {
			if (!$isFold) $flag = $yank | 2;
			else $flag = $yank;
			$boards = bbs_getboards($section_nums[$secNum], $group, $flag);
		} else {
			$boards = bbs_fav_boards($select, 1);
			if ($boards == FALSE) {
	    		//foundErr("��ȡ���б�ʧ��");
			}
		}
		if ($boards !== FALSE) {
			$brd_name = $boards["NAME"]; // Ӣ����
			$brd_desc = $boards["DESC"]; // ��������
			$brd_class = $boards["CLASS"]; // �������
			$brd_bm = $boards["BM"]; // ����
			$brd_artcnt = $boards["ARTCNT"]; // ������
			$brd_unread = $boards["UNREAD"]; // δ�����
			$brd_zapped = $boards["ZAPPED"]; // �Ƿ� z ��
			if ($isFav) {
				$brd_position= $boards["POSITION"];//λ��
				$brd_npos= $boards["NPOS"];//λ��
			}
			$brd_flag = $boards["FLAG"]; //flag
			$brd_bid = $boards["BID"]; //flag
			$brd_currentusers = $boards["CURRENTUSERS"];
			$rows = sizeof($brd_name);
			$showed = 0;
			for ($i = 0; $i < $rows; $i++)	{
				if ($brd_name[$i]=='Registry')
					continue;
				if ($brd_name[$i]=='bbsnet')
					continue;
				if ($brd_name[$i]=='undenypost')
					continue;
				
				if ($isFav && ($brd_bid[$i] == -1)) {
					continue;
				}				

				$isGroup = ((!$isFav) && ($brd_flag[$i] & BBS_BOARD_GROUP)) || ($isFav && ($brd_flag[$i] == -1));
				$j_isGroup = ($isGroup?"true":"false");
				$j_boardDesc = "'" . addslashes(htmlspecialchars($brd_desc[$i], ENT_QUOTES)) . "'";
				if ($isGroup && $isFav) {
					$j_boardName = $j_boardDesc;
				} else {
					$j_boardName = "'" . $brd_name[$i] . "'";
				}

				if ($isGroup) {
					$j_todayNum = $j_nArticles = 0;
				} else {
					$j_todayNum = bbs_get_today_article_num($brd_name[$i]);
					$j_nArticles = $brd_artcnt[$i];
				}
				if ($isFav) {
					$j_npos = $brd_npos[$i];
					$j_bid = $brd_bid[$i];
				} else {
					$j_npos = $j_bid = 0;
				}
				$j_currentusers = intval($brd_currentusers[$i]);
				if ($isFold) {
					$j_isUnread = ($brd_unread[$i] == 1 ? "true" : "false");
					if ($j_nArticles > 0) {
						$j_nThreads = bbs_getthreadnum($brd_bid[$i]);
						$articles = bbs_getthreads($brd_name[$i], 0, 1, 0 ); //$brd_artcnt[$i], 1, $default_dir_mode);
						if ($articles == FALSE) {
							$j_nArticles = $j_lastID = $j_lastTitle = $j_lastOwner = $j_lastPosttime = 0;
						} else {
							$j_lastID = $articles[0]['origin']['ID'];
							$j_lastTitle = "'" . addslashes(htmlspecialchars($articles[0]['origin']['TITLE'],ENT_QUOTES)) . "'";
							$j_lastOwner = "'" . $articles[0]['origin']['OWNER'] . "'";
							$j_lastPosttime = "'" . strftime('%Y-%m-%d %H:%M:%S', intval($articles[0]['origin']['POSTTIME'])) . "'";
						}
					} else {
						$j_nThreads = $j_nArticles = $j_lastID = $j_lastTitle = $j_lastOwner = $j_lastPosttime = 0;
					}
					$j_bm = "'".$brd_bm[$i]."'";
					echo "boards[boards.length] = new Board($j_isGroup,$j_isUnread,$j_boardName,$j_boardDesc,$j_lastID,$j_lastTitle,$j_lastOwner,$j_lastPosttime,$j_bm,$j_todayNum,$j_nArticles,$j_nThreads,$j_npos,$j_bid,$j_currentusers);\n";
				} else {
					echo "boards[boards.length] = BoardS($j_isGroup,$j_boardName,$j_boardDesc,$j_todayNum,$j_nArticles,$j_npos,$j_bid,$j_currentusers);\n";
				}
			}
		}
	}
?>
	boards<?php echo $secNum; ?> = boards;
	curfold<?php echo $secNum; ?> = foldflag<?php echo $secNum; ?> = <?php echo ($isHide ? 0 : ($isFold ? 2 : 1)); ?>;
//-->
</script>
<?php
}


/*
 * $isFav = true ��ʱ���ʾ�����ղؼУ�����˵����$secNum �൱�� bbs2www/html/bbsfav.php ��ͷ�� $select, $group ������ʱû������ - atppp
 * isFold: true when show detailed boards list.
 */
function showSecs($secNum,$group,$isFold,$isFav = false,$isHide = false) {
	global $section_names;
?>
<table cellspacing=0 cellpadding=0 align=center width="97%" class=TableBorder1>
<TR><Th height=25 align=left id=TableTitleLink>&nbsp;
<?php
	if (!$isFav) {
		if ($group == 0) {
			if ($isFold) {
?>
<img src="pic/nofollow.gif" id="followImg<?php echo $secNum; ?>" style="cursor:hand;" onclick="loadSecFollow(<?php echo $secNum; ?>)" border=0 title="�۵������б�">
<?php
			} else {
?>
<img src="pic/plus.gif" id="followImg<?php echo $secNum; ?>" style="cursor:hand;" onclick="loadSecFollow(<?php echo $secNum; ?>)" border=0 title="չ�������б�">
<?php
			}
		} else {
?>
<img src="pic/nofollow.gif" border=0>
<?php
		}
?>
<a href="section.php?sec=<?php echo $secNum ; ?>" title=���뱾����������><?php echo $section_names[$secNum][0]; ?></a>
<?php
		if ($group == 0) {
?>
[<a id="toogleHide<?php echo $secNum ; ?>" href="javascript:toogleHide(<?php echo $secNum; ?>)"><?php echo $isHide ? "չ��" : "�ر�"; ?>�����б�</a>]
<?php
		}
	} else {
		$select = $secNum; //���������� :D
		if ($isFold) {
?>
<img src="pic/nofollow.gif" id="followImg<?php echo $select; ?>" style="cursor:hand;" onclick="loadFavFollow()" border=0 title="�۵������б�">
<?php
		} else {
?>
<img src="pic/plus.gif" id="followImg<?php echo $select; ?>" style="cursor:hand;" onclick="loadFavFollow()" border=0 title="չ�������б�">
<?php
		}
?>
�û��ղؼ�
<?php
		if ($select != 0) {
			$list_father = bbs_get_father($select);
?>
&nbsp;[<a href="favboard.php?select=<?php echo $list_father; ?>">�ص���һ��</a>]
<?php
		}
?>
&nbsp;[<a href="modifyfavboards.php?select=<?php echo $select; ?>">�����ղؼ�Ŀ¼</a>]
<?php
	}
?>
</th></tr>
<?php
	showSecsJS($secNum,$group,$isFold,$isFav,$isHide);
?>
<tr style="display:none" id="followTip<?php echo $secNum; ?>"><td style="text-align:center;border:1px solid black;background-color:lightyellow;color:black;padding:2px" onclick="loadSecFollow('<?php echo $secNum; ?>')">���ڶ�ȡ�����б����ݣ����Ժ��</div></td></tr>
<TR><Td id="followSpan<?php echo $secNum; ?>">
<script language="JavaScript">
<!--
	str = showSec(<?php echo ($isFold?"true":"false"); ?>, <?php echo ($isFav?"true":"false"); ?>, boards, <?php echo $secNum ?>, <?php echo ($isHide?"true":"false"); ?>);
	document.write(str);
//-->
</script>
</td></tr>
</table><br>
<?php
}

function showAnnounce(){
	global $AnnounceBoard;
	global $SiteName;
?>
<tr>
<td align=center width=100% valign=middle colspan=2>
<link rel="stylesheet" type="text/css" href="css/fader.css">
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
					echo '"��ǰû�й���","",';
				} else {
					$num=count($articles);
					for ($i=0;$i<$num;$i++) {
					echo "'<b><a href=\"disparticle.php?boardName=".$brdarr['NAME']."&ID=".$articles[$i]['origin']['ID']."\">" .htmlspecialchars($articles[$i]['origin']['TITLE'],ENT_QUOTES) . "</a></b> (".strftime('%Y-%m-%d %H:%M:%S', intval($articles[$i]['origin']['POSTTIME'])).")',\"\",";
					}
				}
			}
		}
?>"<b>��ӭ����<?php echo $SiteName; ?></b>",""
];
</SCRIPT>
<div id="elFader" style="position:relative;visibility:hidden; height:16" ></div>
</td>
</tr>
<?php
}

function usersysinfo($info){
	if (USEBROWSCAP == 0) { //FireFox, Opera ���жϲ��� - atppp
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
		} else {
			$function_ret="δ֪��δ֪";
		}
	} else {
		$browser = get_browser($info);
		$str1 = $browser->parent;
		$str1 = str_replace("IE","Internet Explorer",$str1);
		if ($str1 == "") $str1 = "δ֪";
		$str2 = $browser->platform;
		$str2 = str_replace("Win","Windows ",$str2);
		if ($str2 == "") $str2 = "δ֪";
		$function_ret = "� �� ����".$str1."������ϵͳ��".$str2;
	}
	return $function_ret;
} 

function showUserInfo(){
?>
<table cellpadding=5 cellspacing=1 class=TableBorder1 align=center style="word-break:break-all;" width="97%">
<TR><Th align=left colSpan=2 height=25>-=&gt; �û�������Ϣ</Th></TR>
<TR><TD vAlign=top class=TableBody1 height=25 width=100% >
<?php
$userip = $_SERVER["HTTP_X_FORWARDED_FOR"];
$userip2 = $_SERVER["REMOTE_ADDR"];
if ($userip=='') $userip = $userip2;
echo '������ʵ�ɣ��ǣ�'. $userip. '��';
echo usersysinfo($_SERVER["HTTP_USER_AGENT"]);

?>
</TD></TR></table><br>
<?php

}

function showOnlineUsers(){
?>
<table cellpadding=5 cellspacing=1 class=TableBorder1 align=center style="word-break:break-all;" width="97%">
<TR><Th colSpan=2 align=left id=TableTitleLink height=25 style="font-weight:normal">
	<b>-=&gt; ��̳����ͳ��</b>&nbsp;[<a href=showonlineuser.php>��ʾ��ϸ�б�</a>]
	<!--[<a href=boardstat.php?reaction=online>�鿴�����û�λ��</a>]-->
</Th></TR>
<TR><TD width=100% vAlign=top class=TableBody1>  Ŀǰ��̳���ܹ��� <b><?php echo bbs_getonlinenumber() ; ?></b> �����ߣ�����ע���û� <b><?php echo bbs_getonlineusernumber(); ?></b> �ˣ��ÿ� <b><?php echo bbs_getwwwguestnumber() ; ?></b> �ˡ�<!--<br>
��ʷ������߼�¼�� <b> </b> ��ͬʱ����-->
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
