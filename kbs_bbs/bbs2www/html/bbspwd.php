<?php
require("funcs.php");
login_init();

function bbs_pwd_form() {
?>  
<script language="JavaScript">
<!--
//document.write("<form action='https://"+window.location.hostname+"/bbspwd.php?do' method='post'>");
-->
</script>
<form action='/bbspwd.php?do' method='post'>
<center>
<table class="t1" width="500" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <td class="t3">���ľ�����</td>
        <td class="t7"> <input maxlength="39" size="12" type="password" name="pw1" class="f1"></td>
    </tr>
    <tr>
        <td class="t3">����������</td>
        <td class="t7"> <input maxlength="39" size="12" type="password" name="pw2" class="f1"></td>
    </tr>
    <tr>
        <td class="t3">������һ��</td>
        <td class="t7"> <input maxlength="39" size="12" type="password" name="pw3" class="f1"></td>
    </tr>
    <tr>
        <td class="t2" colspan="2"><input type="submit" value="ȷ���޸�" class="f1"></td>
    </tr>
</table>
</center>
</form>
<?php    
}

if ($loginok != 1) {
    html_nologin();
    exit();
}
if(!strcmp($currentuser["userid"],"guest"))
    html_error_quit("���ȵ�¼");

html_init("gb2312","","",1);
?>
<body topmargin="0">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr> 
    <td class="b2">
	    <a href="bbssec.php" class="b2"><?php echo BBS_FULL_NAME; ?></a>
	    -
	    <?php echo $currentuser["userid"]; ?>�Ĺ�����
	    -
	    �޸�����
    </td>
   </tr>
   <tr>
   <td height="20"> </td>
   </tr>
   <tr>
        <td align="center">
<?php
    if (isset($_GET['do'])) {
        $pass = $_POST['pw2'];
        if (strlen($pass) < 4 || strlen($pass) > 39)
            html_error_quit("�����볤��ӦΪ 4��39");
        if ($pass != $_POST['pw3'])
            html_error_quit("������������벻��ͬ");
        if (bbs_checkpasswd($currentuser["userid"],$_POST['pw1']) != 0)
            html_error_quit("���벻��ȷ");
        if (!bbs_setpassword($currentuser["userid"],$pass))
            html_error_quit("ϵͳ��������ϵ����Ա");
?>
�����޸ĳɹ����������������趨<br /><br />
[<a href="/<?php echo MAINPAGE_FILE; ?>">������ҳ</a>]
[<a href="javascript:history.go(-2);">���ٷ���</a>]
<?php
    }
    else
        bbs_pwd_form();
?>        
        </td>
   </tr>
</table>
<?php
html_normal_quit();
?>