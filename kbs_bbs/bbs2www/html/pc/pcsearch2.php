<?php
	$needlogin=0;
	require("pcfuncs.php");
	
	pc_html_init("gb2312","Blog����");
?>
<center><br><br><br>
<form action="pcsearch.php" method="get" onsubmit="if(this.keyword.value==''){alert('������ؼ���');return false;}">
<p align="center" class="f2">Blog����:<p>
<p align="center" class="f1">
<input type="text" name="keyword" size="20" class="b2">
(����ģ������ʱ,���ÿո��������ؼ���)
</p><p align="center" class="f1">
��ʽ:
<input type="radio" name="exact" value="1" class="b2" checked>��ȷ
<input type="radio" name="exact" value="0" class="b2">ģ��
</p><p align="center" class="f1">
����:
<input type="radio" name="key" value="u" class="b2" checked>�û���
<input type="radio" name="key" value="c" class="b2">Blog��
<input type="radio" name="key" value="t" class="b2">����
<input type="radio" name="key" value="d" class="b2">Blog����
</p><p align="center" class="f1">
<input type="submit" value="��ʼ��" class="b1">
</p>
</form>
</center>
<p align="center">
<?php pc_main_navigation_bar(); ?>
</p>
<?php	
	html_normal_quit();
?>