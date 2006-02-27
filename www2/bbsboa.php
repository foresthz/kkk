<?php
	require("www2-funcs.php");
	login_init();
	bbs_session_modify_user_mode(BBS_MODE_SELECT);
	require("www2-board.php");

	if (isset($_GET["group"]))
		$group = $_GET["group"];
	else
		$group = 0;
	settype($group, "integer");
	if (isset($_GET["group2"]))
		$group2 = $_GET["group2"];
	else
		$group2 = 0;
	settype($group2, "integer");
	if ($group < 0 || $group >= BBS_SECNUM)
		html_error_quit("����Ĳ���");
	$boards = bbs_getboards(constant("BBS_SECCODE".$group), $group2, 8);
	if ($boards === FALSE)
		html_error_quit("��Ŀ¼��δ�а���");

	page_header(constant("BBS_SECNAME".$group."_0"), "", "<meta name='kbsrc.brd' content='' />");
?>
<h1><?php echo constant("BBS_SECNAME".$group."_0"); ?>����</h1>
<table class="main wide adj">
<col width="2%" class="center"/><col width="2%"/><col width="24%"/><col width="10%" class="center"/><col width="39%"/><col class="center" width="15%"/><col class="right" width="8%"/>
<tr><th>#</th><th> </th><th>����������</th><th>���</th><th>��������</th><th>����</th><th>ƪ��</th></tr>
<?php
	$rows = sizeof($boards);
	if ($group2>0) {	
?>
<tr>
	<td> </td>
	<td> <script type="text/javascript">putImage('groupgroup.gif','alt="up" title="�ص���һ��"');</script></td>
	<td colspan="5"><a href="bbsboa.php?group=<?php echo $group; ?>">�ص���һ��</a></td>
</tr>
<?php
	}
	
	$board_list = array();
	$list_gnum = $rows;
	$list_bnum = $rows;
	for ($i = $rows - 1; $i>=0 ; $i--)
	{
		if ($boards[$i]["FLAG"]&BBS_BOARD_GROUP)
		{
			$board_list[$list_gnum] = $i;
			$list_gnum = $list_gnum + 1;
		}
		else
		{
			$list_bnum  = $list_bnum - 1;
			$board_list[$list_bnum] = $i;
		}
	} 
	if ($list_gnum > $rows)
	{
		for  ($i = $rows; $i < $list_gnum; $i++)
		{
		$board_list[$i - $rows]= $board_list[$i];
		}
	}
	for ($j = 0; $j< $rows; $j++)	
	{
		$i = $board_list[$j];
		if ($boards[$i]["FLAG"]&BBS_BOARD_GROUP)
		  $brd_link="bbsboa.php?group=" . $group . "&group2=" . $boards[$i]["BID"];
		else
		  $brd_link="bbsdoc.php?board=" . urlencode($boards[$i]["NAME"]);
?>
<tr>
<td><?php echo $i+1; ?></td>
<?php
		if ($boards[$i]["FLAG"]&BBS_BOARD_GROUP) {
?>
<td> <script type="text/javascript">putImage('groupgroup.gif','alt="��" title="������"');</script></td>
<?php
		} else {
			$unread = ($boards[$i]["UNREAD"] == 1);
			$unread_tag = $unread ? "" : ' style="display: none"';
			$read_tag = !$unread ? "" : ' style="display: none"';
			$unread_tag .= ' id="kbsrc'.$boards[$i]["BID"].'u"';
			$read_tag .= ' id="kbsrc'.$boards[$i]["BID"].'r"';
?>
<td id="kbsrc<?php echo $boards[$i]["BID"]; ?>_<?php echo $boards[$i]["LASTPOST"]; ?>">
	<script type="text/javascript">putImage('newgroup.gif','alt="��" title="δ����־"<?php echo $unread_tag; ?>');</script>
	<script type="text/javascript">putImage('oldgroup.gif','alt="��" title="�Ѷ���־"<?php echo $read_tag; ?>');</script>
</td>
<?php
		}
?>
<td>
<a href="<?php echo $brd_link; ?>"><?php echo $boards[$i]["NAME"]; ?></a></td>
<?php
		if ($boards[$i]["FLAG"]&BBS_BOARD_GROUP) {
?>
<td><?php echo $boards[$i]["CLASS"]; ?> </td>
<td colspan="3"><a href="<?php echo $brd_link; ?>"><?php echo $boards[$i]["DESC"]; ?></a>[Ŀ¼]</td>
<?php
		} else {
?>
<td><?php echo $boards[$i]["CLASS"];; ?></td>
<td><a href="<?php echo $brd_link; ?>"><?php echo $boards[$i]["DESC"]; ?></a></td>
<td>
<?php
			$bms = explode(" ", trim($boards[$i]["BM"]));
			if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
				echo "����������";
			else {
				if (!ctype_print($bms[0][0]))
					echo $bms[0];
				else {
?>
<a href="bbsqry.php?userid=<?php echo $bms[0]; ?>"><?php echo $bms[0]; ?></a>
<?php
				}
			}
?>
</td>
<td><?php echo $boards[$i]["ARTCNT"]; ?></td>
<?php
		}
?>
</tr>
<?php
	} //for
?>
</table>
<?php
	bbs_boards_navigation_bar();	
	page_footer();
?>
