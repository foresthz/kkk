<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

setStat("ɾ������");

requireLoginok("�οͲ���ɾ�����¡�");

preprocess();

show_nav();

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
doPostAritcles($boardID,$boardName,$boardArr,$reID,$reArticles);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $reID;
	global $reArticles;
	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['board'];
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
	if (bbs_is_readonly_board($boardArr)) {
		foundErr("����Ϊֻ����������");
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
	}
	if (isset($_GET["ID"])) {
		$reID = $_GET["ID"];
	}else {
		foundErr("δָ��ɾ��������.");
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����������ı��");
		}
	}
	$reArticles=$articles;
	return true;
}

function doPostAritcles($boardID,$boardName,$boardArr,$reID,$reArticles){
	$ret=bbs_delfile($boardName,$reArticles[1]['FILENAME']);
	switch ($ret) {
		case -1:
			foundErr("����Ȩɾ�����ġ�");
		case -2:
			foundErr("����İ��������ļ���!");
	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">����ɾ���ɹ�</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ذ��������б�<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=board.php?name=<?php echo $boardName; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="index.php">������ҳ</a></li>
<li><a href="board.php?name=<?php   echo $boardName; ?>">����<?php   echo $boardArr['DESC']; ?></a></li>
</ul></td></tr></table>
<?php
}
?>
