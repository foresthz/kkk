<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardNames;
global $singleBoard;
global $boardArr;

setStat("�������");

preprocess();

show_nav($singleBoard ? $singleBoard : false);

showUserMailBoxOrBR();

if ($boardArr !== false) 
	board_head_var($boardArr['DESC'],$singleBoard,$boardArr['SECNUM']);
else {
	head_var("��̳����",'query.php',0);
}

doSearch($boardNames);

show_footer();

function preprocess(){
	global $boardNames;
	global $currentuser;
	global $boardArr;
	global $singleBoard;
	global $title,$title2,$title3,$author;
	if (!isset($_REQUEST['boardNames'])) {
		foundErr("δָ�����档");
	}
	$boardNames = split(',',$_REQUEST['boardNames']);
	if (count($boardNames) == 1) {
		$singleBoard = $boardNames[0];
		$brdArr=array();
		$boardID= bbs_getboard($singleBoard, $brdArr);
		$boardArr=$brdArr;
		$singleBoard=$brdArr['NAME'];
		if ($boardID==0) {
			foundErr("ָ���İ��治����");
		}
		$usernum = $currentuser["index"];
		if (bbs_checkreadperm($usernum, $boardID) == 0) {
			foundErr("ָ���İ��治����");
		}
	} else {
		$singleBoard = '';
		$boardArr = false;
		if (!isMultiQueryAllowed()) {
			foundErr("�����������ѯ�������²�ѯ��");
		}
	}
	$title=trim($_REQUEST['title']);
	$title2=trim($_REQUEST['title2']);
	$title3=trim($_REQUEST['title3']);
	$author=trim($_REQUEST['userid']);
	
	return true;
}

function doSearch($boardNames){
	global $title,$title2,$title3,$author,$currentuser;
	$singleSearch = (count($boardNames) == 1);
	$maxreturn = ($singleSearch?999:11);
	$totalnum = 0;
?>
<script src="inc/loadThread.js"></script>
<TABLE cellPadding=3 cellSpacing=1 class=TableBorder1 align=center>
<TR valign=middle>
<Th height=25 width=32>״̬</Th>
<Th width=*>�� ��</Th>
<Th width=80>�� ��</Th>
<Th width=64>�ظ�</Th>
<Th width=200>������ | �ظ���</Th>
</TR>
<?php
	$usernum = $currentuser["index"];
	foreach($boardNames as $boardName) {
		$brdArr = array();
		$boardID = bbs_getboard($boardName,$brdArr);
		$boardArr = $brdArr;
		if (bbs_checkreadperm($usernum, $boardID) == 0) continue; //�����ʵ��û��Ҫ�ģ���Ϊbbs_searchtitle()�Ǽ���
		if (bbs_getThreadNum($boardID) == 0) continue; //��ʵ��ҪĿ����ǿ���������� .WEBTHREAD
		$articles=bbs_searchtitle($boardName,$title,$title2,$title3,$author,
		                          intval($_REQUEST['dt']),isset($_REQUEST['mg']),isset($_REQUEST['ag']),$maxreturn);
				
		$num=count($articles);
	
		if ($num==0 || $articles<=0) continue;
		$totalnum += $num;
		if (!$singleSearch) {
?>
<tr><td height="27" align="left" colspan="5" class="TableBody2">&nbsp;&nbsp;<b><a href="board.php?name=<?php echo urlencode($boardName); ?>"><?php echo $boardArr['DESC']; ?></a></b><?php
			if (count($articles) > $maxreturn - 1) {
				$morelink = "queryresult.php?title=".urlencode($title)."&title2=".urlencode($title2)."&title3=".urlencode($title3)."&userid=".urlencode($author)."&dt=".$_REQUEST['dt']."&boardNames=".urlencode($boardName);
				if (isset($_REQUEST['mg'])) {
					$morelink .= "&mg=on";
				}
				if (isset($_REQUEST['ag'])) {
					$morelink .= "&ag=on";
				}
				echo "&nbsp;&nbsp;[<a href=\"".$morelink."\" title=\"�鿴�ڸð��������������\"><font color=red>�����������...</font></a>]";
				$num--;
			}
?></td></tr>
<?php
		}
?>
<script language="JavaScript">
<!--
	boardName = '<?php echo $boardName; ?>';
<?php
		for ($i=1;$i<=$num;$i++) {
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
<?php
	} //foreach $boardNames
?>
<tr><td height="27" align="center" colspan="5" class="TableBody1"><?php
	if ($totalnum > 0) echo "�������⹲��ѯ�� <font color=#FF0000>$totalnum</font> �����";
	else echo "û���ҵ��κ�����";
?></td></tr>
</table>
<?php
}
?>
