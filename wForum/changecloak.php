<?php

require("inc/funcs.php");
setStat("�л�����״̬");

do_changeCloak();

if (isErrFounded()) {
		show_nav();
		head_var("�л�����״̬");
		html_error_quit();
		show_footer();
} else {
  if (!isset($_SERVER["HTTP_REFERER"]) || ( $_SERVER["HTTP_REFERER"]=="") )
  {
	  header("Location: ".$SiteURL);
  }   else  {
	 header("Location: ".$_SERVER["HTTP_REFERER"]);
  } 
} 


function do_changeCloak()
{
	global $currentuser;
	global $currentuinfo;
	
	if (!strcasecmp($currentuser["userid"],"guest")) {
		foundErr("guest����ʹ������");
		return;
	}
	if (!($currentuser["userlevel"] & BBS_PERM_CLOAK)) {
		foundErr("��û������Ȩ��");
		return;
	}
	bbs_update_uinfo("invisible", !$currentuinfo["invisible"]);
} 
?>