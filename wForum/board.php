<?php


$setboard=1;

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;
global $page;

preprocess();

setStat("�����б�");

show_nav($boardName);

if (isErrFounded()) {
	html_error_quit() ;
} else {
	showUserMailBoxOrBR();
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
?>
<table cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
<?php
	showAnnounce(); 
?>
</table>
<?php
	if ($boardArr['FLAG'] & BBS_BOARD_GROUP ) {
		showSecs($boardArr['SECNUM'],$boardID,true);
	} else {
?>
<script src="inc/loadThread.js"></script>
<iframe width=0 height=0 src="" id="hiddenframe" name="hiddenframe"></iframe>

<?php
		showBoardStaticsTop($boardArr);
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
	}
}

//showBoardSampleIcons();
show_footer();

CloseDatabase();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $page;
	if (!isset($_GET['name'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_GET['name'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName, $brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治����");
		return false;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ�����");
		return false;
	}
	if (!isset($_GET['page'])) {
		$page=-1;
	} else {
		$page=intval($_GET['page']);
	}

	bbs_set_onboard($boardID,1);
	return true;
}


function showBoardContents($boardID,$boardName,$page){
	global $dir_modes;
?>
<?php
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
	THREADSPERPAGE = <?php echo THREADSPERPAGE; ?>;
<?php
		print_file_display_javascript($boardName);
		for($i=0;$i<$articleNum;$i++){
			$origin=$articles[$i]['origin'];
			$lastreply=$articles[$i]['lastreply'];
			$threadNum=$articles[$i]['articlenum']-1;
?>
	origin = new Post(<?php echo $origin['ID']; ?>, '<?php echo $origin['OWNER']; ?>', '<?php echo strftime("%Y-%m-%d %H:%M:%S", $origin['POSTTIME']); ?>', '<?php echo $origin['FLAGS'][0]; ?>');
	lastreply = new Post(<?php echo $lastreply['ID']; ?>, '<?php echo $lastreply['OWNER']; ?>', '<?php echo strftime("%Y-%m-%d %H:%M:%S", $lastreply['POSTTIME']); ?>', '<?php echo $lastreply['FLAGS'][0]; ?>');
	writepost(<?php echo $i+$start; ?>, '<?php echo addslashes(htmlspecialchars($origin['TITLE'],ENT_QUOTES)); ?> ', <?php echo $threadNum; ?>, origin, lastreply, <?php echo ($origin['GROUPID'] == $lastreply['GROUPID'])?"true":"false"; ?>);
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
    $lastTenPages=(floor(($page-1)/ 10))*10;
	if ($page==1) {
		echo "<font face=webdings color=\"#FF0000\">9</font>   "; //ToDo: XHTML ������ʹ�� webdings ���塣
	}   else {
		echo "<a href=\"board.php?name=".$boardName."&page=1\" title=��ҳ><font face=webdings>9</font></a>   ";
	} 

	if ($lastTenPages>0)  {
		echo "<a href='?name=". $boardName ."&page=" . $lastTenPages . "' title=��ʮҳ><font face=webdings>7</font></a>   ";  
	} 

	echo "<b>";
	for ($i=$lastTenPages+1; $i<=$lastTenPages+10; $i++) {
		if ($i==$page)	{
			echo "<font color=#ff0000>".$i."</font> ";
		} else {
			echo "<a href='board.php?name=".$boardName."&page=".$i."'>".$i."</a> ";
		} 
		if ($i==$totalPages) {
		  break;
		} 
	} 
	echo "</b>";
	if ($i<$totalPages) {
		echo "<a href='board.php?name=".$boardName."&page=".$i."' title=��ʮҳ><font face=webdings>8</font></a>   ";  
	} 
	if ($page==$totalPages) {
		echo "<font face=webdings color=#ff0000>:</font>   ";
	}  else  {
		echo "<a href='board.php?name=".$boardName."&page=".$totalPages."' title=βҳ><font face=webdings>:</font></a>   ";
	} 
?>
ת��:<input type=text name="page" size=3 maxlength=10  value=1><input type=submit value=Go ></div></td></tr>
</table></form>
<?php
	}
}
?>
