<?php

//$AnnounceBoard="Announce"; //�������

define("ANNOUNCENUMBER",5);  //��ҳ������ʾ�Ĺ�������

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

define("BOARDS_PER_ROW", 3); //�۵������б�ÿ�а�����Ŀ

//define("SECTION_DEF_CLOSE", false); //��ҳ���������б���Ĭ��������Ƿ�ر�

define ('MAINTITLE','<img src="bar/title.jpg" />'); //ҳ�����Ϸ���ʾ��վ�����

define('OLD_REPLY_STYLE', true); //ʹ�ô�ͳ telnet re �ķ�ʽ

define("ENABLE_UBB", true); //�Ƿ�֧�� UBB

define('SHOW_REGISTER_TIME', false); //�Ƿ���ʾ�û�ע��ʱ��

/* ������ÿ�����ߴ磬�����ߴ磬��������� */
define("ATTACHMAXSIZE","5242880");
define ("ATTACHMAXTOTALSIZE","20971520");
define("ATTACHMAXCOUNT","20");

define('SERVERTIMEZONE','����ʱ��'); //������ʱ��
//define("SHOW_SERVER_TIME", true); //��ʾ������ʱ��

define('SHOWTELNETPARAM', 0); //�Ƿ��������� telnet ��ר�õĸ��˲���

//define('MYFACEDIR','uploadFace/'); //�Զ���ͷ���ϴ�����λ��

//define('MYFACEMAXSIZE','524288'); //�Զ���ͷ������ļ���С, ��λbyte

define('SHOWREPLYTREE', 1);  //�Ƿ�����ͼ��ʾ�ظ��ṹ

define('ALLOWMULTIQUERY', false); //�Ƿ�����һ���û�����ȫվ/������ѯ
define('ALLOW_SYSOP_MULTIQUERY', false); //�Ƿ��������Ա����ȫվ/������ѯ
define('ALLOW_SELF_MULTIQUERY', false); //�Ƿ�����ȫվ��ѯ�Լ����������

//define('AUTO_KICK', false); //һ���û���¼����ʱ���Զ����ǣ�������ʾ�����߳���ǰ�ĵ�¼

//define('SHOW_POST_UNREAD', true); //���������б��Ƿ���ʾ new ��δ����־

//define('SMS_SUPPORT', 0); //�Ƿ������ֻ�����

//define('USER_FACE', 1); //�Ƿ������Զ���ͷ��

//define('AUDIO_CHAT', 0); //�Ƿ���ʾ���������ҵ� link
	
$SiteURL="http://172.16.50.79"; //վ�� URL��Ҳ���Ǳ�վ��ҳ��ַ

$SiteName="���˴�BBS";   //վ������

$Banner="pic/ws.jpg"; //ҳ�����Ͻ���ʾ��ͼƬ

/* �������źͷ������� */
$section_nums = array("0", "1", "2", "3", "4");
$section_names = array(
    array("BBS ϵͳ", "[ϵͳ]"),
    array("У԰��Ϣ", "[��Ϣ]"),
    array("���Լ���", "[����]"),
    array("ѧ����ѧ", "[ѧϰ]"),
    array("��������", "[����]")
);


/* ���ݿ����� */
$dbhost='localhost';
$dbuser='';
$dbpasswd='';
$dbname='';

require "default.php";
?>
