<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�༭��ʾǩ����");

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

function main(){
	global $currentuser;
	$filename=bbs_sethomefile($currentuser["userid"],"signatures");
	$fp=@fopen($filename,"w+");
    if ($fp==false) {
		foundErr("�޷����̣�����ϵ����Ա��");
		return false;
	}
	fwrite($fp,str_replace("\r\n", "\n", $_POST["text"]));
	fclose($fp);
	bbs_recalc_sig();
	setSucMsg("ǩ�����ѳɹ��޸ģ�");
	return html_success_quit();
}
?>
