<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("���������޸�");

show_nav();

if ($loginok==1) {
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showUserMailbox();
?>
</table>
<?php
}

head_var($userid."�Ŀ������","usermanagemenu.php",0);

if ($loginok==1) {
	showUserManageMenu();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
} 

show_footer();

function main(){
	global $currentuser;
	require("inc/userdatadefine.inc.php");
	global $SiteName;
	@$userid=$_POST["userid"];
	@$nickname=$_POST["username"];

	@$realname=$_POST["realname"];

    @$address=$_POST["address"];
	@$year=$_POST["year"];
	@$month=$_POST["month"];
	@$day=$_POST["day"];
	@$email=$_POST["email"];
	@$phone=$_POST["userphone"];
	@$mobile_phone=$_POST["mobile"];
	@$gender=$_POST["gender"];
	if($gender!='1')$gender=2;
    settype($year,"integer");
	settype($month,"integer");
	settype($day,"integer");

$ret=bbs_saveuserdata($currentuser['userid'],$realname,$address,$gender,$year,$month,$day,$email,$phone,$mobile_phone, $_POST['OICQ'], $_POST['ICQ'], $_POST['MSN'],  $_POST['homepage'], intval($_POST['face']), $_POST['myface'], intval($_POST['width']), intval($_POST['height']), intval($_POST['groupname']), $_POST['country'],  $_POST['province'], $_POST['city'], intval($_POST['shengxiao']), intval($_POST['blood']), intval($_POST['belief']), intval($_POST['occupation']), intval($_POST['marital']), intval($_POST['education']), $_POST['college'], intval($_POST['character']), $_POST['userphoto'],FALSE);//�Զ�����ע�ᵥ

	switch($ret)
	{
	case 0:
		break;
	case -1:
		foundErr("�û��Զ���ͼ���ȴ���");
		break;
	case -2:
		foundErr("�û��Զ���ͼ��߶ȴ���");
		break;
	case 3:
		foundErr("���û�������!");
		break;
	default:
		foundErr("δ֪�Ĵ���!");
		break;
	}
	if (isErrFounded() ){
		return false;
	}
//	$signature=trim($_POST["Signature"]);  /* preserve format - atppp */
	$signature = $_POST["Signature"];
//	if ($signature!='') { /* allow erase signature - atppp */
		$filename=bbs_sethomefile($currentuser['userid'],"signatures");
		$fp=@fopen($filename,"w+");
		if ($fp!=false) {
			fwrite($fp,str_replace("\r\n", "\n", $signature));
			fclose($fp);
			bbs_recalc_sig();
		}
//	}
//	$personal=trim($_POST["personal"]); /* preserve format - atppp */
	$personal=$_POST["personal"];
//	if ($personal!='') { /* allow erase - atppp */
		$filename=bbs_sethomefile($currentuser['userid'],"plans");
		$fp=@fopen($filename,"w+");
		if ($fp!=false) {
			fwrite($fp,str_replace("\r\n", "\n", $personal));
			fclose($fp);
		}
//	}
	setSucMsg("���������ѳɹ��޸ģ�");
	return html_success_quit('���ؿ������', 'usermanagemenu.php');
}
?>
