<?php
require("pcadmin_inc.php");
function pc_load_groupworks($link)
{
	$query = "SELECT uid , username FROM users WHERE pctype = 1;";
	$result = mysql_query($query,$link);
	$pcs = array();
	while($rows = mysql_fetch_array($result))
		$pcs[] = $rows;
	return $pcs;
}

pc_admin_check_permission();
$link = pc_db_connect();

if($_GET["act"] == "convert2" && $_GET["userid"])
{
	$pcc = pc_load_infor($link,$_GET["userid"]);
	if(!$pcc)
	{
		html_error_quit($_GET["userid"]."����BLOG");
		exit();
	}
	$ret = pc_convertto_group($link,$pcc);
	switch($ret)
	{
		case -1:
			html_error_quit($_GET["userid"]."��BLOG��������");
			exit();
		case -2:
			html_error_quit($_GET["userid"]."��BLOG���ǹ���BLOG");
			exit();
		case -3:
			html_error_quit($_GET["userid"]."����BLOG��LOGĿ¼��ʼ������");
			exit();
		case -4:
			html_error_quit("ϵͳ����");
			exit();
		case -5:
			html_error_quit("LOG����");
			exit();
		default:	
	}
}

$pcs = pc_load_groupworks($link);

pc_html_init("gb2312",$pcconfig["BBSNAME"]."����BLOG����");
pc_admin_navigation_bar();

if($_GET["act"] == "convert" && $_GET["userid"])
{
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
ȷ��Ҫ��<?php echo $_GET["userid"]; ?>��BLOGת��Ϊ����BLOG?
<br />
һ��ת���ɹ���������ת������ͨBLOG
<input type="hidden" name="userid" value="<?php echo $_GET["userid"]; ?>">
<input type="hidden" name="act" value="convert2">
<input type="submit" value="ȷ��ת��">
<input type="button" value="ȡ��" onclick="window.location.href='pcadmin_grp.php'">
</form>
<?php	
}
else
{
?>
<br /><br />
<p align="center"><b>����BLOG����</b></p><center>
<table cellspacing="0" cellpadding="3" class="t1">
<tr>
<td class="t2">BLOG</td>
<td class="t2">����Ϊ��ͨBLOG</td>
</tr>
<?php
	foreach($pcs as $pc)
		echo "<tr><td class=t3><a href=\"index.php?id=".$pc[username]."\">".$pc[username]."</a></td><td class=t4>-</td></tr>";
?>
</table>
<br />
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
ת��һ��BLOGΪ����BLOG(������)
<input type="text" name="userid">
<input type="hidden" name="act" value="convert">
<input type="submit" value="ת��">
</form></center>
<?php	
}
pc_db_close($link);
pc_admin_navigation_bar();
html_normal_quit();
?>