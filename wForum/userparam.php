<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�û��Զ������");

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
	showUserManageMenu();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
		html_error_quit();
} 

show_footer();

function main(){
	global $currentuser;
	global $user_define,$user_define_num;
	require("inc/userdatadefine.inc.php");
?>
<form action="saveuserparam.php" method=POST name="theForm">
<table cellpadding=3 cellspacing=1 border=0 align=center class=TableBorder1>
<tr> 
      <th colspan="2" width="100%">�û����˲�����www��ʽ��</th>
 </tr> 
 <?php
	$userparam=bbs_getuserparam();
	for ($i=$user_define_num-1;$i>=0;$i--){
		if ($user_define[$i][0]!=1) 
			continue;
		$flag=1<<$i;
?>
<tr><td width="40%" class=TableBody1><B><?php echo $user_define[$i][1]; ?></B>��<BR><?php echo $user_define[$i][2]; ?></td>   
        <td width="60%" class=TableBody1>    
			<input type="radio" name="param<?php echo $i; ?>" value="1" <?php if ($userparam & $flag) echo "checked"; ?> ><?php echo $user_define[$i][3]; ?>
			<input type="radio" name="param<?php echo $i; ?>" value="0" <?php if (!($userparam & $flag)) echo "checked"; ?> ><?php echo $user_define[$i][4]; ?>
        </td>   
</tr>
<?php
	}
?>
<tr> 
      <th colspan="2" width="100%">�û����˲�����telnet��ʽ��</th>
 </tr> 
<?php
	$userparam=bbs_getuserparam();
	for ($i=0;$i<$user_define_num;$i++){
		if ($user_define[$i][0]!=0) 
			continue;
		$flag=1<<$i;
?>
<tr><td width="40%" class=TableBody1><B><?php echo $user_define[$i][1]; ?></B>��<BR><?php echo $user_define[$i][2]; ?></td>   
        <td width="60%" class=TableBody1>    
			<input type="radio" name="param<?php echo $i; ?>" value="1" <?php if ($userparam & $flag) echo "checked"; ?> ><?php echo $user_define[$i][3]; ?>
			<input type="radio" name="param<?php echo $i; ?>" value="0" <?php if (!($userparam & $flag)) echo "checked"; ?> ><?php echo $user_define[$i][4]; ?>
        </td>   
</tr>
<?php
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
