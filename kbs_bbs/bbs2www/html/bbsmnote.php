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
		html_init("gb2312");
                $usernum = $currentuser["index"];
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else 
			html_error_quit("�����������");
                $brdarr = array();
                $brdnum = bbs_getboard($board, $brdarr);
                if ($brdnum == 0)
			html_error_quit("�����������");
		if (!bbs_is_bm($brdnum,$usernum))
			html_error_quit("�㲻�ǰ���" . $brdnum . "!!" . $usernum);
		$top_file="vote/" . $board . "/notes";
		if ($_GET["type"]=="update") {
			$fp = fopen($top_file, "w");
			if ($fp==FALSE) {
				html_error_quit("�޷����ļ�");
			} else {
				$data=$_POST["text"];
				fwrite($fp,str_replace("\r\n","\n",$data));
				fclose($fp);
?>
<body>
<center>
�޸ı���¼�ɹ�! <br />
[<a href=bbsdoc.php?board=<?php echo $board; ?>>��������</a>]
</center>
</body>
<?php
			}
		} else {
			$fp = fopen($top_file, "r");
?>
<body>
<center><?php echo BBS_FULL_NAME; ?> -- ����¼ [������: <?php echo $board; ?>]<hr color=green>
<form name=form1 method="post" action=<?php echo "\"bbsmnote.php?type=update&board=" . $board . "\""; ?>>
<table width="610" border="1"><tr><td><textarea  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.form1.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.form1.submit()' name="text" rows="20" cols="80" wrap="physical">
<?php
			if ($fp != FALSE) {
				for ($i=0;!feof($fp)&&$i<200;$i++)
					echo(fgets($fp,256));
				fclose($fp);
	                }
?> 
</textarea></td></tr></table>
<input type="submit" value="����"> <input type="reset" value="��ԭ">
</form>
<hr>
[<a href=bbsdoc.php?board=<?php echo $board; ?>>��������</a>]
</center>
</body>
<?php
        	}
		html_normal_quit();
	}
?>
