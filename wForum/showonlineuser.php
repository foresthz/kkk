<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�����û��б�");

show_nav();

showUserMailBoxOrBR();

head_var("̸��˵��","usermanagemenu.php",0);

main();

show_footer();

function main() {
	global $loginok;
?>
<form action="" method=post id="oForm">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>���</th>
<th valign=middle width=100>�û��˺�</th>
<th valign=middle width=*>�û��ǳ�</th>
<th valign=middle width=120>�û����ߵ�ַ</th>
<th valign=middle width=50>����</th>
<th valign=middle width=220>����</th>
</tr>
<?php
    if( isset( $_GET["start"] ) ){
        $startNum = $_GET["start"];
    } else {
        $startNum = 1;
    }
    if ($startNum <= 0) $startNum = 1;
	$online_user_list = bbs_getonline_user_list($startNum, USERSPERPAGE);
    $total_online_num = bbs_getonlineusernumber();
    
	$count = count ( $online_user_list );

	$i = 0;
	foreach($online_user_list as $friend) {
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $startNum+$i; ?>
</td>
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $friend['userid'] ; ?>" target=_blank>
<?php echo $friend['userid'] ?></a>
</td>
<td class=TableBody1 align=left style="font-weight:normal"><a href="dispuser.php?id=<?php echo $friend['userid'] ; ?>" > <?php echo htmlspecialchars($friend['username'],ENT_QUOTES); ?></a>	</td>
<td align=center class=TableBody1 style="font-weight:normal"><?php echo $friend['userfrom']; ?></td>
<td align=center class=TableBody1 style="font-weight:normal"><?php printf('%02d:%02d',intval($friend['idle']/60), ($friend['idle']%60)); ?></td>
<td align=center valign=middle width=220 class=TableBody1><nobr>
<a target="_blank" href="friendlist.php?addfriend=<?php echo $friend['userid']; ?>">��Ӻ���</a>
| <a href="sendmail.php?receiver=<?php echo $friend['userid']; ?>">�����ʺ�</a>
<?php
	if ($loginok) {
?>
| <a href="javascript:replyMsg('<?php echo $friend['userid'] ; ?>')">������Ϣ</a>
<!-- | <a href="#">���Ͷ���</a> -->
<?php
	}
?>
</nobr></td>
</tr>
<?php
		$i++;
	}
?>
<tr>
<td align=right valign=middle colspan=6 class=TableBody2>
<?php
			
		if ($startNum > 1)
		{
			$i = $startNum - USERSPERPAGE;
			if ($i < 1) $i = 1;
			echo ' [<a href=showonlineuser.php>��һҳ</a>] ';
			echo ' [<a href=showonlineuser.php?start='.$i.'>��һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[��һҳ]</font>
<?php 
		}
		if ($startNum < $total_online_num - USERSPERPAGE) //��һ���ǲ�׼ȷ�ģ���Ϊû�п��������û������Ȳ����ˡ�- atppp
		{
			$i = $startNum + USERSPERPAGE;
			if ($i > $total_online_num -1) $i = $total_online_num -1;
			echo ' [<a href=showonlineuser.php?start='.$i.'>��һҳ</a>] ';
			echo ' [<a href=showonlineuser.php?start='.($total_online_num - USERSPERPAGE).'>���һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[���һҳ]</font>
<?php
		}
?>
<br>
Ŀǰ��̳���ܹ��� <b><?php echo bbs_getonlinenumber() ; ?></b> �����ߣ�����ע���û� <b><?php echo bbs_getonlineusernumber(); ?></b> �ˣ��ÿ� <b><?php echo bbs_getwwwguestnumber() ; ?></b> �ˡ�
</td>
</tr>
</table>
</form>
<?php
}


?>
