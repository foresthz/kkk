<?php
require_once("funcs.php");
login_init();
require_once("style.inc.php");

$bbsstyle = bbs_style_getstyle();
html_init("GB2312");
?>
<body>
<br /><br /><br />
<center>
<form action="/bbsstyle.php" method="get" />
<table cellspacing="0" cellpadding="5" border="0">
<tr><td><b>��ѡ����淽��:</b></td></tr>
<tr><td>
<input type="radio" name="style" value="0" <?php if($bbsstyle==0) echo "checked"; ?> />Ĭ�Ϸ���<br />
<input type="radio" name="style" value="1" <?php if($bbsstyle==1) echo "checked"; ?>/>��ɫ����(С����)<br />
<input type="radio" name="style" value="2" <?php if($bbsstyle==2) echo "checked"; ?>/>��ɫ����(������)<br />
<input type="radio" name="style" value="3" <?php if($bbsstyle==3) echo "checked"; ?>/>��ɫ����(������)<br />
</td></tr>
</table>

<input type="submit" value="����" />
<input type="reset"  value="��ԭ" />
<input type="button" value="����" onclick="history.go(-1)" />
</form>
</center>
<?php
html_normal_quit();
?>
