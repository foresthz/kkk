<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�û��ʼ�����");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();
showMailSampleIcon();

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
	if (getMailBoxPathDesc($boxName, $path, $desc)) {
		showUserManageMenu();
		showmailBoxes();
		showmailBox($boxName, $path, $desc, $startNum);
	} else {
		foundErr("��ָ���˴�����������ƣ�");
	}
}



function showmailBox($boxName, $path, $desc, $startNum){
	global $currentuser;
?>
<br>
<form action="usermailoperations.php" method=post id="oForm">
<input type="hidden" name="boxname" value="<?php echo $boxName; ?>">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>�Ѷ�</th>
<th valign=middle width=100>
<?php   if ($desc=="������")
    echo "�ռ���";  
  else
    echo "������";?>
</th>
<th valign=middle width=*>����</th>
<th valign=middle width=150>����</th>
<th valign=middle width=70>��С</th>
<th valign=middle width=30>ɾ��</th>
</tr>
<?php
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],$path);
	$mail_num = bbs_getmailnum2($mail_fullpath);
	if($mail_num < 0 || $mail_num > 30000) {
		foundErr('����'.$desc.'���ż�̫�࣡');
	}
	if($mail_num == 0) {
?>
<tr>
<td class=TableBody1 align=center valign=middle colspan=6>����<?php echo $desc; ?>��û���ż���</td>
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
	}
	for ($i = $num-1; $i >= 0; $i--){
?>
<tr>
<td class=TableBody1 align=center valign=middle>
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
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $maildata[$i]['OWNER'] ; ?>" target=_blank><?php echo $maildata[$i]['OWNER'] ; ?></a>
</td>
<td class=TableBody1 align=left style="padding-left: 10pt; font-weight:normal"><a href="usermail.php?boxname=<?php echo $boxName; ?>&num=<?php echo $i+$startNum; ?>" > <?php       echo htmlspecialchars($maildata[$i]['TITLE'],ENT_QUOTES); ?> </a>	</td>
<td class=TableBody1 style="font-weight:normal;text-align:center;"><?php echo strftime("%Y-%m-%d %H:%M:%S", $maildata[$i]['POSTTIME']); ?></td>
<td class=TableBody1 style="font-weight:normal;text-align:right;padding-right:10pt;"><?php
	if ($maildata[$i]["ATTACHPOS"]>0) echo "<img src=\"pic/havefolder.gif\" align=\"absmiddle\" title=\"�и���\">";
	$roy = $maildata[$i]['EFFSIZE'];
	if ($roy) echo ($roy);
	else echo "N/A Byte";
?></td>
<td align=center valign=middle class=TableBody1><input type=checkbox name=num id="num" value=<?php echo $i+$startNum; ?>></td>
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
<input type="hidden" name="action" id="Action">
<input type="hidden" name="nums" id="nums">
<script >
function doAction(desc,action) {
	var nums,s,first,i;
	if(confirm(desc))	{
		objForm=getRawObject("oForm");
		objNums=getRawObject("nums");
		objAction=getRawObject("Action");
		objNums.value="";
		objAction.value=action;
		first=true;
		nums=getObjectCollection("num");
		for (i=0;i<nums.length;i++){
			s=nums[i];
			if (s.checked) {
				if (first) {
					first=false;
				} else {
					objNums.value+=',';
				}
				objNums.value+=s.value;
			}
		}
		return objForm.submit()
	}
	return false;
}
</script>
<input type=checkbox name=chkall value=on onclick="CheckAll(this.form)">ѡ��������ʾ�ż�&nbsp;
<!--<input type=button onclick="doAction('ȷ������/�������ѡ���ļ�¼��?','lock');" value="�����ż�">&nbsp;-->
<input type=button onclick="doAction('ȷ��ɾ��ѡ���ļ�¼��?','delete');" value="ɾ���ż�">&nbsp;
<input type=button onclick="doAction('ȷ�����<?php echo $desc; ?>���еļ�¼��?','deleteAll');" value="���<?php   echo $desc; ?>">
</td></tr>
</table>
</form>
<?php
}


?>
