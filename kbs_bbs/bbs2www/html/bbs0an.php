<?php
/**
 * show annouce directory
 * windinsn May 19,2004
 *
 */
 
function bbs_ann_updirs($path,&$up_dirs) {
    $path = ltrim(trim($path));
    if ($path[0]!='/') $path='/'.$path;
    if ($path[strlen($path)-1]=='/') $path = substr($path,0,strlen($path)-1);
    $up_dirs = array();
    $buf = '';
    $dirs = explode('/',$path);
    foreach($dirs as $dir) {
        if ($dir) {
            if (!strcmp('0Announce',$dir))
                continue;
            $buf .= '/'.$dir;
            $up_dirs[] = $buf;    
        }
    }
    return sizeof($up_dirs);
}

function bbs_ann_header($board='') {
?>
<body topmargin="0" leftmargin="0">
<a name="listtop"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tbody>  <tr> 
    <td colspan="2" class="b2">
	    <a href="<?php echo MAINPAGE_FILE; ?>" class="b2"><font class="b2"><?php echo BBS_FULL_NAME; ?></font></a>
	    -
	    ����������
<?php
        if ($board)
            echo ' '.$board.'��';
?>
    </td>
  </tr>
  <tr> 
    <td colspan="2" height="9" background="images/dashed.gif"> </td>
  </tr></tbody>
</table>
<?php
}

function bbs_ann_foot($parent) {
?>   
<p align="center">
[<a href="/<?php echo MAINPAGE_FILE; ?>">������ҳ</a>]
<?php   
    if ($parent){
?>
[<a href="/bbs0an.php?path=<?php echo rawurlencode($parent); ?>">�ϼ�Ŀ¼</a>]
<?php
    }
?>
[<a href="/bbs0an.php">��Ŀ¼</a>]
[<a href="#listtop">���ض���</a>]
[<a href="javascript:location=location">ˢ��</a>] 
</p>
<?php    
}

function bbs_ann_display_articles($articles) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3" class="t1">
<tbody><tr><td class="t2" width="50">���</td><td class="t2" width="30">����</td><td class="t2">����</td><td class="t2" width="80">����</td><td class="t2" width="80">�༭����</td></tr>
</tbody>
<?php
    $i = 1;
    foreach ($articles as $article) {
        echo '<tbody><td class="t3">'.$i.'</td><td class="t4">';
        switch($article['FLAG']) {
            case 0:
                $img = '/images/oldgroup.gif';
                $alt = '����';
                $url = '';
                break;
            case 1:
                $img = '/images/groupgroup.gif';
                $alt = 'Ŀ¼';
                $url = '/bbs0an.php?path='.rawurlencode($article['PATH']);
                break;
            case 2:
            case 3:
            default:
                $img = '/images/newgroup.gif';
                $alt = '�ļ�';
                $url = '/bbsanc.php?path='.rawurlencode($article['PATH']);
        }
        echo '<img src="'.$img.'" alt="'.$alt.'" border="0" />';
        echo '</td><td class="t7">';
        if ($article['FLAG']==3)
            echo '<font color="red">@</font>';
        else
            echo '&nbsp;';
        if ($url)
            echo '<a href="'.$url.'">'.htmlformat($article['TITLE']).'</a>';
        else
            echo htmlformat($article['TITLE']);
        $bm = explode(' ',$article['BM']);
        $bm = $bm[0];
        echo '</td><td class="t4">'.($bm?'<a href="/bbsqry.php?userid='.$bm.'">'.$bm.'</a>':'&nbsp;').'</td><td class="t3">'.date('Y-m-d',$article['TIME']).'</td></tr></tbody>';
        $i ++;
    }
?>
</table>
<?php    
}

require('funcs.php');
require('board.inc.php');
login_init();

if (isset($_GET['path']))
	$path = trim($_GET['path']);
else 
	$path = "";

if (strstr($path, '.Names') || strstr($path, '..') || strstr($path, 'SYSHome'))
    html_error_quit('�����ڸ�Ŀ¼');

$board = '';
$articles = array();
$path_tmp = '';
$ret = bbs_read_ann_dir($path,$board,$path_tmp,$articles);

switch ($ret) {
    case -1:
        html_error_quit('������Ŀ¼������');
        break;
    case -2:
        html_error_quit('�޷�����Ŀ¼�ļ�');
        break;
    case -3:
        html_error_quit('��Ŀ¼��������');
        break;
    case -9:
        html_error_quit('ϵͳ����');
        break;
    default;
}

$path = $path_tmp;
if ($board) {
    $brdarr = array();
    $bid = bbs_getboard($board,$brdarr);
    if ($bid) {
        $board = $brdarr['NAME'];
        $usernum = $currentuser['index'];
        if (bbs_checkreadperm($usernum, $bid) == 0)
    		html_error_quit('�����ڸ�Ŀ¼');
        if (bbs_normalboard($board)) {
            $dotnames = BBS_HOME . '/' . $path . '/.Names';
            if (cache_header('public, must-revalidate',filemtime($dotnames),10))
                return;
        }
        html_init('gb2312','','',1);
        bbs_board_header($brdarr);
    }
    else {
        html_init('gb2312','','',1);
        bbs_ann_header($board);
    }
    
}
else {
    $dotnames = BBS_HOME . '/' . $path . '/.Names';
    if (cache_header('public, must-revalidate',filemtime($dotnames),10))
        return;
    $bid = 0;
    html_init('gb2312','','',1);
    bbs_ann_header();
}

$up_cnt = bbs_ann_updirs($path,$up_dirs);
bbs_ann_display_articles($articles);
if ($bid)
    bbs_board_foot($brdarr,'');
    
if ($up_cnt >= 2)
    bbs_ann_foot($up_dirs[$up_cnt - 2]);
else
    bbs_ann_foot('');
    
html_normal_quit();
?>