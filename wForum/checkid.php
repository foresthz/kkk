<?php

$needlogin = 0;
require("inc/funcs.php");

setStat("��� ID");

html_init();
@$id = urldecode($_GET["id"]);
?>
<body>
<form method="GET" action="checkid.php">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
	<TR><Th height=25>��� ID<?php if ($id != "") echo ": " . htmlspecialchars($id); ?></Th></TR>
	<TR><TD class=TableBody1 
height=24 align="center">
<?php
	
	if ($id == "") echo "��� ID �Ȱɡ�";
	else {
		$ret = bbs_is_invalid_id($id);
		switch($ret) {
			case 0:
				echo "�� ID ����ע�ᡣ";
				break;
			case 1:
				echo "�� ID ʹ���˽����ַ���";
				break;
			case 2:
				echo "�� ID ̫�̡�";
				break;
			case 3:
				echo "�� ID ʹ���˽����ַ���"; //���� ID
				break;
			case 4:
				echo "�� ID �Ѵ��ڡ�";
				break;
			case 5:
				echo "�� ID ̫����";
				break;
		}
	}
?>
	</TD></TR>
	<TR><TD class=TableBody2 
height=24 align="center">
		����� ID: <input type="text" name="id">&nbsp;<input type="submit" name="submit" value="��� ID">
	</td></tr>
</TABLE></form>
<br>
<?php
	show_footer(false);
?>
