<?php

define("ANNOUNCENUMBER",5);

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

$SiteURL="http://bbs.bjsing.net:8081/forum/";

$SiteName="������BBS";

$Banner="pic/ws.jpg";

define ("MAINTITLE","<IMG SRC=\"bar/title.jpg\">");
define("ATTACHMAXSIZE","5242880");
define ("ATTACHMAXTOTALSIZE","20971520");
define("ATTACHMAXCOUNT","20");

$section_nums = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
$section_names = array(
    array("��վϵͳ", "[վ��]"),
    array("ʯ��ѧԺ", "[��У]"),
    array("�ֵ�ԺУ", "[��У][��֯]"),
    array("���Լ���", "[����][��·]"),
    array("��������", "[����][����]"),
    array("ѧ����ѧ", "[����][��ѧ]"),
    array("�����罻", "[��ѧ][����]"),
    array("��������", "[�˶�][����]"),
    array("̸���ĵ�", "[̸��][����]"),
    array("����ת��", "[����][ϵͳ]"),
);

require "default.php";
?>
