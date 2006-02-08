<?php
/* TODO: ����Ƿ��Ǹ������� */
	require("www2-funcs.php");
	require("www2-board.php");
	require("www2-bmp.php");
	login_init();
	bbs_session_modify_user_mode(BBS_MODE_EDIT);
	assert_login();
	$sessionid = false;

	if (isset($_GET["board"]))
		$board = $_GET["board"];
	else
		html_error_quit("�����������");
	// ����û��ܷ��Ķ��ð�
	$brdarr = array();
	$brdnum = bbs_getboard($board, $brdarr);
	if ($brdnum == 0)
		html_error_quit("�����������");
	bbs_set_onboard($brdnum,1);
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $brdnum) == 0)
		html_error_quit("�����������");
	$board = $brdarr['NAME'];
	$brd_encode = urlencode($board);
	if(bbs_checkpostperm($usernum, $brdnum) == 0) {
		html_error_quit("�������������������Ȩ�ڴ���������������");
	}
	if (bbs_is_readonly_board($brdarr))
		html_error_quit("������ֻ����������������");
	$ftype = $dir_modes["NORMAL"];

	bbs_board_nav_header($brdarr, "�޸ĸ���");
	
	if (isset($_GET['id']))
		$id = intval($_GET['id']);
	else
		html_error_quit("������ı��");
	$articles = array();
	$num = bbs_get_records_from_id($brdarr["NAME"], $id, $ftype, $articles);
	if ($num == 0)
		html_error_quit("������ı��");
	
	@$action=$_GET["act"];
	$msg = "";
	$ret = false;
	if ($action=="delete") {
		@$act_attachnum=$_GET["attachnum"];
		settype($act_attachnum, "integer");
		$ret = bbs_attachment_del($board, $id, $act_attachnum);
		if (!is_array($ret)) $msg = "����: " . bbs_error_get_desc($ret);
		else $msg = "ɾ�������ɹ�";
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
			if (compress_bmp($ofile, $oname)) {
				$msg = "���� BMP ͼƬ���Զ�ת���� PNG ��ʽ��";
			}
			$ret = bbs_attachment_add($board, $id, $ofile, $oname);
			if (!is_array($ret)) $msg = "����:" . bbs_error_get_desc($ret);
			else $msg .= "���Ӹ����ɹ�";
			break;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$msg = "�ļ��������� " . sizestring(BBS_MAXATTACHMENTSIZE) . " �ֽ�";
			break;
		case UPLOAD_ERR_PARTIAL:
			$msg = "�ļ����������";
			break;
		case UPLOAD_ERR_NO_FILE:
			$msg = "û���ļ��ϴ���";
			break;
		default:
			$msg = "δ֪����";
		}
	}
	if (!is_array($ret)) {
		$attachments = bbs_attachment_list($board, $id);
		if (!is_array($attachments)) {
			html_error_quit(bbs_error_get_desc($attachments));
		}
	} else {
		$attachments = $ret;
	}
	$filecount = count($attachments);
	$allnames = array();$totalsize=0;$allpos = array();
	for($i=0;$i<$filecount;$i++) {
		$allnames[] = $attachments[$i]["name"];
		$allpos[] = $attachments[$i]["pos"];
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
		var e2="bbseditatt.php?board=<?php echo $board; ?>&id=<?php echo $id; ?>&act=add";
		getObj("winclose").style.display = "none";
		document.forms[0].action=e2;
		document.forms[0].paste.value='���������У����Ժ�...';
		document.forms[0].paste.disabled=true;
		document.forms[0].submit();
		return true;
	}
}

function deletesubmit(f) {
	var e2="bbseditatt.php?board=<?php echo $board; ?>&id=<?php echo $id; ?>&act=delete&attachnum="+f;
	document.forms[1].action=e2;
	document.forms[1].submit();
}

function clickclose() {
	if (document.forms[0].elements["attachfile"].value == "") return window.close();
	else if (confirm("����д���ļ�������û�����ء��Ƿ�ȷ�Ϲرգ�")==true) return window.close();
	return false;
}

addBootFn(function() {
	var conURL = getMirror() + "bbscon.php?bid=<?php echo $brdnum; ?>&id=<?php echo $id; ?>&ap=";
	var pos = [<?php echo implode(",",$allpos); ?>];
	var i;
	for(i=0; i<pos.length; i++) {
		var o = getObj("att" + i);
		if (o) o.href= conURL + pos[i];
	}
});
if (opener) {
	//opener.document.forms["postform"].elements["attachname"].value = "<?php echo $allnames; ?>";
} else {
	addBootFn(function() { getObj("winclose").style.display = "none"; });
}
//-->
</script>
<div style="width: 550px; margin: 1em auto;">
<?php if ($msg) echo "<font color='red'> ��ʾ��".$msg."</font>"; ?>
<form name="addattach" method="post" ENCTYPE="multipart/form-data" class="left" action="">
������: <?php echo $articles[1]['OWNER']; ?>, ����: <?php echo $brd_encode; ?> [<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>">��������</a>]<br/>
��&nbsp;&nbsp;��: <a href="bbscon.php?bid=<?php echo $brdnum; ?>&id=<?php echo $id; ?>"><?php echo $articles[1]['TITLE']; ?> </a><br/><br/>
<?php if ($sessionid) echo "<input type='hidden' name='sid' value='$sessionid' />"; ?>
ѡ����Ҫ����Ϊ�������ļ�����ϴ���<br/>
<?php
	if (!bbs_is_attach_board($brdarr) && !($currentuser["userlevel"]&BBS_PERM_SYSOP)) {
?>
		���治���ϴ�������
		<input type="hidden" name="attachfile" />	
<?php
	} else if ($filecount<BBS_MAXATTACHMENTCOUNT) {
?>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo(BBS_MAXATTACHMENTSIZE);?>" />
		<input type="file" name="attachfile" size="20" />
		<input type="button" value="�ϴ�" name="paste" onclick="addsubmit();" />
<?php
	} else {
?>
		��������������
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
<ol style="padding-left: 2em; margin-left: 0em;">�����ڵĸ����б�: (������ϴ� <?php echo BBS_MAXATTACHMENTCOUNT; ?>
 ��, �����ϴ� <font color="#FF0000"><b><?php echo (BBS_MAXATTACHMENTCOUNT-$filecount); ?></b></font> ��)
<?php
	for($i=0;$i<$filecount;$i++) {
		$f = $attachments[$i];
		echo "<li><a target='_blank' href='#' id='att".$i."'>".$f["name"]."</a> (".sizestring($f["size"])."�ֽ�) <a href=\"javascript:deletesubmit('".($i+1)."');\">ɾ��</a></li>";
	}
?>
</ol>
</form>
</div>
</body>
</html>