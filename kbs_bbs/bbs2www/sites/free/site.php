<?php
define("ATTACHTMPPATH","boards/_attach");
function getattachtmppath($userid,$utmpnum)
{
  return ATTACHTMPPATH . "/" . $userid . "_" . $utmpnum;
}

define("ATTACHMAXSIZE","1048576");
define("ATTACHMAXCOUNT","20");
define("MAINPAGE_FILE","mainpage.html");

$section_nums = array(
		"ab", "cd", "ef", "gh", "ij", "kl", "mn", "opq"
				);
$section_names = array(
    array("��վϵͳ", "[վ��]"),
    array("���ǵļ�", "[��У]"),
    array("��������", "[ѧ��/����]"),
    array("���Լ���", "[����/����]"),
    array("ѧ����ѧ", "[�Ļ�/����]"),
    array("��������", "[���/��Ϣ]"),
    array("֪�Ը���", "[̸��/����]"),
    array("��������", "[�˶�/����]")
);
?>
