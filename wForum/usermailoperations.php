<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

if ($_POST['action']=='lock') {
	setStat("�����ʼ�");
} else {
	setStat("ɾ���ʼ�");
}

show_nav();

echo "<br>";

$boxDesc=getMailBoxName($_POST['boxname']);

if (!isErrFounded()) {
	head_var($userid."��".$boxDesc,"usermailbox.php?boxname=".$_POST['boxname'],0);
}
if ($loginok==1) {
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}


if (isErrFounded()) {
		html_error_quit();
} 
show_footer();

function main(){
	global $_POST;
	global $boxDesc;
	if (!isset($_POST['boxname'])) {
		foundErr("��û��ָ������������!");
		return false;
	}
	$boxName=$_POST['boxname'];
	if (!isset($_POST['nums'])) {
		foundErr("����ָ�����ż�������!");
		return false;
	}
	$action=$_POST['action'];
	if ($action=='deleteAll') {
		if ($boxName=='inbox') {

			deleteAllMails('inbox','.DIR','�ռ���');
			return true;
		}
		if ($boxName=='sendbox') {

			deleteAllMails('sendbox','.SENT','������');
			return true;
		}
		if ($boxName=='deleted') {
			deleteAllMails('deleted','.DELETED','������');
			return true;
		}
		foundErr("��ָ���˴�����������ƣ�");
		return false;
	}
	if ($action=='delete'){
		if ($_POST['nums'] == "") {
			foundErr("��û��ָ���ż���");
			return false;			
		}
		$nums=split(',',$_POST['nums']);
		if ($boxName=='inbox') {

			deleteMails('inbox','.DIR','�ռ���', $nums);
			return true;
		}
		if ($boxName=='sendbox') {

			deleteMails('sendbox','.SENT','������',$nums );
			return true;
		}
		if ($boxName=='deleted') {
			deleteMails('deleted','.DELETED','������',$nums);
			return true;
		}
		foundErr("��ָ���˴�����������ƣ�");
		return false;
	}
	if ($action=='lock'){
		if ($_POST['nums'] == "") {
			foundErr("��û��ָ���ż���");
			return false;			
		}
		$nums=split(',',$_POST['nums']);
		if ($boxName=='inbox') {

			lockMails('inbox','.DIR','�ռ���', $nums);
			return true;
		}
		if ($boxName=='sendbox') {

			lockMails('sendbox','.SENT','������',$nums );
			return true;
		}
		if ($boxName=='deleted') {
			lockMails('deleted','.DELETED','������',$nums);
			return true;
		}
		foundErr("��ָ���˴�����������ƣ�");
		return false;
	}
	foundErr("��������");
	return false;
}

function deleteMails($boxName, $boxPath, $boxDesc, $nums){
	global $currentuser;
	$dir = bbs_setmailfile($currentuser["userid"],$boxPath);

	$total = filesize( $dir ) / 256 ;
	if( $total <= 0 ){
		foundErr("����ָ�����ż������ڡ�");
		return false;
	}
	$mailnum=count($nums);

	for ($i=0;$i<$mailnum;$i++) {
		if( $articles=bbs_getmails($dir, intval($nums[$i]), 1) ) {
			if (strtoupper($articles[0]["FLAGS"][0])!='M') {
				$ret = bbs_delmail($boxPath, $articles[0]["FILENAME"]);
			}
		}
	}
	setSucMsg("�ʼ��ѳɹ�ɾ����");
	return html_success_quit('����'.$boxDesc, 'usermailbox.php?boxname='.$boxName);
}

function deleteAllMails($boxName, $boxPath, $boxDesc) {
	global $currentuser;
	$dir = bbs_setmailfile($currentuser["userid"],$boxPath);
	$mailnum = bbs_getmailnum2($dir);
	if( $mailnum <= 0 ){
		setSucMsg("�ʼ��ѳɹ�ɾ����");
		return true;
	}
	$articles=bbs_getmails($dir, 0, $mailnum);
	for ($i=0;$i<$mailnum;$i++) {
		if (strtoupper($articles[$i]["FLAGS"][0])!='M') {
			bbs_delmail($boxPath,$articles[$i]["FILENAME"]);
		}
	}
	setSucMsg("�ʼ��ѳɹ�ɾ����");
	return html_success_quit('����'.$boxDesc, 'usermailbox.php?boxname='.$boxName);
}

function lockMails($boxName, $boxPath, $boxDesc, $nums){
	
	setSucMsg("��������δʵ��:((");
	return html_success_quit('����'.$boxDesc, 'usermailbox.php?boxname='.$boxName);
}
?>