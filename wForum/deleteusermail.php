<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("ɾ���ʼ�");

show_nav();

echo "<br>";

$boxDesc=getMailBoxName($_GET['boxname']);

if (!isErrFounded()) {
	head_var($userid."��".$boxDesc,"usermailbox.php?boxname=".$_GET['boxname'],0);
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
	global $_GET;
	global $boxDesc;
	$boxName=$_GET['boxname'];
	if (!isset($_GET['file'])) {
		foundErr("����ָ�����ż�������!");
		return false;
	}
	$file=$_GET['file'];
	if ($boxName=='') {
		$boxName='inbox';
	}
	if (getMailBoxPathDesc($boxName, $path, $desc)) {
		deletemail($boxName, $path, $desc, $file);
		return true;
	}
	foundErr("��ָ���˴�����������ƣ�");
	return false;
}

function deletemail($boxName, $boxPath, $boxDesc, $filename){
	global $currentuser;
	$ret = bbs_delmail($boxPath,$filename);
	switch($ret){
	case 0:
		setSucMsg("�ʼ��ѳɹ�ɾ����");
		break;
	case -1:
	    foundErr("����ָ���ż�������, �޷�ɾ����");
		return false;
		break;
	case -2:
        foundErr("����Ĳ���!");
		return false;
        break;
	}
	return html_success_quit('����'.$boxDesc, 'usermailbox.php?boxname='.$boxName);
}

?>