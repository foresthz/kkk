<?php

define('BOARDLISTSTYLE','simplest');

$SiteName="������";

$SiteURL="http://wforum.zixia.net";

$Banner="bar/bar.jpg";

define('OLD_REPLY_STYLE', true); //�Ƿ�ʹ�ô�ͳ telnet re �ķ�ʽ

define('SMS_SUPPORT', true);

define('AUDIO_CHAT', true);

define ("MAINTITLE","<IMG SRC=\"bar/title.jpg\">");

define("ATTACHMAXSIZE","5242880");
define ("ATTACHMAXTOTALSIZE","20971520");
define("ATTACHMAXCOUNT","20");

$section_nums = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
$section_names = array(
    array("�� ͷ ��", "[�ڰ�/ϵͳ]"),
    array("������", "[��/����]"),
    array("��С�ֶ�", "[����/У��]"),
    array("�Ժ�����", "[��ʳ/����]"),
    array("��Ϸ����", "[��Ϸ/����]"),
    array("����Ū��", "[����/�Ļ�]"),
    array("����֮·", "[����/�ۻ�]"),
    array("������ʩ", "[����/��ѵ]"),
    array("��������", "[ʡ��/����]"),
    array("��ʥȡ��", "[רҵ/����]") 
);

require "dbconn.php";
require "default.php";
?>
