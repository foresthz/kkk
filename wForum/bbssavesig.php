<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�༭��ʾǩ����");

requireLoginok();

show_nav(false);

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();

show_footer();

function main(){
	global $currentuser;
	$filename=bbs_sethomefile($currentuser["userid"],"signatures");
	$fp=@fopen($filename,"w+");
    if ($fp==false) {
		foundErr("�޷����̣�����ϵ����Ա��");
	}
	fwrite($fp,str_replace("\r\n", "\n", $_POST["text"]));
	fclose($fp);
	bbs_recalc_sig();
	setSucMsg("ǩ�����ѳɹ��޸ģ�");
	return html_success_quit();
}
?>
