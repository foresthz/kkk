<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;
global $page;
global $isGroup;

preprocess();

setStat($isGroup ? "�����б�" : "�����б�");

show_nav($boardName, false, $isGroup ? "" : getBoardRSS($boardName));

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showAnnounce(); 
?>
</table>
<script src="inc/loadThread.js"></script>
<?php
	if ($isGroup) {
		showSecs(get_secname_index($boardArr['SECNUM']),$boardID,true);
	} else {
		showBoardStaticsTop($boardArr, bbs_is_bm($boardID, $currentuser["index"]));
?>
<TABLE cellPadding=1 cellSpacing=1 class=TableBorder1 align=center>
<?php
		showBroadcast($boardID,$boardName);
		showBoardContents($boardID,$boardName,$page);
		boardSearchAndJump($boardName, $boardID);
		showBoardSampleIcons();
?>
</table>
<?php
		if (ONBOARD_USERS) {
?>
<script language="JavaScript" src="board_online.php?board=<?php echo $boardArr["NAME"]; ?>&amp;js=1"></script> 
<?php
		}
	}
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $page;
	global $isGroup;
	if (!isset($_GET['name'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['name'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName, $brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
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
	$isGroup = ($boardArr['FLAG'] & BBS_BOARD_GROUP );
	
	bbs_set_onboard($boardID,1);
	return true;
}


function showBoardContents($boardID,$boardName,$page){
	$total = bbs_getThreadNum($boardID);
	if ($total<=0) {
?>
<tr><td class=TableBody2 align="center" colspan="5">
	���滹û������
</td></tr>
</table>
<?php
	} else {
?>
<Th height=25 width=32>״̬</th>
<Th width=*>�� ��  (��<img src=pic/plus.gif align=absmiddle>����չ�������б�)</Th>
<Th width=80>�� ��</Th>
<Th width=64>�ظ�</Th>
<Th width=200>������ | �ظ���</Th></TR>
<?php
		
		$totalPages=ceil($total/ARTICLESPERPAGE);
		if (($page>$totalPages)) {
			$page=$totalPages;
		} else if ($page<1) {
			$page=1;
		}
	/*
		$start=$total-$page* ARTICLESPERPAGE+1;
		$num=ARTICLESPERPAGE;
		if ($start<=0) {
			$num+=$start-1;
			$start=1;
		}
    */
		$start=($page-1)* ARTICLESPERPAGE;
		$num=ARTICLESPERPAGE;

		$articles = bbs_getthreads($boardName, $start, $num, 1);
		$articleNum=count($articles);
?>
<script language="JavaScript">
<!--
	boardName = '<?php echo $boardName; ?>';
<?php
		for($i=0;$i<$articleNum;$i++){
			$origin=$articles[$i]['origin'];
			$lastreply=$articles[$i]['lastreply'];
			$threadNum=$articles[$i]['articlenum']-1;
?>
	origin = new Post(<?php echo $origin['ID']; ?>, '<?php echo $origin['OWNER']; ?>', '<?php echo strftime("%Y-%m-%d %H:%M:%S", $origin['POSTTIME']); ?>', '<?php echo $origin['FLAGS'][0]; ?>');
	lastreply = new Post(<?php echo $lastreply['ID']; ?>, '<?php echo $lastreply['OWNER']; ?>', '<?php echo strftime("%Y-%m-%d %H:%M:%S", $lastreply['POSTTIME']); ?>', '<?php echo $lastreply['FLAGS'][0]; ?>');
	writepost(<?php echo $i+$start; ?>, '<?php echo addslashes(htmlspecialchars($origin['TITLE'],ENT_QUOTES)); ?> ', <?php echo $threadNum; ?>, origin, lastreply, <?php echo ($origin['GROUPID'] == $lastreply['GROUPID'])?1:0; ?>, <?php echo ($origin['ATTACHPOS']>0)?1:0; ?>);
<?php
		}
?>
//-->
</script>
</table>
<form method=get action="board.php">
<input type="hidden" name="name" value="<?php echo $boardName ; ?>">
<table border=0 cellpadding=0 cellspacing=3 width=97% align=center >
<tr><td valign=middle>ҳ�Σ�<b><?php echo $page; ?></b>/<b><?php echo $totalPages; ?></b>ҳ ÿҳ<b><?php echo ARTICLESPERPAGE; ?></b> ������<b><?php echo $total ?></b></td><td valign=middle ><div align=right >��ҳ��
<?php
    showPageJumpers($page, $totalPages, "board.php?name=".$boardName);
?>
ת��:<input type=text name="page" size=3 maxlength=10  value=1><input type=submit value=Go ></div></td></tr>
</table></form>
<?php
	}
}
?>
