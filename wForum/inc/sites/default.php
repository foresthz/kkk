<?php
$Version="Powered by wForum Version 0.9";
$Copyright="<a href='http://www.aka.cn/' target=_blank>������Ϣ����(����)���޹�˾</a> & <a href='http://www.smth.cn'>ˮľ�廪BBS</a> ��Ȩ���� 1994 - 2008 <br><font face=Verdana, Arial, Helvetica, sans-serif size=1><b>Roy<font color=#CC0000>@zixia.net</font></b></font> ";

if (!isset($AnnounceBoard)) { //�������
	$AnnounceBoard="Announce";
}

$HTMLCharset = "GB2312"; //����Ҿ��ÿ϶��� gb2312 �ɣ��Ժ�Ҫ��Ļ������ɿ��Զ���Ĳ���

if (!isset($HTMLTitle)) {
	$HTMLTitle = $SiteName;
}

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
	define('USEBROWSCAP',0); //Ĭ�� OFF. ���ó� ON ��Ȼ��׼ȷ�жϳ��� IE ����������ǻ��󽵵ͳ���ҳ���ٶȣ����ã�//ToDo: ���û�з��� site_defines.php
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

if (!defined('SHOWREPLYTREE')) { //�Ƿ�����ͼ��ʾ�ظ��ṹ
	define('SHOWREPLYTREE', 1);  //Ĭ������ͼ��ʾ
}

if (!defined('ALLOWMULTIQUERY')) { //�Ƿ�����ȫվ/������ѯ
	define('ALLOWMULTIQUERY', 0); //Ĭ�Ϲر�
}

if (!defined('SMS_SUPPORT')) { //�Ƿ������ֻ����ţ�ToDo: ��ʵ����� phplib ������� #define SMS_SUPPORT �Ĳ���
	define('SMS_SUPPORT', 0); //Ĭ�Ϲر�
}

if (!defined('USER_FACE')) { //�Ƿ������Զ���ͷ��
	define('USER_FACE', 1); //Ĭ�Ͽ���
}

if (!defined('AUDIO_CHAT')) { //�Ƿ���ʾ���������ҵ� link
	define('AUDIO_CHAT', 0); //Ĭ�Ϲر�
}

if (!defined('OLD_REPLY_STYLE')) { //�Ƿ�ʹ�ô�ͳ telnet re �ķ�ʽ
	define('OLD_REPLY_STYLE', true); //Ĭ�Ϲر�
}

if (!defined('SHOW_REGISTER_TIME')) { //�Ƿ���ʾ�û�ע��ʱ��
	define('SHOW_REGISTER_TIME', false); //Ĭ�Ϲر�
}

