<?php
require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");
require_once("inc/myface.inc.php");

setStat("���������ҳ");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
showUserManageMenu();
main();

show_footer();

function main(){
	global $currentuser;
	require("inc/userdatadefine.inc.php");
?>
<table cellpadding=0 cellspacing=6 width=97% align=center ><tr  align=center ><td  width=28% valign=top><table align=center style="width:100%" height=100% cellspacing=1 cellpadding=6 class=TableBorder1><tr><th height=25>�û�ͷ��</th></tr><tr align=center><td class=TableBody1>
<?php
	echo get_myface($currentuser, "align=absmiddle");
?>
</td></tr><tr><th height=25>������Ϣ</th></tr><tr><td align=left class=TableBody1 valign=top>�û��ȼ��� <?php echo bbs_getuserlevel($currentuser['userid']); ?><br>�û����ɣ� <?php echo showIt($groups[$currentuser['group']]); ?><br>�û��Ƹ��� <?php echo $currentuser['money']; ?><br>�û����飺 <?php echo $currentuser['score']; ?><br>�û������� N/A<br>���������� N/A<br>���������� <?php echo $currentuser['numposts']; ?><br>ע��ʱ�䣺 <?php echo strftime("%Y-%m-%d %H:%M:%S", $currentuser['firstlogin']); ?><br>��½������ <?php echo $currentuser['numlogins']; ?><br></td></tr></table><br><table align=center style="width:100%" height=100% cellspacing=1 cellpadding=6 class=TableBorder1><tr><th height=25>��������</th></tr><tr align=center><td class=TableBody1 align=left>��������ĺ��Ѱɣ�</td></tr><tr><td height=25 class=TableBody2>�����ͼ������ѷ��Ͷ�Ѷ��</td></tr></table></td>

<td valign=top><table cellpadding=3 cellspacing=1 style="width:100%" height=29 align=center  class=TableBorder1><tr><th height=25 align=left>-=> �û���������</td></tr><tr><td class=TableBody1><?php
	bbs_getmailnum($currentuser["userid"],$total,$unread);
	if ($unread<=0)  {
		echo "Ŀǰ��û���µ��ż�";
	} else {
		echo "����".$unread."�����ż�";
	}
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".DIR");
	$mail_num_r = bbs_getmailnum2($mail_fullpath);
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".SENT");
	$mail_num_w = bbs_getmailnum2($mail_fullpath);
?>��<a href=usermailbox.php?boxname=inbox><font color="#FF0000">�ռ���</font></a>�й��� <b>[<?php echo $mail_num_r; ?>]</b> ����Ϣ��<a href=usermailbox.php?boxname=sendbox><font color="#FF0000">������</font></a>�й��� <b>[<?php echo $mail_num_w; ?>]</b> ����Ϣ��<br></td></tr></table><br>

<table cellpadding=3 cellspacing=1 style="width:100%" align=center class=TableBorder1><tr><th colspan=5 height=25 align=left>-=> �����յ����ż�</th></tr><tr><td align=center valign=middle width=30 class=TableTitle2><b>״̬</b></td><td align=center valign=middle width=100 class=TableTitle2><b>������</b></td><td align=center valign=middle width=*  class=TableTitle2><b>����</b></td><td align=center valign=middle width=120 class=TableTitle2><b>����</b></td><td align=center valign=middle width=60 class=TableTitle2><b>��С</b></td></tr>
<?php
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".DIR");
	$num=3;
	$startNum=$mail_num_r-$num;
	$maildata = bbs_getmails($mail_fullpath,$startNum,$num);
	if ($maildata==FALSE) {
		echo '<tr><td class=TableBody1 align=center valign=middle colspan=6>�����ռ�����û���κ����ݡ�</td></tr></td></tr>';
	} else {
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
<td class=TableBody1 align=left style="font-weight:normal"><a href="usermail.php?boxname=<?php echo $boxName; ?>&num=<?php echo $i+$startNum; ?>" > <?php       echo htmlspecialchars($maildata[$i]['TITLE'],ENT_QUOTES); ?></a>	</td>
<td class=TableBody1 style="font-weight:normal"><?php echo strftime("%Y-%m-%d %H:%M:%S", $maildata[$i]['POSTTIME']); ?></td>
<td class=TableBody1 style="font-weight:normal"> N/A Byte</td>
</tr>
<?php
		}
	}
