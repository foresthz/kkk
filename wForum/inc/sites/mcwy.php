<?php

define("ANNOUNCENUMBER",5);

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

define('SERVERTIMEZONE','����ʱ��');

define('USEBROWSCAP', 0);

$SiteURL="http://bbs.stanford.edu/wForum/";

$SiteName="��������";

$HTMLTitle="��������";

$HTMLCharset="GB2312";

$DEFAULTStyle="defaultstyle";

$Banner="/mcwy/bm3_08.jpg";

$BannerURL="http://bbs.stanford.edu";

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
$sectionCount=count($section_names);

$user_define1=array(array(1,"���� IP", "�Ƿ��ĺͱ���ѯ��ʱ�������Լ��� IP ��Ϣ","��������","��ȫ����") /* DEF_HIDEIP */
);

define('SHOWTELNETPARAM',0);

define('ALLOWMULTIQUERY', 1);

require "default.php";
$AnnounceBoard = "Announcement"; //override what's in default.php
?>
