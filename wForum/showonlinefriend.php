<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("���ߺ����б�");

requireLoginok();

show_nav();

showUserMailBox();
head_var("̸��˵��","usermanagemenu.php",0);
main();

show_footer();

function main() {
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
	$online_user_list = bbs_getonlinefriends();
    
	$count = count ( $online_user_list );

	$i = 0;
	foreach($online_user_list as $friend) {
		$i++;
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $i; ?>
</td>
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $friend['userid'] ; ?>" target=_blank>
<?php echo $friend['userid'] ?></a>
</td>
<td class=TableBody1 align=left style="font-weight:normal"><a href="dispuser.php?id=<?php echo $friend['userid'] ; ?>" > <?php echo htmlspecialchars($friend['username'],ENT_QUOTES); ?></a>	</td>
<td align=center class=TableBody1 style="font-weight:normal"><?php echo $friend['userfrom']; ?></td>
<td align=center class=TableBody1 style="font-weight:normal"><?php printf('%02d:%02d',intval($friend['idle']/60), ($friend['idle']%60)); ?></td>
<td align=center valign=middle width=220 class=TableBody1><nobr>
<a target="_blank" href="friendlist.php?delfriend=<?php echo $friend['userid']; ?>">ɾ������</a>
| <a href="sendmail.php?receiver=<?php echo $friend['userid']; ?>">�����ʺ�</a>
| <a href="javascript:replyMsg('<?php echo $friend['userid'] ; ?>')">������Ϣ</a>
<!-- | <a href="#">���Ͷ���</a> -->
</nobr></td>
</tr>
<?php
	}
?>
<tr>
<td align=right valign=middle colspan=6 class=TableBody2>
<a href="friendlist.php">�༭��������</a>�������� <b><?php echo $count; ?></b> λ�������ߡ�
</td>
</tr>
</table>
</form>
<?php
}


?>
