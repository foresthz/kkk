<?php
$needlogin=1;

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;

preprocess();

setStat("��������");

show_nav();

if (isErrFounded()) {
	html_error_quit() ;
} else {
	?>
	<br>
	<TABLE cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
	<?php

	if ($loginok==1) {
		showUserMailbox();
?>
</table>
<?php
	}

	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);

	doPostAritcles($boardID,$boardName,$boardArr,$reID);

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
	if (!isset($_POST['subject'])) {
		foundErr("û��ָ�����±��⣡");
		return false;
	}
	if (!isset($_POST['Content'])) {
		foundErr("û��ָ���������ݣ�");
		return false;
	}
		if (isset($_POST["reID"])) {
		$reID = $_POST["reID"];
	}else {
		$reID = 0;
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
				foundErr("����� Re �ı��");
				return false;
		}
		if ($articles[1]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
			return false;
		}
	}
	return true;
}

function 	doPostAritcles($boardID,$boardName,$boardArr,$reID){
	global $_POST;	$ret=bbs_postarticle($boardName,preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['subject']),preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['Content']),intval($_POST['signature']), $reID,intval($_POST['outgo']),intval($_POST['anonymous']));
	switch ($ret) {
		case -1:
			foundErr("��������������ơ�");
			return false;
		case -2:
			foundErr("����Ϊ����Ŀ¼�棡");
			return false;
		case -3:
			foundErr("����Ϊ�ա�");
			return false;
		case -4:
			foundErr("����������Ψ����, ����������Ȩ���ڴ˷������¡�");
			return false;
		case -5:
			foundErr("�ܱ�Ǹ, �㱻������Աֹͣ�˱����postȨ����");
			return false;
		case -6:
			foundErr("���η��ļ������, ����Ϣ��������ԡ�");
			return false;
		case -7:
			foundErr("�޷���ȡ�����ļ�����Ѹ��֪ͨվ����Ա��лл��");
			return false;
		case -8:
			foundErr("���Ĳ��ɻأ�");
			return false;
		case -9:
			foundErr("ϵͳ�ڲ�������Ѹ��֪ͨվ����Ա��лл��");
			return false;
	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">���ĳɹ���</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ذ��������б�<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=board.php?name=<?php echo $boardName; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="index.php">������ҳ</a></li>
<li><a href="board.php?name=<?php   echo $boardName; ?>">����<?php   echo $boardArr['DESC']; ?></a></li>
</ul></td></tr></table>
<?php
}
?>
