<?php


$needlogin=0;

require("inc/funcs.php"); 
setStat("�û���½");

if ($_POST['action']=="doLogon") {
	doLogon();
	if (isErrFounded()){
		show_nav();
		head_var("�û���½");
		html_error_quit(); 
	} 
} else {
	show_nav();
	head_var("�û���½");
	showLogon();
}

show_footer();

function showLogon(){
?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method=post> 
	<input type="hidden" name="action" value="doLogon">
	<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
    <tr>
    <th valign=middle colspan=2 align=center height=25>�����������û����������½</td></tr>
    <tr>
    <td valign=middle class=tablebody1>�����������û���</td>
    <td valign=middle class=tablebody1><INPUT name=id type=text> &nbsp; <a href="register.php">û��ע�᣿</a></td></tr>
    <tr>
    <td valign=middle class=tablebody1>��������������</font></td>
    <td valign=middle class=tablebody1><INPUT name=password type=password> &nbsp; <a href="foundlostpass.php">�������룿</a></td></tr>
    <tr>
    <td class=tablebody1 valign=top width=30% ><b>Cookie ѡ��</b><BR> ��ѡ����� Cookie ����ʱ�䣬�´η��ʿ��Է������롣</td>
    <td valign=middle class=tablebody1>
	<input type=radio name=CookieDate value=0 checked>�����棬�ر��������ʧЧ<br>
                <input type=radio name=CookieDate value=1>����һ��<br>
                <input type=radio name=CookieDate value=2>����һ��<br>
                <input type=radio name=CookieDate value=3>����һ��<br>                
	</td></tr>
	<input type=hidden name=comeurl value="<?php echo $_SERVER['HTTP_REFERER']; ?>">
    <tr>
    <td class=tablebody2 valign=middle colspan=2 align=center><input type=submit name=submit value="�� ½"></td></tr></table>
</form>
<?php
}

function doLogon(){
	GLOBAL $loginok, $guestloginok;
	@$id = $_POST["id"];
	@$passwd = $_POST["password"];
	if ($id=='') {
		foundErr("�����������û���");
		return false;
	}
	if  ( ($loginok==1) || ($guestloginok==1) ) {
		bbs_wwwlogoff();
	}
	if (($id!='guest') && (bbs_checkpasswd($id,$passwd)!=0)){
		foundErr("�����û����������ڣ����������������");
		return;
	}
	$ret=bbs_wwwlogin(1);
	switch ($ret) {
	case -1:
		foundErr("���ѵ�½���˺Ź��࣬�޷��ظ���½!");
		return false;
	case 3:
		foundErr("�����˺��ѱ�����Ա���ã�");
		return false;
	case 4:
		foundErr("����ʹ�õ�IP�ѱ���վ���ã�");
		return false;
	case 5:
		foundErr("����Ƶ����½!");
		return false;
	case 1:
		foundErr("ϵͳ���������Ѵ����ޣ����Ժ��ٷ��ʱ�վ��");
		return false;
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
	show_nav();
	head_var("�û���½");
?>
<meta HTTP-EQUIV=REFRESH CONTENT='2; URL=<?php   echo $comeurl; ?>' >
<table cellpadding=3 cellspacing=1 align=center class=tableborder1 >
<tr>
<th height=25>��½�ɹ���<?php   echo $Forum_info[0]; ?>��ӭ���ĵ���</th>
</tr>
<tr><td class=tablebody1><br>
<ul><?php   echo $comeurlname; ?><li><a href=index.php>������ҳ</a></li></ul>
</td></tr>
</table>
<?php 
}
?>