<?php
require("funcs.php");
login_init();
if ($loginok != 1) {
    html_nologin();
    exit;
}
html_init("gb2312","","",1);
?>
<body topmargin="0">
<?php

if (! bbs_can_send_mail() )
    html_error_quit("�����ܷ����ż�");

$incept = trim(ltrim($_POST['userid']));
if (!$incept)
    html_error_quit("�������ռ���ID");
$lookupuser = array();
if (!bbs_getuser($incept,$lookupuser))
    html_error_quit("������ռ���ID");
$incept = $lookupuser['userid'];

$title = preg_replace("/\\\(['|\"|\\\])/","$1",trim($_POST["title"]));
if (!$title) $title = '������';


$sig = intval($_POST['signature']); //ǩ����
$backup = isset($_POST['backup'])?strlen($_POST['backup']):0; //����
$content = preg_replace("/\\\(['|\"|\\\])/","$1",$_POST["text"]); 

$ret = bbs_postmail($incept,$title,$content,$sig,$backup);

if ($ret < 0)  {
    switch($ret) {
        case -1:
        case -2:
            html_error_quit("�޷������ļ�");
            break;
        case -3:
            html_error_quit($incept." ���������ʼ�");
            break;
        case -4:
            html_error_quit($incept." ����������");
            break;
        case -6:
            html_error_quit("����ʼ��б����");
            break;
        case -7:
?>
<script language="javascript">
<!--
alert('�ʼ����ͳɹ�����δ�ܱ��浽������');
-->
</script>
<?php
            break;
        default:
            html_error_quit("ϵͳ��������ϵ����Ա");
    }
}
?>
<br /><br /><br /><center>
�ż����ͳɹ�
<br /><br /><br />
[<a href="/bbsmail.php">�����ҵ�����</a>]
[<a href="javascript:history.go(-2)">���ٷ���</a>]
</center>
<?php   
html_normal_quit();
?>