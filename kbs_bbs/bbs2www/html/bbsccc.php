<?php
/**
 * ת��
 * windinsn
 */
require('funcs.php');
require('board.inc.php');
login_init();
html_init("gb2312","","",1);

if ($loginok != 1)
    html_nologin();

if (isset($_GET['board']))
    $board = $_GET['board'];
else
    html_error_quit('�����������');

$brdarr = array();
$bid = bbs_getboard($board,$brdarr);
if (!$bid)
    html_error_quit('�����������');
$board = $brdarr['NAME'];
$brd_encode = urlencode($board);

if (isset($_GET['id']))
    $id = intval($_GET['id']);
else
    html_error_quit('���������ID');


if (!bbs_normalboard($board))
    if (bbs_checkreadperm($currentuser["index"], $bid) == 0)
        html_error_quit("�����������");

$ftype = $dir_modes["NORMAL"];
$articles = array ();
$num = bbs_get_records_from_id($board, $id, $ftype, $articles);
if ($num == 0)
	html_error_quit("��������º�,ԭ�Ŀ����Ѿ���ɾ��");
$id = $articles[1]["ID"];
bbs_board_header($brdarr)
?>
<center><br/>
<?php
if (isset($_GET['do'])) {
    $target = trim(ltrim($_POST['target']));
    if (!$target)
        html_error_quit("������ת���������");
    $outgo = isset($_POST['outgo'])?1:0;
    switch (bbs_docross($board,$id,$target,$outgo)) {
        case 0:
?>
<b>ת���ɹ�!</b><br /><br /><br />
[<a href="/bbsdoc.php?board=<?php echo $target; ?>">���� <?php echo $target; ?> ������</a>]
[<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>&ftype=<?php echo $ftype; ?>"><?php echo htmlspecialchars($articles[1]["TITLE"]); ?>
</b></a>]
<?php
            break;
        case -1:
              html_error_quit("����������");
              break;
        case -2:
              html_error_quit("������ ".$target. " ������");
              break;
        case -3:
              html_error_quit("����ת��ֻ��������");
              break;
        case -4:
              html_error_quit("������ ".$target." �������ķ���Ȩ��");
              break;
        case -5:
              html_error_quit("��������� ".$target." �������ķ���Ȩ��");
              break;
        case -6:
              html_error_quit("ת�����´���");
              break;
        case -7:
              html_error_quit("�����ѱ�ת�ع�һ��");
              break;
        case -8:
              html_error_quit("���ܽ�����ת�ص�����");
              break;
        case -9:
              html_error_quit($target." ����������ճ������");
              break;
        default:
              html_error_quit("ϵͳ��������ϵ����Ա");
    }
}
else {
?>
<p>ת�����£�<b>
<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>&ftype=<?php echo $ftype; ?>"><?php echo htmlspecialchars($articles[1]["TITLE"]); ?>
</b></a>
</p><br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?do&board=<?php echo $brd_encode; ?>&id=<?php echo $id; ?>" method="post" />
������Ҫת���������
<input type="text" name="target" size="18" maxlength="20" />
<input type="checkbox" name="outgo" checked />ת��
<input type="submit" value="ת��" />
</form>
<?php
}
?>
<br /><br />
<br /></center>
<?php
bbs_board_foot($brdarr,"CROSS");
html_normal_quit();
?>