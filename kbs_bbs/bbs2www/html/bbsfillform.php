<?php
	/**
	 * This file Fill registry form.
	 * by binxun 2003.5
	 */
	require("funcs.php");
login_init();
	require("reg.inc.php");
	html_init("gb2312");

	if ($loginok != 1)
		html_nologin();
	else
	{
		@$realname=$_POST["realname"];
		@$dept=$_POST["dept"];
		@$address=$_POST["address"];
		@$year=$_POST["year"];
		@$month=$_POST["month"];
		@$day=$_POST["day"];
		@$email=$_POST["email"];
		@$phone=$_POST["phone"];
		@$gender=$_POST["gender"];
		@$mobile_phone=$_POST["mobile"];


	if(!strcmp($currentuser["userid"],"guest"))
		html_error_quit("������������ʺ���дע�ᵥ!");

	//��鼤����
	$ret = bbs_getactivation($currentuser["userid"],$activation);
	if($ret==0) //��Ҫ����
	{
		if(!bbs_reg_haveactivated($activation))
			html_error_quit("�Բ������ȼ��������ʺš���������������ע��Email�<a href=\"/bbssendacode.php\">[�һ�û�յ�������]</a>");
	
		/*
		if(strtolower($email) != strtolower(bbs_reg_getactivationemail($activation)))
			html_error_quit("�Բ�������ע��Email�б䶯����<a href=\"/bbssendacode.php?react=1\">���¼���</a>");
		*/
		$email = bbs_reg_getactivationemail($activation);
	}
	
	//48Сʱ�����ע��
	if ( time() - $currentuser["firstlogin"] < MIN_REG_TIME * 3600 )
		html_error_quit("���ڵ�һ�ε�¼ ".MIN_REG_TIME."Сʱ ������дע�ᵥ������Ϥһ������Ļ����ɡ�");
	
	//�û��Ѿ�ͨ��ע��
	//δ���ȴ�ʱ��(�ȷŵ�phplib��������)
	if(!strcmp($gender,"��"))$gender=1;
    else
        $gender=2;
	settype($year,"integer");
	settype($month,"integer");
	settype($day,"integer");
    if (BBS_WFORUM==0)  {
        $ret=bbs_createregform($currentuser["userid"],$realname,$dept,$address,$gender,$year,$month,$day,$email,$phone,$mobile_phone,FALSE);//�Զ�����ע�ᵥ
    } else {
        $ret=bbs_createregform($currentuser["userid"],$realname,$dept,$address,$gender,$year,$month,$day,$email,$phone,$mobile_phone, $_POST['OICQ'], $_POST['ICQ'], $_POST['MSN'],  $_POST['homepage'], intval($_POST['face']), $_POST['myface'], intval($_POST['width']), intval($_POST['height']), intval($_POST['groupname']), $_POST['country'],  $_POST['province'], $_POST['city'], intval($_POST['shengxiao']), intval($_POST['blood']), intval($_POST['belief']), intval($_POST['occupation']), intval($_POST['marital']), intval($_POST['education']), $_POST['college'], intval($_POST['character']), FALSE);//�Զ�����ע�ᵥ
    }
//	$ret=bbs_createregform($currentuser["userid"],$realname,$dept,$address,$gender,$year,$month,$day,$email,$phone,"",FALSE); //�ֹ���дע�ᵥ

	switch($ret)
	{
	case 0:
		break;
	case 1:
		html_error_quit("����ע�ᵥ��û�д��������ĵȺ�");
		break;
	case 2:
		html_error_quit("���û�������!");
		break;
	case 3:
		html_error_quit("��������");
		break;
	case 4:
		html_error_quit("���Ѿ�ͨ��ע����!");
		break;
	case 5:
		html_error_quit("��ע���в���48Сʱ,�����״�ע��48Сʱ������дע�ᵥ.");
		break;
	default:
		html_error_quit("δ֪�Ĵ���!");
		break;
	}
}
?>
<body>
ע�ᵥ�Ѿ��ύ,24Сʱ��վ�񽫻����,���ͨ��,��ͻ��úϷ��û�Ȩ�ޣ�<br>
<a href="javascript:history.go(-1)">���ٷ���</a>
</body>
</html>
