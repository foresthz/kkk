<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/ubbcode.php");
require("inc/userdatadefine.inc.php");
require("inc/treeview.inc.php");
require_once("inc/myface.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $articles;
global $groupID;
global $start;
global $listType;

setStat("�����Ķ�");

preprocess();

setStat(htmlspecialchars($articles[0]['TITLE'] ,ENT_QUOTES) . " " );

show_nav($boardName);

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
showArticleThreads($boardName,$boardID,$groupID,$articles,$start,$listType);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $articles;
	global $groupID;
	global $dir_modes;
	global $listType;
	global $start;
	if (!isset($_GET['boardName'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['boardName'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
	}
	if (!isset($_GET['ID'])) {
		foundErr("��ָ�������²����ڣ�");
	} else {
		$groupID=intval($_GET['ID']);
	}
	$listType=0;
	if(isset($_GET['listType'])) {
		if ($_GET['listType']=='1')
			$listType=1;
	}
	if (!isset($_GET['start'])) {
		$start=0;
	} else {
		$start=intval($_GET['start']);
	}

	bbs_set_onboard($boardID,1);
	
	$num = bbs_get_threads_from_gid($boardID, $groupID, $groupID, $articles, $haveprev );
	if ($num==0) {
		foundErr("��ָ�������²����ڣ�");
	}
	if ($start < 0) $start = 0;
	if ($start >= $num) $start = $num - 1;
	return true;
}

function article_bar($boardName,$boardID,$groupID,$article,$startNum,$listType){
	global $dir_modes;
?>
<table cellpadding=2 cellspacing=0 border=0 width=97% align=center>
	<tr><td width=2> </td>
	<td align=left valign=middle style="height:27"><table cellpadding=0 cellspacing=0 border=0 ><tr>
	<td width="110"><a href=postarticle.php?board=<?php echo $boardName; ?>><div class="buttonClass1" border=0 alt=������></div></a></td>
<!--	<td width="110"><a href=# onclick="alert('���������ڿ����У�')"><div class="buttonClass2" border=0 alt=������ͶƱ></div></a></td>-->
	<td width="110"><a href="postarticle.php?board=<?php echo $boardName; ?>&reID=<?php echo $article['ID']; ?>"><div class="buttonClass4" border=0 alt=�ظ�������></div></a></td>
	</tr></table>
	</td>
	<td align=right valign=middle>
<!--
	<a href="disparticle.php?boardName=<?php echo $boardName; ?>&ID=<?php echo $groupID>1?$groupID-1:1; ?>"><img src="pic/prethread.gif" border=0 alt=�����һƪ���� width=52 height=12></a>&nbsp;
-->
	<a href="javascript:this.location.reload()"><img src="pic/refresh.gif" border=0 alt=ˢ�±����� width=40 height=12></a> &nbsp;
<?php
	if ($listType==1) {
?>
	<a href="?boardName=<?php echo $boardName; ?>&ID=<?php echo $groupID; ?>&start=<?php echo (ceil(($startNum+1)/THREADSPERPAGE)-1)*THREADSPERPAGE;; ?>&listType=0"><img src="pic/flatview.gif" width=40 height=12 border=0 alt=ƽ����ʾ����></a>
<?php
	} else {
?>
	<a href="?boardName=<?php echo $boardName; ?>&ID=<?php echo $groupID; ?>&start=<?php echo $startNum; ?>&listType=1"><img src="pic/treeview.gif" width=40 height=12 border=0 alt=������ʾ����></a>
<?php
	}
?>
<!--
	��<a href="disparticle.php?boardName=<?php 	echo $boardName; ?>&ID=<?php echo $groupID<bbs_getThreadNum($boardID)?$groupID+1:$groupID; ?>"><img src="pic/nextthread.gif" border=0 alt=�����һƪ���� width=52 height=12></a>
-->
	</td>
	</tr>
</table>
<?php
}

function dispArticleTitle($boardName,$boardID,$groupID,$article, $startNum){
?>
<TABLE cellPadding=0 cellSpacing=1 align=center class=TableBorder1>
	<tr align=middle> 
	<td align=left valign=middle width="100%" height=25>
		<table width=100% cellPadding=0 cellSpacing=0 border=0>
		<tr>
		<th align=left valign=middle width="73%" height=25>
		&nbsp;* ��������</B>�� <?php echo htmlspecialchars($article['TITLE'],ENT_QUOTES); ?>  
<?php /* ���������⣬������ 
<script language="JavaScript">
<!--
	var flags = "<?php echo $article['FLAGS'][0]; ?>";
	if (article_is_zhiding(flags)) {
		document.write("[�̶�]");
	} else if(article_is_noreply(flags)) {
		document.write("[����]");
	} else if(article_is_digest(flags)) {
		document.write("[����]");
	}
//-->
</script>
*/ ?>
		</th>
		<th width=37% align=right>
<!--
		<a href=# onclick="alert('��������δʵ��');"><img src="pic/saveas.gif" border=0 alt=�����ҳΪ�ļ�></a>&nbsp;
		<a href=# onclick="alert('��������δʵ��');"><img src="pic/report.gif" alt=���汾�������� border=0></a>&nbsp;
		<a href=# onclick="alert('��������δʵ��');"><img src="pic/printpage.gif" alt=��ʾ�ɴ�ӡ�İ汾 border=0></a>&nbsp;
		<a href=# onclick="alert('��������δʵ��');"><img src="pic/pag.gif" border=0 alt=�ѱ�������ʵ�></a>&nbsp;
		<a href=# onclick="alert('��������δʵ��');"><IMG SRC="pic/fav_add.gif" BORDER=0 alt=�ѱ���������̳�ղؼ�></a>&nbsp;
		<a href=# onclick="alert('��������δʵ��');"><img src="pic/emailtofriend.gif" border=0 alt=���ͱ�ҳ�������></a>&nbsp;
-->
		<a href=# onClick="window.external.AddFavorite(location.href, document.title);"><IMG SRC="pic/fav_add1.gif" BORDER=0 width=15 height=15 alt=�ѱ�������IE�ղؼ�></a>&nbsp;
		</th>
		</tr>
		</table>
	</td>
	</tr>
</table>
<?php
}

function showArticleThreads($boardName,$boardID,$groupID,$articles,$start,$listType) {
	global $dir_modes;
	$total=count($articles);

	$totalPages=ceil(($total)/THREADSPERPAGE);
	$num=THREADSPERPAGE;
	if ($start<0) {
		$start=0;
	} elseif ($start>=$total) {
		$start=$total-1;
	}
	if (($start+$num)>$total) {
		$num=$total-$start;
	}
	if ($listType==1) {
		$num=1;
	} 
	$page=ceil(($start+1)/THREADSPERPAGE);
	article_bar($boardName,$boardID,$groupID, $articles[0], $start, $listType);
	dispArticleTitle($boardName,$boardID,$groupID,$articles[0],$start);
?>
<TABLE cellPadding=5 cellSpacing=1 align=center class=TableBorder1 style=" table-layout:fixed;word-break:break-all">
<?php
	for($i=0;$i<$num;$i++) {
			if (($i+$start)==0) 
				showArticle($boardName,$boardID,0,intval($articles[0]['ID']),$articles[0],$i%2);
			else 
				showArticle($boardName,$boardID,$i+$start,intval($articles[$i+$start]['ID']),$articles[$i+$start],$i%2);
	}
?>
</table>
<table cellpadding=0 cellspacing=3 border=0 width=97% align=center><tr><td valign=middle nowrap>����������<b><?php echo $total; ?></b>
<?php
	if ($listType!=1) {
?>
����ҳ�� 
<?php
		if ($page>4) {
			echo "<a href=\"?boardName=".$boardName."&ID=".$groupID."&start=0\">[1]</a> ";
			if ($page>5) {
				echo "...";
			}
		} 

		if ($totalPages>$page+3){
			$endpage=$page+3;
		}  else{
			$endpage=$totalPages;
		} 

		for ($i=($page-3>0)?($page-3):1; $i<=$endpage; $i++){
			if ($i==$page)   {
				echo " <font color=#ff0000>[".$i."]</font>";
			} else {
				echo " <a href=\"?boardName=".$boardName."&ID=".$groupID."&start=".($i-1)*THREADSPERPAGE."\">[".$i."]</a>";
			} 
		} 

		if ($endpage<$totalPages) {
			if ($endpage<$totalPages-1){
				echo "...";
			}
			echo " <a href=\"?boardName=".$boardName."&ID=".$groupID."&start=".($totalPages-1)*THREADSPERPAGE."\">[".$totalPages."]</a>";
		} 
	}
?></td><td valign=middle nowrap align=right>
<?php 
	boardJump();
?></td></tr></table>
<br>
<?php
	if ($listType==1) {
?>
<table cellpadding=3 cellspacing=1 class=TableBorder1 align=center>
<tr><th align=left width=90% valign=middle> &nbsp;*����Ŀ¼</th>
<th width=10% align=right valign=middle height=24 id=TableTitleLink> <a href=#top><img src=pic/gotop.gif border=0>����</a>&nbsp;</th></tr>
<?php
		showTree($boardName,$groupID,$articles,"showTreeItem", TREEVIEW_MAXITEM, $start);
?>
</table>
<?php
	}
}

function showArticle($boardName,$boardID,$num, $startNum,$thread,$type){
	global $loginok;
	$user=array();
	$user_num=bbs_getuser($thread['OWNER'],$user);
	$bgstyle='TableBody'.($type+1);
	$fgstyle='TableBody'.(2-$type);
?>
<tr><td class=<?php echo $bgstyle ;?> valign=top width=175 >
<table width=100% cellpadding=4 cellspacing=0 >
<tr><td width=* valign=middle style="filter:glow(color=#9898BA,strength=2)" >&nbsp;<a href="dispuser.php?id=<?php echo $thread['OWNER']; ?>" target=_blank title="�鿴<?php echo $thread['OWNER']; ?>�ĸ�������" style="TEXT-DECORATION: none;"><font color=#990000><B><?php echo $thread['OWNER']; ?></B></font></a></td>
<td width=25 valign=middle>
<?php 
	$is_online = bbs_isonline($thread['OWNER']);
	$show_detail = ($user['userdefine0'] & BBS_DEF_SHOWDETAILUSERDATA);
	if ($show_detail) {
		if ( chr($user['gender'])=='M' ){
			$c = "˧��Ӵ";
			if ($is_online) {
				$img = "pic/Male.gif";
			} else {
				$img = "pic/ofmale.gif";
			}
		} else {
			$c = "��ŮӴ";
			if ($is_online) {
				$img = "pic/Female.gif";
			} else {
				$img = "pic/offemale.gif";
			}
		}
	} else {
		$c = "�Ա���Ӵ";
		if ($is_online) {
			$img = "pic/online1.gif";
		} else {
			$img = "pic/offline1.gif";
		}
	}
	if ($loginok && $is_online) {
		echo '<a href="javascript:replyMsg(\''.$thread['OWNER'].'\')"><img src="'.$img.'" border=0 alt="'.$c.'�����ߣ�����������"/></a>';
	} else {
		echo '<img src="'.$img.'" border=0 alt="'.$c.'������"/>';
	}
?>
</td>
<td width=16 valign=middle></td></tr></table>
&nbsp;&nbsp;<?php echo get_myface($user, "align=absmiddle"); ?><br>
&nbsp;&nbsp;<img src=pic/level10.gif><br>
&nbsp;&nbsp;�ȼ���<?php echo bbs_getuserlevel($thread['OWNER']); ?><BR>
&nbsp;&nbsp;���£�<?php echo $user['numposts']; ?><br>
&nbsp;&nbsp;���֣�<?php echo $user['score']; ?><br>
<?php
	if (SHOW_REGISTER_TIME) {
?>
&nbsp;&nbsp;ע�᣺<?php echo strftime('%Y-%m-%d',$user['firstlogin']); ?><BR>
<?php
	}
	if ($show_detail) {
?>
&nbsp;&nbsp;������<?php echo get_astro($user['birthmonth'],$user['birthday']); ?>
<?php
	}
?>
</td>

<td class=<?php echo $bgstyle ;?> valign=top width=*>

<table width=100% ><tr><td width=* valign='center'>
<?php
/*	if ($loginok) {
?>
<a href="javascript:replyMsg('<?php echo $thread['OWNER']; ?>')"><img src="pic/message.gif" border=0 alt="��<?php echo $thread['OWNER']; ?>����һ������Ϣ"></a>&nbsp;
<?php
	}*/
?>
<a href="friendlist.php?addfriend=<?php echo $thread['OWNER']; ?>" target=_blank><img src="pic/friend.gif" border=0 alt="��<?php echo $thread['OWNER']; ?>�������"></a>&nbsp;
<!--<a href="dispuser.php?id=<?php echo $thread['OWNER']; ?>" target=_blank><img src="pic/profile.gif" border=0 alt="�鿴<?php echo $thread['OWNER']; ?>�ĸ�������"></a>&nbsp;-->
<a href="queryresult.php?userid=<?php echo $thread['OWNER']; ?>&boardNames=<?php echo $boardName; ?>"><img src="pic/find.gif" border=0 alt="����<?php echo $thread['OWNER']; ?>�ڱ������������"></a>&nbsp;
<a href="sendmail.php?board=<?php echo $boardName; ?>&reID=<?php echo $thread['ID']; ?>"><IMG alt="������﷢�͵��ʸ�<?php echo $thread['OWNER']; ?>" border=0 src=pic/email.gif></A>&nbsp;
<a href="editarticle.php?board=<?php echo $boardName; ?>&reID=<?php echo $thread['ID']; ?>"><img src="pic/edit.gif" border=0 alt=�༭></a>&nbsp;
<a href="deletearticle.php?board=<?php echo $boardName; ?>&ID=<?php echo $thread['ID']; ?>" onclick="return confirm('�����Ҫɾ��������?')"><img src="pic/delete.gif" border=0 alt=ɾ��></a>&nbsp;
<?php
	if (!OLD_REPLY_STYLE) {
?>
<a href="postarticle.php?board=<?php echo $boardName; ?>&reID=<?php echo $thread['ID']; ?>&quote=1"><img src="pic/reply.gif" border=0 alt=���ûظ��������></a>&nbsp;
<?php
	}
?>
<a href="postarticle.php?board=<?php echo $boardName; ?>&reID=<?php echo $thread['ID']; ?>"><img src="pic/reply_a.gif" border=0 alt=�ظ��������></a>
</td><td width=50><b><?php echo $num==0?'¥��':'��<font color=#ff0000>'.$num.'</font>¥'; ?></b></td></tr><tr><td bgcolor=#D8C0B1 height=1 colspan=2></td></tr>
</table>

<blockquote><table class=TableBody2 border=0 width=90% style=" table-layout:fixed;word-break:break-all"><tr><td width="100%" style="font-size:9pt;line-height:12pt"><img src=face/face1.gif border=0 alt=��������>&nbsp;<?php echo  htmlspecialchars($thread['TITLE'],ENT_QUOTES); ?> <b></b><br><?php 
	 $isnormalboard=bbs_normalboard($boardName);
	 $filename=bbs_get_board_filename($boardName, $thread["FILENAME"]);
	 if ($loginok) {
		 bbs_brcaddread($boardName, $thread['ID']);
	 };
	 echo dvbcode(bbs_printansifile($filename,1,'bbscon.php?bid='.$boardID.'&id='.$thread['ID']),0,$fgstyle);
	
?> </blockquote></td></tr></table>
</td>

</tr>
<!--
<tr>
<td class=<?php echo $bgstyle ;?> valign=middle align=center width=175><a href=# onclick="alert('��������δʵ��');" target=_blank><img align=absmiddle border=0 width=13 height=15 src="pic/ip.gif" alt="����鿴�û���Դ������<br>����IP��*.*.*.*"></a> <?php echo strftime("%Y-%m-%d %H:%M:%S",$thread['POSTTIME']); ?></td>
<td class=<?php echo $bgstyle ;?> valign=middle width=*>
<table width=100% cellpadding=0 cellspacing=0><tr>
  <td align=left valign=middle> &nbsp;&nbsp;<a href=# onclick="alert('��������δʵ��');" title="ͬ������۵㣬����һ���ʻ�����������5���Ǯ"><img src=pic/xianhua.gif border=0>�ʻ�</a>(<font color=#FF0000>0</font>)&nbsp;&nbsp;<a href=# onclick="alert('��������δʵ��');" title="��ͬ������۵㣬����һ����������������5���Ǯ"><img src=pic/jidan.gif border=0>����</a>(<font color=#FF0000>0</font>)</td><td align=right nowarp valign=bottom width=200></td>
  <td align=right valign=bottom width=4><a href="bmmanage.php?board=<?php echo $boardName; ?>&ID=<?php echo $thread['ID']; ?>"><img src="pic/jing.gif" border=0 alt=�л�����</a></td>
</tr></table>
</td></tr>-->


<?php
}

function showTreeItem($boardName,$groupID,$article,$startNum,$level, $lastflag){
	global $start; //������˼�����ȫ�ֱ��� - atppp
	echo '<TR><td class=TableBody2 width="100%" height=22 colspan=2>';
	for ($i=0;$i<$level;$i++) {
		if ($lastflag[$i]) {
			if ($i == $level - 1) echo '<img src="pic/treenode2.gif">'; // |-
			else echo '<img src="pic/treenode.gif">';                   // |
		} else {
			if ($i == $level - 1) echo '<img src="pic/treenode1.gif">'; // \
			else echo "&nbsp;&nbsp;";                               // nothing
		}
	}
	if ($article == null) {
		echo ' ... <a href="disparticle.php?boardName='.$boardName.'&ID='.$groupID.'&start='.$startNum.'&listType=1"><span style="color:red">���и���</span></a> ...';
	} else {
		echo '<img src=face/face1.gif height=16 width16>  <a href="disparticle.php?boardName='.$boardName.'&ID='.$groupID.'&start='.$startNum.'&listType=1">';
		if ($start==$startNum) {
			echo "<font color=#FF0000>";
		}
		echo htmlspecialchars($article['TITLE'],ENT_QUOTES).' </a><I><font color=gray>(';
		if ($article["EFFSIZE"] < 1000) echo $article["EFFSIZE"];
		else {
			printf("%.1f",$article["EFFSIZE"]/1000.0); echo "k";
		}
		echo '��) �� <a href=dispuser.php?id='.$article['OWNER'].' target=_blank title="��������"><font color=gray>'.$article['OWNER'].'</font></a>��'.strftime("%Y��%m��%d�� %T",$article['POSTTIME']);
		if ($start==$startNum) {
			echo "</font>";
		}
		echo '</I>';
	}
	echo '</td></tr>';	
}
?>
