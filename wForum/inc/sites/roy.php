<?php
require("default.php");

function getattachtmppath($userid,$utmpnum)
{
  $attachdir="cache/home/" . strtoupper(substr($userid,0,1)) . "/" . $userid . "/" . $utmpnum . "/upload"; 
  return $attachdir;
}
define("ANNOUNCENUMBER",5);
define("ARTICLESPERPAGE",20);
define("THREADSPERPAGE",5); //�����Ķ�ʱÿҳ��ʾ��������
$SiteName="���˴�BBS";

$HTMLTitle="���˴�BBS";

$HTMLCharset="GB2312";

$DEFAULTStyle="defaultstyle";

$Banner="pic/ws.jpg";

$BannerURL="http://172.16.50.79";

//$SiteURL=$_SERVER['SERVER_NAME'];

$SiteURL="http://172.16.50.79";



define("ATTACHMAXSIZE","1048576");
define("ATTACHMAXCOUNT","5");

$section_nums = array("0", "1", "2", "3", "4");
$section_names = array(
    array("BBS ϵͳ", "[ϵͳ]"),
    array("У԰��Ϣ", "[��Ϣ]"),
    array("���Լ���", "[����]"),
    array("ѧ����ѧ", "[ѧϰ]"),
    array("��������", "[����]")
);
$sectionCount=count($section_names);
?>
