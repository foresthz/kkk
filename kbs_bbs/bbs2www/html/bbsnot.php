<?php
	/**
	 * $Id$ 
	 */
	require("funcs.php");
login_init();
	if ($loginok !=1 )
		html_nologin();
	else
	{
                $usernum = $currentuser["index"];
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else 
			html_error_quit("�����������");
                $brdarr = array();
                $brdnum = bbs_getboard($board, $brdarr);
                if ($brdnum == 0)
			html_error_quit("�����������");
		if (bbs_checkreadperm($usernum,$brdnum)==0)
			html_error_quit("�����������");
		$top_file= bbs_get_vote_filename($brdarr["NAME"], "notes");
		$fp = fopen($top_file, "r");
		if ($fp == FALSE) {
		        html_init("gb2312");
			html_error_quit("����û�б���¼");
                }
                fclose($fp);
                if (cache_header("public",filemtime($top_file),300))
                	return;
		html_init("gb2312");
		$brd_encode = urlencode($brdarr["NAME"]);
?>
<body>
<center><?php echo BBS_FULL_NAME; ?> -- ����¼ [������: <?php echo $brdarr["NAME"]; ?>]
<hr class="default">
<table border=1 width=610><tr><td>
<?php
	echo bbs_printansifile($top_file);
?></tr></td>
</table>
[<a href=bbsdoc.php?board=<?php echo $brd_encode; ?>>��������</a>]
<?php
    if (bbs_is_bm($brdnum,$usernum))
	{
?>
[<a href="bbsmnote.php?board=<?php echo $brd_encode; ?>">�༭���滭��</a>]
<?php
	}
?> 
</center>
<?php
		html_normal_quit();
	}
?>
