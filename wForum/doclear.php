<?php

require("inc/funcs.php");

global $boardName;

setStat("���δ��");

requireLoginok();

preprocess();

bbs_brcclear($boardName);

if (!isset($_SERVER["HTTP_REFERER"]) || ( $_SERVER["HTTP_REFERER"]=="") )
{
	header("Location: index.php");
} else {
	header("Location: ".$_SERVER["HTTP_REFERER"]);
} 

function preprocess() {
	global $boardName;
	global $currentuser;
	if (!isset($_GET['boardName'])) {
		foundErr("δָ�����档");
	}
	$boardName = $_GET['boardName'];
	$brdArr = array();
	$boardID = bbs_getboard($boardName, $brdArr);
	$boardArr = $brdArr;
	$boardName = $brdArr['NAME'];
	if ($boardID == 0) {
		foundErr("ָ���İ��治���ڡ�");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����档");
	}
	if ($brdArr["FLAG"] & BBS_BOARD_GROUP) {
		foundErr("����Ŀ¼���档");
    }
}
?>