<?php
	require("www2-funcs.php");
	require("www2-board.php");
	login_init();
	bbs_session_modify_user_mode(BBS_MODE_POSTING);
	assert_login();

	if (isset($_GET["board"]))
		$board = $_GET["board"];
	else
		html_error_quit("�����������");
	// ����û��ܷ��Ķ��ð�
	$brdarr = array();
	$brdnum = bbs_getboard($board, $brdarr);
	if ($brdnum == 0)
		html_error_quit("�����������");
	$board = $brdarr["NAME"];
	bbs_set_onboard($brdnum,1);
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $brdnum) == 0)
		html_error_quit("�����������");
	if(bbs_checkpostperm($usernum, $brdnum) == 0) {
		html_error_quit("�������������������Ȩ�ڴ���������������");
	}
	if (bbs_is_readonly_board($brdarr))
		html_error_quit("������ֻ����������������");
	if (isset($_GET["reid"]))
	{
		$reid = $_GET["reid"];
		if(bbs_is_noreply_board($brdarr))
			html_error_quit("����ֻ�ɷ�������,���ɻظ�����!");
	}
	else {
		$reid = 0;
	}
	settype($reid, "integer");
	$articles = array();
	if ($reid > 0)
	{
		$num = bbs_get_records_from_id($board, $reid,$dir_modes["NORMAL"],$articles);
		if ($num == 0)
		{
			html_error_quit("����� Re �ı��");
		}
		if ($articles[1]["FLAGS"][2] == 'y')
			html_error_quit("���Ĳ��ɻظ�!");
	}
	$brd_encode = urlencode($board);
	
	bbs_board_nav_header($brdarr, $reid ? "�ظ�����" : "��������");
?>
<link rel="stylesheet" type="text/css" href="ansi.css"/>
<form name="postform" method="post" action="bbssnd.php?board=<?php echo $brd_encode; ?>&reid=<?php echo $reid; ?>" class="large">
<?php
	if (bbs_normalboard($board)) {
?>
<div class="article smaller" id="bbsnot">������������������¼...</div>
<iframe src="bbsnot.php?board=<?php echo $board; ?>" width="0" height="0" frameborder="0" scrolling="no"></iframe>
<?php
	} else {
?>
<div class="article smaller"><a href="bbsnot.php?board=<?php echo $brd_encode; ?>" target="_blank">�鿴����������¼</a></div>
<?php
	}
?>
<fieldset><legend><?php echo $reid ? "�ظ�����" : "��������"; ?></legend>
������: <?php echo $currentuser["userid"]; ?>, ����: <?php echo $brd_encode; ?> [<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>">��������</a>]<br/>
<?php
		if ($reid)
		{
	        if(!strncmp($articles[1]["TITLE"],"Re: ",4))$nowtitle = $articles[1]["TITLE"];
	        else
	            $nowtitle = "Re: " . $articles[1]["TITLE"];
	    } else {
	        $nowtitle = "";
	    }
?>
��&nbsp;&nbsp;��: <input type="text" tabindex="1" name="title" size="40" maxlength="100" value="<?php echo $nowtitle?htmlspecialchars($nowtitle,ENT_QUOTES)." ":""; ?>" <?php if (!$reid) echo 'id="sfocus"'; ?>/><br/>
<?php
		if (bbs_is_attach_board($brdarr))
		{
?>
��&nbsp;&nbsp;��: <input type="text" name="attachname" size="50" value="" disabled="disabled" />
<a href="bbsupload.php" target="_blank">��������</a>(�´��ڴ�)<br/>
<?php
		}
?>
ʹ��ǩ���� <select name="signature">
<?php
		if ($currentuser["signum"] == 0)
		{
?>
<option value="0" selected="selected">��ʹ��ǩ����</option>
<?php
		}
		else
		{
?>
<option value="0">��ʹ��ǩ����</option>
<?php
			for ($i = 1; $i <= $currentuser["signum"]; $i++)
			{
				if ($currentuser["signature"] == $i)
				{
?>
<option value="<?php echo $i; ?>" selected="selected">�� <?php echo $i; ?> ��</option>
<?php
				}
				else
				{
?>
<option value="<?php echo $i; ?>">�� <?php echo $i; ?> ��</option>
<?php
				}
			}
?>
<option value="-1" <?php if ($currentuser["signature"] < 0) echo "selected "; ?>>���ǩ����</option>
<?php
		}
?>
</select>
 [<a target="_blank" href="bbssig.php">�鿴ǩ����</a>]
<?php
    if (bbs_is_anony_board($brdarr))
    {
?>
<input type="checkbox" name="anony" value="1" />����
<?php
    }
    if (bbs_is_outgo_board($brdarr)) {
        $local_save = 0;
        if ($reid > 0) $local_save = !strncmp($articles[1]["INNFLAG"], "LL", 2);
?>
<input type="checkbox" name="outgo" value="1"<?php if (!$local_save) echo " checked=\"checked\""; ?> />ת��
<?php
    }
?>
<input type="checkbox" name="mailback" value="1" />re�ĳ�������
<br />
<textarea name="text" tabindex="2" onkeydown='return textarea_okd(dosubmit, event);' wrap="physical" <?php if ($reid) echo 'id="sfocus"'; ?>>
<?php
    if($reid > 0){
    $filename = $articles[1]["FILENAME"];
    $filename = "boards/" . $board . "/" . $filename;
	if(file_exists($filename))
	{
	    $fp = fopen($filename, "r");
        if ($fp) {
		    $lines = 0;
            $buf = fgets($fp,256);       /* ȡ����һ���� ���������µ� ������Ϣ */
			$end = strrpos($buf,")");
			$start = strpos($buf,":");
			if($start != FALSE && $end != FALSE)
			    $quser=substr($buf,$start+2,$end-$start-1);

            echo "\n�� �� " . $quser . " �Ĵ������ᵽ: ��\n";
            for ($i = 0; $i < 3; $i++) {
                if (($buf = fgets($fp,500)) == FALSE)
                    break;
            }
            while (1) {
                if (($buf = fgets($fp,500)) == FALSE)
                    break;
                if (strncmp($buf, "��", 2) == 0)
                    continue;
                if (strncmp($buf, ": ", 2) == 0)
                    continue;
                if (strncmp($buf, "--\n", 3) == 0)
                    break;
                if (strncmp($buf, "\n", 1) == 0)
                    continue;
                if (++$lines > QUOTED_LINES) {
                    echo ": ...................\n";
                    break;
                }
                //if (stristr($buf, "</textarea>") == FALSE)  //filter </textarea> tag in the text
                    echo ": ". htmlspecialchars($buf);
            }
			echo "\n\n";
            fclose($fp);
        }
    }
}
?>
</textarea><br/>
<div class="oper">
<input type="button" onclick="dosubmit();" tabindex="3" name="post" value="����" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input class="sb1" type="reset" value="����" onclick="history.go(-1)" />
</div>
</fieldset></form>
<?php
page_footer();
?>
