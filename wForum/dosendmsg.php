<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

html_init();

?>
<div id="msgcontent">
<?php
if ($loginok==1) {
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

?>
<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
<?php
if (isErrFounded()) {
?>
         <tr> 
            <th colspan=3>���Ͷ���Ϣʧ��</th>
         </tr>
		<tr>
		<td width="100%" class=tablebody1 colspan=2>
		<?php echo $errMsg; ?>
		</td></tr>
<?php
} else {
?>
         <tr> 
            <th colspan=3>���Ͷ���Ϣ�ɹ�</th>
         </tr>
		<td width="100%" class=tablebody1 colspan=2>
		<?php echo $sucmsg; ?>
		</td></tr>
<?php
}
?>
         </tr>
		<td width="100%" class=tablebody2 colspan=2 align="center">
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
		foundErr("���Ų�������");
		return false;
	}
	if (bbs_sendwebmsg($destid, $msg, $destutmp, $errmsg)==FALSE){
		foundErr($errmsg);
		return false;
	}
	setSucMsg("��Ϣ�ѳɹ����ͣ�");
}


?>
</div>
<script>
parent.document.all.floater.innerHTML=msgcontent.innerHTML;
parent.document.all.floater.style.visibility='visible';
</script>