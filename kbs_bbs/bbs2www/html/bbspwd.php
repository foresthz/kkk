<?php
	require("www2-funcs.php");
	login_init();
	toolbox_header("�����޸�");
	assert_login();

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
		html_success_quit("�����޸ĳɹ����������������趨");
		exit;
	}
?>
<form action="bbspwd.php?do" method="post" class="small">
	<fieldset><legend>�޸�����</legend>
		<div class="inputs">
			<label>���ľ�����:</label><input maxlength="39" size="12" type="password" name="pw1" id="sfocus"/><br/>
			<label>����������:</label><input maxlength="39" size="12" type="password" name="pw2"/><br/>
			<label>������һ��:</label><input maxlength="39" size="12" type="password" name="pw3"/>
		</div>
	</fieldset>
	<div class="oper"><input type="submit" value="ȷ���޸�"></div>
</form>
<?php
	page_footer();
?>
