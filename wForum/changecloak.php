<?php

require("inc/funcs.php");
setStat("�л�����״̬");

requireLoginok("guest����ʹ������");

do_changeCloak();

if (!isset($_SERVER["HTTP_REFERER"]) || ( $_SERVER["HTTP_REFERER"]=="") )
{
	header("Location: index.php");
}   else  {
	header("Location: ".$_SERVER["HTTP_REFERER"]);
} 

function do_changeCloak()
{
	global $currentuser;
	global $currentuinfo;

	if (!($currentuser["userlevel"] & BBS_PERM_CLOAK)) {
		foundErr("��û������Ȩ��");
	} else {
		bbs_update_uinfo("invisible", !$currentuinfo["invisible"]);
	}
} 
?>