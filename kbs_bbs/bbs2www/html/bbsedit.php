<?php
	/**
	 * This file lists articles to user.
	 * $Id$
	 */
	require("funcs.php");
    login_init();
	require("boards.php");
	require("board.inc.php");
	if ($loginok != 1)
		html_nologin();
	else
	{
		html_init("gb2312","","",1);
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else
			html_error_quit("�����������");
		// ����û��ܷ��Ķ��ð�
		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		if ($brdnum == 0)
			html_error_quit("�����������");
		bbs_set_onboard($brdnum,1);
		$usernum = $currentuser["index"];
		if (bbs_checkreadperm($usernum, $brdnum) == 0)
			html_error_quit("�����������");
		$board = $brdarr['NAME'];
		if(bbs_checkpostperm($usernum, $brdnum) == 0) {
                    if (!strcmp($currentuser["userid"],"guest"))
		      html_error_quit("����ע���ʺ�");
                    else 
		      html_error_quit("�������������������Ȩ�ڴ���������������");
                }
		if (bbs_is_readonly_board($brdarr))
			html_error_quit("������ֻ����������������");
        
        if (isset($_GET['id']))
            $id = intval($_GET['id']);
        else
            html_error_quit("������ı��");
		$articles = array();
		$num = bbs_get_records_from_id($brdarr["NAME"], $id,$dir_modes["NORMAL"],$articles);
		if ($num == 0)
			html_error_quit("������ı��");
		$ret=bbs_caneditfile($board,$articles[1]['FILENAME']);
    	switch ($ret) {
        	case -1:
        		html_error_quit("���������ƴ���");
        		break;
        	case -2:
        		html_error_quit("���治���޸�����");
        		break;
        	case -3:
        		html_error_quit("�����ѱ�����ֻ��");
        		break;
        	case -4:
        		html_error_quit("�޷�ȡ���ļ���¼");
        		break;
        	case -5:
        		html_error_quit("�����޸���������!");
        		break;
        	case -6:
        		html_error_quit("ͬ��ID�����޸���ID������");
        		break;
        	case -7:
        		html_error_quit("����POSTȨ����");
        		break;
            default:
    	}

		$brd_encode = urlencode($brdarr["NAME"]);
	}
?>
<link rel="stylesheet" type="text/css" href="/ansi.css"/>
<script language=javascript>
<!--
function dosubmit() {
    document.postform.post.value='�ύ�У����Ժ�...';
    document.postform.post.disabled=true;
    document.postform.submit();
}
//-->
</script>
<body topmargin="0">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr> 
    <td class="b2">
	    <a href="bbssec.php" class="b2"><font class="b2"><?php echo BBS_FULL_NAME; ?></font></a>
	    -
	    <a href="/bbsdoc.php?board=<?php echo $brd_encode; ?>"><font class="b2"><?php echo $board; ?></font></a> ��
	    - �޸����� [ʹ����: <?php echo $currentuser["userid"]; ?>]
	 </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr><td align="center">
<?php
if (isset($_GET['do'])) {
    $ret=bbs_updatearticle($board,$articles[1]['FILENAME'],preg_replace("/\\\(['|\"|\\\])/","$1",$_POST['text']));
	switch ($ret) {
		case -1:
			html_error_quit("�޸�����ʧ�ܣ����¿��ܺ��в�ǡ������");
			break;
		case -10:
			html_error_quit("�Ҳ����ļ�!");
			break;
        case 0:
?>
<b>�����޸ĳɹ�</b>
<br /><br /><br />
[<a href="/bbsdoc.php?board=<?php echo $brd_encode; ?>">����<?php echo $brdarr['DESC']; ?>������</a>]
[<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>">���ء�<?php echo htmlspecialchars($articles[1]['TITLE']); ?>��</a>]
<br /><br />
<?php
            bbs_board_foot($brdarr,'');
            break;
        default:
            html_error_quit("ϵͳ����");
        
	}
}
else {
?>
<form name="postform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>&do">
<table border="0" cellspacing="5">
<tr>
<td class="b2 sb5">
<?php
		$notes_file = bbs_get_vote_filename($brdarr["NAME"], "notes");
		$fp = FALSE;
		if(file_exists($notes_file))
		{
		    $fp = fopen($notes_file, "r");
		    if ($fp == FALSE)
		    {
    	    	$notes_file = "vote/notes";
                if(file_exists($notes_file))
	    		    $fp = fopen($notes_file, "r");
    		}
		}
		if ($fp == FALSE)
    	{
?>
<font color="green">����ע������: <br />
����ʱӦ���ؿ������������Ƿ��ʺϹ������Ϸ������������ˮ��лл���ĺ�����<br/></font>
<?php
		}
        else
		{
		    fclose($fp);
			echo bbs_printansifile($notes_file);
		}
?>
</td>
</tr>
<tr><td class="b2 sb5">
������: <a href="/bbsqry.php?userid=<?php echo $articles[1]['OWNER']; ?>"><?php echo $articles[1]['OWNER']; ?></a>, ����: <?php echo $brd_encode; ?> [<a href="/bbsdoc.php?board=<?php echo $brd_encode; ?>">��������</a>]<br>
��&nbsp;&nbsp;��: <input readonly class="sb1" type="text" name="title" size="40" maxlength="100" value="<?php echo $articles[1]['TITLE']; ?>" />
[<a href="/bbsedittitle.php?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>">�޸ı���</a>]
<br /><br />
<textarea class="sb1" name="text"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {dosubmit(); return false;}'  onkeypress='if(event.keyCode==10) return dosubmit()' rows="20" cols="80" wrap="physical">
<?php
    bbs_printoriginfile($board,$articles[1]['FILENAME']);
?>
</textarea><br>
<center>
<input class="sb1" type="button" onclick="dosubmit();" name="post" value="�޸�" />
<input class="sb1" type="reset" value="��ԭ" />
<input class="sb1" type="reset" value="����" onclick="history.go(-1)" />
</center>
</td></tr>
</table></form>
<?php

}
?>
</td></tr>
</table>
<?php
html_normal_quit();
?>
