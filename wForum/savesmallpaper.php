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
	global $title;
	global $Content;
	
	$title=trim($_POST["title"]);
	$Content=trim($_POST["Content"]);

	if ($title=="") {
	    foundErr("<br><li>���ⲻӦΪ�ա�");
		return false;
	}
	if (strlen($title)>80) {
		foundErr("<br><li>���ⳤ�Ȳ��ܳ���80");
		return false;
	}
	if ($Content=="") {
		foundErr("<br><li>û����д���ݡ�");
		return false;
	}
	if (strlen($Content)>500) {
		foundErr("<br><li>�������ݲ��ô���500");
		return false;
	} 
	if ($loginok!=1) {
		foundErr("�οͲ��ܷ���С�ֱ���");
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
	global $currentuser;
	global $title;
	global $Content;

    $sql="insert into smallpaper_tb (boardID,Owner,Title,Content,Addtime) values (".$boardID.",'". $currentuser['userid']."','". htmlspecialchars($title, ENT_QUOTES)."','". htmlspecialchars($Content,ENT_QUOTES)."',now())";
	$conn->query($sql);
	setSucMsg("���ɹ��ķ�����С�ֱ���");
  	return html_success_quit('���ذ���', 'board.php?name='.$boardName);
} 

?>