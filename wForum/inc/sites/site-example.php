<?php

define("ANNOUNCENUMBER",5);

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

define('SERVERTIMEZONE','����ʱ��');

$SiteURL="http://172.16.50.79";

$SiteName="���˴�BBS";

$HTMLTitle="���˴�BBS";

$HTMLCharset="GB2312";

$DEFAULTStyle="defaultstyle";

$Banner="pic/ws.jpg";

$BannerURL="http://172.16.50.79";

define ("MAINTITLE","<IMG SRC=\"bar/title.jpg\">");
define("ATTACHMAXSIZE","5242880");
define ("ATTACHMAXTOTALSIZE","20971520");
define("ATTACHMAXCOUNT","20");

$section_nums = array("0", "1", "2", "3", "4");
$section_names = array(
    array("BBS ϵͳ", "[ϵͳ]"),
    array("У԰��Ϣ", "[��Ϣ]"),
    array("���Լ���", "[����]"),
    array("ѧ����ѧ", "[ѧϰ]"),
    array("��������", "[����]")
);
$sectionCount=count($section_names);

define('SHOWTELNETPARAM', 0); //���������� telnet ��ר�õĸ��˲���
define('SHOWREPLYTREE', 1);  //����ͼ��ʾ�ظ��ṹ
define('ALLOWMULTIQUERY', 1); //����ȫվ/������ѯ

require "default.php";
?>
