<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;

setStat("��������");

requireLoginok("�οͲ��ܷ������¡�");

preprocess();

show_nav(false);
showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
doPostAritcles($boardID,$boardName,$boardArr,$reID);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
	global $reID;
	global $reArticles;
	if (!isset($_POST['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_POST['board'];
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
	if (!isset($_POST['subject'])) {
		foundErr("û��ָ�����±��⣡");
	}
	if (!isset($_POST['Content'])) {
		foundErr("û��ָ���������ݣ�");
	}
	if (isset($_POST["reID"])) {
		$reID = $_POST["reID"];
	} else {
		$reID = 0;
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����� Re �ı��");
		}
		if ($articles[1]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
		}
	}
	return true;
}

function doPostAritcles($boardID,$boardName,$boardArr,$reID){
	global $_POST;
	if (bbs_is_outgo_board($boardArr)) $outgo = intval($_POST["outgo"]);
	else $outgo = 0;
	$ret=bbs_postarticle($boardName,preg_replace("/\\\(['|\"|\\\])/","$1",trim($_POST['subject'])),
	                     preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['Content']),
	                     intval($_POST['signature']), $reID,$outgo,intval($_POST['anonymous']),
	                     ($reID>0)?0:intval($_POST['emailflag']),SUPPORT_TEX?intval($_POST['texflag']):0);
	switch ($ret) {
		case -1:
			foundErr("��������������ơ�");
		case -2:
			foundErr("����Ϊ����Ŀ¼�棡");
		case -3:
			foundErr("����Ϊ�ա�");
		case -4:
			foundErr("����������Ψ����, ����������Ȩ���ڴ˷������¡�");
		case -5:
			foundErr("�ܱ�Ǹ, �㱻������Աֹͣ�˱����postȨ����");
		case -6:
			foundErr("���η��ļ������, ����Ϣ��������ԡ�");
		case -7:
			foundErr("�޷���ȡ�����ļ�����Ѹ��֪ͨվ����Ա��лл��");
		case -8:
			foundErr("���Ĳ��ɻأ�");
		case -9:
			foundErr("ϵͳ�ڲ�������Ѹ��֪ͨվ����Ա��лл��");
	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr align=center><th width="100%">���ĳɹ���</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ذ��������б�<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=board.php?name=<?php echo $boardName; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="index.php">������ҳ</a></li>
<li><a href="board.php?name=<?php   echo $boardName; ?>">����<?php   echo $boardArr['DESC']; ?></a></li>
</ul></td></tr></table>
<?php
}
?>
