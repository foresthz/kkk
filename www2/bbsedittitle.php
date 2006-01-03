<?php
require("www2-funcs.php");
login_init();
bbs_session_modify_user_mode(BBS_MODE_EDIT);
assert_login();

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
bbs_set_onboard($brdnum,1);
$usernum = $currentuser["index"];
if (bbs_checkreadperm($usernum, $brdnum) == 0)
	html_error_quit("�����������");

if(bbs_checkpostperm($usernum, $brdnum) == 0) 
	html_error_quit("�������������������Ȩ�ڴ���������������");

$mode = $dir_modes["NORMAL"]; /* TODO: support for other modes? */

$articles = array ();
$num = bbs_get_records_from_id($board, $id, $mode, $articles);
if($num==0)
	html_error_quit("��������º�,ԭ�Ŀ����Ѿ���ɾ��");

bbs_board_nav_header($brdarr, "�޸����±���");

if(isset($_POST["title"]))
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
		html_success_quit("�����޸ĳɹ���<br/>" . 
			"��ҳ�潫��3����Զ����ذ��������б�<meta http-equiv=refresh content='3; url=bbsdoc.php?board=" . $brd_encode . "'/>",
			array("<a href='" . MAINPAGE_FILE . "'>������ҳ</a>", 
			"<a href='bbsdoc.php?board=" . $brd_encode . "'>���� " . $brdarr['DESC'] . "</a>",
			"<a href='bbscon.php?board=" . $brd_encode . "&id=" . $id . "'>���ء�" . $_POST["title"] . "��</a>"));
	}
}
else
{
?>	
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>" method="post" class="large"/>
	<fieldset><legend>�޸����±���</legend>
		<div class="inputs">
			<label>�����±���:</label>
			<input type="text" name="title" id="sfocus" size="40" value="<?php echo htmlspecialchars($articles[1]["TITLE"]); ?>" />
		</div>
	</fieldset>
	<div class="oper"><input type="submit" value="�޸�" /></div>
</form>
<?php	
}
page_footer();
?>
