<?php

$needlogin=0;
require("inc/funcs.php");
setStat("��������");

do_changeStyle();

if (!isset($_SERVER["HTTP_REFERER"]) || ( $_SERVER["HTTP_REFERER"]=="") )
{
	header("Location: index.php");
} else {
	header("Location: ".$_SERVER["HTTP_REFERER"]);
} 

function do_changeStyle()
{
	if (!isset($_GET["style"])) { //ToDo: �Ƿ�Ҫ��� style �ǲ��ǺϷ���
		foundErr("�Ƿ��Ĳ�����");
	} 
	setcookie("style",$_GET["style"],time()+ 604800 ); //7*24*60*60
} 
?>