<?php
define("www2dev", true);
define("ATTACHMAXSIZE",BBS_MAXATTACHMENTSIZE);        //�������ֽ��������ޣ���λ bytes
define("ATTACHMAXCOUNT",BBS_MAXATTACHMENTCOUNT);      //������Ŀ������
define("MAINPAGE_FILE","mainpage.php");              //��ҳ������ URL
define("QUOTED_LINES", BBS_QUOTED_LINES);             //web ���ı�������������
define("SITE_NEWSMTH", 1);
define("RUNNINGTIME", 1);                             //�ײ���ʾҳ������ʱ��
define("AUTO_BMP2PNG_THRESHOLD", 100000); // requires ImageMagick and safe_mode off
define("HAVE_PC", 1);

if (!defined("BBS_HAVE_BLOG"))
	define("BBS_HAVE_BLOG", 1); // pig2532 ymsw

// web ǰ���� squid ���� apache �� mod_proxy �ȴ����ʱ��������ѡ��
//define("CHECK_X_FORWARDED_FOR", 1);


// ���淽��������
$style_names = array(
	"��ɫ����",
	"��ѩ����",
	"�����������"
);

// �����ʱ����.............
define("BBS_NICKNAME", "ˮľ");

// Ȩ������
$permstrings = array(
    "����Ȩ��",
    "����������",
    "������������",
    "��������",
    "ʹ����������ȷ",
    "ʵϰվ��",
    "������",
    "�ɼ�����",
    "�����ʺ�",
    "�༭ϵͳ����",
    "����",
    "�ʺŹ���Ա",
    "ˮľ�廪������",
    "�������Ȩ��",
    "ϵͳά������Ա",
    "Read/Post ����",
    "�������ܹ�",
    "�������ܹ�",
    "������ܹ�",
    "���� ZAP(������ר��)",
    "������OP(Ԫ��Ժר��)",
    "ϵͳ�ܹ���Ա",
    "�����ʺ�",
    "����Ȩ�� 5",
    "�ٲ�ίԱ",
    "����Ȩ�� 7",
    "��ɱ������",
    "����ר���ʺ�",
    "��ϵͳ���۰�",
    "���Mail"
);

?>
