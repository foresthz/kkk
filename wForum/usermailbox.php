<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�û��ʼ�����");

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

head_var($userid."�Ŀ������","usermanagemenu.php",0);

if ($loginok==1) {
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}


if (isErrFounded()) {
		html_error_quit();
} else {
	showMailSampleIcon();
}

show_footer();

function main() {
	global $_GET;

	$boxName=$_GET['boxname'];
	if (!isset($_GET['start'])) {
		$startNum=99999;
	} else {
		$startNum=intval($_GET['start']);
	}

	if ($boxName=='') {
		$boxName='inbox';
	}
	if ($boxName=='inbox') {
		showUserManageMenu();
		showmailBoxes();
		showmailBox('inbox','.DIR','�ռ���', $startNum);
		return true;
	}
	if ($boxName=='sendbox') {
		showUserManageMenu();
		showmailBoxes();
		showmailBox('sendbox','.SENT','������',$startNum );
		return true;
	}
	if ($boxName=='deleted') {
		showUserManageMenu();
		showmailBoxes();
		showmailBox('deleted','.DELETED','������',$startNum);
		return true;
	}
	foundErr("��ָ���˴�����������ƣ�");
	return false;
}



function showmailBox($boxName, $path, $desc, $startNum){
	global $currentuser;
?>
<br>
<form action="usermailoperations.php" method=post id="oForm">
<input type="hidden" name="boxname" value="<?php echo $boxName; ?>">
<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
<tr>
<th valign=middle width=30 height=25>�Ѷ�</th>
<th valign=middle width=100>
<?php   if ($Desc=="������")
    echo "�ռ���";  
  else
    echo "������";?>
</th>
<th valign=middle width=380>����</th>
<th valign=middle width=120>����</th>
<th valign=middle width=50>��С</th>
<th valign=middle width=30>ɾ��</th>
</tr>
<?php
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],$path);
	$mail_num = bbs_getmailnum2($mail_fullpath);
	if($mail_num < 0 || $mail_num > 30000) {
		foundErr('����'.$desc.'���ż�̫�࣡');
		return false;
	}
	if($mail_num == 0) {
?>
<tr>
<td class=tablebody1 align=center valign=middle colspan=6>����<?php echo $desc; ?>��û���ż���</td>
</tr>
</table>
<?php
	return false;
	}
	$num=ARTICLESPERPAGE;
	if ($startNum > $mail_num - $num ) $startNum = $mail_num - $num ;
	if ($startNum < 0)
	{
		$startNum = 0;
		$num = $mail_num;
	}
	$maildata = bbs_getmails($mail_fullpath,$startNum,$num);
	if ($maildata == FALSE) {
			foundErr("��ȡ�ʼ�����ʧ��!");
			return false;
	}
	for ($i = 0; $i < $num; $i++){
?>
<tr>
<td class=tablebody1 align=center valign=middle>
<?php 
		
		if ($maildata[$i]["FLAGS"][1]=='R') {
			
			switch($maildata[$i]["FLAGS"][0]){
			case 'M':
			case 'm':
				echo  '<img src="pic/m_lockreplys.gif">';
					break;
			default:
				echo  '<img src="pic/m_replys.gif">';
			}
		} else {
			switch($maildata[$i]["FLAGS"][0]){
			case 'N':
				echo  '<img src="pic/m_news.gif">';
				break;
			case 'M':
				echo  '<img src="pic/m_oldlocks.gif">';
					break;
			case 'm':
				echo  '<img src="pic/m_newlocks.gif">';
					break;
			default:
				echo  '<img src="pic/m_olds.gif">';
			}
		}
?>
</td>
<td class=tablebody1 align=center valign=middle style="font-weight:normal">
<a href="userinfo.php?id=<?php echo $maildata[$i]['OWNER'] ; ?>" target=_blank><?php echo $maildata[$i]['OWNER'] ; ?></a>
</td>
<td class=tablebody1 align=left style="font-weight:normal"><a href="usermail.php?boxname=<?php echo $boxName; ?>&num=<?php echo $i+$startNum; ?>" > <?php       echo htmlspecialchars($maildata[$i]['TITLE'],ENT_QUOTES); ?></a>	</td>
<td class=tablebody1 style="font-weight:normal"><?php echo strftime("%Y-%m-%d %H:%M:%S", $maildata[$i]['POSTTIME']); ?></td>
<td class=tablebody1 style="font-weight:normal"> N/A Byte</td>
<td align=center valign=middle width=30 class=tablebody1><input type=checkbox name=num id="oNum" value=<?php echo $i+$startNum; ?>></td>
</tr>
<?php
	}
?>
<tr> 
<td align=right valign=middle colspan=6 class=tablebody2>��������ʹ����<?php echo bbs_getmailusedspace() ;?>K����ռ䣬����<?php echo $mail_num; ?>����&nbsp;
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
