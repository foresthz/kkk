<?php
define("ATTACHTMPPATH","boards/_attach");
function getattachtmppath($userid,$utmpnum)
{
	return ATTACHTMPPATH . "/" . $userid . "_" . $utmpnum;
}

define("ATTACHMAXSIZE","1048576");
define("ATTACHMAXCOUNT","20");
define("HAVE_PC", 1); // ֧�ָ����ļ�
define("MAINPAGE_FILE","mainpage.php"); // ��ʹ�þ�̬ mainpage ҳ��
$section_nums = array("0", "1", "3", "4", "5", "6", "7", "8", "9");
$section_names = array(
	array("��վϵͳ", "[HAPPYBBSϵͳ��]"),
	array("�������", "[WWW][FTP][У԰��]"),
	array("Ⱥ����֯", "[Ժϵ][Э��][����]"),
	array("���˿ռ�", "[��] [��] [��] [��]"),
	array("�����Ļ�", "[����][����][ѧ��]"),
	array("���Լ���", "[����][ϵͳ][��·]"),
	array("��������", "[����][����][��Ϸ]"),
	array("֪�Ը���", "[����][����]"),
	array("��������", "[δ����]")
);

$order_articles = TRUE;
$default_dir_mode = $dir_modes["ORIGIN"];
?>
