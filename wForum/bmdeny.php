<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;

setStat("����������");

requireLoginok();

preprocess();

$processed = processAct();

show_nav($processed ? false : $boardName);

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);

if ($processed) {
	html_success_quit("���ط������", "bmdeny.php?board=" . $boardName);
} else {
	showDenys();
}

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	if (!isset($_GET['board']))	{
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
	if (bbs_checkreadperm($usernum,	$boardID) == 0)	{
		foundErr("����Ȩ�Ķ�����");
	}
	if (!bbs_is_bm($boardID, $usernum))
		foundErr("�㲻�ǰ���");
	return true;
}

/* return if we	did	any	process	*/
function processAct() {
	global $boardName;
	if (!isset($_GET['act'])) return false;
	switch ($_GET['act']) {
		case 'del':
			$userid = trim($_GET['userid']);
			if (!$userid)
				foundErr("���������û���ID");
			switch (bbs_denydel($boardName,$userid)) {
				case -1:
				case -2:
					foundErr("����������");
					break;
				case -3:
					foundErr($userid." ���ڷ���б���");
					break;
				default:
					setSucMsg("$userid �ѱ����");
					return true;
			}
			break;
		case 'add':
			$userid = trim($_POST['userid']);
			$denyday = intval($_POST['denyday']);
			$exp = trim($_POST['exp']);
			if (!$userid)
				foundErr("���������û���ID");
			if (!$denyday)
				foundErr("��������ʱ��");
			if (!$exp)
				foundErr("������������");
			if (!strcasecmp($userid,'guest') || !strcasecmp($userid,'SYSOP'))
				foundErr("���ܷ�� ".$userid);
			switch (bbs_denyadd($boardName,$userid,$exp,$denyday,0)) {
				case -1:
				case -2:
					foundErr("����������");
					break;
				case -3:
					foundErr("����ȷ��ʹ����ID");
					break;
				case -4:
					foundErr("�û� ".$userid." ���ڷ���б���");
					break;
				case -5:
					foundErr("���ʱ�����");
					break;
				case -6:
					foundErr("������������");
					break;
				default:
					setSucMsg("$userid �ѱ�����������");
					return true;
			}
			break;
		default:
	}
	return false;
}

function showDenys() {
	global $boardName;
	global $currentuser;
	$denyusers = array();
	$ret = bbs_denyusers($boardName,$denyusers);
	switch ($ret) {
	case -1:
		foundErr("ϵͳ��������ϵ����Ա");
		break;
	case -2:
		foundErr("�����������");
		break;
	case -3:
		foundErr("������Ȩ��");
		break;
	default:    
	}

	$maxdenydays = ($currentuser["userlevel"]&BBS_PERM_SYSOP)?70:14;
	$brd_encode = urlencode($boardName);
?>
<br>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>���</th>
<th valign=middle width=100>�û��˺�</th>
<th valign=middle width=*>����</th>
<th valign=middle width=120>˵��</th>
<th valign=middle width=40>���</th>
</tr>
<?php
	$i = 1;
	foreach ($denyusers as $user) {
?>
<tr>
<td class="TableBody1" align="center"><?php echo $i++; ?></td>
<td class="TableBody1" align="center"><a href="dispuser.php?id=<?php echo $user['ID']; ?>"><?php echo $user['ID']; ?></a></td>
<td class="TableBody1"><?php echo htmlspecialchars($user['EXP']); ?></td>
<td class="TableBody1" align="center"><?php echo htmlspecialchars($user['COMMENT']); ?></td>
<td class="TableBody1" align="center"><a onclick="return confirm('ȷ�����<?php echo $user['ID']; ?>��?')" href="bmdeny.php?board=<?php echo $brd_encode; ?>&act=del&userid=<?php echo $user['ID']; ?>">���</a></td>
</tr>
<?php
	}
?>
<tr>
<td align=right valign=middle colspan=5 class=TableBody2>
���湲����� <b><?php echo count($denyusers); ?></b> λ�û���
</td>
</tr>
</table><br />
<form action="bmdeny.php?act=add&board=<?php echo $brd_encode; ?>" method="post" name="adddeny">
<table align="center">
<tr>
	<td>��ӷ���û�: �û���<input type="text" name="userid" size="12" value="<?php
	    if (isset($_GET["userid"])) echo htmlspecialchars($_GET["userid"], ENT_QUOTES); ?>" maxlength="12" />�����ʱ��
	<select name="denyday">
<?php
	$i = 1;
	while ($i <= $maxdenydays) {
		echo '<option value="'.$i.'">'.$i.'</option>';
		$i += ($i >= 14)?7:1;
	}    
?>    
	</select>�� <input type="submit" value="��ӷ��" />
	</td>
</tr>
<tr>
	<td>���ԭ��
<script language="JavaScript">
<!--
	function setReason(sReason) {
		if (sReason) {
			document.adddeny.exp.value = sReason;
			document.adddeny.exp.focus();
		}
	}
//-->
</script>
	<select name="explist" onchange="setReason(this.options[this.selectedIndex].value)">
<option value="" selected="selected">ѡ��Ԥ����������</option>
<?php
$denyreasons = array(
        '��ˮ',
        '���Ű�����������',
        '�������',
        '������',
        '�����޹ػ���',
        '����ǡ������',
        'test(�����Է��ʹ��)'
);
	foreach ($denyreasons as $reason) {
		echo '<option value="'.htmlspecialchars($reason).'">'.htmlspecialchars($reason).'</option>';
	}
?>    
	</select>
	<input type="text" name="exp" size="28" maxlength="28" />
	</td>
</tr>
</table></form>
<?php
}
?>
