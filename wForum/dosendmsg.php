<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

html_init();

?>
<body>
<script language="javascript" type="text/javascript" src="inc/browser.js"></script>
<div id="msgcontent">
<div onkeydown="if(event.keyCode==13 ) { closeWindow(); } ">
<?php
requireLoginok(false, false);

if (!isErrFounded()) {
	main();
}

?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<?php
if (isErrFounded()) {
?>
         <tr> 
            <th colspan=3>���Ͷ���Ϣʧ��</th>
         </tr>
		<tr>
		<td width="100%" class=TableBody1 colspan=2>
		<?php echo $errMsg; ?>
		</td></tr>
<?php
} else {
?>
         <tr> 
            <th colspan=3>���Ͷ���Ϣ�ɹ�</th>
         </tr>
		<td width="100%" class=TableBody1 colspan=2>
		<?php echo $sucmsg; ?>
		</td></tr>
<?php
}
?>
         </tr>
		<td width="100%" class=TableBody2 colspan=2 align="center">
		<a href="#" onclick="closeWindow();">[�ر�]</a>
		</td></tr>
		</table>
<?php

function main(){
	global $_POST;
	if (isset($_POST["destid"]))
		$destid = $_POST["destid"];
	else
		$destid = "";
	if (isset($_POST["msg"]))
		$msg = $_POST["msg"];
	else
		$msg = "";
	if (isset($_POST["destutmp"]))
		$destutmp = $_POST["destutmp"];
	else
		$destutmp = 0;
	settype($destutmp, "integer");

	if (strlen($destid) == 0 || strlen($msg) == 0)	{
?>
<script language="javascript">
	parent.closeWindow();
</script>
<?php
		exit(0);
	}
	if (isset($_POST['isSMS']) && SMS_SUPPORT) {
		if (bbs_send_sms($destid, $msg)!=0){
			foundErr("�ֻ����ŷ���ʧ�ܣ�", false);
			return false;
		}
	} else {
		if (bbs_sendwebmsg($destid, $msg, $destutmp, $errmsg)==FALSE){
			foundErr($errmsg, false);
			return false;
		}
	}
	setSucMsg("��Ϣ�ѳɹ����ͣ�");
}


?>
</div>
	<script language="javascript">
	oFloater=getParentRawObject("floater");
	oMsg=getRawObject("msgcontent");
	oFloater.innerHTML=oMsg.innerHTML;
	show(oFloater);
	</script>
</body>
