<?php
function bbs_boards_navigation_bar()
{
?>
<p align="center">
[<a href="/<?php echo MAINPAGE_FILE; ?>">��ҳ����</a>]
[<a href="/bbssec.php">����������</a>]
[<a href="/bbsnewbrd.php">�¿�������</a>]
[<a href="/bbsrecbrd.php">�Ƽ�������</a>]
[<a href="/bbsbrdran.php">��������������</a>]
[<a href="/cgi-bin/bbs/bbs0an">����������</a>]
[<a href="javascript:history.go(-1)">���ٷ���</a>]
<br />
</p>
<?php	
}

function undo_html_format($str)
{
	$str = preg_replace("/&gt;/i", ">", $str);
	$str = preg_replace("/&lt;/i", "<", $str);
	$str = preg_replace("/&quot;/i", "\"", $str);
	$str = preg_replace("/&amp;/i", "&", $str);
	return $str;
}

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

function bbs_board_header($brdarr,$articles=0)
{
	global $section_names,$currentuser;
	$brd_encode = urlencode($brdarr["NAME"]);
	$ann_path = bbs_getannpath($brdarr["NAME"]);
	
	$bms = explode(" ", trim($brdarr["BM"]));
	$bm_url = "";
	if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
		$bm_url = "����������";
	else
	{
		if (!ctype_alpha($bms[0][0]))
			$bm_url = $bms[0];
		else
		{
			foreach ($bms as $bm)
			{
				$bm_url .= sprintf("<a class=\"b3\" href=\"/bbsqry.php?userid=%s\"><font class=\"b3\">%s</font></a> ", $bm, $bm);
			}
			$bm_url = trim($bm_url);
		}
	}
	
?>
<body topmargin="0" leftmargin="0">
<a name="listtop"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tbody>  <tr> 
    <td colspan="2" class="b2">
	    <a href="<?php echo MAINPAGE_FILE; ?>" class="b2"><font class="b2"><?php echo BBS_FULL_NAME; ?></font></a>
	    -
	    <?php
	    	$sec_index = get_secname_index($brdarr["SECNUM"]);
		if ($sec_index >= 0)
		{
	    ?>
		<a href="/bbsboa.php?group=<?php echo $sec_index; ?>" class="b2"><font class="b2"><?php echo $section_names[$sec_index][0]; ?></font></a>
	    <?php
		}
	    ?>
	    -
	    <?php echo $brdarr["NAME"]; ?>��(<a href="bbsnot.php?board=<?php echo $brd_encode; ?>" class="b2"><font class="b2">���滭��</font></a>
	    |
	    <a href="/bbsfav.php?bname=<?php echo $brdarr["NAME"]; ?>&select=0" class="b2"><font class="b2">��ӵ��ղؼ�</font></a>
<?php
	if( defined("HAVE_BRDENV") ){
		if( bbs_board_have_envelop($brdarr["NAME"]) ){
?>
	    |
	    <a href="/bbsenv.php?board=<?php echo $brd_encode; ?>" class="b2"><font class="b2">���浼��</font></a>
<?php
		}
	}
?>
	    )
    </td>
  </tr>
  <tr> 
    <td colspan="2" align="center" class="b4"><?php echo $brdarr["NAME"]."(".$brdarr["DESC"].")"; ?> ��</td>
  </tr>
  <tr><td class="b1">
  <img src="images/bm.gif" alt="����" align="absmiddle">���� <?php echo $bm_url; ?>
  </td></tr>
  <tr> 
    <td class="b1">
    <img src="images/online.gif" alt="������������" align="absmiddle">���� <font class="b3"><?php echo $brdarr["CURRENTUSERS"]+1; ?></font> ��
<?php
	if($articles)
	{
?>
    <img src="images/postno.gif" alt="����������" align="absmiddle">���� <font class="b3"><?php echo $articles; ?></font> ƪ
<?php
	}
?>
    </td>
    <td align="right" class="b1">
	    <img src="images/gmode.gif" align="absmiddle" alt="��ժ��"><a class="b1" href="bbsgdoc.php?board=<?php echo $brd_encode; ?>"><font class="b1">��ժ��</font></a> 
<?php
    	if ($ann_path != FALSE)
	{
        	if (!strncmp($ann_path,"0Announce/",10))
			$ann_path=substr($ann_path,9);
?>
	    | 
  	    <img src="images/soul.gif" align="absmiddle" alt="������"><a class="b1" href="/cgi-bin/bbs/bbs0an?path=<?php echo urlencode($ann_path); ?>"><font class="b1">������</font></a>
	    <?php
	}
?>
	    | 
  	    <img src="images/search.gif" align="absmiddle" alt="���ڲ�ѯ"><a class="b1" href="/bbsbfind.php?board=<?php echo $brd_encode; ?>"><font class="b1">���ڲ�ѯ</font></a>
<?php
	if (strcmp($currentuser["userid"], "guest") != 0)
	{
?>
	    | 
  	    <img src="images/vote.gif" align="absmiddle" alt="����ͶƱ"><a class="b1" href="/bbsshowvote.php?board=<?php echo $brd_encode; ?>"><font class="b1">����ͶƱ</font></a>
	    | 
  	    <img src="images/model.gif" align="absmiddle" alt="����ģ��"><a class="b1" href="/bbsshowtmpl.php?board=<?php echo $brd_encode; ?>"><font class="b1">����ģ��</font></a>
<?php
	}
?>
    	        </td>
  </tr>
  <tr> 
    <td colspan="2" height="9" background="images/dashed.gif"> </td>
  </tr></tbody>
</table>
<?php	
}

function bbs_board_foot($brdarr,$listmode)
{
	global $currentuser;
	$brd_encode = urlencode($brdarr["NAME"]);
	$usernum = $currentuser["index"];
	$brdnum  = $brdarr["NUM"];
?>

<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tbody>  <tr> 
    <td colspan="2" height="9" background="images/dashed.gif"> </td>
  </tr>
  <tr> 
    <td colspan="2" align="center" class="b1">
    	[<a href="#listtop">���ض���</a>]
    	[<a href="javascript:location=location">ˢ��</a>]
<?php
	switch($listmode)
	{
		case "NORMAL":
?>
[<a href="bbsodoc.php?board=<?php echo $brd_encode; ?>">ͬ����ģʽ</a>]
<?php		
			break;
		case "ORIGIN":
?>
[<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>">��ͨģʽ</a>]
<?php
	}
?>
[<a href="/bbsbfind.php?board=<?php echo $brd_encode; ?>">���ڲ�ѯ</a>]
<?php
if (bbs_is_bm($brdnum, $usernum))
{
?>
[<a href="bbsmdoc.php?board=<?php echo $brd_encode; ?>">����ģʽ</a>]
<?php
}
?>
    </td>
  </tr>
<?php
	$relatefile = $_SERVER["DOCUMENT_ROOT"]."/brelated/".$brdarr["NAME"].".html";
	if( file_exists( $relatefile ) )
	{
?>
<tr>
<td colspan="2" align="center" class="b1">
���������˳�ȥ���������棺
<?php
	include($relatefile);
?>
</td>
</tr>
<?php
	}
?>
</tbody></table>
<?php	
}

function bbs_board_avtiveboards()
{
?>
<table width="100%" cellpadding="3" cellspacing="0" border="0" />
<tr>
	<td width="150" height="77"><img src="images/logo.gif" width="144" height="71"></td>
	<td><SPAN ID='aboards'>Active Boards</SPAN></td>
</tr>
</table>
<SCRIPT SRC='abs.js'></SCRIPT>
<script language='javascript'>
display_active_boards();
</script>
<?php	
}


?>