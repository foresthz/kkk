<?php
define("ATTACHMAXSIZE",BBS_MAXATTACHMENTSIZE);        //�������ֽ��������ޣ���λ bytes
define("ATTACHMAXCOUNT",BBS_MAXATTACHMENTCOUNT);      //������Ŀ������
define("MAINPAGE_FILE","mainpage.php");              //��ҳ������ URL
define("QUOTED_LINES", BBS_QUOTED_LINES);             //web ���ı�������������
define("SITE_NEWSMTH", 1);
define("RUNNINGTIME", 1);                             //�ײ���ʾҳ������ʱ��

$section_nums = array();
$section_names = array();
for($i=0;$i<BBS_SECNUM;$i++) {
	$section_nums[] = constant("BBS_SECCODE".$i);
	$section_names[] = array(constant("BBS_SECNAME".$i."_0"),constant("BBS_SECNAME".$i."_1"));
}

// ���淽��������
$style_names = array(
	"Ĭ�Ϸ�������ɫ���䣩",
	"�׵ģ�����������ã�"
);

?>
