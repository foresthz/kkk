<?php

require("inc/funcs.php");
require("inc/board.inc.php");
require("inc/user.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;

setStat("����С�ֱ�");

requireLoginok("�οͲ��ܷ���С�ֱ���");

preprocess();

show_nav($boardName);

showUserMailBox();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
main($boardID,$boardName);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
	global $title;
	global $Content;
	
	$title=trim($_POST["title"]);
	$Content=trim($_POST["Content"]);

	if ($title=="") {
	    foundErr("���ⲻӦΪ�ա�");
	}
	if (strlen($title)>80) {
		foundErr("���ⳤ�Ȳ��ܳ���80");
	}
	if ($Content=="") {
		foundErr("û����д���ݡ�");
	}
	if (strlen($Content)>500) {
		foundErr("�������ݲ��ô���500");
	} 
	if (!isset($_POST['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_POST['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治���ڡ�");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����棡");
	}
	if (bbs_is_readonly_board($boardArr)) {
		foundErr("����Ϊֻ����������");
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�ڱ��淢��С�ֱ���");
	}

	return true;
}



function main($boardID,$boardName) {
	global $conn;
	global $currentuser;
	global $title;
	global $Content;

    $sql="insert into smallpaper_tb (boardID,Owner,Title,Content,Addtime) values (".$boardID.",'". $currentuser['userid']."','". htmlspecialchars($title, ENT_QUOTES)."','". htmlspecialchars($Content,ENT_QUOTES)."',now())";
	$conn->query($sql);
	setSucMsg("���ɹ��ķ�����С�ֱ���");
  	return html_success_quit('���ذ���', 'board.php?name='.$boardName);
} 

?>
