<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�û��Զ������");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
showUserManageMenu();
main();

show_footer();

function showOptions($var_name, $userparam, $isWWW) {
	global $$var_name;
	$userdefine = $$var_name;
	$ccc = count($userdefine);
	for ($i = 0; $i < $ccc; $i++) {
		if ($userdefine[$i][0]!=$isWWW) 
			continue;
		$flag=1<<$i;
?>
<tr><td width="40%" class=TableBody1><B><?php echo $userdefine[$i][1]; ?></B>��<BR><?php echo $userdefine[$i][2]; ?></td>   
        <td width="60%" class=TableBody1>    
			<input type="radio" name="<?php echo $var_name."_".$i; ?>" value="1" <?php if ($userparam & $flag) echo "checked"; ?> ><?php echo $userdefine[$i][3]; ?>
			<input type="radio" name="<?php echo $var_name."_".$i; ?>" value="0" <?php if (!($userparam & $flag)) echo "checked"; ?> ><?php echo $userdefine[$i][4]; ?>
        </td>   
</tr>
<?php
	}	
}

function showDefines($isWWW) {
	global $currentuser;
	global $currentuinfo;
	showOptions("user_define", $currentuser['userdefine0'], $isWWW);
	showOptions("user_define1", $currentuser['userdefine1'], $isWWW);
	showOptions("mailbox_prop", $currentuinfo['mailbox_prop'], $isWWW);
}

function main(){
	global $currentuser;
	global $user_define,$user_define_num;
	require("inc/userdatadefine.inc.php");
?>
<br>
<form action="saveuserparam.php" method=POST name="theForm">
<table cellpadding=3 cellspacing=1 border=0 align=center class=TableBorder1>
<tr> 
      <th colspan="2" width="100%">�û����˲���</th>
 </tr> 
<?php
	showDefines(1);
	if (SHOWTELNETPARAM == 1) {
?>
<tr> 
      <th colspan="2" width="100%">�û����˲�����telnet��ʽר�ã�</th>
 </tr> 
<?php
		showDefines(0);
	}
?>
<tr align="center"> 
<td colspan="2" width="100%" class=TableBody2>
<input type=Submit value="�� ��" name="Submit"> &nbsp; <input type="reset" name="Submit2" value="�� ��">
</td></tr>
</table></form>
<?php
}
?>
