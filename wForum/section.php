<?php


if ( (!isset($_COOKIE['iscookies'])) || ($_COOKIE['iscookies']==''))
{
	setcookie('iscookies','0',time()+3650*24*3600);
	print '<META http-equiv=Content-Type content=text/html; charset=gb2312><meta HTTP-EQUIV=REFRESH CONTENT=3>���ڵ�½��̳����<br><br>��ϵͳҪ��ʹ��COOKIES�������������������COOKIES���������ܵ�¼��ϵͳ����';
	exit();
} 

$setboard=1;

require("inc/funcs.php");
require("inc/user.inc.php");

global $secNum;

preprocess();

show_nav();

setStat("���������б�");

if (isErrFounded()) {
	html_error_quit();
} else {
?>
	<br>
	<TABLE cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	if ($loginok==1) {
		showUserMailbox();
	}
?>	
	</table>
<?php
	head_var($section_names[$secNum][0],'section.php?sec='.$secNum, 0);
?>
	<TABLE cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
 <?php
	showAnnounce(); 
?>
	<tr>
	<td align=center width=100% valign=middle colspan=2>
	<hr>
	</td></tr>
	</TABLE>
<?php
	showSecs($secNum,0,true);
	if (isErrFounded()) {
		html_error_quit();
	} else {
		showUserInfo();
		showSample();
	}
}
show_footer();

/*--------------- function defines ------------------*/

function preprocess(){
	GLOBAL $_GET;
	GLOBAL $sectionCount;
	global $secNum;

	$path='';
	$secNum=intval($_GET['sec']);
	if ( ($secNum<0)  && ($secNum>=$sectionCount)) {
		foundErr("��ָ���ķ��������ڣ�");
	} else {
		if ($_GET['ShowBoards']=='N') {
			setcookie('ShowSecBoards'.$secNum, '' ,time()+604800);
			$_COOKIE['ShowSecBoards'.$secNum]='';
		} else {
			setcookie('ShowSecBoards'.$secNum, 'Y' ,time()+604800);
			$_COOKIE['ShowSecBoards'.$secNum]='Y';
		}
	}
}

?>
