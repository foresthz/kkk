<?php
require("funcs.php");

if( !defined("HAVE_BRDENV") )
	exit();

if (isset($_GET["board"]))
	$board = $_GET["board"];
else{
        html_init("gb2312","","",1);
	html_error_quit("�����������");
	exit();
}

$brdarr = array();
$brdnum = bbs_getboard($board, $brdarr);
if ($brdnum == 0){
        html_init("gb2312","","",1);
	html_error_quit("�����������");
	exit();
}
$usernum = $currentuser["index"];
if (bbs_checkreadperm($usernum, $brdnum) == 0){
        html_init("gb2312","","",1);
	html_error_quit("�����������");
	exit();
}

$brd_encode = urlencode($brdarr["NAME"]);
$relatefile = $_SERVER["DOCUMENT_ROOT"]."/brelated/".$brdarr["NAME"].".html";
html_init("gb2312","","",1);
?>
<body topmargin="0" leftmargin="0">
<center>
<?php
if( file_exists( $relatefile ) ){
?>
���������˳�ȥ���������棺
<?php
include($relatefile);
}
?>
</center>
<?php
html_normal_quit();
?>