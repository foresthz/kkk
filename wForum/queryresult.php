<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");


global $boardName;
global $boardArr;
global $boardID;

setStat("�������");

show_nav();

preprocess();

if ($loginok==1) {
?>
<table border="0" width="97%">
<?php
	showUserMailbox();
?>
</table>
<?php
} else {
	echo "<br><br>";
}

if ($boardName!='') 
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
else {
	head_var("��̳����",'',0);
}

if (isErrFounded()) {
		html_error_quit();
} else {
	doSearch($boardID,$boardName);
}

if (isErrFounded()) {
	html_error_quit();
}

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $title,$title2,$title3,$author;
	if (!isset($_REQUEST['boardName'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_REQUEST['boardName'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
		return false;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
		return false;
	}
	$title=trim($_REQUEST['title']);
	$title2=trim($_REQUEST['title2']);
	$title3=trim($_REQUEST['title3']);
	$author=trim($_REQUEST['author']);
	
	return true;
}

function doSearch($boardID,$boardName){
	global $title,$title2,$title3,$author;
	$result=bbs_searchtitle($boardName,$title,$title2,$title3,$author,intval($_REQUEST['dt']),isset($_REQUEST['mg']),isset($_REQUEST['ag']),isset($_REQUEST['og']));
	$num=count($result);
	if ($num==0 || $result<=0) {
		foundErr("<font color=#ff0000>û���ҵ���Ҫ�Ľ��</font>");
		return false;
	}
?>
<table cellpadding=0 cellspacing=0 border=0 width="97%" align=center>
<tr><td>�������⹲��ѯ��<font color=#FF0000><?php echo$num; ?></font>�����
</td></tr></table>
<TABLE cellPadding=3 cellSpacing=1 class=TableBorder1 align=center>
<TR valign=middle>
<Th height=25 width=32>״̬</Th>
<Th width=*>�� ��</Th>
<Th width=80>�� ��</Th>
<Th width=195>������ | �ظ���</Th>
</TR>
<?php
	for ($i=1;$i<=$num;$i++) {
?>
  <TR><TD align=middle class=TableBody2 width=32><img src=pic/blue/folder.gif alt=������������>
  </TD>
  <TD  class=TableBody1 width=*><a href='disparticle.php?boardName=<?php echo $boardName; ?>&ID=<?php echo $result[$i]['ID']; ?>' target=_blank><img src='face/face1.gif' border=0 alt="���´������������"></a> <a href='disparticle.php?boardName=<?php echo $boardName; ?>&ID=<?php echo $result[$i]['ID']; ?>'>
<?php echo $result[$i]['TITLE']; ?>
</a>    </TD> 
    <TD align=middle  class=TableBody2  width=80><a href="dispuser.php?id=<?php echo $result[$i]['OWNER']; ?>"><?php echo $result[$i]['OWNER']; ?></a></TD> 
    <TD  class=TableBody1 width=195><?php echo strftime("%y-%m-%d %H:%M", $result[$i]['POSTTIME']); ?>
&nbsp;<font color="#FF0000">|</font>&nbsp;
<a href="dispuser.php?id=<?php echo $result[$i]['OWNER']; ?>"><?php echo $result[$i]['OWNER']; ?></a>
</TD>
</TR> 
<?php
	}

}
?>
