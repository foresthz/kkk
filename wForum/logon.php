<?php

$needlogin=0;

require("inc/funcs.php"); 
setStat("�û���¼");

global $comeurl;
	
if ($_POST['action']=="doLogon") {
	doLogon();
} else {
	show_nav();
	echo "<br>";
	head_var();
	showLogon();
}

show_footer();

function doLogon(){
	GLOBAL $loginok, $guestloginok;
	global $SiteName;
	global $comeurl;
	
	if ((strpos(strtolower($_POST['comeurl']),'register.php')!==false) || (strpos(strtolower($_POST['comeurl']),'logon.php') !==false) || trim($_POST['comeurl'])=='')  {
		$comeurlname="";
		$comeurl="index.php";
	} else {
		$comeurl=$_POST['comeurl'];
		$comeurlname="<li><a href=\"".$comeurl."\">".$comeurl."</a></li>";
	}
	
	@$id = $_POST["id"];
	@$passwd = $_POST["password"];
	if ($id=='') {
		errorQuit("�����������û�����");
	}
	if  ( ($loginok==1) || ($guestloginok==1) ) {
		bbs_wwwlogoff();
	}
	if (($id!='guest') && (bbs_checkpasswd($id,$passwd)!=0)){
		errorQuit("�����û����������ڣ����������������");
	}
	$ret=bbs_wwwlogin(1);
	switch ($ret) {
	case -1:
		errorQuit("���ѵ�¼���˺Ź��࣬�޷��ظ���¼!");
	case 3:
		errorQuit("�����˺��ѱ�����Ա���ã�");
	case 4:
		errorQuit("����ʹ�õ�IP�ѱ���վ���ã�");
	case 5:
		errorQuit("����Ƶ����¼!");
	case 1:
		errorQuit("ϵͳ���������Ѵ����ޣ����Ժ��ٷ��ʱ�վ��");
	case 7:
		errorQuit("�Բ���,��ǰλ�ò������¼��ID��");
	}
	$data=array();
	$num=bbs_getcurrentuinfo($data);
	$time=0;
	switch (intval($_POST['CookieDate'])) {
	case 1;
		$time=time()+86400; //24*60*60 sec
		break;
	case 2;
		$time=time()+2592000; //30*24*60*60 sec
		break;
	case 3:
		$time=time()+31536000; //365*24*60*60 sec
		break;
	}
	$path='';
	setcookie("W_UTMPKEY",$data["utmpkey"],time()+360000,$path);
	setcookie("W_UTMPNUM",$num,time()+360000,$path);
	setcookie("W_UTMPUSERID",$data["userid"],$time,$path);
	setcookie("W_LOGINTIME",$data["logintime"],0,$path);
	setcookie("W_PASSWORD",$passwd,$time,$path);

	show_nav(false);
	echo "<br>";
	head_var();
?>
<meta HTTP-EQUIV=REFRESH CONTENT='2; URL=<?php   echo $comeurl; ?>' >
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr>
<th height=25>��¼�ɹ���<?php echo $SiteName; ?>��ӭ���ĵ���</th>
</tr>
<tr><td class=TableBody1><br>
<ul><?php   echo $comeurlname; ?><li><a href=index.php>������ҳ</a></li></ul>
</td></tr>
</table>
<?php 
}

function errorQuit($errMsg) {
	global $comeurl;

	show_nav(false);
	echo "<br>";
	head_var();
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr align=center>
<th height=25>��̳������Ϣ</th>
</td>
</tr>
<tr>
<td class=TableBody1>
<b>��������Ŀ���ԭ��</b>
<ul>
<li><?php   echo $errMsg; ?></li>
</ul>
</td></tr></table>
<?php
	showLogon(1, $comeurl);
	show_footer(false, false);
	exit;
}
?>
