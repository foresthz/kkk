<?php
/* �����ܹ������ȷŸ�����ģ�� - atppp */
/* maybe this array should be put into .inc later... - atppp */
$bbsman_modes = array(
    "DEL"   => 1,
    "MARK"  => 2,
    "DIGEST"=> 3,
    "NOREPLY" => 4,
    "ZHIDING" => 5
    );

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

setStat("��������");

requireLoginok("�οͲ����л����¡�");

preprocess();

show_nav();

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
doSwitchAritcles($boardID,$boardName,$boardArr,$reID,$reArticles);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
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
	if (isset($_GET["ID"])) {
		$reID = $_GET["ID"];
	}else {
		foundErr("δָ���л�������.");
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("��������±��");
		}
	}
	$reArticles=$articles;
	return true;
}

function doSwitchAritcles($boardID,$boardName,$boardArr,$reID,$reArticles){
	global $bbsman_modes;
	$ret=bbs_bmmanage($boardName,$reID,$bbsman_modes["MARK"],0);
	switch ($ret) {
        case -2:
            foundErr('����Ȩ�л�����');
        case -1:
        case -3:
        case -9:
            foundErr('ϵͳ����'.$ret);
        case -4:
            foundErr('����ID����');
        default:  

	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">�����л��ɹ�</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ظ�����<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=disparticle.php?boardName=<?php echo $boardName; ?>&ID=<?php echo $reID; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="index.php">������ҳ</a></li>
<li><a href="board.php?name=<?php   echo $boardName; ?>">����<?php   echo $boardArr['DESC']; ?></a></li>
<li><a href="disparticle.php?boardName=<?php echo $boardName; ?>&ID=<?php echo $reID; ?>">���ظ�����</a></li>
</ul></td></tr></table>
<?php
}
?>
