<?php
	/**
	 * This file registry a new id, work with bbsreg.html
	 * by binxun 2003.5
	 */
	$needlogin=false;
	require("funcs.php");
	require("reg.inc.php");

	@$num_auth=$_POST["num_auth"];
	@$userid=$_POST["userid"];
	@$nickname=$_POST["username"];
	/*
	@$realname=$_POST["realname"];
	@$dept=$_POST["dept"];
	@$address=$_POST["address"];
	@$year=$_POST["year"];
	@$month=$_POST["month"];
	@$day=$_POST["day"];
	*/
	@$reg_email=$_POST["reg_email"];
	/*
	@$phone=$_POST["phone"];
	@$gender=$_POST["gender"];
	@$m_register=$_POST["m_register"];
	@$mobile_phone=$_POST["mobile_phone"];
	*/
	@$password = $_POST["pass1"];
	@$re_password = $_POST["pass2"];
	
	session_start();
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
	if(!($activation=bbs_create_activation()))
		html_error_quit("���ɼ������������ϵ����Ա!");
	
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
	
	$ret = bbs_setactivation($userid,"0".$activation.$reg_email);
	if($ret != 0)
		html_error_quit("���ü��������");
	
$mailbody="
<?xml version=\"1.0\" encoding=\"gb2312\">
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<body><P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">  " . $realname  . "��ӭ������</SPAN><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">" . BBS_FULL_NAME . "��</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">����ע����Ϣ�ǣ�</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">�û�����" . $userid . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">�ǳƣ�" . $nickname . "<SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">���룺" . $password . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><SPAN lang=EN-US>email</SPAN><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">��" . $reg_email . "</SPAN></FONT></P>
<P class=MsoNormal><FONT size=2><A 
href=\"https://www.smth.edu.cn/bbsact.php?userid=".$userid."&acode=".$activation."<SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\">������Ｄ������" . BBS_FULL_NAME . "�����ʺ�</SPAN>
<br /><br />
<P class=MsoNormal><FONT size=2><SPAN 
style=\"FONT-FAMILY: ����; mso-ascii-font-family: 'Times New Roman'; mso-hansi-font-family: 'Times New Roman'\"></SPAN></FONT></P>
</body>
</html>
";
/* To send HTML mail, you can set the Content-type header. */
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=gb2312\r\n";

/* additional headers */
$headers .= "From: BBSˮľ�廪վ <https://www.smth.edu.cn>\r\n";

html_init("gb2312");
if(!mail($reg_email, "welcome to " . BBS_FULL_NAME, $mailbody,$headers))
{
?>
<body>
����BBSˮľ�廪ID�ɹ�,<font color=red>ע���뷢�͵�����ע��Emailʧ��!��¼����ȷ������Email��ַ�����·���ע����</font><br>
���ͨ����˺�,��Ž���úϷ��û�Ȩ�ޣ�<br/><a href="https://www.smth.edu.cn">���ڵ�¼��վ</a>
</body>
<?php
}   
else
{	
?>
<body>
����BBSˮľ�廪ID�ɹ�,�����ڻ�û��ͨ�������֤,ֻ���������Ȩ��,���ܷ���,����,�����,��������յ���ע��ȷ��Email���������ļ������Ӽ������ڱ�վ���ʺ�.<br>
���ͨ����˺�,�㽫��úϷ��û�Ȩ�ޣ�<br/><a href="https://www.smth.edu.cn">���ڵ�¼��վ</a>
</body>
<?php
}
?>
</html>
