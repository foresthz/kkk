<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $filename;
global $readmode;
global $attachURL;
global $ftype;
global $is_tex;

preprocess();

@$attachpos=$_GET["ap"];
if ($attachpos!=0) {
	require("inc/attachment.inc.php");
	if (bbs_normalboard($boardName)) {
		if (cache_header('public',filemtime($filename),300))
			return;
	}
	output_attachment($filename, $attachpos);
	exit;
}

setStat($readmode."�����Ķ�");

show_nav($boardName, $is_tex, ($ftype==$dir_modes["DIGEST"])?getBoardRSS($boardName, $ftype, $readmode):"");

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
display_file($filename, $is_tex);
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $ftype;
	global $readmode;
	global $filename;
	global $attachURL;
	global $is_tex;
	$ftype = @intval($_GET['ftype']);
	$sorted = false;
	$readmode = getModenameIfAllowed($ftype, $sorted);
	if ($readmode === false) {
		foundErr("�����ģʽ��");
	}

	if (!isset($_GET['bid'])) {
		foundErr("δָ�����档");
	}
	$boardID = intval($_GET["bid"]);
	if( $boardID == 0 ){
		foundErr("δָ�����档");
	}
	$boardName = bbs_getbname($boardID);
	if( !$boardName ){
		foundErr("ָ���İ��治����");
	}
	$brdArr=array();
	$boardID= bbs_getboard($boardName, $brdArr);
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
	}
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardArr['FLAG'] & BBS_BOARD_GROUP ) {
		foundErr("ָ���İ��治����");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
	}
	$total = bbs_countarticles($boardID, $ftype);
	if (!$sorted) {
		$num = @intval($_GET["num"]);
		if (($num <= 0) || ($num > $total)) {
			foundErr("��������º�");
		}
		if (($articles = bbs_getarticles($boardName, $num, 1, $ftype)) === false) {
			foundErr("��������º�");
		}
		$id = @intval($_GET["id"]);
		if ($id != $articles[0]["ID"]) {
			foundErr("��������ºţ�������".$readmode."�����仯��");
		}
		$article = $articles[0];
		$attachURL = "boardcon.php?bid=$boardID&amp;num=$num&amp;id=$id&amp;ftype=$ftype";
	} else {
		if ($total <= 0) {
			foundErr("��������º�");
		}
		$id = @intval($_GET["id"]);
		$articles = array();
		$num = bbs_get_records_from_id($boardName, $id, $ftype, $articles);
		if ($num == 0) {
			foundErr("��������º�");
		}
		$article = $articles[1];
		$attachURL = "boardcon.php?bid=$boardID&amp;id=$id&amp;ftype=$ftype";
	}
	$is_tex = $article["IS_TEX"];
	$filename = bbs_get_board_filename($boardName, $article["FILENAME"]);
	if (isset($_GET["lw"])) {
		viewArticle($article, $boardID, $boardName, $attachURL);
		exit;
	}
	bbs_set_onboard($boardID,1);
	return true;
}


function display_file($filename, $is_tex) {
	global $ftype;
	global $attachURL;
	global $readmode;
	global $boardName;

	require("inc/ubbcode.php");
?>
<table cellPadding="1" cellSpacing="1" align="center" class="TableBorder1" style="table-layout:fixed;word-break:break-all;">
<tr><th height="25" width="100%" id="TableTitleLink"><?php echo $readmode; ?>�����Ķ� [<a href="boarddoc.php?name=<?php echo $boardName; ?>&amp;ftype=<?php echo $ftype; ?>">����</a>]</th></tr>
<tr><td width="100%" style="font-size:9pt;line-height:12pt;padding:10px" class="TableBody1">
<?php
		echo DvbTexCode(bbs_printansifile($filename,1,$attachURL),0,"TableBody1",$is_tex);
?>
</td></tr>
<tr><td height="25" align="center" class="TableBody2">[<a href="boarddoc.php?name=<?php echo $boardName; ?>&amp;ftype=<?php echo $ftype; ?>">����<?php echo $readmode; ?>Ŀ¼</a>]</td></tr></table>
<?php
}

function viewArticle($article, $boardID, $boardName, $this_link) {
	global $SiteURL;

	require("inc/ubbcode.php");
	
	$is_tex = SUPPORT_TEX ? $article["IS_TEX"] : 0;
	setStat(htmlspecialchars($article['TITLE'] ,ENT_QUOTES) . " " );
	html_init("","",$is_tex);
	$full_link = 'disparticle.php?boardName='.$boardName.'&amp;ID='.$article["GROUPID"];
?>
<body>
<table cellpadding="0" cellspacing="0" class="TableBorder1" style="table-layout:fixed;word-break:break-all">
<tr><td class="TableBody1"><?php
	$filename = bbs_get_board_filename($boardName, $article["FILENAME"]);
	$str = bbs_printansifile($filename,1,$this_link,$is_tex,0);
	echo DvbTexCode($str, 0, "TableBody2", $is_tex);
?></td></tr>
</table>
<p>
ԭ�����ӣ�<a href="<?php echo $this_link; ?>"><?php echo $SiteURL.$this_link; ?></a><br />
�������ӣ�<a href="<?php echo $full_link; ?>"><?php echo $SiteURL.$full_link; ?></a>
</p>
</body>
</html>
<?php
}
?>
