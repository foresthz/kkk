<?php
    require("funcs.php");
login_init();
    if ($loginok != 1)
		html_nologin();
    else
    {
        html_init("gb2312");

        if ($currentuser["userid"]=="guest")
			html_error_quit("�Ҵҹ��Ͳ��ܷ��Ͷ��ţ����ȵ�¼");

		if (isset($_POST["dest"])){
			$dest = $_POST["dest"];

			if(!isset($_POST["msgstr"]))
				html_error_quit("��������Ϣ");

			$msgstr = $_POST["msgstr"];

			$ret = bbs_send_sms($dest, $msgstr);

			if( $ret == 0 )
				echo "���ͳɹ�";
			else
				echo "����ʧ��".$ret;

		}else{
?>
<form action=/bbssendsms.php method=post>
�Է��ֻ���<input type=text name=dest maxlength=11><br>
��Ϣ<input type=text name=msgstr><br>
<input type=submit value=����>
<input type=reset>
</form>
<?php
		}
	}
?>
