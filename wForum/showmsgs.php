<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�û����ŷ���");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();

show_footer();

function main() {
	$msgs=bbs_getwebmsgs();
	$num=count($msgs);
?>
<form method="post" action="mailmsgs.php">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 width="97%"><tr><th colspan=3>����Ϣ��¼</td></tr>
<?php
	if ($num==0) {
?>
      <tr><td  class=TableBody1 align="center">���ڱ��η��ʱ�վ�ڼ�û���յ��κζ���Ϣ</td></tr>
<?php
	} else {
		for ($i=0;$i<$num;$i++) {
?>
     <tr> 
         <td class=TableBody2>
<?php
			if (!$msgs[$i]['SENT']) 
				echo '����'.strftime('%Y-%m-%d %H:%M:%S', $msgs[$i]['TIME']).'���͸� <b>'.$msgs[$i]['ID'].'</b> �Ķ���Ϣ��';
			else {
				if ($msgs[$i]['MODE'] == 3)
					echo '<b>վ��</b> �� '.strftime('%Y-%m-%d %H:%M:%S', $msgs[$i]['TIME']).' �㲥��';
				else
					echo '<b>'.$msgs[$i]['ID'].'</b> �� '.strftime('%Y-%m-%d %H-%M-%S', $msgs[$i]['TIME']).' ���͸����Ķ���Ϣ��[<a href="javascript:replyMsg(\''.$msgs[$i]['ID'].'\')">����Ϣ</a>]';
			}
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
           <tr><td  class=TableBody2 align="middle"><input type="submit" value="����¼���浽������"></td></tr>
<?php
	}
?>
</table></form>
<?php
}
?>
