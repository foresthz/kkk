<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�����û��б�");

show_nav();

if ($loginok==1) {
?>
<table border="0" width="97%">
<?php
	showUserMailbox();
?>
</table>
<?php
}

head_var("̸��˵��","usermanagemenu.php",0);

//if ($loginok==1) {
	main();
//}else {
//	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
//}


if (isErrFounded()) {
		html_error_quit();
}

show_footer();

function main() {
	global $currentuser;
?>
<br>
<form action="usermailoperations.php" method=post id="oForm">
<input type="hidden" name="boxname" value="<?php echo $boxName; ?>">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>���</th>
<th valign=middle width=100>�û��˺�</th>
<th valign=middle width=280>�û��ǳ�</th>
<th valign=middle width=120>�û����ߵ�ַ</th>
<th valign=middle width=50>����</th>
<th valign=middle width=130>����</th>
</tr>
<?php
	$online_user_list = bbs_getonline_user_list();

	$num = count ( $online_user_list );

	for ( $i=0; $i<$num ; $i++ ) {
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $i+1 ?>
</td>
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $online_user_list[$i]['userid'] ; ?>" target=_blank>
<?php echo $online_user_list[$i]['userid'] ?></a>
</td>
<td class=TableBody1 align=left style="font-weight:normal"><a href="usermail.php?boxname=<?php echo $boxName; ?>&num=<?php echo $i+$startNum; ?>" > <?php       echo htmlspecialchars($online_user_list[$i]['username'],ENT_QUOTES); ?></a>	</td>
<td class=TableBody1 style="font-weight:normal"><?php echo $online_user_list[$i]['userfrom']; ?></td>
<td class=TableBody1 style="font-weight:normal"><?php printf('%02d:%02d',intval($online_user_list[$i]['idle']/60), ($online_user_list[$i]['idle']%60)); ?></td>
<td align=center valign=middle width=130 class=TableBody1>
<a href="#">��Ӻ���</a> <a href="#">ɾ������</a> <a href="#">������Ϣ</a> <a href="#">���Ͷ���</a>
</td>
</tr>
<?php
	}
?>
<tr> 
<td align=right valign=middle colspan=6 class=TableBody2>��������ʹ����<?php echo bbs_getmailusedspace() ;?>K����ռ䣬����<?php echo $mail_num; ?>����&nbsp;
<?php
			
		if ($startNum > 0)
		{
			$i = $startNum - ARTICLESPERPAGE;
			if ($i < 0) $i = 0;
			echo ' [<a href=usermailbox.php?boxname='.$boxName.'&start=0>��һҳ</a>] ';
			echo ' [<a href=usermailbox.php?boxname='.$boxName.'&start='.$i.'>��һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[��һҳ]</font>
<?php 
		}
		if ($startNum < $mail_num - ARTICLESPERPAGE)
		{
			$i = $startNum + ARTICLESPERPAGE;
			if ($i > $mail_num -1) $i = $mail_num -1;
			echo ' [<a href=usermailbox.php?boxname='.$boxName.'&start='.$i.'>��һҳ</a>] ';
			echo ' [<a href=usermailbox.php?boxname='.$boxName.'>���һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[���һҳ]</font>
<?php
		}
?>
<br>
<input type="hidden" name="action" id="oAction">
<input type="hidden" name="nums" id="oNums">
<input type="hidden" id="oNum">
<script >
function doAction(desc,action) {
	var nums,s,first;
	if(confirm(desc))	{
		oForm.oNums.value="";
		oForm.oAction.value=action;
		first=true;
		for (nums=new Enumerator(document.all.item("oNum"));!nums.atEnd();nums.moveNext()){
			s=nums.item();
			if (s.checked) {
				if (first) {
					first=false;
				} else {
					oForm.oNums.value+=',';
				}
				oForm.oNums.value+=s.value;
			}
		}
		return oForm.submit()
	}
	return false;
}
</script>
<input type=checkbox name=chkall value=on onclick="CheckAll(this.form)">ѡ��������ʾ�ż�&nbsp;<input type=button onclick="doAction('ȷ������/�������ѡ���ļ�¼��?','lock');" value="�����ż�">&nbsp;<input type=button onclick="doAction('ȷ��ɾ��ѡ���ļ�¼��?','delete');" value="ɾ���ż�">&nbsp;<input type=button onclick="doAction('ȷ�����<?php echo $desc; ?>���еļ�¼��?','deleteAll');" value="���<?php   echo $desc; ?>"></td>
</tr>
</table>
</form>
<?php
}


?>
