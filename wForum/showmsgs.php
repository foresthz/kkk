<?php


require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�û����ŷ���");

show_nav();

if ($loginok==1) {
?>
<table border="0" width="97%">
<?php
	showUserMailbox();
?>
</table>
<?php
	head_var($userid."�Ŀ������","usermanagemenu.php",0);
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
}

show_footer();

function main() {
	global $currentuser;
	$msgs=bbs_getwebmsgs();
	$num=count($msgs);
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 width="97%"><tr><th colspan=3>����Ϣ��¼</td></tr>
<?php
	if ($num==0) {
?>
      <tr><td  class=TableBody1>���ڱ��η��ʱ�վ�ڼ�û���յ��κζ���Ϣ</td></tr>
<?php
	} else {
		for ($i=0;$i<$num;$i++) {
?>
     <tr> 
         <td class=TableBody2>
<?php
			if ($msg[$i]['SENT']) 
				echo '����'.strftime('%Y-%m-%d %H:%M:%S', $msgs[$i]['TIME']).'���͸�<b>'.$msgs[$i]['ID'].'</b>�Ķ���Ϣ��';
			else 
				echo '<b>'.$msgs[$i]['ID'].'</b>��'.strftime('%Y-%m-%d %H-%M-%S', $msgs[$i]['TIME']).'���͸����Ķ���Ϣ��';
?>
          </td>
          </tr>
           <tr> 
            <td  class=TableBody1>
			<?php echo $msgs[$i]['content']; ?>
            </td>
          </tr>
<?php
		}
?>
           <tr><td  class=TableBody2 align="middle"><form method="post" action="mailmsgs.php"><input type="submit" value="����¼���浽������"></form></td></tr>
<?php
	}
?>
        </table>
<?php
}
?>
