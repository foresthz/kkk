<?php
/*
** activate register user
** @author: windinsn Apr 12 , 2004
*/
require("funcs.php");
require("reg.inc.php");
$retry_time = intval($_COOKIE["ACTRETRYTIME"]);

if($retry_time > 9)
	html_error_quit("�������Դ������࣬���Ժ��ټ���");
	
$userid = trim($_GET["userid"]);
$acode  = trim($_GET["acode"]);

if(!$userid || !$acode)
	html_error_quit("ȱ���û����򼤻���");

$lookupuser = array();
if(bbs_getuser($userid,$lookupuser)==0)
	html_error_quit("�û�".$userid."������");
	
$userid = $lookupuser["userid"];
$ret = bbs_getactivation($userid,$activation);

switch($ret)
{
	case -1:
		html_error_quit("�û�������");
		break;
	case -2:
		html_error_quit("�����벻����");
		break;
	default:
}

if(bbs_reg_haveactivated($activation))
	html_error_quit("�û��Ѽ���");

if(bbs_reg_getactivationcode($activation)!=$acode)
{
	setcookie("ACTRETRYTIME",$retry_time+1,time()+360000);
	html_error_quit("���������");
}

$ret = bbs_setactivation($userid,bbs_reg_successactivation($activation));
if($ret != 0)
	html_error_quit("ϵͳ����");

html_init("gb2312");
?>
<body>
<br /><br /><br />
<p align="center">��ϲ�������Ѽ��������ʺţ����¼��վ��дע�ᵥ��</p>
<p align="center"><a href="/">[��¼��վ]</a></p>
<p align="center"><font color=red>��ʾ����¼�������[���˲����趨]->[��дע�ᵥ]�������û�ע�ᡣ</font></p>
<?php
html_normal_quit();
?>