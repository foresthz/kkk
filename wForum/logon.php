<?php

$needlogin=0;

require("inc/funcs.php"); 
setStat("�û���¼");
show_nav();
echo "<br>";
head_var("�û���¼");

if ($_POST['action']=="doLogon") {
	doLogon();
} else {
	showLogon();
}

show_footer();

function doLogon(){
	GLOBAL $loginok, $guestloginok;
	@$id = $_POST["id"];
	@$passwd = $_POST["password"];
	if ($id=='') {
		foundErr("�����������û���");
	}
	if  ( ($loginok==1) || ($guestloginok==1) ) {
		bbs_wwwlogoff();
	}
	if (($id!='guest') && (bbs_checkpasswd($id,$passwd)!=0)){
		foundErr("�����û����������ڣ����������������");
	}
	$ret=bbs_wwwlogin(1);
	switch ($ret) {
	case -1:
		foundErr("���ѵ�½���˺Ź��࣬�޷��ظ���½!");
	case 3:
		foundErr("�����˺��ѱ�����Ա���ã�");
	case 4:
		foundErr("����ʹ�õ�IP�ѱ���վ���ã�");
	case 5:
		foundErr("����Ƶ����½!");
	case 1:
		foundErr("ϵͳ���������Ѵ����ޣ����Ժ��ٷ��ʱ�վ��");
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

	if ((strpos(strtolower($_POST['comeurl']),'register.php')!==false) || (strpos(strtolower($_POST['comeurl']),'logon.php') !==false) || trim($_POST['comeurl'])=='')  {
		$comeurlname="";
		$comeurl="index.php";
	} else {
		$comeurl=$_POST['comeurl'];
		$comeurlname="<li><a href=".$_POST['comeurl'].">".$_POST['comeurl']."</a></li>";
	} 
?>
<meta HTTP-EQUIV=REFRESH CONTENT='2; URL=<?php   echo $comeurl; ?>' >
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 >
<tr>
<th height=25>��½�ɹ���<?php   echo $Forum_info[0]; ?>��ӭ���ĵ���</th>
</tr>
<tr><td class=TableBody1><br>
<ul><?php   echo $comeurlname; ?><li><a href=index.php>������ҳ</a></li></ul>
</td></tr>
</table>
<?php 
}
?>
