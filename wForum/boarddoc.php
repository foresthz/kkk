<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;
global $page;
global $ftype;
global $sorted;
global $readmode;

preprocess();

setStat($readmode."�����б�");

show_nav($boardName, false, ($ftype==$dir_modes["DIGEST"])?getBoardRSS($boardName, $ftype, $readmode):"");

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showAnnounce();
?>
</table>
<?php
	showBoardStaticsTop($boardArr, bbs_is_bm($boardID, $currentuser["index"]), $ftype);
?>
<TABLE cellPadding=1 cellSpacing=1 class=TableBorder1 align=center>
<?php
	showBroadcast($boardID,$boardName);
	showBoardContents($boardID,$boardName,$page,$ftype,$sorted);
?>
</table>
<?php
	if (ONBOARD_USERS) {
?>
<script language="JavaScript" src="board_online.php?board=<?php echo $boardArr["NAME"]; ?>&amp;js=1"></script> 
<?php
	}
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $page;
	global $ftype;
	global $readmode;
	global $sorted;
	$ftype = @intval($_GET['ftype']);
	$sorted = false;
	$readmode = getModenameIfAllowed($ftype, $sorted);
	if ($readmode === false) {
		foundErr("�����ģʽ��");
	}

	if (!isset($_GET['name'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['name'];
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
	if (!isset($_GET['page'])) {
		$page=-1;
	} else {
		$page=intval($_GET['page']);
	}	
	bbs_set_onboard($boardID,1);
	return true;
}


function showBoardContents($boardID,$boardName,$page,$ftype,$sorted){
	global $dir_modes;
	$total = bbs_countarticles($boardID, $ftype);
	if ($total<=0) {
?>
<tr><td class=TableBody2 align="center" colspan="5">
	���滹û������
</td></tr>
</table>
<?php
	} else {
?>
<Th height=25 width=40>���</Th>
<Th width=30>���</Th>
<Th width=85>�� ��</Th>
<Th width=130>�� �� ʱ ��</Th>
<Th width=*>�� ��</Th>
</TR>
<?php
		$totalPages=ceil($total/ARTICLESPERPAGE);
		if (($page>$totalPages) || ($page<1)) {
			$page=$totalPages;
		}
		$start = ($page - 1)* ARTICLESPERPAGE + 1;
		$num = ARTICLESPERPAGE;
		if ($start + $num > $total + 1) $num = $total - $start + 1;
		$articles = bbs_getarticles($boardName, $start, $num, $ftype);
		$articleNum = count($articles);
		$cur = $start;
		$topimg = "<img src=\"pic/istop.gif\" title=\"�ö�\" align=\"absmiddle\">";
		foreach ($articles as $article) {
			$flags = $article["FLAGS"];
			$nn = (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) ? $topimg : $cur;
?>
<tr>
<td height="25" align="center" class="TableBody1"><?php echo $nn; ?></td>
<td align="center" class="TableBody2"><?php if ($article["FLAGS"][3] == "@") echo "<img src=\"pic/havefolder.gif\" align=\"absmiddle\" title=\"�и���\">"; ?></td>
<td align="center" class="TableBody1"><a target="_blank" href="dispuser.php?id=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a></td>
<td align="center" class="TableBody2"><?php echo strftime("%Y-%m-%d %H:%M:%S", $article["POSTTIME"]); ?></td>
<td class="TableBody1">&nbsp;<?php
			$title = htmlspecialchars($article["TITLE"]);
			if (strncmp($title, "Re: ", 4) != 0)
				$title = "��&nbsp;&nbsp;" . $title;
			$url = "boardcon.php?bid=$boardID&amp;id=".$article["ID"]."&amp;ftype=$ftype";
			if (!$sorted) {
				$url .= "&amp;num=$cur";
			}
			echo "<a href=\"$url\">$title </a>";
?></td>
</tr>
<?php
			$cur++;
		}
?>
</table>
<form method=get action="boarddoc.php">
<input type="hidden" name="name" value="<?php echo $boardName ; ?>">
<input type="hidden" name="ftype" value="<?php echo $ftype ; ?>">
<table border=0 cellpadding=0 cellspacing=3 width=97% align=center >
<tr><td valign=middle>ҳ�Σ�<b><?php echo $page; ?></b>/<b><?php echo $totalPages; ?></b>ҳ ÿҳ<b><?php echo ARTICLESPERPAGE; ?></b> ������<b><?php echo $total ?></b> &nbsp; &nbsp; <b>��ͳģʽ�л�</b>��
<a href="boarddoc.php?name=<?php echo $boardName; ?>&amp;ftype=<?php echo $dir_modes["DIGEST"]; ?>">��ժ��</a> |
<a href="boarddoc.php?name=<?php echo $boardName; ?>&amp;ftype=<?php echo $dir_modes["NORMAL"]; ?>">ȫ������</a> |
<a href="boarddoc.php?name=<?php echo $boardName; ?>&amp;ftype=<?php echo $dir_modes["ORIGIN"]; ?>">ԭ��</a>
</td>
<td valign=middle ><div align=right >��ҳ��
<?php
	showPageJumpers($page, $totalPages, "boarddoc.php?name=$boardName&amp;ftype=$ftype&amp;page=");
?>
ת��:<input type=text name="page" size=3 maxlength=10  value=1><input type=submit value=Go ></div></td></tr>
</table></form>
<?php
	}
}
?>
