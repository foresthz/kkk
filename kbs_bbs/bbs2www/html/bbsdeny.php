<?php
/**
 * ������
 * windinsn.04.05.17
 */
$denyreasons = array(
        '��ˮ',
        '���Ű�����������',
        '�������',
        '������',
        '�����޹ػ���',
        '����ǡ������',
        'test(�����öԷ�ͬ��󹩲��Է��ʹ��)'
                );
 
require("funcs.php");
require("board.inc.php");
login_init();
if ($loginok != 1)
	html_nologin();

html_init("gb2312","","",1);

if (isset($_GET["board"]))
    $board = $_GET["board"];
else
    html_error_quit("�����������");

$brdarr = array();
$bid = bbs_getboard($board, $brdarr);
if ($bid == 0)
    html_error_quit("�����������");
$usernum = $currentuser["index"];
if (!bbs_is_bm($bid, $usernum))
    html_error_quit("�㲻�ǰ���");
$board = $brdarr['NAME'];
$brd_encode = urlencode($board);

if (isset($_GET['act'])) {
    switch ($_GET['act']) {
        case 'del':
            $userid = ltrim(trim($_GET['userid']));
            if (!$userid)
                html_error_quit("���������û���ID");
            switch (bbs_denydel($board,$userid)) {
                case -1:
                case -2:
                    html_error_quit("����������");
                    break;
                case -3:
                    html_error_quit($userid." ���ڷ���б���");
                    break;
                default:
            }
            break;
        case 'add':
            $userid = ltrim(trim($_POST['userid']));
            $denyday = intval($_POST['denyday']);
            $exp = (trim($_POST['exp2']))?trim($_POST['exp2']):$denyreasons[intval($_POST['exp'])];
            if (!$userid || !$denyday || !$exp)
                break;
            if (!strcasecmp($userid,'guest') || !strcasecmp($userid,'SYSOP'))
                html_error_quit("���ܷ�� ".$userid);
            switch (bbs_denyadd($board,$userid,$exp,$denyday,1)) {
                case -1:
                case -2:
                    html_error_quit("����������");
                    break;
                case -3:
                    html_error_quit("����ȷ��ʹ����ID");
                    break;
                case -4:
                    html_error_quit("�û� ".$userid." ���ڷ���б���");
                    break;
                case -5:
                    html_error_quit("���ʱ�����");
                    break;
                case -6:
                    html_error_quit("������������");
                    break;
                default:
            }
            break;
        default:
    }
}

$denyusers = array();
$ret = bbs_denyusers($board,$denyusers);
switch ($ret) {
    case -1:
        html_error_quit("ϵͳ��������ϵ����Ա");
        break;
    case -2:
        html_error_quit("�����������");
        break;
    case -3:
        html_error_quit("������Ȩ��");
        break;
    default:    
}

$maxdenydays = ($currentuser["userlevel"]&BBS_PERM_SYSOP)?70:14;

bbs_board_header($brdarr);
?>
<center><br/>
<p>�������</p>
<table cellspacing="0" cellpadding="3" border="0" width="90%" class="t1">
    <tbody><tr>
        <td width="40" class="t2">���</td>
        <td width="100" class="t2">�û���</td>
        <td class="t2">����</td>
        <td width="120" class="t2">˵��</td>
        <td width="40" class="t2">���</td>
    </tr></tbody>
<?php
    $i = 1;
    foreach ($denyusers as $user) {
        echo '<tbody><tr><td class="t3">'.$i.'</td><td class="t4"><a href="/bbsqry.php?userid='.$user['ID'].'">'.$user['ID'].'</a></td>'.
             '<td class="t7">'.$user['EXP'].' </td>'.
             '<td class="t5">'.htmlspecialchars($user['COMMENT']).'</td>'.
             '<td class="t4"><a onclick="return confirm(\'ȷʵ�����?\')" href="'.$_SERVER['PHP_SELF'].'?board='.$brd_encode.'&act=del&userid='.$user['ID'].'">���</a></td>'.
             '</tr></tbody>';
        $i ++ ;
    }
?>
</table><br />
<form action="<?php $_SERVER['PHP_SELF']; ?>?act=add&board=<?php echo $brd_encode; ?>" method="post">
<table cellspacing="0" cellpadding="3" border="0" width="60%" class="t1">
<tr>
    <td colspan="2" class="t2">��ӷ���û�</td>
</tr>
<tr>
    <td width="100" class="t3">�û���</td>
    <td class="t7"><input type="text" name="userid" size="12" maxlength="12" /></td>
</tr>
<tr>
    <td width="100" class="t3">���ʱ��</td>
    <td class="t7">
    <select name="denyday">
<?php
    $i = 1;
    while ($i <= $maxdenydays) {
        echo '<option value="'.$i.'">'.$i.'</option>';
        $i += ($i >= 14)?7:1;
    }    
?>    
    </select>
    ��
    </td>
</tr>
<tr>
    <td width="100" class="t3">���ԭ��</td>
    <td class="t7">
    <select name="exp">
<?php
    $i = 0;
    foreach ($denyreasons as $reason) {
        echo '<option value="'.$i.'">'.htmlspecialchars($reason).'</option>';
        $i ++;
    }
?>    
    </select><br />
    ���ֶ����������ɣ�
    <input type="text" name="exp2" size="20" maxlength="28" />
    </td>
</tr>
<tr>
    <td colspan="2" class="t3">
    <input type="submit" value="��ӷ��" />
    </td>
</tr>
</table></form>
</center>
<?php
bbs_board_foot($brdarr,'DENYLIST');
html_normal_quit();
?>