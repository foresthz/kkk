<?php

function getattachtmppath($userid,$utmpnum)
{
  $attachdir="cache/home/" . strtoupper(substr($userid,0,1)) . "/" . $userid . "/" . $utmpnum . "/upload";
  return $attachdir;
//  return "/tmp/wForum-upload";

}

define("ANNOUNCENUMBER",5);

define("ARTICLESPERPAGE",30); //Ŀ¼�б���ÿҳ��ʾ��������

define("THREADSPERPAGE",10); //�����Ķ�ʱÿҳ��ʾ��������

define('SERVERTIMEZONE','����ʱ��');

define('USEBROWSCAP', 1);

$SiteURL="http://bbs.stanford.edu/wForum/";

$SiteName="��������";

$HTMLTitle="��������";

$HTMLCharset="GB2312";

$DEFAULTStyle="defaultstyle";

$Banner="/mcwy/bm3_08.jpg";

$BannerURL="http://bbs.stanford.edu";

define ("MAINTITLE","wForum �ǻ���ˮľ�廪 BBS ϵͳ WWW �ں˵ĸ�������̳��Ŀǰϣ��<br>��������������Ȥ���˲���������������ߵ���վ wForum �汨����<br>Ҫ����Ϥ PHP�����ö� c ���Գ���û�ˡ�");
define("ATTACHMAXSIZE","8388608");
define ("ATTACHMAXTOTALSIZE","8388608");
define("ATTACHMAXCOUNT","20");

$section_nums = array("0", "1", "2", "3", "4", "5", "6");
$section_names = array(
    array("��վϵͳ", "[��վ]"),
    array("��������", "[У԰][��ҵ]"),
    array("������", "[��ѧ][ѧУ][����]"),
    array("ѧ������", "[ѧ��][��ѧ][����]"),
    array("��������", "[����][����][����]"),
    array("֪�Ը���", "[֪��][����][����]"),
    array("�� �� ��", "[�Ŷ�][���ֲ�]")
);
$sectionCount=count($section_names);

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

$user_define_num=count($user_define);
require "default.php";
$AnnounceBoard = "Announcement"; //override what's in default.php
?>
