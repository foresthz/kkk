<?php
	/**
	 * This file registry a new id, work with bbsreg.html
	 * by binxun 2003.5
	 */
	require("funcs.php");
	set_fromhost();
	if (defined("HAVE_ACTIVATION")) {
		require("reg.inc.php");
	}

	@$num_auth=$_POST["num_auth"];
	@$userid=$_POST["userid"];
	@$nickname=$_POST["username"];
	@$reg_email=$_POST["reg_email"];
	@$password = $_POST["pass1"];
	@$re_password = $_POST["pass2"];
	
	session_start();
	html_init("gb2312");
	
	if(!isset($_SESSION['num_auth']))
  	    html_error_quit("��ȴ�ʶ���ͼƬ��ʾ���!");
	if(strcasecmp($_SESSION['num_auth'],$num_auth))
	    html_error_quit("ͼƬ�ϵ��ַ���ʶ�����!�ѵ����ǻ����ˣ�");

	if(!strchr($reg_email,'@'))
	    html_error_quit("�����ע�� email ��ַ!");
	
	if($password != $re_password)
	    html_error_quit("������ȷ�����벻һ��! ");
	    
	if(strlen($password) < 4 || strlen($password) > 39)
	    html_error_quit("���벻�淶, ���볤��ӦΪ 4-39 λ! ");
	
	//generate activation code
	if (defined("HAVE_ACTIVATION")) {
		if(!($activation=bbs_create_activation()))
			html_error_quit("���ɼ������������ϵ����Ա!");
	}
	
	//create new id
	$ret=bbs_createnewid($userid,$password,$nickname);

	switch($ret)
	{
	case 0:
			break;
	case 1:
			html_error_quit("�û����з�������ĸ�ַ��������ַ�������ĸ!");
			break;
	case 2:
			html_error_quit("�û�������Ϊ������ĸ!");
			break;
	case 3:
			html_error_quit("ϵͳ���ֻ�������!");
			break;
	case 4:
			html_error_quit("���û����Ѿ���ʹ��!");
			break;
	case 5:
			html_error_quit("�û���̫��,�12���ַ�!");
			break;
	case 6:
			html_error_quit("����̫��,�39���ַ�!");
			break;
	case 10:
			html_error_quit("ϵͳ����,����ϵͳ����ԱSYSOP��ϵ.");
			break;
	default:
			html_error_quit("ע��IDʱ����δ֪�Ĵ���!");
			break;
	}

if (defined("HAVE_ACTIVATION")) {
	$ret = bbs_setactivation($userid,"0".$activation.$reg_email);
	if($ret != 0)
		html_error_quit("���ü��������");

html_init("gb2312");
	
	$ret=bbs_sendactivation($userid);
    if($ret)
{
?>
<body>
����<?php echo BBS_FULL_NAME; ?>ID�ɹ�,<font color=red>ע���뷢�͵�����ע��Emailʧ��!��¼����ȷ������Email��ַ�����·���ע����</font><br>
���ͨ����˺�,��Ž���úϷ��û�Ȩ�ޣ�<br/><a href="/">���ڵ�¼��վ</a>
</body>
<?php
}   
else
{	
?>
<body>
����<?php echo BBS_FULL_NAME; ?>ID�ɹ�,�����ڻ�û��ͨ�������֤,ֻ���������Ȩ��,���ܷ���,����,�����,��������յ���ע��ȷ��Email���������ļ������Ӽ������ڱ�վ���ʺ�.<br>
���ͨ����˺�,�㽫��úϷ��û�Ȩ�ޣ�<br/><a href="/">���ڵ�¼��վ</a>
</body>
<?php
}

} else { // !defined("HAVE_ACTIVATION")
	@$realname=$_POST["realname"];
	@$dept=$_POST["dept"];
	@$address=$_POST["address"];
	@$year=$_POST["year"];
	@$month=$_POST["month"];
	@$day=$_POST["day"];
	@$phone=$_POST["phone"];
	@$gender=$_POST["gender"];
	$m_register = 0;
	$mobile_phone = 0;
	if(!strcmp($gender,"��"))$gender=1;
    	else $gender=2;
    	settype($year,"integer");
	settype($month,"integer");
	settype($day,"integer");
	settype($m_register,"bool");

    	if(!$m_register)$mobile_phone="";

	$ret=@bbs_createregform($userid,$realname,$dept,$address,$gender,$year,$month,$day,$reg_email,$phone,$mobile_phone, $_POST['OICQ'], $_POST['ICQ'], $_POST['MSN'],  $_POST['homepage'], intval($_POST['face']), $_POST['myface'], intval($_POST['width']), intval($_POST['height']), intval($_POST['groupname']), $_POST['country'],  $_POST['province'], $_POST['city'], intval($_POST['shengxiao']), intval($_POST['blood']), intval($_POST['belief']), intval($_POST['occupation']), intval($_POST['marital']), intval($_POST['education']), $_POST['college'], intval($_POST['character']), FALSE);//�Զ�����ע�ᵥ
	switch($ret)
	{
	case 0:
		break;
	case 2:
		html_error_quit("���û�������!");
		break;
	case 3:
		html_error_quit("����ע�ᵥ���� ��������! ���ֹ���дע�ᵥ");
		break;
	default:
		html_error_quit("����ע�ᵥ���� δ֪�Ĵ���! ���ֹ���дע�ᵥ");
		break;
	}
	html_init("gb2312");
?>
<body>
<h1>����ID�ɹ�</h1>
����<?php echo BBS_FULL_NAME; ?>ID�ɹ�,�����ڻ�û��ͨ�������֤,ֻ���������Ȩ��,���ܷ���,����,�����,ϵͳ�Ѿ��Զ�����ע�ᵥ.<br>
ע�ᵥͨ��վ����˺�,�㽫��úϷ��û�Ȩ�ޣ�<br/><a href="index.html">���ڵ�¼��վ</a>
</body>
<?php
}
?>
</html>
