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

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
main($boardID,$boardName);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;

	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['board'];
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

	$conn->query("delete from smallpaper_tb where Addtime<subdate(Now(),interval 2 day)");
?>
<form action="savesmallpaper.php" method="post"> 
<input type="hidden" name="action" value="savepaper">
    <table cellpadding=6 cellspacing=1 align=center class=TableBorder1>
    <tr>
    <th valign=middle colspan=2>
    ����С�ֱ�</th></tr>
    <td class=TableBody1 valign=middle><b>�� ��</b>(���30��)</td>
    <td class=TableBody1 valign=middle><INPUT name="title" type=text size=60></td></tr>
    <tr>
    <td class=TableBody1 valign=top width=30%>
<b>�� ��</b><BR>
<!--
�ڱ��淢��С�ֱ���������<font color="<?php   echo $Forum_body[8]; ?>"><b><?php   echo $GroupSetting[46]; ?></b></font>Ԫ����<br>
-->
<font color=#ff0000><b>48</b></font>Сʱ�ڷ����С�ֱ��������ȡ<font color="#ff0000"><b>5</b></font>��������ʾ����̳��<br>
<ul>
<li>HTML��ǩ��������</li>
<li>UBB ��ǩ������</li>
<li>���ݲ��ó���500��</li>
</ul>
</td>
<td class=TableBody1 valign=middle>
<textarea class="smallarea" cols="60" name="Content" rows="8" wrap="VIRTUAL"></textarea>
<INPUT name="board" type=hidden value="<?php   echo $boardName; ?>">
                </td></tr>
    <tr>
    <td class=TableBody2 valign=middle colspan=2 align=center><input type=submit name="submit" value="�� ��"></td></tr></table>
</form>
<?php   
} 

?>
