<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�����û��б�");

show_nav();

if ($loginok==1) {
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showUserMailbox();
?>
</table>
<?php
} else {
	echo "<br>";
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
<form action="" method=post id="oForm">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>���</th>
<th valign=middle width=100>�û��˺�</th>
<th valign=middle width=280>�û��ǳ�</th>
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
<td class=TableBody1 style="font-weight:normal"><?php echo $friend['userfrom']; ?></td>
<td class=TableBody1 style="font-weight:normal"><?php printf('%02d:%02d',intval($friend['idle']/60), ($friend['idle']%60)); ?></td>
<td align=center valign=middle width=220 class=TableBody1>
<a target="_blank" href="friendlist.php?addfriend=<?php echo $friend['userid']; ?>">��Ӻ���</a> <a target="_blank" href="friendlist.php?delfriend=<?php echo $friend['userid']; ?>">ɾ������</a> <a href="javascript:replyMsg('<?php echo $friend['userid'] ; ?>')">������Ϣ</a> <a href="#">���Ͷ���</a>
</td>
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
