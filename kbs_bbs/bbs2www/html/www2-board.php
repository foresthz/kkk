<?php
if (!defined('_BBS_WWW2_BOARD_PHP_'))
{
define('_BBS_WWW2_BOARD_PHP_', 1);

function bbs_boards_navigation_bar()
{
?>
<p align="center">
[<a href="<?php echo MAINPAGE_FILE; ?>">��ҳ����</a>]
[<a href="bbssec.php">����������</a>]
[<a href="bbsxmlbrd.php?flag=2">�¿�������</a>]
[<a href="bbsxmlbrd.php?flag=0">�Ƽ�������</a>]
[<a href="bbsxmlbrd.php?flag=1">��������������</a>]
[<a href="bbs0an.php">����������</a>]
[<a href="javascript:history.go(-1)">���ٷ���</a>]
<br />
</p>
<?php	
}

function undo_html_format($str)
{
	$str = preg_replace("/&apos;/i", "'", $str);
	$str = preg_replace("/&gt;/i", ">", $str);
	$str = preg_replace("/&lt;/i", "<", $str);
	$str = preg_replace("/&quot;/i", "\"", $str);
	$str = preg_replace("/&amp;/i", "&", $str);
	return $str;
}

if (version_compare(PHP_VERSION,'5','>='))
	require_once('domxml-php4-to-php5.inc.php'); //Load the PHP5 converter

# iterate through an array of nodes
# looking for a text node
# return its content
function get_content($parent)
{
	$nodes = $parent->child_nodes();
	while($node = array_shift($nodes))
		if ($node->node_type() == XML_TEXT_NODE)
			return $node->node_value();
	return "";
}

# get the content of a particular node
function find_content($parent,$name)
{
	$nodes = $parent->child_nodes();
	while($node = array_shift($nodes))
		if ($node->node_name() == $name)
			return undo_html_format(urldecode(get_content($node)));
	return "";
}

function bbs_board_header($brdarr,$articles=0,$ftype=0) {
	global $section_names, $currentuser, $dir_modes, $dir_name;
	$brd_encode = urlencode($brdarr["NAME"]);
	$ann_path = bbs_getannpath($brdarr["NAME"]);
	
	/* TODO: use javascript completely */
	$bms = explode(" ", trim($brdarr["BM"]));
	if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
		$bm_url = "'����������'";
	else
	{
		if (!ctype_alpha($bms[0][0]))
			$bm_url = "'" . $bms[0] . "'";
		else
		{
			$bm_url = "['" . implode("','", $bms) . "']";
		}
	}
	page_header($brdarr["NAME"] . " ��" . $dir_name[$ftype], FALSE);
?>
<body><div id="divNav">
<div class="fleft">
<a href="<?php echo MAINPAGE_FILE; ?>"><?php echo BBS_FULL_NAME; ?></a> �� 
<?php
	$sec_index = get_secname_index($brdarr["SECNUM"]);
	if ($sec_index >= 0) {
?>
<a href="bbsboa.php?group=<?php echo $sec_index; ?>" class="b2"><?php echo $section_names[$sec_index][0]; ?></a> �� 
<?php
	}
?>
<a href="bbsdoc.php?board=<?php echo $brdarr["NAME"]; ?>">
<?php echo htmlspecialchars($brdarr["DESC"]). "(" . $brdarr["NAME"] . ")"; ?></a><?php echo $dir_name[$ftype]; ?>
(<a href="bbsfav.php?bname=<?php echo $brdarr["NAME"]; ?>&select=0">�ղ�</a> |
<?php bbs_add_super_fav ($brdarr['DESC'], 'bbsdoc.php?board='.$brdarr['NAME']); ?>
)</div>
<div class="fright">
����:<script>writeBMs(<?php echo $bm_url; ?>);</script>, ���� <?php echo $brdarr["CURRENTUSERS"]+1; ?> ��<?php 
	if($articles) {
?>, ���� <?php echo $articles; ?> ƪ
<?php
	}
?>
</div>
</div>
<?php if (!$ftype) { ?>
<div id="postimg"><a href="bbspst.php?board=<?php echo $brd_encode; ?>"><img src="images/postnew.gif" alt="������"></a></div>
<?php }  /* TODO: remove following nobr. this is a workaround for fx under linux faint */ ?>
<h1 class="bt"><nobr><?php echo $brdarr["NAME"]."(".htmlspecialchars($brdarr["DESC"]).")"; ?> �� <?php echo $dir_name[$ftype]; ?></nobr></h1>
<div class="boper">
<a href="bbsnot.php?board=<?php echo $brd_encode; ?>">���滭��</a>
| <a href="bbsdoc.php?board=<?php echo $brd_encode; ?>&ftype=<?php echo $dir_modes["DIGEST"] ?>">��ժ��</a> 
<?php
	if ($ann_path != FALSE)	{
		if (!strncmp($ann_path,"0Announce/",10))
			$ann_path=substr($ann_path,9);
?>
| <a href="bbs0an.php?path=<?php echo urlencode($ann_path); ?>">������</a>
<?php
	}
?>
| <a href="bbsbfind.php?board=<?php echo $brd_encode; ?>" onclick="return showFindBox('<?php echo $brd_encode; ?>')">���ڲ�ѯ</a>
<?php
	if (strcmp($currentuser["userid"], "guest") != 0) {
?>
| <a href="bbsshowvote.php?board=<?php echo $brd_encode; ?>">����ͶƱ</a>
| <a href="bbsshowtmpl.php?board=<?php echo $brd_encode; ?>">����ģ��</a>
<?php
	}
?>
</div>
<?php
}

function bbs_board_foot($brdarr,$listmode='') {
	global $currentuser, $dir_modes;
	$brd_encode = urlencode($brdarr["NAME"]);
	$usernum = $currentuser["index"];
	$brdnum  = $brdarr["NUM"];
?>

<div class="oper smaller">
	[<a href="#listtop">���ض���</a>]
	[<a href="javascript:location.reload()">ˢ��</a>]
<?php
	if ($listmode != "ORIGIN") {
?>
[<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>&ftype=<?php echo $dir_modes["ORIGIN"]; ?>">ͬ����ģʽ</a>]
<?php		
	}
	if ($listmode != "NORMAL") {
?>
[<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>">��ͨģʽ</a>]
<?php
	}
?>
[<a href="bbsbfind.php?board=<?php echo $brd_encode; ?>">���ڲ�ѯ</a>]
<?php
if (bbs_is_bm($brdnum, $usernum)) {
?>
[<a href="bbsdeny.php?board=<?php echo $brd_encode; ?>">�������</a>] 
[<a href="bbsmnote.php?board=<?php echo $brd_encode; ?>">���滭��</a>]
[<a href="bbsmvote.php?board=<?php echo $brd_encode;?>">����ͶƱ</a>]
<?php
	if ($listmode != 'MANAGE') {
?>
[<a href="bbsmdoc.php?board=<?php echo $brd_encode; ?>">����ģʽ</a>]
<?php
	}
	else {
?>
[<a href="bbsclear.php?board=<?php echo $brd_encode; ?>">���δ��</a>]
<?php        
	}
}
	$relatefile = $_SERVER["DOCUMENT_ROOT"]."/brelated/".$brdarr["NAME"].".html";
	if( file_exists( $relatefile ) )
	{
?>
<br/>���������˳�ȥ���������棺
<?php
	include($relatefile);
	}
?>
</div>
<?php
}

function bbs_board_avtiveboards()
{
?>
<table width="100%" cellpadding="3" cellspacing="0" border="0" />
<tr>
	<td width="150" height="77"><img src="images/logo.gif"></td>
	<td><SPAN ID='aboards'>Active Boards</SPAN></td>
</tr>
</table>
<SCRIPT SRC='abs.js'></SCRIPT>
<script language='javascript'>
display_active_boards();
</script>
<?php	
}

function htmlformat($str,$multi=false) {
	$str = str_replace(' ','&nbsp;',htmlspecialchars($str));
	if ($multi)
		$str = nl2br($str);
	return $str;    
}

 
function bbs_ann_updirs($path,&$board,&$up_dirs) {
	$board = '';
	$path = ltrim(trim($path));
	if ($path[0]!='/') $path='/'.$path;
	if ($path[strlen($path)-1]=='/') $path = substr($path,0,strlen($path)-1);
	$up_dirs = array();
	$buf = '';
	$dirs = explode('/',$path);
	$j = 0;
	foreach($dirs as $dir) {
		if (($dir)&&($dir!='.')) {
			if (!strcmp('0Announce',$dir))
				continue;
			$buf .= '/'.$dir;
			$up_dirs[] = $buf;
			if ($j == 2) $board = $dir;    
			$j ++;
		}
	}
	return sizeof($up_dirs);
}

function bbs_ann_header($board='') {
	if ($board) {
		page_header("����������", '<a href="bbsdoc.php?board='.$board.'">'.$board.'��</a>');
	} else {
		page_header("����������");
	}
}

function bbs_ann_xsearch($board) {
?>
<form action="bbsxsearch.php" class="right wide smaller">
	<label><a href="bbsxsearch.php">����徫������������</a></label>
	<input type="text" name="q" size="31" />
<?php
	if ($board) {
?>
��Χ
<input type="radio" name="b" value="" />ȫվ
<input type="radio" name="b" checked value="<?php echo urlencode($board); ?>" /><?php echo $board; ?>��
<?php        
	}
?>	     
	<input type="submit" style="width: 80px" value="��ʼ��" />
</form>    
<?php
}


function bbs_ann_foot($parent) {
?>   
<div class="oper">
[<?php bbs_add_super_fav ('������'); ?>]
[<a href="<?php echo MAINPAGE_FILE; ?>">������ҳ</a>]
<?php   
	if ($parent){
?>
[<a href="bbs0an.php?path=<?php echo rawurlencode($parent); ?>">�ϼ�Ŀ¼</a>]
<?php
	}
?>
[<a href="bbs0an.php">��Ŀ¼</a>]
[<a href="bbsxsearch.php">����徫��������</a>]
[<a href="#listtop">���ض���</a>]
[<a href="javascript:location.reload()">ˢ��</a>] 
[<a href="javascript:history.go(-1)">����</a>] 
</div>
<?php    
}











/**
 * Constants of board flags, packed in an array.
 */
$BOARD_FLAGS = array(
	"VOTE" => 0x01,
	"NOZAP" => 0x02,
	"READONLY" => 0x04,
	"JUNK" => 0x08,
	"ANONY" => 0x10,
	"OUTGO" => 0x20,
	"CLUBREAD" => 0x40,
	"CLUBWRITE" => 0x80,
	"CLUBHIDE" => 0x100,
	"ATTACH" => 0x200,
	"NOREPLY" => 0x2000
	);



/**
 * Checking whether a board is set with some specific flags or not.
 * 
 * @param $board the board object to be checked
 * @param $flag the flags to check
 * @return TRUE  the board is set with the flags
 *         FALSE the board is not set with the flags
 * @author flyriver
 */
function bbs_check_board_flag($board,$flag)
{
	if ($board["FLAG"] & $flag)
		return TRUE;
	else
		return FALSE;
}

/**
 * Checking whether a board is an anonymous board or not.
 * 
 * @param $board the board object to be checked
 * @return TRUE  the board is an anonymous board
 *         FALSE the board is not an anonymous board
 * @author flyriver
 */
function bbs_is_anony_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["ANONY"]);
}

/**
 * Checking whether a board is an outgo board or not.
 * 
 * @param $board the board object to be checked
 * @return TRUE  the board is an outgo board
 *         FALSE the board is not an outgo board
 * @author flyriver
 */
function bbs_is_outgo_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["OUTGO"]);
}

/**
 * Checking whether a board is a junk board or not.
 * 
 * @param $board the board object to be checked
 * @return TRUE  the board is a junk board
 *         FALSE the board is not a junk board
 * @author flyriver
 */
function bbs_is_junk_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["JUNK"]);
}

/**
 * Checking whether a board is an attachment board or not.
 * 
 * @param $board the board object to be checked
 * @return TRUE  the board is an attachment board
 *         FALSE the board is not an attachment board
 * @author flyriver
 */
function bbs_is_attach_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["ATTACH"]);
}

/**
 * Checking whether a board is a readonly board or not.
 * 
 * @param $board the board object to be checked
 * @return TRUE  the board is a readnoly board
 *         FALSE the board is not a readonly board
 * @author flyriver
 */
function bbs_is_readonly_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["READONLY"]);
}

function bbs_is_noreply_board($board)
{
	global $BOARD_FLAGS;
	return bbs_check_board_flag($board, $BOARD_FLAGS["NOREPLY"]);
}


} // !define ('_BBS_WWW2_BOARD_PHP_')
?>
