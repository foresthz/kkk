<?php
/*
 * ����� web��atppp ���졣
 * Suppports UTF8. Absolutely NO javascript!
 * BUG: û�����ƴ���������������ַ�������
 */
define('UTF8', FALSE);
define('ARTCNT', 20);
define('MAXCHAR', 20000);

require("www2-funcs.php");
require("www2-board.php");
login_init();

if (UTF8) {
	iconv_set_encoding("internal_encoding", "gb18030");
	iconv_set_encoding("output_encoding", "UTF-8");
	ob_start("ob_iconv_handler");
}

$atomic_header_shown = false;
$atomic_board = false;
$atomic_brdarr = array();
$atomic_brdnum = false;
$atomic_ftype = 0;


$act = @$_GET["act"];
switch($act) {
	case "post":
		atomic_post();
		break;
	case "article":
		atomic_article();
		break;
	case "board":
		atomic_board();
		break;
	case "mail":
		atomic_mail();
		break;
	case "mailread":
		atomic_mailread();
		break;
	case "mailpost":
		atomic_mailpost();
		break;
	case "logout":
		bbs_wwwlogoff();
		delete_all_cookie();
		cache_header("nocache");
		header("Location: atomic.php");
		exit;
	default:
		atomic_mainpage();
		break;
}

function atomic_header() {
	global $atomic_header_shown, $cachemode;
	if ($atomic_header_shown) return;
	if ($cachemode=="") {
		cache_header("nocache");
	}
	$atomic_header_shown = true;
	header("Content-Type: text/html; charset=".(UTF8?"UTF-8":"gb2312"));
	echo '<html><head><meta http-equiv="content-type" content="text/html; charset=' . (UTF8?"UTF-8":"gb2312") . '">'.
	     '<title>'.BBS_FULL_NAME.'</title></head><body>';
}

function atomic_footer() {
	echo "</body></html>";
}

function atomic_error($msg) {
	atomic_header();
	echo $msg . " <a href='atomic.php'>����ҳ</a>";
	atomic_footer();
	exit;
}

function atomic_get_input($str) {
	if (UTF8) return (iconv("UTF-8", "gb18030", $str));
	else return $str;
}

function atomic_show_boardjump() {
	global $atomic_board;
	if ($atomic_board) $bb = "��ǰ������: " . $atomic_board . ". ";
	else $bb = "";
		echo <<<END
<form action="" method="get"><input type="hidden" name="act" value="board"/>$bb
ȥ������: <input type="text" name="board" /> <input type="submit" value="Go"/> <a href='atomic.php'>����ҳ</a>
</form>
END;
}


function atomic_get_board($checkpost = false) {
	global $currentuser, $atomic_board, $atomic_brdarr, $atomic_brdnum, $atomic_ftype, $dir_modes;
	if (isset($_GET["board"]))
		$atomic_board = $_GET["board"];
	else{
		atomic_error("�����������");
	}
	$brdarr = array();
	$atomic_brdnum = bbs_getboard($atomic_board, $brdarr);
	$atomic_brdarr = $brdarr;
	if ($atomic_brdnum == 0){
		$boards = array();
		if (bbs_searchboard($atomic_board,0,$boards)) {
			if (sizeof($boards)==1) {
				cache_header("nocache");
				header("Location: atomic.php?act=board&board=" . urlencode($boards[0]['NAME']));
				exit;
			}
			if (sizeof($boards) > 1) {
				atomic_header();
				$html = "���ƥ���������: ";
				foreach ($boards as $board) {
					if (!$board['NAME']) continue;
					$html .= '<a href="?act=board&board=' . $board['NAME'] . '">' . $board['NAME'] . '</a> ';
				}
				echo $html;
				atomic_footer();
				exit;
			}
		}
		atomic_error("�����������");
	}
	$atomic_board = $atomic_brdarr["NAME"];
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $atomic_brdnum) == 0){
		atomic_error("�����������");
	}
	if ($atomic_brdarr["FLAG"]&BBS_BOARD_GROUP) {
		atomic_error("��֧�ְ�����");
	}
	bbs_set_onboard($atomic_brdnum,1);
	
	if ($checkpost) {
		if(bbs_checkpostperm($usernum, $atomic_brdnum) == 0) {
			atomic_error("�������������������Ȩ�ڴ���������������");
		}
		if (bbs_is_readonly_board($atomic_brdarr)) {
			atomic_error("������ֻ����������������");
		}
	}
	if (isset($_GET["ftype"])) {
		$atomic_ftype = @intval($_GET["ftype"]);
		switch($atomic_ftype) {
			case $dir_modes["DIGEST"]:
			case $dir_modes["MARK"]:
			case $dir_modes["NORMAL"]:
				break;
			default:
				atomic_error("�����ģʽ");
		}
	} else {
		$atomic_ftype = $dir_modes["NORMAL"];
	}
}

