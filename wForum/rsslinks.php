<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;

if (!RSS_SUPPORT) exit;

preprocess();

setStat("���� RSS");

show_nav($boardName);

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
main($boardName);
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName, $brdArr);
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
	}
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardArr['FLAG'] & BBS_BOARD_GROUP ) {
		foundErr("ָ���İ��治����");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
	}
	return true;
}

function main($boardName) {
	global $SiteURL;
?>
<table cellPadding="1" cellSpacing="1" align="center" class="TableBorder1" style="table-layout:fixed;word-break:break-all;">
<tr><th height="25" width="100%"><?php echo $boardName; ?> �� RSS Feeds</th></tr>
<tr><td width="100%" class="TableBody1" style="padding: 10px;">
<?php
	$urls = array(
			array("������� 20 ƪ����",
				"���������ݣ�����ָ�������Ķ������� Firefox live bookmark ��", "rss.php?board=$boardName",
				"���������ݣ�����ָ������Ķ������� Thunderbird �����Ķ���", "rss.php?board=$boardName&amp;lw=1",
				"�������ݣ�����ָ�������Ķ������� Sage��FeedDemon ��", "rss.php?board=$boardName&amp;ic=1"),
			array("��ժ����� 20 ƪ",
				"���������ݣ�����ָ����ժ�Ķ������� Firefox live bookmark ��", "rss.php?board=$boardName&amp;ftype=1",
				"���������ݣ�����ָ������Ķ������� Thunderbird �����Ķ���", "rss.php?board=$boardName&amp;lw=1&amp;ftype=1",
				"�������ݣ�����ָ����ժ�Ķ������� Sage��FeedDemon ��", "rss.php?board=$boardName&amp;ic=1&amp;ftype=1"),
			array("ȫվʮ��",
				"���������ݣ�����ָ������Ķ������� Thunderbird �����Ķ���", "rsstopten.php")
				);
	$str = "<ul>";
	foreach ($urls as $url) {
		$str .= "<li>".$url[0]."<ul>";
		for ($i = 1; $i < count($url); $i+=2) {
			$str .= "<li><p>".$url[$i].":<br/> <a href=\"$SiteURL".$url[$i+1]."\" target=\"_blank\">$SiteURL".$url[$i+1]."</a></p></li>";
		}
		$str .= "</ul></li>";
	}
	$str .= "</ul>";
	echo $str;
?>
</td></tr></table>
<?php
}
?>