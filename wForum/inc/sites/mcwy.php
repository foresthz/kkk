<?php

define('SERVERTIMEZONE','����ʱ��');

$SiteURL="http://bbs.stanford.edu/wForum/";

$SiteName="��������";

$Banner="/mcwy/bm3_08.jpg";

$AnnounceBoard = "Announcement";

define ("MAINTITLE","&nbsp;");
define("ATTACHMAXSIZE","8388608");
define ("ATTACHMAXTOTALSIZE","8388608");
define("ATTACHMAXCOUNT","20");

$section_nums = array("0", "1", "2", "3", "4", "5", "6");
$section_names = array(
    array("��վϵͳ", "[��վ]"),
    array("��������", "[У԰][��ҵ]"),
    array("������", "[��ѧ][ѧУ][����]"),
    array("ѧ������", "[ѧ��][��ѧ][����]"),
    array("��������", "[����][����][����]")
);

$user_define1=array(array(1,"���� IP", "�Ƿ��ĺͱ���ѯ��ʱ�������Լ��� IP ��Ϣ","��������","��ȫ����") /* DEF_HIDEIP */
);

define('SHOWTELNETPARAM',0);

define('ALLOWMULTIQUERY', 1);

//$dbhost='localhost';
$dbuser='wForum';
$dbpasswd='fuckatp';
$dbname='wForum';

require "default.php";
?>
