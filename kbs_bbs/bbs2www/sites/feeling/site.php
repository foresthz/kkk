<?php
function getattachtmppath($userid,$utmpnum)
{
  $attachdir="cache/home/" . strtoupper(substr($userid,0,1)) . "/" . $userid . "/" . $utmpnum . "/upload";
  return $attachdir;
}

define("ATTACHMAXSIZE","2097152");
define("ATTACHMAXCOUNT","3");
$section_nums = array("0", "1", "2", "3", "4", "5", "6", "7", "8");
$section_names = array(
    array("��ܰС��", "[��վ][ϵͳ]"),
    array("У԰����", "[У԰][Э��]"),
    array("BBS ����", "[�汾][����][ת��]"),
    array("���Լ���", "[���][����][ϵͳ]"),
    array("ѧ����ѧ", "[ѧϰ][����]"),
    array("�Ļ�����", "[��ѧ]"),
    array("��������", "[����][����][����]"),
    array("֪�Ը���", "[����][����]"),
    array("��������", "[����][����][��Ϣ]")
);
?>
