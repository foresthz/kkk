<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�༭��ʾǩ����");

show_nav();

if ($loginok==1) {
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
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

function main(){
	global $currentuser;
?>
<form name=form1 method="post" action="bbssavesig.php">
<table cellpadding=3 cellspacing=1 class=TableBorder1 align=center>
	<tr>
    <th width=100% height=25 colspan=2 align=center>�༭��ʾǩ���� [ʹ����: <?php echo $currentuser["userid"]; ?>]</th>
    </tr>
	<tr>
          <td width=100% class=TableBody1 align="center"><textarea name="text"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.form1.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.form1.submit()' rows="20" cols="100" wrap="physical">
<?php
	$filename=bbs_sethomefile($currentuser["userid"],"signatures");
    $fp = @fopen ($filename, "r");
    if ($fp!=false) {
		while (!feof ($fp)) {
			$buffer = fgets($fp, 300);
			echo $buffer;
		}
		fclose ($fp);
    }

?>
</textarea></td></tr>
	<tr><td width=100% class=TableBody1 align="center">
	<input type="submit" value="����" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="��ԭ" />
	</td></tr>
</table>
</form>
<?php
}
?>
