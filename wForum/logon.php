<?php

$needlogin=0;

require("inc/funcs.php"); 
setStat("�û���¼");

global $comeurl;
	
if (isset($_POST["id"])) {
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
	
	if ((strpos(strtolower($_POST['comeurl']),'register.php')!==false) || (strpos(strtolower($_POST['comeurl']),'bbsleft.php')!==false) || (strpos(strtolower($_POST['comeurl']),'logon.php') !==false) || trim($_POST['comeurl'])=='')  {
		$comeurlname="";
		$comeurl="index.php";
	} else {
		$comeurl=$_POST['comeurl'];
		$comeurlname="<li><a href=\"".$comeurl."\">".$comeurl."</a></li>";
	}
	
	@$id = $_POST["id"];
	@$passwd = $_POST["passwd"];
	$cookieDate = 0;
	$cookieDate = intval($_POST['CookieDate']);
	if ($id=='') {
		errorQuit("�����������û�����");
	}
	if  ( ($loginok==1) || ($guestloginok==1) ) {
		bbs_wwwlogoff();
	}
	if (($id!='guest') && (bbs_checkpasswd($id,$passwd)!=0)){
		errorQuit("�����û����������ڣ����������������");
	}
	if (AUTO_KICK) {
		$kick = 1;
	} else {
		if (isset($_POST["kick"])) $kick = 1;
		else $kick = 0;
	}
	$ret=bbs_wwwlogin($kick);
	switch ($ret) {
	case -1:
		if (AUTO_KICK) {
			errorQuit("���ѵ�¼���˺Ź��࣬�޷��ظ���¼!");
		} else {
			promptKick($id, $passwd, $comeurl, $cookieDate);
		}
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
	switch ($cookieDate) {
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
	setcookie(COOKIE_PREFIX."UTMPKEY",$data["utmpkey"],time()+3600,COOKIE_PATH);
	setcookie(COOKIE_PREFIX."UTMPNUM",$num,time()+3600,COOKIE_PATH);
	setcookie(COOKIE_PREFIX."UTMPUSERID",$data["userid"],$time,COOKIE_PATH);
	setcookie(COOKIE_PREFIX."LOGINTIME",$data["logintime"],0,COOKIE_PATH);
	if ($time!=0) {
		$u = array();
		if (bbs_getcurrentuser($u) > 0) {
			setcookie(COOKIE_PREFIX."PASSWORD",base64_encode($u["md5passwd"]),$time,COOKIE_PATH);
		}
	}

	show_nav(false);
	echo "<br>";
	head_var();
?>
<meta HTTP-EQUIV=REFRESH CONTENT='2; URL=<?php   echo $comeurl; ?>' >
<script language="JavaScript">
<!--
    refreshLeft();
//-->
</script>
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

function promptKick($id, $passwd, $comeurl, $cookieDate) {
	global $comeurl;

	show_nav(false);
	echo "<br>";
	head_var();
?>
<form name="infoform" action="logon.php" method="post">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
<input type="hidden" name="password" value="<?php echo htmlspecialchars($passwd); ?>">
<input type="hidden" name="comeurl" value="<?php echo htmlspecialchars($comeurl); ?>">
<input type="hidden" name="CookieDate" value="<?php echo $cookieDate; ?>">
<input type="hidden" name="kick" value="1">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr align=center>
<th height=25>��̳��ʾ��Ϣ</th>
</td>
</tr>
<tr>
	<td class=TableBody1>
		<b>��������Ŀ���ԭ��</b>
		<ul><li>���¼�Ĵ��ڹ��࣬��� ȷ����¼ �߳�����ĵ�¼��</li></ul>
	</td>
</tr>
<tr>
	<td class=TableBody2 valign=middle colspan=2 align=center>
		<input type=submit name=submit value="ȷ����¼">&nbsp;&nbsp;
		<input type=button name="back" value="�� ��" onclick="location.href='<?php echo htmlspecialchars($comeurl, ENT_QUOTES); ?>'">
	</td>
</tr>
</table>
</form>
<?php
	show_footer(false, false);
	exit;

}
?>
