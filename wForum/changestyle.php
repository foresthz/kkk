<?php

$needlogin=0;
require("inc/funcs.php");
setStat("��������");

do_changeStyle();

jumpReferer();

function do_changeStyle()
{
	if (!isset($_GET["style"])) { //ToDo: �Ƿ�Ҫ��� style �ǲ��ǺϷ���
		foundErr("�Ƿ��Ĳ�����");
	} 
	setcookie("style",$_GET["style"],time()+ 604800 ); //7*24*60*60
} 
?>