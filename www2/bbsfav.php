<?php
	require("www2-funcs.php");
	login_init();
	assert_login();
	page_header($currentuser["userid"] . " ���ղؼ�");

	if (isset($_GET["select"]))
		$select = $_GET["select"];
	else
		$select = 0;
	settype($select, "integer");

	if ($select < 0)// || $group > sizeof($section_nums))
		html_error_quit("����Ĳ���");
	if(bbs_load_favboard($select)==-1)
		html_error_quit("����Ĳ���");
	if (isset($_GET["delete"]))
	{
		$delete_s=$_GET["delete"];
		settype($delete_s,"integer");
		bbs_del_favboard($select,$delete_s);
	}
	if (isset($_GET["deldir"]))
	{
		$delete_s=$_GET["deldir"];
		settype($delete_s,"integer");
		bbs_del_favboarddir($select,$delete_s);
	}
	if (isset($_GET["dname"]))
	{
		$add_dname=trim($_GET["dname"]);
		if ($add_dname)
			bbs_add_favboarddir($add_dname);
	}
	if (isset($_GET["bname"]))
	{
		$add_bname=trim($_GET["bname"]);
		if ($add_bname)
			$sssss=bbs_add_favboard($add_bname);
	}
	$boards = bbs_fav_boards($select, 1);
	$list_father = bbs_get_father($select);
	if ($boards === FALSE)
		html_error_quit("��ȡ���б�ʧ��");
?>
<table class="main wide adj">
<col width="2%" class="center"/><col width="2%"/><col width="23%"/><col width="10%" class="center"/><col width="33%"/><col class="center" width="14%"/><col class="right" width="8%"/><col width="6%" class="center"/>
<tr><th>#</th><th> </th><th>����������</th><th>���</th><th>��������</th><th>����</th><th>ƪ��</th><th> </th></tr>
<?php
	$brd_name = $boards["NAME"]; // Ӣ����
	$brd_desc = $boards["DESC"]; // ��������
	$brd_class = $boards["CLASS"]; // �������
	$brd_bm = $boards["BM"]; // ����
	$brd_artcnt = $boards["ARTCNT"]; // ������
	$brd_unread = $boards["UNREAD"]; // δ�����
	$brd_zapped = $boards["ZAPPED"]; // �Ƿ� z ��
	$brd_position= $boards["POSITION"];//λ��
	$brd_npos= $boards["NPOS"];//λ��
	$brd_flag= $boards["FLAG"];//Ŀ¼��ʶ
	$brd_bid= $boards["BID"];//Ŀ¼��ʶ
	$rows = sizeof($brd_name);
	if($select != 0)
	{
?>
<tr>
<td> </td>
<td> <script language="javascript">putImage('groupgroup.gif','alt="up" title="�ص���һ��"');</script></td>
<td colspan="6"><a href="bbsfav.php?select=<?php echo $list_father; ?>">�ص���һ��</a></td>
</tr>
<?php
	}
	for ($i = 0; $i < $rows; $i++)  
	{
	if( $brd_unread[$i] ==-1 && $brd_artcnt[$i] ==-1) continue;
	if ($brd_bid[$i] == -1) continue;
?>
<tr>
<td><?php echo $i+1; ?></td>
<?php
	if ($brd_flag[$i] == -1 ) {
?>
<td> <script language="javascript">putImage('groupgroup.gif','alt="��" title="������"');</script></td>
<td><a href="bbsfav.php?select=<?php echo $brd_bid[$i];?>"><?php echo $brd_desc[$i];?></a></td>
<td>[Ŀ¼]</td>
<td colspan="3"> </td>
<td><a href="bbsfav.php?select=<?php echo $select;?>&deldir=<?php echo $brd_npos[$i];?>">ɾ��</a></td>
</tr>
<?php
		continue;
	}
	if ($brd_unread[$i] == 1) {
?>
<td> <script language="javascript">putImage('newgroup.gif','alt="��" title="δ����־"');</script></td>
<td>
<?php                              
	} else {
?>
<td> <script language="javascript">putImage('oldgroup.gif','alt="��" title="�Ѷ���־"');</script></td>
<td>
<?php
	}
		if ($brd_zapped[$i] == 1)
				echo "*";
		else
				echo "&nbsp;";
?><a href="bbsdoc.php?board=<?php echo urlencode($brd_name[$i]); ?>"><?php echo $brd_name[$i]; ?></a>
</td>
<td><?php echo $brd_class[$i]; ?></td>
<td>&nbsp;&nbsp;
<a href="bbsdoc.php?board=<?php echo urlencode($brd_name[$i]); ?>"><?php echo $brd_desc[$i]; ?></a>
</td>
<td>
<?php
		$bms = explode(" ", trim($brd_bm[$i]));
		if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
				echo "����������";
		else
		{
			if (!ctype_print($bms[0][0]))
				echo $bms[0];
			else
			{
?>
<a href="bbsqry.php?userid=<?php echo $bms[0]; ?>"><?php echo $bms[0]; ?></a>
<?php
			}
		}
?>
</td>
<td><?php echo $brd_artcnt[$i]; ?></td>
<td><a href="bbsfav.php?select=<?php echo $select;?>&delete=<?php echo ($brd_npos[$i]);?>">ɾ��</a></td>
</tr>
<?php
	}
?>
</table>
<div class="oper"><form action=bbsfav.php>����Ŀ¼: <input name=dname size=24 maxlength=20 type=text value="">
<input type=submit value=ȷ��><input type=hidden name=select value=<?php echo $select;?>>
</form></div>
<div class="oper"><form action=bbsfav.php>���Ӱ���: <input name=bname size=24 maxlength=20 type=text value="">
<input type=submit value=ȷ��><input type=hidden name=select value=<?php echo $select;?>>
</form></div>
<?php
	page_footer();
?>
