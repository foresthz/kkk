<?php
require("inc/funcs.php");

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
	doSearch($boardName);
}

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	if (!isset($_GET['boardName'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_GET['boardName'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
		return false;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
		return false;
	}
	return true;
}

function showSearchMenu(){

}
?>
