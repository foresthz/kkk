<?php

require("bbs0anbm_pre.php");

if($has_perm_boards < 1)
	html_error_quit("��û��Ȩ�޲�����Ŀ¼��");
	
page_header("����Ŀ¼", "����������");

?>
<form action="bbs0anbm_mkdir.php?path=<?php echo rawurlencode($path); ?>" method="post" class="medium">
	<fieldset><legend>����������Ŀ¼</legend>
		<div class="inputs">
			<label>�ļ�����</label><input type="text" maxlength="38" size="15" name="filename"><br>
			<label>�ꡡ�⣺</label><input type="text" maxlength="38" size="38" name="title"><br>
			<label>�桡����</label><input type="text" maxlength="38" size="15" name="bm"><br>
		</div>
	</fieldset>
	<div class="oper"><input type="submit" value="����Ŀ¼"></div>
</form>
<?php

page_footer();
	
?>