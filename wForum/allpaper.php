<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;
global $page;

setStat("С�ֱ��б�");

preprocess();

if (!isset($_POST['action'])) {
	show_nav($boardName);
} else {
	show_nav(false);
}

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
if ($_POST['action']=="delpaper") {
	batch();
	html_success_quit('����С�ֱ��б�', 'allpaper.php?action=batch&board='.$boardName);
} else {
	boardeven($boardID,$boardName);
} 

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $usernum;
	if (!isset($_REQUEST['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_REQUEST['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName, $brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
	}
	return true;
}

function boardeven($boardID,$boardName){
	global $usernum;
	global $conn;
	global $currentuser;
	$isbm = bbs_is_bm($boardID,$usernum);
	$username = $currentuser["userid"];
?>
	<form action=allpaper.php method=post name=paper id="paper">
	<input type="hidden" name="action" value="delpaper" />
	<input type=hidden name=board value="<?php echo $boardName; ?>" />
	<table class=TableBorder1 cellspacing="1" cellpadding="3" align="center">
	<tr align=center> 
	<th width="15%" height=25>�û�</th>
	<th width="35%">����</th>
	<th width="20%">����ʱ��</th>
	<th width="15%">����</th>
	<th width="15%">����</th>
	</tr> 
<?php
	$sql="select * from smallpaper_tb where boardID=".$boardID." order by Addtime desc";

	if ($conn !== false) {
		$sth=$conn->query($sql);
		$totalrec=$sth->numRows();
	} else {
		$totalrec = 0;
	}
	if ($totalrec==0) {
		echo  "<tr> <td class=TableBody1 align=center colspan=5 height=25>���滹û��С�ֱ�</td></tr>";
	} else {
		while($rs=$sth->fetchRow(DB_FETCHMODE_ASSOC)) {
			echo '<tr>';
			echo '<td class=TableBody1 align=center height=24>';
			echo '<a href=dispuser.php?id='.$rs['Owner'].' target=_blank>'.$rs['Owner'].'</a>';
			echo '</td>';
			echo '<td class=TableBody1>';
			echo "<a href=javascript:openScript('viewpaper.php?id=".$rs["ID"]."&boardname=".$boardName."',500,400)>".htmlspecialchars($rs['Title'],ENT_QUOTES).'</a>';
			echo '</td>';
			echo '<td align=center class=TableBody1>'.$rs['Addtime'].'</td>';
			echo '<td align=center class=TableBody1>'.$rs["Hits"].'</td>';
			echo '<td align=center class=TableBody1>';
			if ($isbm || ($username == $rs["Owner"])) echo "<input type=checkbox name=num value=".$rs["ID"].">";
			echo "</td></tr>";
		}
?>
<input type="hidden" id="nums" name="nums">
<script >
function doAction(desc) {
	var nums,s,first,i;
	if(confirm(desc))	{
		objForm=getRawObject("paper");
		objNums=getRawObject("nums");
		objNums.value="";
		first=true;
		nums=getObjectCollection("num");
		for (i=0;i<nums.length;i++){
			s=nums[i];
			if (s.checked) {
				if (first) {
					first=false;
				} else {
					objNums.value+=',';
				}
				objNums.value+=s.value;
			}
		}
		return objForm.submit()
	}
	return false;
}
</script>
<tr><td class=TableBody2 colspan=5 align=right>��ѡ��Ҫɾ����С�ֱ�
<input type=checkbox name=chkall id=chkall value=on onclick="CheckAll(this.form)"><label style="cursor:hand;" for="chkall">ȫѡ</label>
<input type=submit name=Submit value=ɾ��  onclick="doAction('��ȷ��ɾ����ЩС�ֱ���?')">
</td></tr>
<?php
		$rs=null;
	} 
	print "</table></form>";
	return true;
} 

function batch()
{
	global $loginok;
	global $boardID;
	global $usernum;
	global $currentuser;
	global $conn;
	if ($loginok!=1) {
		foundErr("����Ȩɾ��С�ֱ���");
	}
	if ($_POST["nums"]=="") {
		foundErr("��ָ�����С�ֱ���");
	}
	if ($conn === false) {
		foundErr("���ݿ���ϡ�");
	}
	$query = "delete from smallpaper_tb where boardID=".$boardID." and ID in (".$_POST["nums"].")";
	if (!bbs_is_bm($boardID,$usernum)) {
		$query .= " and Owner = '".$currentuser["userid"]."'";
	}
	$conn->query($query);
  	setSucMsg("��ָ����С�ֱ��Ѿ�ɾ����"); // ʵ������ ��Ȩɾ���� С�ֱ� - atppp
  	return true;
} 
?>
