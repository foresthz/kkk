<?php
define("ATTACHTMPPATH","boards/_attach");
function getattachtmppath($userid,$utmpnum)
{
	return ATTACHTMPPATH . "/" . $userid . "_" . $utmpnum;
}

define("ATTACHMAXSIZE","1048576");
define("ATTACHMAXCOUNT","20");
$section_nums = array("0", "1", "3", "4", "5", "6", "7", "8", "9");
$section_names = array(
    array("BBS ϵͳ", "[��վ]"),
    array("���־ۻ�", "[Ժϵ][Э��][����]"),
    array("���Լ���", "[����][ϵͳ][��·]"),
    array("������Ϸ", "[��Ϸ]"),
    array("�����Ļ�", "[����][����][ѧ��]"),
    array("ת��ר��", "[ת��]"),
    array("��������", "[����][����][����]"),
    array("֪�Ը���", "[����][����]"),
    array("����ʱ��", "[����][�ؿ�][��Ϣ]")
);

$order_articles = TRUE;
$default_dir_mode = $dir_modes["ORIGIN"];
?>
