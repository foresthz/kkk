<?php

define("ANNOUNCENUMBER",5);

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

$SiteURL="http://192.168.0.11:8080/wForum/";

$SiteName="����վ";

$HTMLTitle="BBS ����վվ";

$Banner="bar/bar.jpg";

define('SMS_SUPPORT', 0);

define ("MAINTITLE","<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0\" height=\"84\" width=\"600\"> <param name=\"MOVIE\" value=\"bar/smth2.swf\" /><embed SRC=\"bar/smth2.swf\" height=\"84\" width=\"600\"></embed></object>");

define("ATTACHMAXSIZE","2097152");
define("ATTACHMAXCOUNT","3");
$section_nums = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9","A","B","C");
$section_names = array(
    array("BBS ϵͳ", "[վ��]"),
    array("�廪��ѧ", "[��У]"),
    array("ѧ����ѧ", "[ѧ��/����]"),
    array("��������", "[����/����]"),
    array("�Ļ�����", "[�Ļ�/����]"),
    array("�����Ϣ", "[���/��Ϣ]"),
    array("��Ϸ���", "[��Ϸ/����]"),
    array("��������", "[�˶�/����]"),
    array("֪�Ը���", "[̸��/����]"),
    array("������Ϣ", "[����/��Ϣ]"),
    array("�������", "[����/����]"),
    array("����ϵͳ", "[ϵͳ/�ں�]"),
    array("���Լ���", "[ר���]")
);

define('COOKIE_PREFIX', '');
define('COOKIE_PATH', '/');

require "default.php";
?>