function atomic_board() {
	global $currentuser, $atomic_board, $atomic_brdarr, $atomic_brdnum, $dir_modes, $atomic_ftype;
	atomic_get_board();
	$isnormalboard = bbs_normalboard($atomic_board);
	if ($isnormalboard && (isset($_GET["page"])||$atomic_ftype) ) {
		$dotdirname = bbs_get_board_index($atomic_board, $atomic_ftype);
		if (cache_header("public",@filemtime($dotdirname),$atomic_ftype?300:10)) return;
	}
	atomic_header();
	atomic_show_boardjump();
	
	$total = bbs_countarticles($atomic_brdnum, $atomic_ftype);
	if ($total <= 0) {
		atomic_error("��������Ŀǰû������");
	}

	$page = isset($_GET["page"]) ? @intval($_GET["page"]) : 0;
	if (isset($_GET["start"])) {
		$page = (@intval($_GET["start"]) + ARTCNT - 1) / ARTCNT;
	}
	settype($page, "integer");
	$start = ($page > 0) ? ($page - 1) * ARTCNT + 1 : 0;
	if ($start == 0 || $start > ($total - ARTCNT + 1))
	{
		if ($total <= ARTCNT)
		{
			$start = 1;
			$page = 1;
		}
		else
		{
			$start = ($total - ARTCNT + 1);
			$page = ($start + ARTCNT - 1) / ARTCNT + 1;
		}
	}
	else
		$page = ($start + ARTCNT - 1) / ARTCNT;
	settype($page, "integer");
	$articles = bbs_getarticles($atomic_board, $start, ARTCNT, $atomic_ftype);
	if ($articles == FALSE)
		atomic_error("��ȡ�����б�ʧ��");
	
	$html = '<form action="?" method="get"><input type="hidden" name="act" value="board"/>';
	if ($atomic_ftype) {
		$html .= '<input type="hidden" name="ftype" value="' . $atomic_ftype . '"/>';
	}
	$html .= '<input type="hidden" name="board" value="'.$atomic_board.'"/>';
	$html .= '<a href="?act=post&board='.$atomic_board.'">����</a> ';
	$bl = '?act=board&board='.$atomic_board;
	if ($atomic_ftype) $bl .= '&ftype=' . $atomic_ftype;
	if ($page > 1) {
		$html .= '<a href="' . $bl .'&page=1">��һҳ</a> ';
		$html .= '<a href="' . $bl .'&page='.($page - 1).'">��ҳ</a> ';
	} else {
		$html .= '��һҳ ��ҳ ';
	}
	if ($start <= $total - 20) {
		$html .= '<a href="' . $bl .'&page='.($page + 1).'">��ҳ</a> ';
		$html .= '<a href="' . $bl .'">���һҳ</a> ';
	} else {
		$html .= '��ҳ ���һҳ ';
	}
	$html .= '<input type="submit" value="��ת��"/> �� <input type="text" name="start" size="3" /> ƪ ';
	if ($atomic_ftype != $dir_modes["NORMAL"]) {
		$html .= "<a href='?act=board&board=".$atomic_board."'>����</a> ";
	} else $html .= "<b>����</b> ";
	if ($atomic_ftype != $dir_modes["DIGEST"]) {
		$html .= "<a href='?act=board&board=".$atomic_board."&ftype=".$dir_modes["DIGEST"]."'>��ժ</a> ";
	} else $html .= "<b>��ժ</b> ";
	if ($atomic_ftype != $dir_modes["MARK"]) {
		$html .= "<a href='?act=board&board=".$atomic_board."&ftype=".$dir_modes["MARK"]."'>����</a> ";
	} else $html .= "<b>����</b> ";
	$html .= "</form>";

	$html .= "<pre> ���   �� �� ��     ��  ��  ���±���<br/>";
	$i = 0;
	foreach ($articles as $article)	{
		$title = $article["TITLE"];
		if (strncmp($title, "Re: ", 4) != 0)
			$title = "�� " . $title;

		$flags = $article["FLAGS"];

		if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) {
			$html .= " [��ʾ] ";
		} else {
			$html .= sprintf("%5d ", ($start+$i));
			if ($flags[0] == 'N' || $flags[0] == '*'){
				$html .= " "; //$flags[0];  //��Ҫδ����� windinsn
			} else{
				$html .= $flags[0];
			}
			$html .= " ";
		}
		$html .= sprintf("%-12.12s ", $article["OWNER"]);
		$html .= strftime("%b %e ", $article["POSTTIME"]);
		$html .= $flags[3];
		$articleurl = "?act=article&board=".$atomic_board."&id=".$article["ID"];
		if ($atomic_ftype) {
			$articleurl .= "&ftype=" . $atomic_ftype . "&num=" . ($start+$i);
		}
		$html .= "<a href='".$articleurl."'>".htmlspecialchars($title)." </a><br/>";
		$i++;
	}
	$html .= "</pre>";

	echo $html;
	atomic_footer();
}