/* ��ʽ��ÿ�������������һ�� 0 ��ʾ telnet ��ר�ò������ڶ����ǲ������ƣ��������ǲ���������ͣ��������ǲ��� ON �� OFF ������ľ��庬�� */
if (!isset($user_define)) {
	$user_define=array(array(0,"��ʾ�����", "��telnet��ʽ���Ƿ���ʾ�����","��ʾ","����ʾ"), /* DEF_ACBOARD */
	    array(0,"ʹ�ò�ɫ", "��telnet��ʽ���Ƿ�ʹ�ò�ɫ��ʾ", "ʹ��", "��ʹ��"),                /* DEF_COLOR */
	    array(0, "�༭ʱ��ʾ״̬��","��telnet��ʽ�±༭����ʱ�Ƿ���ʾ״̬��", "��ʾ","����ʾ"),         /* DEF_EDITMSG */
	    array(0,"������������ New ��ʾ", "��telnet��ʽ���Ƿ���δ����ʽ�Ķ�����������", "��", "��"),    /* DEF_NEWPOST */
	    array(0,"ѡ����ѶϢ��","��telnet��ʽ���Ƿ���ʾѡ��ѶϢ��","��ʾ","����ʾ"),             /* DEF_ENDLINE */
	    array(0,"��վʱ��ʾ��������","��telnet��ʽ����վʱ�Ƿ���ʾ������������","��ʾ","����ʾ"),       /* DEF_LOGFRIEND */
	    array(1,"�ú��Ѻ���","���������ر�ʱ�Ƿ�������Ѻ���","��", "��"),               /* DEF_FRIENDCALL */
	    array(0, "ʹ���Լ�����վ����", "telnet��ʽ���Ƿ�ʹ���Լ�����վ����","��", "��"),      /* DEF_LOGOUT */
	    array(0, "��վʱ��ʾ����¼", "telnet��ʽ�½�վʱ�Ƿ���ʾ����¼", "��", "��"),        /* DEF_INNOTE */
	    array(0, "��վʱ��ʾ����¼", "telnet��ʽ����վʱ�Ƿ���ʾ����¼", "��", "��"),        /* DEF_OUTNOTE */
	    array(0, "ѶϢ��ģʽ", "telnet��ʽ��ѶϢ������ʾ����",  "������״̬", "��������"), /* DEF_NOTMSGFRIEND */
	    array(0, "�˵�ģʽѡ��", "telnet��ʽ�µĲ˵�ģʽ", "ȱʡģʽ", "����ģʽ"), /* DEF_NORMALSCR */
	    array(0, "�Ķ������Ƿ�ʹ���ƾ�ѡ��", "telnet��ʽ���Ķ������Ƿ��ƾ�ѡ��", "��","��"),/* DEF_CIRCLE */
	    array(0, "�Ķ������α�ͣ춵�һƪδ��","telnet��ʽ�������б�ʱ����Զ���λ��λ��", "��һƪδ������", "����һƪ����"),       /* DEF_FIRSTNEW */
	    array(0, "��Ļ����ɫ��", "telnet��ʽ����Ļ����ɫ����ʾģʽ", "��׼", "�Զ��任"), /* DEF_TITLECOLOR */
	    array(1, "���������˵�ѶϢ", "�Ƿ����������˸���������Ϣ��","��","��"),         /* DEF_ALLMSG */
	    array(1, "���ܺ��ѵ�ѶϢ", "�Ƿ���Ѹ���������Ϣ��", "��", "��"),          /* DEF_FRIENDMSG */
	    array(1, "�յ�ѶϢ��������","�յ����ź��Ƿ���������������","��","��"),         /* DEF_SOUNDMSG */
	    array(0, "��վ��Ļ�����ѶϢ","���˳���½���Ƿ�Ѷ���Ϣ��¼�����������䣿", "��", "��"),       /* DEF_MAILMSG */
	    array(0, "������ʱʵʱ��ʾѶϢ","telnet��ʽ�±༭����ʱ�Ƿ�ʵʱ��ʾ����Ϣ��","��", "��"),     /*"���к�����վ��֪ͨ",    DEF_LOGININFORM */
	    array(0,"�˵�����ʾ������Ϣ","telnet��ʽ���Ƿ��ڲ˵�����ʾ������Ϣ��", "��", "��"),       /* DEF_SHOWSCREEN */
	    array(0, "��վʱ��ʾʮ������","telnet��ʽ��վʱ�Ƿ���ʾʮ�����Ż��⣿", "��ʾ", "����ʾ"),       /* DEF_SHOWHOT */
	    array(0, "��վʱ�ۿ����԰�","telnet��ʽ�½�վʱ�Ƿ���ʾ���԰�","��ʾ","����ʾ"),         /* DEF_NOTEPAD */
	    array(0, "����ѶϢ���ܼ�", "telnet��ʽ�����ĸ������Զ��ţ�", "Enter��","Esc��"),       /* DEF_IGNOREMSG */
	    array(0, "ʹ�ø�������","telnet��ʽ���Ƿ�ʹ�ø������棿", "ʹ��", "��ʹ��"),         /* DEF_HIGHCOLOR */
	    array(0, "��վʱ�ۿ���վ����ͳ��ͼ", "telnet��ʽ�½�վʱ�Ƿ���ʾ��վ����ͳ��ͼ��", "��ʾ", "����ʾ"), /* DEF_SHOWSTATISTIC Haohmaru 98.09.24 */
	    array(0, "δ������ַ�","telnet��ʽ�����ĸ��ַ���Ϊδ�����", "*","N"),           /* DEF_UNREADMARK Luzi 99.01.12 */
	    array(0, "ʹ��GB���Ķ�","telnet��ʽ����GB���Ķ���", "��", "��"),             /* DEF_USEGB KCN 99.09.03 */
	    array(0, "�Ժ��ֽ������ִ���", "telnet��ʽ���Ƿ�Ժ��ֽ������ִ���","��", "��"),  /* DEF_CHCHAR 2002.9.1 */
	    array(1,"��ʾ��ϸ�û���Ϣ", "�Ƿ��������˿��������Ա����ա���ϵ��ʽ������", "����", "������"),  /*DEF_SHOWDETAILUSERDATA 2003.7.31 */
	    array(1,"��ʾ��ʵ�û���Ϣ",  "�Ƿ��������˿���������ʵ��������ַ������", "����", "������") /*DEF_REALDETAILUSERDATA 2003.7.31 */
	);
}

if (!isset($user_define1)) {
	$user_define1=array(array(1,"���� IP", "�Ƿ��ĺͱ���ѯ��ʱ�������Լ��� IP ��Ϣ","������","����") /* DEF_HIDEIP */
	);
}

if (!isset($mailbox_prop)) {
	$mailbox_prop=array(array(1,"����ʱ�����ż���������", "�Ƿ���ʱ�Զ�ѡ�񱣴浽������","����","������"),
		array(1,"ɾ���ż�ʱ�����浽������", "�Ƿ�ɾ���ż�ʱ�����浽������","������","����"),
		array(0,"��������", "telnet��ʽ�°��水 'v' ʱ����ʲô���棿","����������","�ռ���")
	);
}
?>
