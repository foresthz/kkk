<?php
require("inc/funcs.php");
require("inc/board.inc.php");
require("inc/user.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;

preprocess();

setStat("����С�ֱ�");

show_nav($boardName);

if (isErrFounded()) {
		echo"<br><br>";
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
	main($boardID,$boardName);
}

show_footer();

CloseDatabase();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;

	if ($loginok!=1) {
		foundErr("�οͲ��ܷ���С�ֱ���");
		return false;
	}
	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_GET['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治���ڡ�");
		return false;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����棡");
		return false;
	}
	if (bbs_is_readonly_board($boardArr)) {
			foundErr("����Ϊֻ����������");
			return false;
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�ڱ��淢��С�ֱ���");
		return false;
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
<li>HTML��ǩ��������
<li>UBB ��ǩ������
<li>���ݲ��ó���500��
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
