<?php

define('ANNOUNCENUMBER',5);

define('ARTICLESPERPAGE',30); //Ŀ¼�б���ÿҳ��ʾ��������

define('THREADSPERPAGE',10); //�����Ķ�ʱÿҳ��ʾ��������

$SiteURL="http://172.16.50.79/";

$SiteName="���˴�BBS";

$Banner="pic/ws.jpg";

define ('MAINTITLE','<img src="bar/title.jpg" />');
define('ATTACHMAXSIZE','5242880');
define ('ATTACHMAXTOTALSIZE','20971520');
define('ATTACHMAXCOUNT','20');

$section_nums = array("0", "1", "2", "3", "4");
$section_names = array(
    array("BBS ϵͳ", "[ϵͳ]"),
    array("У԰��Ϣ", "[��Ϣ]"),
    array("���Լ���", "[����]"),
    array("ѧ����ѧ", "[ѧϰ]"),
    array("��������", "[����]")
);

$FooterBan='<table cellSpacing=0 cellPadding=0 border=0 align=center><tr><td width=100 align=center><a href="http://www.turckware.ru/en/e_mmc.htm#download" target="_blank"><img border="0" src="/images/copyright/mmcache02.gif"></a></td><td width=100 align=center><a href="http://www.php.net/" target="_blank"><img border="0" src="/images/copyright/php-small-trans-light.gif"></a></td><td width=100 align=center><a href="http://www.apache.org/" target="_blank"><img border="0" src="/images/copyright/apache_pb.gif"></a></td><td width=100 align=center><a href="http://www.redhat.com/" target="_blank"><img border="0" src="/images/copyright/poweredby.png"></a></td><td width=100 align=center><a href="http://www.mysql.com/" target="_blank"><img border="0" src="/images/copyright/mysql-17.gif"></a></td></tr></table>';

require "default.php";
?>
