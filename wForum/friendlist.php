<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�����б�");

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
<th valign=middle width=280>����˵��</th>
<th valign=middle width=130>����</th>
</tr>
<?php
	define("USERSPERPAGE", 20); //ToDo: USERSPERPAGE should always be 20 here because of phplib - atppp
	$total_friends = bbs_countfriends($currentuser["userid"]);
	if( isset( $_GET["start"] ) ){
		$startNum = $_GET["start"];
		if ($startNum >= $total_friends) $startNum = $total_friends - USERSPERPAGE;
		if ($startNum < 0) $startNum = 0;
	} else {
		$startNum = 0;
	}
	$friends_list = bbs_getfriends($currentuser["userid"], $startNum);
    
	$count = count ( $friends_list );

	for ( $i=0; $i<$count ; $i++ ) {
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $startNum+$i+1; ?>
</td>
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $friends_list[$i]['ID'] ; ?>" target=_blank>
<?php echo $friends_list[$i]['ID'] ?></a>
</td>
<td class=TableBody1 align=left style="font-weight:normal"><a href="dispuser.php?id=<?php echo $friends_list[$i]['ID'] ; ?>" > <?php       echo htmlspecialchars($friends_list[$i]['EXP'],ENT_QUOTES); ?></a>	</td>
<td align=center valign=middle width=130 class=TableBody1>
<a href="#">ɾ������</a> [ToDo]
</td>
</tr>
<?php
	}
?>
<tr>
<td align=right valign=middle colspan=4 class=TableBody2>
<?php
			
		if ($startNum > 0)
		{
			$i = $startNum - USERSPERPAGE;
			if ($i < 0) $i = 0;
			echo ' [<a href=friendlist.php>��һҳ</a>] ';
			echo ' [<a href=friendlist.php?start='.$i.'>��һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[��һҳ]</font>
<?php 
		}
		if ($startNum < $total_friends - USERSPERPAGE)
		{
			$i = $startNum + USERSPERPAGE;
			if ($i >= $total_friends) $i = $total_friends - USERSPERPAGE;
			echo ' [<a href=friendlist.php?start='.$i.'>��һҳ</a>] ';
			echo ' [<a href=friendlist.php?start='.($total_friends - USERSPERPAGE).'>���һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[���һҳ]</font>
<?php
		}
?>
<br>
������ <b><?php echo $total_friends ; ?></b> λ���ѡ�
</td>
</tr>
</table>
</form>
<?php
}


?>
