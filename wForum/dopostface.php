<?php
require("inc/funcs.php");
require("inc/board.inc.php");
if (!USER_FACE) exit;
html_init();
if ($loginok != 1) die("�οͣ�");
?>
<body style="margin: 0pt;">
<script src="inc/browser.js"  language="javascript"></script>
<script language="javascript">
	oSubmit=getParentRawObject("oSubmit");
	oSubmit2=getParentRawObject("oSubmit2");
	oSubmit.disabled=false;
	oSubmit2.disabled=false;	
</script>
<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><td class=TableBody2 valign=middle height=28>
<?php

function get_extname($name) //�ҵı�����ʵֻ�Ƿ�ֹ .PHP ���ļ���������������... - atppp
{
	$dot = strrchr($name, '.');
	if ($dot == $name)
		return false;
	if (strcasecmp($dot, ".jpg") == 0 || strcasecmp($dot, ".jpeg") == 0)
		return $dot;
	if (strcasecmp($dot, ".gif") == 0)
		return $dot;
	if (strcasecmp($dot, ".png") == 0)
		return $dot;
	return false;
}

@$errno=$_FILES['upfile']['error'];
if ($errno==UPLOAD_ERR_OK) {
	$buf=$_FILES['upfile']['name'];
	$tok = strtok($buf,"/\\");
	$act_attachname="";
	while ($tok) {
		$act_attachname=$tok;
		$tok = strtok("/\\");
	}
	$act_attachname=strtr($act_attachname,$filename_trans);
	$act_attachname=substr($act_attachname,-60);
	if ($act_attachname!="") {
		if ($_FILES['upfile']['size']>MYFACEMAXSIZE) 
			$errno=UPLOAD_ERR_FORM_SIZE;
		else {
			$ext = get_extname($act_attachname);
			if ($ext === false) $errno = 100;
		}
	} else {
		$errno=100;
	}
}
switch ($errno) {
case UPLOAD_ERR_OK:
	$tmp_filename = $_FILES['upfile']['tmp_name'];
	if (is_uploaded_file($tmp_filename)) {
		$sizeinfo = @getimagesize ($tmp_filename); //�����ʵ���Խ��������ȥ������ JS �Զ���䳤��ֵ����������Ҳû���� load���������ɡ�- atppp
		if ($sizeinfo === false) errorQuit("������������Ч��ͼ���ļ���");
		$width = $sizeinfo[0];
		$height = $sizeinfo[1];
		if ($width > 120 || $height > 120) {
			if ($width > $height) {
				$height = (int)((float)$height / $width * 120);
				$width = 120;
			} else {
				$width = (int)((float)$width / $height * 120);
				$height = 120;
			}
		}
		require_once("inc/myface.inc.php");
		$myface_filename = get_myface_filename($currentuser["userid"], $ext);
		if (move_uploaded_file($tmp_filename, get_myface_fs_filename($myface_filename))) {
			break;
		}
		errorQuit("���渽���ļ�ʧ�ܣ�");
	}
	break;
case UPLOAD_ERR_INI_SIZE:
case UPLOAD_ERR_FORM_SIZE:
	errorQuit( "�ļ�����Ԥ���Ĵ�С" . intval(MYFACEMAXSIZE/1024) . "KB");
	break;
case UPLOAD_ERR_PARTIAL:
	errorQuir( "�ļ��������");
	break;
case UPLOAD_ERR_NO_FILE:
	errorQuit( "û���ļ��ϴ���");
	break;
case 100:
	errorQuit( "�ļ���������չ���е����⣡");
	break;
default:
	errorQuit( "δ֪����");
	break;
}
?>
<script language="javascript">
	newsrc = '<?php echo $myface_filename; ?>';
	o = getParentRawObject("imgmyface");
	o.src = newsrc;
	o.width = <?php echo $width; ?>;
	o.height = <?php echo $height; ?>;
	getParentRawObject("myface").value = newsrc;
	getParentRawObject("width").value = <?php echo $width; ?>;
	getParentRawObject("height").value = <?php echo $height; ?>;
</script>
�ļ��ϴ��ɹ� [ <a href="postface.php">���ѿ�����Ҫ�����ϴ�</a> ]
</td></tr>
</table>
</body>
</html>
<?php

function errorQuit($str){
?>
&nbsp; <?php echo $str; ?> [ <a href="postface.php">�����ϴ�</a> ]
</td></tr>
</body>
</html>
<?php
	exit(0);
}
?>
