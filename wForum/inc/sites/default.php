<?php
/*
 * �����ò������б�
 *
 * ��ʽ
 * 0. integer: �ǳ���(0)���Ǳ���(1)��һ��վ�㶨�������Ӧ���ǳ���
 * 1. string:  �������������
 * 2. string:  ������������ͣ�integer(i), string(s), boolean(b), enum ����
 * 3. mixed:   Ĭ��ֵ
 * 4. string:  ����˵��
 */
$site_defines = array(
"��վ��������",
array(1, "SiteURL", 's', "http://localhost/", "վ�����ַ��ע�����һ���ַ������� /"),
array(1, "SiteName", 's', "����վ", "վ������"),
array(1, "Banner", 's', "pic/ws.jpg", "ҳ�����Ͻ���ʾ��վ�����ͼƬ"),
array(0, "MAINTITLE", 's', "<img src=\"bar/title.jpg\" />", "ҳ�����Ϸ���ʾ��վ�����"),
array(0, "OLD_REPLY_STYLE", 'b', false, "�Ƿ�ʹ�ô�ͳ telnet re �ķ�ʽ"),
array(0, "ENABLE_UBB", 'b', true, "�Ƿ�֧�� UBB"),
array(0, "SHOW_REGISTER_TIME", 'b', false, "�Ƿ���ʾ�û�ע��ʱ��"),
array(1, "AnnounceBoard", 's', "Announce", "ϵͳ�������Ӣ������"),
array(0, "SERVERTIMEZONE", 's', '����ʱ��', "������ʱ��"),
array(0, "SHOW_SERVER_TIME", 'b', true, "��ʾ������ʱ��"),
"ҳ�����",
array(0, "ANNOUNCENUMBER", 'i', 5, "��ҳ������ʾ�Ĺ�������"),
array(0, "ARTICLESPERPAGE", 'i', 30, "Ŀ¼�б���ÿҳ��ʾ��������"),
array(0, "USERSPERPAGE", 'i', 20, "�����û�/���ѵ��б�ÿҳ��ʾ������"),
array(0, "THREADSPERPAGE", 'i', 10, "ͬ�����Ķ�ÿҳ������"),
array(0, "BOARDS_PER_ROW", 'i', 3, "�۵������б�ÿ�а�����Ŀ"),
array(0, "SHOWREPLYTREE", 'b', true, "�Ƿ�����ͼ��ʾ�ظ��ṹ"),
array(0, "TREEVIEW_MAXITEM", 'i', 51, "���λظ��ṹ�����ʾ�Ļظ���"),
array(0, "SHOW_POST_UNREAD", 'b', true, "���������б��Ƿ���ʾ new ��δ����־"),
array(0, "SECTION_DEF_CLOSE", 'b', false, "��ҳ���������б���Ĭ��������Ƿ�ر�"),
"�������",
array(0, "ATTACHMAXSIZE", 's', '524288', "��󸽼��ߴ磬��Ҫͬʱ�޸�php.ini���upload_max_size ,��λbyte"),
array(0, "ATTACHMAXTOTALSIZE", 's', '2097152', "һƪ���������и������ܴ�С����, ��λbyte"),
array(0, "ATTACHMAXCOUNT", 's', '5', "һƪ�������������صĸ�������"),
"�Զ���ͷ�����",
array(0, "USER_FACE", 'b', true, "�Ƿ������Զ���ͷ��"),
array(0, "MYFACEDIR", 's', "uploadFace/", "�Զ���ͷ���ϴ�����λ��"),
array(0, "MYFACEMAXSIZE", 's', "524288", "�Զ���ͷ������ļ���С, ��λbyte"),
"���ݿ����",
array(0, "DB_ENABLED", 'b', false, "�Ƿ��������ݿ�֧�֣�Ŀǰ������С�ֱ�"),
array(1, "dbhost", 's', "localhost", "���ݿ��������ַ"),
array(1, "dbuser", 's', "", "���ݿ�������û�"),
array(1, "dbpasswd", 's', "", "���ݿ����������"),
array(1, "dbname", 's', "", "���ݿ��"),
"�������ӹ���",
array(0, "SUPPORT_TEX", 'b', false, "�Ƿ�֧�ֶ�̬ tex2mathml ת��"),
array(0, "AUTO_KICK", 'b', true, "һ���û���¼����ʱ���Զ����ǣ�������ʾ�����߳���ǰ�ĵ�¼"),
array(0, "SHOWTELNETPARAM", 'b', true, "�Ƿ��������� telnet ��ר�õĸ��˲���"),
array(0, "ALLOWMULTIQUERY", 'b', false, "�Ƿ�����һ���û�����ȫվ/������ѯ"),
array(0, "ALLOW_SYSOP_MULTIQUERY", 'b', false, "�Ƿ��������Ա����ȫվ/������ѯ"),
array(0, "ALLOW_SELF_MULTIQUERY", 'b', false, "�Ƿ�����ȫվ��ѯ�Լ����������"),
array(0, "RSS_SUPPORT", 'b', true, "�Ƿ����� RSS"),
array(0, "ONBOARD_USERS", 'b', false, "�Ƿ�������ʾ���������û�"),
array(0, "SMS_SUPPORT", 'b', false, "�Ƿ������ֻ�����"),
array(0, "AUDIO_CHAT", 'b', false, "�Ƿ���ʾ���������ҵ� link"),
false
);

