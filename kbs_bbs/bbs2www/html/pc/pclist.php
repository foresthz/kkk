<?php
require("pcfuncs.php");
require("pcstat.php");
require("pcmainfuncs.php");

//if(pc_update_cache_header())
//	return;

$link = pc_db_connect();
pcmain_html_init();
?>
<form action="pcsearch.php" method="get" onsubmit="if(this.keyword.value==''){alert('������ؼ���');return false;}">
<tr>
	<td bgcolor="#F6F6F6">
	BLOG����
	<input name="keyword" type="text" class="textinput" size="20"> 
	<input type="hidden" name="exact" value="0">
	<select name="key">
	<option value="u" selected>�û���</option>
	<option value="c">Blog��</option>
	<option value="d">Blog����</option>
	</select>
	<input type="submit" class="textinput" value="��ʼ��">
	</td>
</tr>
</form>
<?php
pc_db_close($link);
pcmain_html_quit()
?>