<?php

require("inc/funcs.php");
require("inc/board.inc.php");
html_init();
?>
<body style="margin: 0pt;">
<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><td class=TableBody2 valign=middle height=28>
<?php 
if (!isset($_GET['board'])) {
	errorQuit("δָ�����档");
}
$boardName=$_GET['board'];
$brdArr=array();
$boardID= bbs_getboard($boardName,$brdArr);
$boardArr=$brdArr;
$boardName=$brdArr['NAME'];
if ($boardID==0) {
	errorQuit("ָ���İ��治���ڡ�");
}
$usernum = $currentuser["index"];
if (bbs_checkreadperm($usernum, $boardID) == 0) {
	errorQuit("����Ȩ�Ķ����棡");
}
if (bbs_is_readonly_board($boardArr)) {
	errorQuit("����Ϊֻ����������");
}
if (bbs_checkpostperm($usernum, $boardID) == 0) {
	errorQuit("����Ȩ�ڱ��淢�����£�");
}

$attachdir=bbs_getattachtmppath($currentuser["userid"] ,$utmpnum);
@mkdir($attachdir);

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
		if ($_FILES['upfile']['size']>ATTACHMAXSIZE) 
			$errno=UPLOAD_ERR_FORM_SIZE;
	} else
		$errno=100;
}
$filecount=0;
$filenames=array();
$filesizes=array();
if (($fp=@fopen($attachdir . "/.index","r"))!=FALSE) {
	while (!feof($fp)) {
		$buf=fgets($fp);
		$buf=substr($buf,0,-1); //remove "\n"
		if ($buf=="")
			continue;
		$file=substr($buf,0,strpos($buf,' '));
		if ($file=="")
			continue;
		$name=strstr($buf,' ');
		$name=substr($name,1);
		$filenames[] = $name;
		$filesizes[$name] = filesize($file);
			$totalsize+=$filesizes[$name];
			$filecount++;
		$allnames = $allnames . $name . ";";
	}
	fclose($fp);
}
if ($_FILES['upfile']['size']+$totalsize>ATTACHMAXTOTALSIZE) {
	unlink($attachdir . "/" . $act_attachname);
	//unset($filenames,$act_attachname);
	$errno=UPLOAD_ERR_FORM_SIZE;
}
if ($filecount>ATTACHMAXCOUNT) {
	errorQuit( "�������������涨��");
	break;
} 
switch ($errno) {
case UPLOAD_ERR_OK:
	@mkdir($attachdir);
	$tmpfilename=tempnam($attachdir,"att");
	if (isset($filesizes[$act_attachname])) {
		errorQuit( "����ͬ���ļ���");
	} else {
		if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
			move_uploaded_file($_FILES['upfile']['tmp_name'], 
				$tmpfilename);
			 /* ��д .index*/
			if (($fp=@fopen($attachdir . "/.index", "a"))==FALSE) {
					unlink($attachdir . "/" . $act_attachname);
			} else {
				fputs($fp,$tmpfilename . " " . $act_attachname . "\n");
				fclose($fp);
				$filenames[] = $act_attachname;
				$filesizes[$act_attachname] = filesize($tmpfilename);
				$totalsize+=$filesizes[$act_attachname];
				$filecount++;
				$allnames = $allnames . $act_attachname . ";";
				break;
			}
		}
		errorQuit("���渽���ļ�ʧ�ܣ�");
	}
	break;
case UPLOAD_ERR_INI_SIZE:
case UPLOAD_ERR_FORM_SIZE:
	errorQuit( "�ļ�����Ԥ���Ĵ�С" . intval(ATTACHMAXSIZE/1024) . "KB");
	break;
case UPLOAD_ERR_PARTIAL:
	errorQuir( "�ļ��������");
	break;
case UPLOAD_ERR_NO_FILE:
	errorQuit( "û���ļ��ϴ���");
	break;
case 100:
	errorQuit( "��Ч���ļ�����");
	break;
default:
	errorQuit( "δ֪����");
	break;
}
?>
<script language="javascript">
	oCon=getParentRawObject("oArticleContent");
	oCon.value+='[upload=<?php echo $filecount; ?>][/upload]';
</script>
<?php
	if($filecount < ATTACHMAXCOUNT)
		print $filecount."���ļ��ϴ��ɹ� [ <a href=\"postupload.php?board=". $boardName. "\">�����ϴ�</a> ]";
	else
		print $filecount."���ļ��ϴ��ɹ�!�����Ѵﵽ�ϴ������ޡ�";
?>
</td></tr>
</table>
</body>
</html>
<?php
function errorQuit($str){
?>
&nbsp; <?php echo $str; ?>
</td></tr>
</body>
</html>
<?php
	exit(0);
}
?>
