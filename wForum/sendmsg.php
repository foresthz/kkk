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
<div id="msgcontent" onkeydown="if(event.keyCode==13 && event.ctrlKey)messager.submit()">
<script>
function dosendmsg(){
}
</script>
<form action="dosendmsg.php" method=post name=messager id=messager>
<input type="hidden" name="destutmp" value="<?php echo $destutmp; ?>"/>
<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
          <tr> 
            <th colspan=3>���Ͷ���Ϣ��������������Ϣ��</th>
          </tr>
          <tr> 
            <td class=tablebody1 valign=middle><b>��ѶϢ��:</b></td>
            <td class=tablebody1 valign=middle>
              <input name="destid" maxlength="12" value="<?php echo $destid; ?>" size="12"/>
              <SELECT name=font onchange=DoTitle(this.options[this.selectedIndex].value)>
              <OPTION selected value="">ѡ��</OPTION>

			  </SELECT>
            </td>
          </tr>
           <tr> 
            <td class=tablebody1 valign=top width=15%><b>���ݣ�</b></td>
            <td  class=tablebody1 valign=middle>
              <input name="msg" maxlength="50" size="50" />
            </td>
          </tr>
          <tr> 
            <td  class=tablebody1 colspan=2>
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
            <td  class=tablebody2 valign=middle colspan=2 align=center> 
              <input type=button value="����" name=Submit onclick="dosendmsg();">
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
	<script>
	parent.document.all.floater.innerHTML=msgcontent.innerHTML;
	</script>
<?php

}
?>