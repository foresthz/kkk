<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("ɾ���ʼ�");

requireLoginok();

show_nav();

echo "<br>";
$boxDesc=getMailBoxName($_GET['boxname']);
head_var($userid."��".$boxDesc,"usermailbox.php?boxname=".$_GET['boxname'],0);
main();

show_footer();

function main(){
	global $_GET;
	global $boxDesc;
	$boxName=$_GET['boxname'];
	if (!isset($_GET['file'])) {
		foundErr("����ָ�����ż�������!");
	}
	$file=$_GET['file'];
	if ($boxName=='') {
		$boxName='inbox';
	}
	if (getMailBoxPathDesc($boxName, $path, $desc)) {
		deletemail($boxName, $path, $desc, $file);
	} else {
		foundErr("��ָ���˴�����������ƣ�");
	}
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
		break;
	case -2:
        foundErr("����Ĳ���!");
        break;
	}
	return html_success_quit('����'.$boxDesc, 'usermailbox.php?boxname='.$boxName);
}

?>