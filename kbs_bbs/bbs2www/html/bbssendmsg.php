<?php
	/**
	 * This file send message for current user.
	 * $Id$
	 */
    require("funcs.php");
login_init();
    if ($loginok != 1)
		html_nologin();
    else
    {
        html_init("gb2312");
        if ($currentuser["userid"]=="guest")
			html_error_quit("�Ҵҹ��Ͳ��ܷ���ѶϢ�����ȵ�¼");
		// get user input
		if (isset($_POST["destid"]))
			$destid = $_POST["destid"];
		elseif (isset($_GET["destid"]))
			$destid = $_GET["destid"];
		else
			$destid = "";
		if (isset($_POST["msg"]))
			$msg = $_POST["msg"];
		else
			$msg = "";
		if (isset($_POST["destutmp"]))
			$destutmp = $_POST["destutmp"];
		elseif (isset($_GET["destutmp"]))
			$destutmp = $_GET["destutmp"];
		else
			$destutmp = 0;
		settype($destutmp, "integer");
		if (strlen($destid) == 0 || strlen($msg) == 0)
		{
?>
<body onload="document.form0.msg.focus()">
<form name="form0" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="hidden" name="destutmp" value="<?php echo $destutmp; ?>"/>
��ѶϢ��: <input name="destid" maxlength="12" value="<?php echo $destid; ?>" size="12"/><br/>
ѶϢ����: <input name="msg" maxlength="50" size="50" value="<?php echo $msg; ?>"/><br/>
<input type="submit" value="ȷ��" width="6"/>
</form> 
<?php
			html_normal_quit();
		}
		bbs_sendwebmsg($destid, $msg, $destutmp, $errmsg);
?>
<body onload="document.form1.b1.focus()">
<?php echo $errmsg; ?>
<script>top.fmsg.location="bbsgetmsg.php?refresh"</script>
<br/>
<form name="form1">
<input name="b1" type="button" onclick="history.go(-2)" value="[����]"/>
</form>
<?php
		html_normal_quit();
    }
?>
