<?php
$Version="Powered by wForum Version 0.9";
$Copyright="<a href='http://www.aka.cn/' target=_blank>������Ϣ����(����)���޹�˾</a> & <a href='http://www.smth.cn'>ˮľ�廪BBS</a> ��Ȩ���� 1994 - 2008 <br><font face=Verdana, Arial, Helvetica, sans-serif size=1><b>Roy<font color=#CC0000>@zixia.net</font></b></font> ";

$AnnounceBoard="Announce";
if (!defined('BOARDLISTSTYLE')) { 
/* �����б�ʽ
 * default ȱʡ
 * simplest ����ʾ�б�
 */
	define('BOARDLISTSTYLE','default');
}

if (!defined('ANNOUNCENUMBER')) {   //��ҳ������ʾ�Ĺ�������
	define('ANNOUNCENUMBER',5);
}

if (!defined('ARTICLESPERPAGE')) {
	define('ARTICLESPERPAGE',30); //Ŀ¼�б���ÿҳ��ʾ��������
}

if (!defined('USERSPERPAGE')) {
    define("USERSPERPAGE", 20); //�����û�/���ѵ��б�ÿҳ��ʾ������
}

if (!defined('THREADSPERPAGE')) { //ͬ�����Ķ�ÿҳ������
	define('THREADSPERPAGE',10);
}

if (!defined('MAINTITLE')) { //����ͼƬ
	define ('MAINTITLE','<IMG SRC="bar/title.jpg">');
}

if (!defined('ATTACHMAXSIZE')) { //��󸽼��ߴ磬��Ҫͬʱ�޸�php.ini���upload_max_size ,��λbyte
	define('ATTACHMAXSIZE','524288');
}

if (!defined('ATTACHMAXTOTALSIZE')) { //һƪ���������и������ܴ�С����, ��λbyte
	define('ATTACHMAXTOTALSIZE','2097152');
}

if (!defined('ATTACHMAXCOUNT')) { //һƪ�������������صĸ�������
	define('ATTACHMAXCOUNT','5');
}

if (!defined('SERVERTIMEZONE')) { //������ʱ�� - ��ʱ������� - atppp
	define('SERVERTIMEZONE','����ʱ��');
}

if (!defined('USEBROWSCAP')) { //�Ƿ�ʹ�� browscap ��������׼ȷ���ж�������Ͳ���ϵͳ���ͣ���Ҫ���� PHP - atppp
	define('USEBROWSCAP',0); //Ĭ�� OFF
}

if (!defined('SHOWTELNETPARAM')) { //�Ƿ��������� telnet ��ר�õĸ��˲��� - atppp
	define('SHOWTELNETPARAM',1); //Ĭ�� ON�����ֺ���ǰ�ļ�����
}

if (!defined('MYFACEDIR')) { //�Զ���ͷ���ϴ�����λ��
	define('MYFACEDIR','uploadFace/');
}

if (!defined('MYFACEMAXSIZE')) { //�Զ���ͷ������ļ���С, ��λbyte
	define('MYFACEMAXSIZE','524288');
}

?>