/* site.php û�ж�����Ĳ���������һ�� */
$ccc = count($site_defines);
for ($i = 0; $i < $ccc; $i++) {
	if ($site_defines[$i] === false) break;
	if (is_string($site_defines[$i])) continue;
	
	$default = $site_defines[$i][3];
	$cur_var = $site_defines[$i][1];
	if ($site_defines[$i][0] == 0) {
		if (!defined($cur_var)) define($cur_var, $default);
	} else {
		if (!isset($$cur_var)) $$cur_var = $default;
	}
}




/* ����һЩ���������Ŀ����ò������Ժ����Ҫ��Ҫ���� site_defines */
$HTMLCharset = "GB2312"; //����Ҿ��ÿ϶��� gb2312 �ɣ��Ժ�Ҫ��Ļ������ɿ��Զ���Ĳ���

if (!defined('COOKIE_PREFIX')) { //cookie���Ƶ�ǰ׺
    define('COOKIE_PREFIX', "W_");
}
if (!defined('COOKIE_PATH')) {
    define('COOKIE_PATH', "");
}
if (!defined('COOKIE_DOMAIN')) {
    define('COOKIE_DOMAIN', "");
}
if (!defined('QUOTE_LEV')) {
	define('QUOTE_LEV', BBS_QUOTE_LEV);
}
if (!defined('QUOTED_LINES')) {
	define('QUOTED_LINES', BBS_QUOTED_LINES);
}

if (!isset($HTMLTitle)) {
	$HTMLTitle = $SiteName;
}

if (!defined('USEBROWSCAP')) { //�Ƿ�ʹ�� browscap ��������׼ȷ���ж�������Ͳ���ϵͳ���ͣ���Ҫ���� PHP - atppp
	define('USEBROWSCAP',0); //Ĭ�� OFF. ���ó� ON ��Ȼ��׼ȷ�жϳ��� IE ����������ǻ��󽵵ͳ���ҳ���ٶȣ����ã�
}

$sectionCount=count($section_names);

if (!isset($DEFAULTStyle)) {
	$DEFAULTStyle="defaultstyle";  //Ĭ��CSS������Ҫ�Ķ�ǧ��Ҫȷ�ϸ�CSS���ڡ�
}


/*
 * �û��Զ������
 *
 * ��ʽ��ÿ�������������һ�� 0 ��ʾ telnet ��ר�ò������ڶ����ǲ������ƣ�
 *                     �������ǲ���������ͣ��������ǲ��� ON �� OFF ������ľ��庬��
 */
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
	    array(0, "��վ��Ļ�����ѶϢ","���˳���¼���Ƿ�Ѷ���Ϣ��¼�����������䣿", "��", "��"),       /* DEF_MAILMSG */
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
} else $user_define_default = 0;

if (!isset($user_define1)) {
	$user_define1=array(array(1,"���� IP", "�Ƿ��ĺͱ���ѯ��ʱ�������Լ��� IP ��Ϣ","����","������") /* DEF_HIDEIP */
	);
} else $user_define1_default = 0;

if (!isset($mailbox_prop)) {
	$mailbox_prop=array(array(1,"����ʱ�����ż���������", "�Ƿ���ʱ�Զ�ѡ�񱣴浽������","����","������"),
		array(1,"ɾ���ż�ʱ�����浽������", "�Ƿ�ɾ���ż�ʱ�����浽������","������","����"),
		array(0,"��������", "telnet��ʽ�°��水 'v' ʱ����ʲô���棿","����������","�ռ���")
	);
} else $mailbox_prop_default = 0;
?>
