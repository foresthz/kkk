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
    if( isset( $_GET["start"] ) ){
        $startNum = $_GET["start"];
    } else {
        $startNum = 1;
    }
    if ($startNum <= 0) $startNum = 1;
    define("USERSPERPAGE", 20); //ToDo: put this define into site.php  - atppp
	$online_user_list = bbs_getonline_user_list($startNum, USERSPERPAGE);
    $total_online_num = bbs_getonlineusernumber();
    
	$count = count ( $online_user_list );

	for ( $i=0; $i<$count ; $i++ ) {
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $startNum+$i; ?>
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
