<?php
	require("www2-funcs.php");
	login_init();
	require("www2-board.php");

	if (isset($_GET["group"]))
		$group = $_GET["group"];
	else
		$group = 0;
	settype($group, "integer");
	if (isset($_GET["yank"]))
		$yank = $_GET["yank"];
	else
		$yank = 0;
	settype($yank, "integer");
	if (isset($_GET["group2"]))
		$group2 = $_GET["group2"];
	else
		$group2 = 0;
	settype($group, "integer");
	if ($group < 0 || $group > sizeof($section_nums))
		html_error_quit("����Ĳ���");
	$boards = bbs_getboards($section_nums[$group], $group2, $yank);
	//print_r($boards);
	if ($boards == FALSE)
		html_error_quit("��Ŀ¼��δ�а���");

	page_header($section_names[$group][0]);
?>
<h1><?php echo $section_names[$group][0]; ?>����</h1>
<div class="right smaller">
<?php
	if( $group2 != -2 ){
		if ($yank == 0) {
?>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?group=<?php echo $group; ?>&yank=1">�������п���</a> 
<?php
		} else {
?>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?group=<?php echo $group; ?>">�����Ѷ��Ŀ���</a>
<?php
		}
	}
?>
</div>
<table class="main wide adj">
<col width="2%" class="center"/><col width="2%"/><col width="24%"/><col width="10%" class="center"/><col width="39%"/><col class="center" width="15%"/><col class="right" width="8%"/>
<tr><th>#</th><th> </th><th>����������</th><th>���</th><th>��������</th><th>����</th><th>ƪ��</th></tr>
<?php
	$brd_name = $boards["NAME"]; // Ӣ����
	$brd_desc = $boards["DESC"]; // ��������
	$brd_class = $boards["CLASS"]; // �������
	$brd_bm = $boards["BM"]; // ����
	$brd_artcnt = $boards["ARTCNT"]; // ������
	$brd_unread = $boards["UNREAD"]; // δ�����
	$brd_zapped = $boards["ZAPPED"]; // �Ƿ� z ��
	$brd_flag = $boards["FLAG"]; //flag
	$brd_bid = $boards["BID"]; //flag
	$rows = sizeof($brd_name);
	if ($group2>0) {	
?>
<tr>
	<td> </td>
	<td> <img src="images/groupgroup.gif" alt="up" title="�ص���һ��"></td>
	<td colspan="5"><a href="bbsboa.php?group=<?php echo $group; ?>">�ص���һ��</a></td>
</tr>
<?php
	}
	
	$board_list = array();
	$list_gnum = $rows;
	$list_bnum = $rows;
	for ($i = $rows - 1; $i>=0 ; $i--)
	{
		if ($brd_flag[$i]&BBS_BOARD_GROUP)
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
		if ($brd_flag[$i]&BBS_BOARD_GROUP)
		  $brd_link="bbsboa.php?group=" . $group . "&group2=" . $brd_bid[$i];
		else
		  $brd_link="bbsdoc.php?board=" . urlencode($brd_name[$i]);
?>
<tr>
<td><?php echo $i+1; ?></td>
<?php
		if ($brd_flag[$i]&BBS_BOARD_GROUP) {
?>
<td> <img src="images/groupgroup.gif" alt="��" title="������"></td>
<?php
		} else if ($brd_unread[$i] == 1) {
?>
<td> <img src="images/newgroup.gif" alt="��" title="δ����־"></td>
<?php
		} else {
?>
<td> <img src="images/oldgroup.gif" alt="��" title="�Ѷ���־"></td>
<?php
		}
?>
<td>
<?php
		if ($yank == 1) {
			if ($brd_zapped[$i] == 1)
				echo "*";
			else
				echo "&nbsp;";
		}	
?>
<a href="<?php echo $brd_link; ?>"><?php echo $brd_name[$i]; ?></a></td>
<?php
		if ($brd_flag[$i]&BBS_BOARD_GROUP) {
?>
<td><?php echo $brd_class[$i]?> </td>
<td colspan="3"><a href="<?php echo $brd_link; ?>"><?php echo $brd_desc[$i]; ?></a>[Ŀ¼]</td>
<?php
		} else {
?>
<td><?php echo $brd_class[$i]; ?></td>
<td><a href="<?php echo $brd_link; ?>"><?php echo $brd_desc[$i]; ?></a></td>
<td>
<?php
			$bms = explode(" ", trim($brd_bm[$i]));
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
<td><?php echo $brd_artcnt[$i]; ?></td>
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
