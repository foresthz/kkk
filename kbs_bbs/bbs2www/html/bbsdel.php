<?php
	/**
	 *  This file delete board article.
	 */
	require("funcs.php");
login_init();
	if ($loginok !=1 )
		html_nologin();
	else
	{
		html_init("gb2312");
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
?>
ɾ���ɹ�.<br><a href="/bbsdoc.php?board=<?php echo $board;?>">���ر�������</a>
<?php
			break;
		}
	}
?>
