<?php


$setboard=1;

require("inc/funcs.php");
require("inc/user.inc.php");

preprocess();

show_nav();

?>
<br>
<TABLE cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php

if ($loginok==1) {
	showUserMailbox();
}

showAnnounce(); 
?>
<td align=center width=100% valign=middle colspan=2>
<hr>
</td></tr>
<?php
showTitle();

?>
</TABLE>
<?php



if ($loginok!=1) {
	FastLogin();
}


showAllSecs();
if (isErrFounded()) {
	html_error_quit();
} else {
	showUserInfo();
	showOnlineUsers();
	showSample();
}
show_footer();

/*--------------- function defines ------------------*/

function preprocess(){
	GLOBAL $_GET;
	GLOBAL $_COOKIE;
	GLOBAL $sectionCount;

	$path='';
	if ($_GET['ShowBoards']=='Y') {
		$secNum=intval($_GET['sec']);
		if ( ($secNum>=0)  && ($secNum<$sectionCount)) {
			setcookie('ShowSecBoards'.$secNum, 'Y' ,time()+604800,''); 
			$_COOKIE['ShowSecBoards'.$secNum]='Y';
		}
	}
	if ($_GET['ShowBoards']=='N') {
		$secNum=intval($_GET['sec']);
		if ( ($secNum>=0)  && ($secNum<$sectionCount)) {
			setcookie('ShowSecBoards'.$secNum, '' ,time()+604800);
			$_COOKIE['ShowSecBoards'.$secNum]='';
		}
	}
}


function showTitle() {
	global $dir_modes;
	$boardID=bbs_getboard('newcomers');
	$num=bbs_countarticles($boardID, $dir_modes['normal']);
	$user=bbs_getarticles("newcomers",$num,1,$dir_modes['normal']);
?>
<TR><TD style="line-height: 20px;">
��ӭ�¼����Ա <a href=dispuser.php?id=<?php echo $user[0]['OWNER']; ?> target=_blank><b><?php echo $user[0]['OWNER']; ?></b></a>&nbsp;[<a href="board.php?name=newcomers">�½�����</a>]<BR>��̳���� <B><?php  ?></B> λע���Ա , ����������<b><?php echo $rs[0]; ?></b> , ����������<b><?php echo $rs[1]; ?></b><BR>������̳��������<FONT COLOR="<?php echo $Forum_body[8]; ?>"><B><?php echo $rs[2]; ?></B></FONT> , ���շ�����<B><?php echo $rs[5]; ?></B> , ����շ�����<B><?php echo $rs[6]; ?></B></td><TD valign=bottom align=right style="line-height: 20px;"><a href=# onclick="alert('���������ڿ����У�');">�鿴����</a> , <a href=topten.php>���Ż���</a> , <a href=# onclick="alert('���������ڿ����У�');">��������</a> , <a href="/onlineuser.php">�����û�</a><BR>�����һ�η������ڣ�<?php echo strftime("%Y-%m-%d %H:%M:%S"); ?>
</TD></TR>
<?php
}


?>
