<?php
/*
 * edit article's title
 * @author: windinsn apr 28,2004
 */
require("funcs.php");
login_init();

html_init("gb2312","","",1);
if ($loginok != 1)
	html_nologin();

if (isset($_GET["board"]))
	$board = $_GET["board"];
else
	html_error_quit("�����������");

$id = intval($_GET["id"]);
if(!$id)
	html_error_quit("���������");

// ����û��ܷ��Ķ��ð�
$brdarr = array();
$brdnum = bbs_getboard($board, $brdarr);
if ($brdnum == 0)
	html_error_quit("�����������");

$board=$brdarr["NAME"];
$brd_encode = urlencode($board);
bbs_set_onboard($brcnum,1);
$usernum = $currentuser["index"];
if (bbs_checkreadperm($usernum, $brdnum) == 0)
	html_error_quit("�����������");

if(bbs_checkpostperm($usernum, $brdnum) == 0) 
{
	if (!strcmp($currentuser["userid"],"guest"))
		html_error_quit("����ע���ʺ�");
	else 
		html_error_quit("�������������������Ȩ�ڴ���������������");
}

if (!isset($_GET["mode"]))
	$mode = $dir_modes["NORMAL"];
else
{
	if($_GET["mode"] != $dir_modes["NORMAL"] && $_GET["mode"] != $dir_modes["ZHIDING"] && $_GET["mode"] != $dir_modes["DIGEST"])
		html_error_quit("������Ķ�ģʽ");
	$mode = $_GET["mode"];
}

$articles = array ();
$num = bbs_get_records_from_id($board, $id, $mode, $articles);
if($num==0)
	html_error_quit("��������º�,ԭ�Ŀ����Ѿ���ɾ��");

if($_POST["title"])
{
	$ret = bbs_edittitle($board,$id,$_POST["title"],$mode);
	if($ret != 0)
	{
		switch($ret)
		{
			case -1:
				html_error_quit("�����������");
				break;
			case -2:
				html_error_quit("�Բ��𣬸������������޸ı���");
				break;
			case -3:
				html_error_quit("�Բ��𣬸�������Ϊֻ��������");
				break;
			case -4:
				html_error_quit("��������º�");
				break;
			case -5:
				html_error_quit("�Բ������ѱ�ֹͣ��".$board."��ķ���Ȩ��");
				break;
			case -6:
				html_error_quit("�Բ�������Ȩ�޸ı���");
				break;
			case -7:
				html_error_quit("���⺬�в�������");
				break;
			case -8:
				html_error_quit("�Բ��𣬵�ǰģʽ�޷��޸ı���");
				break;
			case -9:
				html_error_quit("�������");
				break;
			default:
				html_error_quit("ϵͳ��������ϵ����Ա");
		}
	}
	else
	{
?>
<br /><br /><br />
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">�����޸ĳɹ���</td>
</tr><tr><td width="100%" class=TableBody1>
��ҳ�潫��3����Զ����ذ��������б�<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=bbsdoc.php?board=<?php echo $brd_encode; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="<?php echo MAINPAGE_FILE; ?>">������ҳ</a></li>
<li><a href="/bbsdoc.php?board=<?php   echo $brd_encode; ?>">����<?php   echo $brdarr['DESC']; ?></a></li>
<li><a href="/bbscon.php?board=<?php   echo $brd_encode; ?>&id=<?php echo $id; ?>">���ء�<?php  echo $_POST["title"]; ?>��</a></li>
</ul></td></tr></table>
<?php		
	}
?>

<?php
}
else
{
?>	
<br /><br /><br /><br /><center>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>&mode=<?php echo $mode; ?>" method="post" />
�����±��⣺
<input type="text" name="title" id="title" size="40" value="<?php echo htmlspecialchars($articles[1]["TITLE"]); ?>" />
<input type="submit" value="�޸�" />
</form>	</center>
<?php	
}
html_normal_quit();
?>