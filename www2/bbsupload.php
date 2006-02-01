<?php

	$filenames=array();
	$filesizes=array();
	require("www2-funcs.php");
	$sessionid = login_init(TRUE);
	assert_login();
	
	@$action=$_GET["act"];
	$msg = "";
	if ($action=="delete") {
		@$act_attachname=$_GET["attachname"];
		settype($act_attachname, "string");
		$ret = bbs_upload_del_file($act_attachname);
		switch($ret) {
			case -2:
				$msg = "û������ļ�";
				break;
			case 0:
				$msg = "ɾ���ļ��ɹ�";
				break;
			default:
				break;
		}
	} else if ($action=="add") {
		if (isset($_FILES['attachfile'])) {
			@$errno=$_FILES['attachfile']['error'];
		} else {
			$errno = UPLOAD_ERR_PARTIAL;
		}
		switch ($errno) {
		case UPLOAD_ERR_OK:
			if (!is_uploaded_file($_FILES['attachfile']['tmp_name'])) {
				die;
			}
			$ret = bbs_upload_add_file($_FILES['attachfile']['tmp_name'], $_FILES['attachfile']['name']);
			switch($ret) {
				case 0:
					$msg = "�ļ����سɹ���";
					break;
				case -1:
					$msg = "ϵͳ����";
					break;
				case -2:
					$msg = "�������������涨��";
					break;
				case -3:
					$msg = "��Ч���ļ�����";
					break;
				case -4:
					$msg = "����ͬ���ļ���";
					break;
				case -5:
					$msg = "���渽���ļ�ʧ�ܣ�";
					break;
				case -6:
					$msg = "�ļ������������� " . sizestring(BBS_MAXATTACHMENTSIZE) . " �ֽ�";
					break;
				default:
					break;
			}
			break;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$msg = "�ļ��������� " . sizestring(BBS_MAXATTACHMENTSIZE) . " �ֽ�";
			break;
		case UPLOAD_ERR_PARTIAL:
			$msg = "�ļ��������";
			break;
		case UPLOAD_ERR_NO_FILE:
			$msg = "û���ļ��ϴ���";
			break;
		default:
			$msg = "δ֪����";
		}
	}
	$attachments = bbs_upload_read_fileinfo();
	$filecount = count($attachments);
	$allnames = array();$totalsize=0;
	for($i=0;$i<$filecount;$i++) {
		$allnames[] = $attachments[$i]["name"];
		$totalsize += $attachments[$i]["size"];
	}
	$allnames=implode(",",$allnames);
	page_header("ճ������", FALSE);
?>
<body style="padding: 1em;">
<script type="text/javascript">
<!--
function addsubmit() {
	var obj=document.forms[0].elements["attachfile"];
	if (!obj) return true;
	if (obj.value == ""){
		alert('����ûѡ���ϴ��ĸ���');
		return false;
	} else {
		var e2="bbsupload.php?act=add";
		document.forms[0].action=e2;
		document.forms[0].paste.value='���������У����Ժ�...';
		document.forms[0].paste.disabled=true;
		document.forms[0].submit();
		return true;
	}
}

function deletesubmit(f) {
	var e2="bbsupload.php?act=delete&attachname="+f;
	document.forms[1].action=e2;
	document.forms[1].submit();
}

function clickclose() {
	if (document.forms[0].elements["attachfile"].value == "") return window.close();
	else if (confirm("����д���ļ�������û�����ء��Ƿ�ȷ�Ϲرգ�")==true) return window.close();
	return false;
}

opener.document.forms["postform"].elements["attachname"].value = <?php echo "\"$allnames\""; ?>;
//-->
</script>
<?php if ($msg) echo "<font color='red'> ��ʾ��".$msg."</font>"; ?>
<form name="addattach" method="post" ENCTYPE="multipart/form-data" class="left" action="">
<?php if ($sessionid) echo "<input type='hidden' name='sid' value='$sessionid' />"; ?>
ѡ����Ҫ�ϴ����ļ����ճ���������ж�������ļ�Ҫճ�������ظ�������裩<br/>
<?php
	if ($filecount<BBS_MAXATTACHMENTCOUNT) {
?>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo(BBS_MAXATTACHMENTSIZE);?>" />
		<input type="file" name="attachfile" size="20" />
		<input type="button" width="61" height="21" value="ճ��" name="paste" onclick="addsubmit();" />
<?php
	} else {
?>
		��������������</td>
		<input type="hidden" name="attachfile" />
<?php
	}
?>
<p>�����ļ�����Ϊ��<font color="#FF0000"><b><?php echo sizestring($totalsize); ?>�ֽ�</b></font>,
���ޣ�<font color="#FF0000"><b><?php echo sizestring(BBS_MAXATTACHMENTSIZE); ?>�ֽ�</b></font>,
�����ϴ���<font color="#FF0000"><b><?php $rem = BBS_MAXATTACHMENTSIZE-$totalsize; 
	if ($rem < 0) $rem = 0; echo sizestring($rem); ?>�ֽ�</b></font>.
<input type="button" width="61" height="21" value="���" onclick="return clickclose()" /></p>
</form>

<form name="deleteattach" ENCTYPE="multipart/form-data" method="post" class="left" action=""> 
<?php if ($sessionid) echo "<input type='hidden' name='sid' value='$sessionid' />"; ?>
<ol style="padding-left: 2em; margin-left: 0em;">�Ѿ��ϴ��ĸ����б�: (������ϴ� <?php echo BBS_MAXATTACHMENTCOUNT; ?>
 ��, �����ϴ� <?php echo (BBS_MAXATTACHMENTCOUNT-$filecount); ?> ��)
<?php
	for($i=0;$i<$filecount;$i++) {
		$f = $attachments[$i];
		echo "<li>".$f["name"]." (".sizestring($f["size"])."�ֽ�) <a href=\"javascript:deletesubmit('".$f["name"]."');\">ɾ��</a></li>";
	}
?>
</ol>
</form>
</body>
</html>
