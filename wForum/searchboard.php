<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $keywords;
global $exact;
global $boards;

preprocess();

if ($boards !== false && sizeof($boards) == 1) {
	header("Location: board.php?name=" . urlencode($boards[0]['NAME']));
	exit(0);
}

setStat("��������");

show_nav();

showUserMailBoxOrBR();

head_var();

if ($keywords) {
	showSearch($boards);
}
showSearchForm($keywords);

show_footer();

function preprocess(){
	global $keywords;
	global $exact;
	global $boards;
	@$keywords = $_REQUEST['board'];
	if (!$keywords) {
		$boards = false;
		return;
	}
	if (isset($_REQUEST['exact'])) {
		$exact = $_REQUEST['exact'] ? 1 : 0;
	} else {
		$exact = 0;
	}
	$boards = array();
	if (!bbs_searchboard($keywords,$exact,$boards)) $boards = false;
	
}

function showSearch($boards) {
?>
<TABLE cellPadding=3 cellSpacing=1 class=TableBorder1 align=center>
<TR valign=middle>
<Th height=25 width=40>���</Th>
<Th width=120>������</Th>
<Th width=180>˵  ��</Th>
<Th width=*>�ؼ���</Th>
</TR>
<?php
	if ($boards !== false) {
		$i = 1;
		foreach ($boards as $board) {
        		echo '<tr><td height="27" align="center" class="TableBody2">'.$i.'</td>' .
			     '<td class="TableBody1">&nbsp;<a href="board.php?name='.urlencode($board['NAME']).'">'.htmlspecialchars($board['NAME']).'</a></td>' .
			     '<td class="TableBody2">&nbsp;'.htmlformat($board['TITLE']).'</td>'.
			     '<td class="TableBody1">&nbsp;'.htmlformat($board['DESC']).'&nbsp;</td></tr>';    
        		$i ++;
	    	}
	} else {
		echo '<tr><td height="27" align="center" colspan="4" class="TableBody1">û���������κΰ���</td></tr>';
	}
?>
</table>
<?php
}

function showSearchForm($keywords) {
?>
<form method="GET" action="searchboard.php">
<table align=center><tr><td>
������ؼ���:
<input type="text" name="board" value="<?php echo htmlspecialchars($keywords); ?>" />
<input type="checkbox" name="exact" id="exact" />��ȷƥ��
<input type="submit" name="submit" value="��ѯ����">
</td></tr></table>
</form>
<?php
}
?>