function atomic_article() {
	global $currentuser, $atomic_board, $atomic_brdarr, $atomic_brdnum, $dir_modes, $atomic_ftype;
	atomic_get_board();
	$id = @intval($_GET["id"]);
	if ($id <= 0) atomic_error("��������º�");

	$url = "?act=article&board=" . $atomic_board . "&id=";
	@$ptr=$_GET["p"];
	// ͬ�����ָʾ�����ﴦ��
	if ($ptr == "tn" || $ptr == "tp") {
		$articles = bbs_get_threads_from_id($atomic_brdnum, $id, $dir_modes["NORMAL"],($ptr == "tp")?-1:1);
		if ($articles == FALSE)
			$redirt_id = $id;
		else
			$redirt_id = $articles[0]["ID"];
		header("Location: atomic.php" . $url . $redirt_id);
		exit;
	}

	$total = bbs_countarticles($atomic_brdnum, $atomic_ftype);
	if ($total <= 0) {
		atomic_error("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
	}

    if (!$atomic_ftype) {
        $articles = array ();
        $num = bbs_get_records_from_id($atomic_board, $id, $atomic_ftype, $articles);
        if ($num <= 0) atomic_error("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
        $article = $articles[1];
    } else {
        $num = @intval($_GET["num"]);
        if (($num <= 0) || ($num > $total)) atomic_error("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
        if (($articles = bbs_getarticles($atomic_board, $num, 1, $atomic_ftype)) === false) atomic_error("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
        if ($id != $articles[0]["ID"]) atomic_error("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
        $article = $articles[0];
    }
	$filename = bbs_get_board_filename($atomic_board, $article["FILENAME"]);
	$isnormalboard = bbs_normalboard($atomic_board);
	if ($isnormalboard) {
		if (cache_header("public",@filemtime($filename),300))
			return;
	}

	if (!$atomic_ftype) {
		$idx = ($ptr == 'p') ? 0 : (($ptr == 'n') ? 2 : -1);
		if (($idx != -1) && $articles[$idx]["ID"] != 0) {
			header("Location: atomic.php" . $url . $articles[$idx]["ID"]);
			exit;
		}
		if ($currentuser["userid"] != "guest") bbs_brcaddread($atomic_board, $article["ID"]);
	}

	atomic_header();
	$html = '<p>';
	if (!$atomic_ftype) {
		$html .= '<a href="?act=post&board='.$atomic_board.'">����</a> <a href="?act=post&board='.$atomic_board.'&reid='.$id.'">�ظ�</a> ';
		$html .= '<a href="' . $url . $article["ID"] . '&p=p">��ƪ</a> ';
		$html .= '<a href="' . $url . $article["ID"] . '&p=n">��ƪ</a> ';
		$html .= '<a href="' . $url . $article["ID"] . '&p=tp">������ƪ</a> ';
		$html .= '<a href="' . $url . $article["ID"] . '&p=tn">������ƪ</a> ';
		$html .= '<a href="' . $url . $article["GROUPID"] . '">¥��</a> ';
		$html .= '<a href="' . $url . $article["REID"] . '">��Դ</a> ';
	}
	$html .= '<a href="?act=board&board='.$atomic_board.'&page='.intval(($num + ARTCNT - 1) / ARTCNT).
	         ($atomic_ftype?"&ftype=".$atomic_ftype:"").'">�ذ���</a> ';
	$ourl = 'bbscon.php?board=' . $atomic_board . '&id=' . $article["ID"];
	if ($atomic_ftype) $ourl .= "&ftype=".$atomic_ftype."&num=".$num;
	$html .= '<a href="' . $ourl . '">ԭ��</a> ';
	$html .= '</p>';
	echo $html;
	echo bbs2_readfile_text($filename, MAXCHAR, 2);
	if ($article["ATTACHPOS"]) echo '<p>���ĺ��и���: <a href="' . $ourl . '">ԭ������</a></p>';
	atomic_footer();
}


function atomic_post() {
	global $currentuser, $atomic_board, $atomic_brdarr, $atomic_brdnum, $dir_modes;
	atomic_get_board(TRUE);

	$reid = (isset($_GET["reid"])) ? @intval($_GET["reid"]) : 0;
	if ($reid > 0) {
		if(bbs_is_noreply_board($atomic_brdarr))
			atomic_error("����ֻ�ɷ�������,���ɻظ�����!");

		$articles = array();
		$num = bbs_get_records_from_id($atomic_board, $reid, $dir_modes["NORMAL"], $articles);
		if ($num == 0) atomic_error("����� Re �ı��");
		if ($articles[1]["FLAGS"][2] == 'y')
			atomic_error("���Ĳ��ɻظ�!");
	}
	if (isset($_GET["post"])) {
		if (!isset($_POST["title"])) atomic_error("û��ָ�����±���!");
		if (!isset($_POST["text"])) atomic_error("û��ָ����������!");
		$title = atomic_get_input(trim($_POST["title"]));
		$text = atomic_get_input($_POST["text"]);
		if (isset($_GET["reid"])) $reID = @intval($_GET["reid"]);
		else $reID = 0;
		$outgo = bbs_is_outgo_board($atomic_brdarr) ? 1 : 0;
		$anony = 0;
		$ret = bbs_postarticle($atomic_board, $title, $text, $currentuser["signature"], $reID, $outgo, $anony, 0, 0);
		switch ($ret) {
		case -1:
			atomic_error("���������������!");
			break;
		case -2: 
			atomic_error("����Ϊ����Ŀ¼��!");
			break;
		case -3: 
			atomic_error("����Ϊ��!");
			break;
		case -4: 
			atomic_error("����������Ψ����, ����������Ȩ���ڴ˷�������!");
			break;		
		case -5:	
			atomic_error("�ܱ�Ǹ, �㱻������Աֹͣ�˱����postȨ��!");
			break;	
		case -6:
			atomic_error("���η���/�ż������,����Ϣ��������!");	
			break;
		case -7: 
			atomic_error("�޷���ȡ�����ļ�! ��֪ͨվ����Ա, лл! ");
			break;
		case -8:
			atomic_error("���Ĳ��ɻظ�!");
			break;
		case -9:
			atomic_error("ϵͳ�ڲ�����, ��Ѹ��֪ͨվ����Ա, лл!");
			break;
		}
		atomic_header();
		$url = "?act=board&board=" . $atomic_board;
		if ($ret == 2) {
			echo "<p>�ܱ�Ǹ�����Ŀ��ܺ��в������ݣ��辭��˷��ɷ���<br/><br/>" .
                  "���ݡ��ʺŹ���취������ϵͳ���˵�������ͬ�������������ĵȴ�<br/>" .
                  "վ����Ա����ˣ���Ҫ��γ��Է�������¡�<br/><br/>" .
                  "�������ʣ������� SYSOP ��ѯ��</p>";
            echo "����<a href='$url'>���������б�</a>";
		} else {
			echo "���ĳɹ�����ҳ�潫��3����Զ�����<a href='$url'>���������б�</a><meta http-equiv='refresh' content='3; url=" . $url . "'/>";
		}
		atomic_footer();
		return;
	}
	if ($reid) {
		if(!strncmp($articles[1]["TITLE"],"Re: ",4))$nowtitle = $articles[1]["TITLE"];
		else $nowtitle = "Re: " . $articles[1]["TITLE"];
	} else $nowtitle = "";
	atomic_header();
	$html = "<p><a href='?act=board&board=" . $atomic_board . "'>" . $atomic_board . " ��</a>��������</p>";
	$html .= "<form action='?act=post&board=" . $atomic_board . "&reid=" . $reid . "&post=1' method='post'>";
	$html .= '����: <input type="text" name="title" size="40" maxlength="100" value="' . ($nowtitle?htmlspecialchars($nowtitle,ENT_QUOTES)." ":"") . '"/><br/>';
	$html .= '<textarea name="text" rows="20" cols="80" wrap="physical">';
	if($reid > 0){
		$filename = bbs_get_board_filename($atomic_board, $articles[1]["FILENAME"]);
		$fp = @fopen($filename, "r");
		if ($fp) {
			$lines = 0;
			$quser = "δ֪";
			$buf = fgets($fp,256);       /* ȡ����һ���� ���������µ� ������Ϣ */
			$end = strrpos($buf,")");
			$start = strpos($buf,":");
			if($start != FALSE && $end != FALSE)
				$quser=substr($buf,$start+2,$end-$start-1);

			$html .= "\n\n�� �� " . $quser . " �Ĵ������ᵽ: ��\n";
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
					$html .= ": ...................\n";
					break;
				}
				$html .= ": ". htmlspecialchars($buf);
			}
			fclose($fp);
		}
	}
	$html .= '</textarea><br/><input type="submit" value="����" /></form>';
	echo $html;
	atomic_footer();
}

function atomic_mail_header() {
	global $currentuser;
	if ( ! strcmp($currentuser["userid"], "guest") ) {
		atomic_error("�ο�û������");
	}
	atomic_header();
	$html = "<p>" . $currentuser["userid"] . " ������ <a href='?'>����ҳ</a></p>";
	echo $html;
}

function atomic_mail() {
	global $currentuser;
	atomic_mail_header();
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".DIR");
	$mail_num = bbs_getmailnum2($mail_fullpath);
	if($mail_num <= 0 || $mail_num > 30000) atomic_error("��ȡ�ʼ�����ʧ��!");
	$start = (isset($_GET["start"])) ? @intval($_GET["start"]) : 999999;
	$num = ARTCNT;
	if ($start > $mail_num - ARTCNT + 1) $start = $mail_num - ARTCNT + 1;
	if ($start <= 0) {
		$start = 1;
		if ($num > $mail_num) $num = $mail_num;
	}
	$maildata = bbs_getmails($mail_fullpath, $start - 1, $num);
	if ($maildata == FALSE) atomic_error("��ȡ�ʼ�����ʧ��!");
	
	$html = '<form action="?" method="get"><input type="hidden" name="act" value="mail"/>';
	$html .= '<a href="?act=mailpost">д��</a> ';
	if ($start > 1) {
		$i = $start - ARTCNT;
		if ($i < 1) $i = 1;
		$html .= '<a href="?act=mail&start=1">��һҳ</a> ';
		$html .= '<a href="?act=mail&start='.$i.'">��һҳ</a> ';
	} else {
		$html .= '��һҳ ��һҳ ';
	}
	if ($start <= $mail_num - ARTCNT) {
		$i = $start + ARTCNT;
		if ($i > $mail_num) $i = $mail_num;
		$html .= '<a href="?act=mail&start='.$i.'">��һҳ</a> ';
		$html .= '<a href="?act=mail">���һҳ</a> ';
	} else {
		$html .= '��һҳ ���һҳ ';
	}
	$html .= '<input type="submit" value="��ת��"/> �� <input type="text" name="start" size="3" /> ƪ</form>';
	$html .= '<pre> ���    ������       ��  ��  ��  ��<br/>';
	for ($i = 0; $i < count($maildata); $i++) {
		$article = $maildata[$i];
		$title = $article["TITLE"];
		if (strncmp($title, "Re: ", 4) != 0)
			$title = "�� " . $title;

		$html .= sprintf("%5d ", ($start+$i));
		$html .= $maildata[$i]["FLAGS"];

		$html .= sprintf(" %-12.12s ", $article["OWNER"]);
		$html .= strftime("%b %e ", $article["POSTTIME"]);
		$html .= ($maildata[$i]["ATTACHPOS"]>0) ? "@" : " ";
		$html .= "<a href='?act=mailread&num=".($start+$i)."'>".htmlspecialchars($title)." </a><br/>";
	}
	$html .= "</pre>";
	echo $html;
	atomic_footer();
}

function atomic_mailread() {
	global $currentuser;
	atomic_mail_header();
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".DIR");
	$mail_num = bbs_getmailnum2($mail_fullpath);
	if($mail_num <= 0 || $mail_num > 30000) atomic_error("��ȡ�ʼ�����ʧ��!");
	if (isset($_GET["num"]))
		$num = @intval($_GET["num"]);
	else {
		atomic_error("����Ĳ���");
	}
	if ($num <= 0 || $num > $mail_num) atomic_error("����Ĳ���");
	$articles = array ();
	if( bbs_get_records_from_num($mail_fullpath, $num-1, $articles) ) {
		$filename = bbs_setmailfile($currentuser["userid"], $articles[0]["FILENAME"]);
	}else{
		atomic_error("����Ĳ���");
	}

	$html = "<p>";
	$html .= '<a href="?act=mailpost">д��</a> <a href="?act=mailpost&num='.$num.'">����</a> ';
	if($num > 1){
		$html .= '<a href="?act=mailread&num=' . ($num-1) . '">��һƪ</a> ';
	}
	$html .= '<a href="?act=mail&start=' . $num . '">�ռ���</a> ';
	if($num < $mail_num){
		$html .= '<a href="?act=mailread&num=' . ($num+1) . '">��һƪ</a> ';
	}
	$html .= "</p>";
	echo $html;
	echo bbs2_readfile_text($filename, 0, 2);
	bbs_setmailreaded($mail_fullpath,$num-1);
	atomic_footer();
}

function atomic_mailpost() {
	global $currentuser;
	atomic_mail_header();
	if (!bbs_can_send_mail()) atomic_error("�����ܷ����ż�");

	$num = (isset($_GET["num"])) ? @intval($_GET["num"]) : 0;
	$mail_fullpath = bbs_setmailfile($currentuser["userid"],".DIR");
	if ($num > 0) {
		$articles = array ();
		if( bbs_get_records_from_num($mail_fullpath, $num-1, $articles) ) {
			$title = $articles[0]["TITLE"];
			$receiver = $articles[0]["OWNER"];
			$shortfilename = $articles[0]["FILENAME"];
			$filename = bbs_setmailfile($currentuser["userid"], $shortfilename);
		}else{
			atomic_error("����Ĳ���");
		}
		
	}
	if (isset($_GET["post"])) {
		$title = atomic_get_input(trim(@$_POST["title"]));
		if (!$title) $title = '������';
		$content = atomic_get_input(@$_POST["text"]);
		$sig = $currentuser["signature"];
		$backup = (bbs_is_save2sent() != 0);
		
		if ($num > 0) {
			$ret = bbs_postmail($mail_fullpath, $shortfilename, $num-1, $title, $content, $sig, $backup);
		} else {
			$incept = trim(@$_POST['userid']);
			if (!$incept)
				atomic_error("�������ռ���ID");
			$lookupuser = array();
			if (!bbs_getuser($incept,$lookupuser))
				atomic_error("������ռ���ID");
			$incept = $lookupuser['userid'];
		
			if (!strcasecmp($incept,'guest')) atomic_error("���ܷ��Ÿ�guest");
			
			$ret = bbs_postmail($incept,$title,$content,$sig,$backup);
		}

		if ($ret < 0)  {
			switch($ret) {
				case -1:
				case -2:
					atomic_error("�޷������ļ�");
					break;
				case -3:
					atomic_error($incept." ���������ʼ�");
					break;
				case -4:
					atomic_error($incept." ����������");
					break;
				case -5:
					atomic_error("���η���/�ż������,����Ϣ��������!");	
					break;
				case -6:
					atomic_error("����ʼ��б����");
					break;
				case -7:
					atomic_error("�ʼ����ͳɹ�����δ�ܱ��浽������");
					break;
				case -8:
					atomic_error("�Ҳ������ظ���ԭ�š�");
					break;
				case -100:
					atomic_error("������ռ���ID");
					break;
				default:
					atomic_error("ϵͳ��������ϵ����Ա");
			}
		}
		
		if ($num > 0) {
			$url = "?act=mailread&num=".$num;
			echo "���ͳɹ�����ҳ�潫��3����Զ�����<a href='$url'>ԭ�ż�</a><meta http-equiv='refresh' content='3; url=" . $url . "'/>";
		} else {
			$url = "?act=mail";
			echo "���ͳɹ�����ҳ�潫��3����Զ�����<a href='$url'>�ż��б�</a><meta http-equiv='refresh' content='3; url=" . $url . "'/>";
		}
		atomic_footer();
		return;
	}
	
	if ($num > 0) {
		if(!strncmp($title,"Re: ",4)) $nowtitle = $title;
		else $nowtitle = "Re: " . $title;
	} else {
		$nowtitle = "";
	}
	$html = "<form action='?act=mailpost&num=".$num."&post=1' method='post'>";
	$html .= '����: <input type="text" name="title" size="40" maxlength="100" value="' . ($nowtitle?htmlspecialchars($nowtitle,ENT_QUOTES)." ":"") . '"/><br/>';
	if ($num > 0) {
		$html .= "�ռ���: " . $receiver . "<br/>";
	} else {
		$html .= '�ռ���: <input type="text" name="userid"/><br/>';
	}
	$html .= '<textarea name="text" rows="20" cols="80" wrap="physical">';
	if ($num > 0) {
		$html .= "\n\n�� �� " . $receiver . " ���������ᵽ: ��\n";
		$fp = fopen($filename, "r");
		if ($fp) {
			$lines = 0;
			for ($i = 0; $i < 4; $i++) {
				if (($buf = fgets($fp,500)) == FALSE)
					break;
			}
			while (1) {
				if (($buf = fgets($fp,500)) == FALSE)
					break;
				if (strncmp($buf, ": ��", 4) == 0)
					continue;
				if (strncmp($buf, ": : ", 4) == 0)
					continue;
				if (strncmp($buf, "--\n", 3) == 0)
					break;
				if (strncmp($buf,'\n',1) == 0)
					continue;
				if (++$lines > 10) {
					$html .= ": ...................\n";
					break;
				}
				$html .= ": ". htmlspecialchars($buf);
			}
			fclose($fp);
		}
	}
	$html .= '</textarea><br/><input type="submit" value="����" /></form>';
	echo $html;
	atomic_footer();
}

function atomic_mainpage() {
	global $currentuser;
	atomic_header();
	if ( strcmp($currentuser["userid"], "guest") ) {
		$html = "<p>��ӭ " . $currentuser["userid"] . ". <a href='?act=logout'>ע��</a></p>";
		$select = 0;
		if(bbs_load_favboard($select)!=-1) {
			$boards = bbs_fav_boards($select, 1);
			if ($boards) {
				$html .= "<p>�����ղؼ�: ";
				$brd_name = $boards["NAME"];
				$brd_flag= $boards["FLAG"];
				$brd_bid= $boards["BID"];
				$rows = sizeof($brd_name);
				for ($i = 0; $i < $rows; $i++) {
					if ($brd_bid[$i] == -1) continue;
					if ($brd_flag[$i] == -1 ) continue;
					if ($brd_flag[$i] & BBS_BOARD_GROUP) continue;
					$html .= '<a href="?act=board&board=' . $brd_name[$i] . '">' . $brd_name[$i] . '</a> ';
				}
				$html .= '</p>';
			}
		}
		$oldtotal = 0; $oldunread = 0;
		if (!bbs_getmailnum($currentuser["userid"],$total,$unread, $oldtotal, $oldunread)) {
			$unread = $total = 0;
		}
		$html .= "<p><a href='?act=mail'>����</a>: $total ��, ����: $unread ��. <a href='?act=mailpost'>д��</a></p>";
		echo $html;
	} else {
		echo <<<END
<form action="bbslogin.php?mainurl=atomic.php" method="post">
�û���: <input type="text" name="id" /> ����: <input type="password" name="passwd" maxlength="39" />
<input type="submit" value="��¼"/>
</form>
END;
	}
	atomic_show_boardjump();
	echo "UTF8: " . (UTF8 ? "ON" : "OFF") . ". ������ʾ��������: " . MAXCHAR . ". &lt;�����Ჱ atppp ����&gt;";
	atomic_footer();
}

?>