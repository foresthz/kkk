<?php
	/**
	 * This file registry a new id, work with bbsreg.html
	 * by binxun 2003.5
	 */
	$needlogin=false;
	require("funcs.php");

	@$num_auth=$_POST["num_auth"];
	@$userid=$_POST["userid"];
	@$nickname=$_POST["username"];
	@$realname=$_POST["realname"];
	@$dept=$_POST["dept"];
	@$address=$_POST["address"];
	@$year=$_POST["year"];
	@$month=$_POST["month"];
	@$day=$_POST["day"];
	@$reg_email=$_POST["reg_email"];
	@$phone=$_POST["phone"];
	@$gender=$_POST["gender"];
	@$m_register=$_POST["m_register"];
	@$mobile_phone=$_POST["mobile_phone"];

	session_start();
	if(!isset($_SESSION['num_auth']))
  	    html_error_quit("��ȴ�ʶ���ͼƬ��ʾ���!");
	if(strcasecmp($_SESSION['num_auth'],$num_auth))
	    html_error_quit("ͼƬ�ϵ��ַ���ʶ�����!�ѵ����ǻ����ˣ�");

	if(!strchr($reg_email,'@'))
	    html_error_quit("�����ע�� email ��ַ!");

	//generate passwd
	$password=bbs_findpwd_check("","","");

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


$mailbody="
<?xml version=\"1.0\" encoding=\"gb2312\">
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<body><P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">  " . $realname  . "��ӭ������</SPAN><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">BBSˮľ�廪վ��</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">����ע����Ϣ�ǣ�</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">�û�����" . $userid . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">������" . $realname . "<SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">������" . $address . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">���룺" . $password . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN lang=EN-US>email</SPAN><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">��" . $reg_email . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><A 
href=\"https://www.smth.edu.cn\"><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">��������¼BBS ˮľ�廪</SPAN>
</body>
</html>
";
/* To send HTML mail, you can set the Content-type header. */
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=gb2312\r\n";

/* additional headers */
$headers .= "From: BBSˮľ�廪վ <https://www.smth.edu.cn>\r\n";

if(!mail($reg_email, "welcome to BBSˮľ�廪վ",$mailbody,$headers))
    html_error_quit("�������뵽����ע��Emailʧ��!��ȷ�����ĸ�Email��ַ��ȷ");

	if(!strcmp($gender,"��"))$gender=1;
    else
        $gender=2;
    settype($year,"integer");
	settype($month,"integer");
	settype($day,"integer");
	settype($m_register,"bool");

    if(!$m_register)$mobile_phone="";
    if (BBS_WFORUM==0)  {
        $ret=bbs_createregform($userid,$realname,$dept,$address,$gender,$year,$month,$day,$reg_email,$phone,$mobile_phone,TRUE);//�Զ�����ע�ᵥ
    } else {
        $ret=bbs_createregform($userid,$realname,$dept,$address,$gender,$year,$month,$day,$email,$phone,$mobile_phone, $_POST['OICQ'], $_POST['ICQ'], $_POST['MSN'],  $_POST['homepage'], intval($_POST['face']), $_POST['myface'], intval($_POST['width']), intval($_POST['height']), intval($_POST['groupname']), $_POST['country'],  $_POST['province'], $_POST['city'], intval($_POST['shengxiao']), intval($_POST['blood']), intval($_POST['belief']), intval($_POST['occupation']), intval($_POST['marital']), intval($_POST['education']), $_POST['college'], intval($_POST['character']), TRUE);//�Զ�����ע�ᵥ
    }
	switch($ret)
	{
	case 0:
		break;
	case 2:
		html_error_quit("���û�������!");
		break;
	case 3:
		html_error_quit("����ע�ᵥ���� ��������! ��������ֹ���дע�ᵥ");
		break;
	default:
		html_error_quit("����ע�ᵥ���� δ֪�Ĵ���! ��������ֹ���дע�ᵥ");
		break;
	}
	html_init("gb2312");
?>
<body>
����BBSˮľ�廪ID�ɹ�,�����ڻ�û��ͨ�������֤,ֻ���������Ȩ��,���ܷ���,����,�����,�����ϵͳ���Զ�����ע�ᵥ.<br>
ע�ᵥͨ����˺�,�㽫��úϷ��û�Ȩ�ޣ�<br/><a href="https://www.smth.edu.cn">���ڵ�¼��վ</a>
</body>
</html>
