<?php
	require("www2-funcs.php");
	login_init();
	assert_login();
	page_header("ɾ������");
	$board = $_GET["board"];
	$file = $_GET["file"];

	$ret = bbs_delfile($board,$file); // 0 success -1 no perm  -2 wrong parameter
	switch($ret)
	{
	case -1:
		html_error_quit("����Ȩɾ������!");
		break;
	case -2:
		html_error_quit("����İ��������ļ���!");
		break;
	default:
		html_success_quit("ɾ���ɹ�.<br><a href=\"bbsdoc.php?board=" . $board . "\">���ر�������</a>");
	}
?>
