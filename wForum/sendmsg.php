<?php


require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

html_init();

if ($loginok==1) {
	main();
}

function main() {
	global $currentuser;
	global $_GET;
	if (isset($_GET["destid"]))
		$destid = $_GET["destid"];
	else
		$destid = "";
	if (isset($_GET["destutmp"]))
		$destutmp = $_GET["destutmp"];
	else
		$destutmp = 0;
	settype($destutmp, "integer");
?>
<body>
<script language="javascript" type="text/javascript" src="inc/browser.js"></script>
<script language="javascript">
parent.pauseMsg();
</script>
<div id="msgcontent" >
<div onkeydown="if(event.keyCode==13 && event.ctrlKey) { obj=getRawObject('oSend');obj.focus();obj.click();} ">
<form action="dosendmsg.php" method=post name=messager id=messager >
<input type="hidden" name="destutmp" value="<?php echo $destutmp; ?>"/>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
          <tr> 
            <th colspan=3>���Ͷ���Ϣ��������������Ϣ��</th>
          </tr>
          <tr> 
            <td class=TableBody1 valign=middle><b>��ѶϢ��:</b></td>
            <td class=TableBody1 valign=middle>
              <input id="odestid" name="destid" maxlength="12" value="<?php echo $destid; ?>" size="12" onchange="msg_idchange(this.value);"/>
<?php
		if (!isset($_GET["destid"])) {
?>
              <SELECT name=font onchange=DoTitle(this.options[this.selectedIndex].value)>
              <OPTION selected value="">ѡ��</OPTION>

			  </SELECT>
<?php
}
?>
            </td>
          </tr>
           <tr> 
            <td class=TableBody1 valign=top width=15%><b>���ݣ�</b></td>
            <td  class=TableBody1 valign=middle>
              <input id="oMsgText" name="msg" maxlength="50" size="50" onchange="msg_textchange(this.value);" />
            </td>
          </tr>
		<tr> 
            <td  class=TableBody1 colspan=2> <input type="checkbox" name="isSMS" id="isSMS" <?php echo (isset($_GET['type'])&& ($_GET['type']=='sms'))?'checked':''; ?> onchange="msg_typechanged(this.checked);">&nbsp; <b>�����ֻ�����</b>
			</td>
		</tr>
          <tr> 
            <td  class=TableBody1 colspan=2>
<b>˵��</b>��<br>
�� ������ʹ��<b>Ctrl+Enter</b>����ݷ��Ͷ���<br>
<!--
�� ������Ӣ��״̬�µĶ��Ž��û�������ʵ��Ⱥ�������<b>5</b>���û�<br>
�� �������<b>50</b>���ַ����������<b>300</b>���ַ�<br>
-->
</form>
            </td>
          </tr>
          <tr> 
            <td  class=TableBody2 valign=middle colspan=2 align=center> 
              <input type=button value="����" name=Submit id="oSend" onclick="dosendmsg();">
             &nbsp;
<!--
			  <form action="showmsgs.php">
              <input type=button value="�鿴�����¼" name="chatlog" >
			  </form>
			 -->
              &nbsp; 
              <input type="button" name="close" value="�ر�" onclick="closeWindow();">
            </td>
          </tr>
        </table>
</div>
</div>
	<script language="javascript">
	oFloater=getParentRawObject("floater");
	oMsg=getRawObject("msgcontent");
	oFloater.innerHTML=oMsg.innerHTML;
	show(oFloater);
	</script>
<?php

}
?>
</body>