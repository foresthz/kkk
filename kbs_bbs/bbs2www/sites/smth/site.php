<?php

define("ATTACHMAXSIZE","2097152");
define("ATTACHMAXCOUNT","10");
define("HAVE_PC", 1); // ֧�ָ����ļ�
define("MAINPAGE_FILE","mainpage.html"); // ʹ�þ�̬ mainpage ҳ��
define("STATIC_FRAME",1); //ʹ�þ�̬ frame
define("HAVE_BRDENV", 1); //֧�ְ��浼��
define("QUOTED_LINES","3");
define("ACTIVATIONLEN",15); //�����볤��
define("ENABLE_ABOARDS" , 1);//web��ҳʹ�û����
define("SERVICE_QUIZ" , 1); //www���Ĵǵ�
define("SITE_SMTH",1);
define('BBS_NEWPOSTSTAT',1);
define('MIN_REG_TIME', 48);

$domain_name = explode(":",trim($_SERVER["HTTP_HOST"]));
define("BBS_DOMAIN_NAME" , $domain_name[0]); //����

$section_nums = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9","A","B","C");
$section_names = array(
    array("BBS ϵͳ", "[վ��]"),
    array("�ഺУ԰", "[�廪/ԺУ]"),
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
?>
