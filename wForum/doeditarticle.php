<?php
$needlogin=1;

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

preprocess();

setStat("�༭����");

show_nav();

if (isErrFounded()) {
	html_error_quit() ;
} else {
	showUserMailBoxOrBR();
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
	doPostAritcles($boardID,$boardName,$boardArr,$reID,$reArticles);
	if (isErrFounded()) {
		html_error_quit() ;
	}
}

//showBoardSampleIcons();
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
	global $reID;
	global $reArticles;
	if ($loginok!=1) {
		foundErr("�οͲ��ܷ������¡�");
		return false;
	}
	if (!isset($_POST['board'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_POST['board'];
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
	if (bbs_is_readonly_board($boardArr)) {
			foundErr("����Ϊֻ����������");
			return false;
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
		return false;
	}
	if (!isset($_POST['Content'])) {
		foundErr("û��ָ���������ݣ�");
		return false;
	}
	if (isset($_POST["reID"])) {
		$reID = $_POST["reID"];
	}else {
		foundErr("δָ���༭������.");
		return false;
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����������ı��");
			return false;
		}
	}
	$ret=bbs_caneditfile($boardName,$articles[1]['FILENAME']);
	switch ($ret) {
	case -1:
		foundErr("���������ƴ���");
		return false;
	case -2:
		foundErr("���治���޸�����");
		return false;
	case -3:
		foundErr("�����ѱ�����ֻ��");
		return false;
	case -4:
		foundErr("�޷�ȡ���ļ���¼");
		return false;
	case -5:
		foundErr("�����޸���������!");
		return false;
	case -6:
		foundErr("ͬ��ID�����޸���ID������");
		return false;
	case -7:
		foundErr("����POSTȨ����");
		return false;
	}
	$reArticles=$articles;
	return true;
}

function 	doPostAritcles($boardID,$boardName,$boardArr,$reID,$reArticles){
	global $_POST;
	$ret=bbs_updatearticle($boardName,$reArticles[1]['FILENAME'],preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['Content']));
	switch ($ret) {
		case -1:
			foundErr("�޸�����ʧ�ܣ����¿��ܺ��в�ǡ������");
			return false;
		case -10:
			foundErr("�Ҳ����ļ�!");
			return false;

	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">���±༭�ɹ�</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ذ��������б�<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=board.php?name=<?php echo $boardName; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="index.php">������ҳ</a></li>
<li><a href="board.php?name=<?php   echo $boardName; ?>">����<?php   echo $boardArr['DESC']; ?></a></li>
</ul></td></tr></table>
<?php
}
?>