?>
</table><br>

<table cellpadding=3 cellspacing=1 style="width:100%" align=center class=TableBorder1><tr><th colspan=5 height=25 align=left>-=> �����ϴ��ļ�</th></tr><tr><td align=center valign=middle width=30 class=TableTitle2><b>����</b></td><td align=center valign=middle width=100 class=TableTitle2><b>��С</b></td><td align=center valign=middle width=* class=TableTitle2><b>�ļ�</b></td><td align=center valign=middle width=120 class=TableTitle2><b>����</b></td><td align=center valign=middle width=60 class=TableTitle2><b>����</b></td></tr><tr><td align=center valign=middle  class=TableBody1><img src='images/files/jpg.gif' border=0></td><td align=left valign=middle  class=TableBody1>42777 Byte</td><td align=left valign=middle  class=TableBody1><a href="myfile.asp" >200371516193444364.jpg</a></td><td align=left valign=middle  class=TableBody1>2003-7-15 16:19:34</td><td align=center valign=middle  class=TableBody1><b>ͼƬ��</b></td></tr><tr><td align=center valign=middle  class=TableBody1><img src='images/files/jpg.gif' border=0></td><td align=left valign=middle  class=TableBody1>57474 Byte</td><td align=left valign=middle  class=TableBody1><a href="myfile.asp" >20037151345023194.jpg</a></td><td align=left valign=middle  class=TableBody1>2003-7-15 13:45:00</td><td align=center valign=middle  class=TableBody1><b>ͼƬ��</b></td></tr><tr><td align=center valign=middle  class=TableBody1><img src='images/files/jpg.gif' border=0></td><td align=left valign=middle  class=TableBody1>227751 Byte</td><td align=left valign=middle  class=TableBody1><a href="myfile.asp" >20037151344982267.jpg</a></td><td align=left valign=middle  class=TableBody1>2003-7-15 13:44:09</td><td align=center valign=middle  class=TableBody1><b>ͼƬ��</b></td></tr></table><br>
<table cellpadding=3 cellspacing=1 style="width:100%" align=center class=TableBorder1><tr><th height=25 align=left>-=> ������������</th></tr> <tr><td align=left class=TableBody1>&nbsp;����&nbsp;<a href=dispbbs.asp?boardid=1&id=7&replyid=15#15>jkhkh</a>&nbsp;--&nbsp;2003-7-25 17:19:56</td></tr> <tr><td align=left class=TableBody1>&nbsp;����&nbsp;<a href=dispbbs.asp?boardid=1&id=7&replyid=14#14>khjklj</a>&nbsp;--&nbsp;2003-7-25 17:19:12</td></tr> <tr><td align=left class=TableBody1>&nbsp;����&nbsp;<a href=dispbbs.asp?boardid=1&id=7&replyid=13#13>jkhj,</a>&nbsp;--&nbsp;2003-7-25 17:18:44</td></tr> <tr><td align=left class=TableBody1>&nbsp;����&nbsp;<a href=dispbbs.asp?boardid=1&id=7&replyid=12#12>kjkj</a>&nbsp;--&nbsp;2003-7-25 17:18:35</td></tr> <tr><td align=left class=TableBody1>&nbsp;����&nbsp;<a href=dispbbs.asp?boardid=1&id=7&replyid=11#11>nmhjhjk</a>&nbsp;--&nbsp;2003-7-25 17:18:25</td></tr></table><br></td></tr></table>
<?php
}
?>
