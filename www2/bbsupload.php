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
			$ofile = $_FILES['attachfile']['tmp_name'];
			$oname = $_FILES['attachfile']['name'];
			if (!is_uploaded_file($ofile)) {
				die;
			}
			if (defined("AUTO_BMP2JPG_THRESHOLD")) {
				$oname = basename($oname);
				if (strcasecmp(".bmp", substr($oname, -4)) == 0 && (filesize($ofile) > AUTO_BMP2JPG_THRESHOLD)) {
					$h = popen("identify -format \"%m\" ".$ofile, "r");
					if ($h) {
						$read = fread($h, 1024);
						pclose($h);
						if (strncasecmp("BMP", $read, 3) == 0) {
							$tp = tempnam("/tmp", "BMP2JPG");
							exec("convert -quality 90 $ofile jpg:$tp");
							if (file_exists($tp)) {
								unlink($ofile);
								$ofile = $tp;
								$oname = substr($oname, 0, -4) . ".jpg";
							}
						}
					}
				}
			}
			$ret = bbs_upload_add_file($ofile, $oname);
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
<body>
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
		getObj("winclose").style.display = "none";
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

if (opener) {
	opener.document.forms["postform"].elements["attachname"].value = "<?php echo $allnames; ?>";
} else {
	addBootFn(function() { getObj("winclose").style.display = "none"; });
}
//-->
</script>
<div style="width: 550px; margin: 1em auto;">
<?php if ($msg) echo "<font color='red'> ��ʾ��".$msg."</font>"; ?>
<form name="addattach" method="post" ENCTYPE="multipart/form-data" class="left" action="">
<?php if ($sessionid) echo "<input type='hidden' name='sid' value='$sessionid' />"; ?>
ѡ����Ҫ�ϴ����ļ����ճ���������ж�������ļ�Ҫճ�������ظ�������裩<br/>
<?php
	if ($filecount<BBS_MAXATTACHMENTCOUNT) {
?>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo(BBS_MAXATTACHMENTSIZE);?>" />
		<input type="file" name="attachfile" size="20" />
		<input type="button" value="ճ��" name="paste" onclick="addsubmit();" />
<?php
	} else {
?>
		��������������</td>
		<input type="hidden" name="attachfile" />
<?php
	}
?>
&nbsp;&nbsp;&nbsp;<input type="button" id="winclose" value="�ϴ����, �رմ���" onclick="return clickclose()" />
<p>�����ļ�������<?php echo sizestring($totalsize); ?> �ֽ�,
���ޣ�<?php echo sizestring(BBS_MAXATTACHMENTSIZE); ?> �ֽ�,
�����ϴ���<font color="#FF0000"><b><?php $rem = BBS_MAXATTACHMENTSIZE-$totalsize; 
	if ($rem < 0) $rem = 0; echo sizestring($rem); ?> �ֽ�</b></font>.</p>
</form>

<form name="deleteattach" ENCTYPE="multipart/form-data" method="post" class="left" action=""> 
<?php if ($sessionid) echo "<input type='hidden' name='sid' value='$sessionid' />"; ?>
<ol style="padding-left: 2em; margin-left: 0em;">�Ѿ��ϴ��ĸ����б�: (������ϴ� <?php echo BBS_MAXATTACHMENTCOUNT; ?>
 ��, �����ϴ� <font color="#FF0000"><b><?php echo (BBS_MAXATTACHMENTCOUNT-$filecount); ?></b></font> ��)
<?php
	for($i=0;$i<$filecount;$i++) {
		$f = $attachments[$i];
		echo "<li>".$f["name"]." (".sizestring($f["size"])."�ֽ�) <a href=\"javascript:deletesubmit('".$f["name"]."');\">ɾ��</a></li>";
	}
?>
</ol>
</form>
</div>
</body>
</html>
