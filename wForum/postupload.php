<?php
require("inc/funcs.php");
require("inc/board.inc.php");
html_init();
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
		return false;
}
if (bbs_checkpostperm($usernum, $boardID) == 0) {
	errorQuit("����Ȩ�ڱ��淢�����£�");
}
?>
<body style="margin: 0pt;">
<script src="inc/browser.js"  language="javascript"></script>
<script language="javascript">
function disableEdit(){
	oSubmit=getParentRawObject("oSubmit");
	oSubmit.disabled=true;
}
</script>
<form name="form" method="post" action="dopostupload.php?board=<?php echo $_GET['board']; ?>" enctype="multipart/form-data" onSubmit="disableEdit();" id="oForm">
<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><td class=TableBody2 valign=middle height=28>
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo ATTACHMAXSIZE; ?>">
<input type="file" name="upfile">
<input type="submit" name="Submit" value="�ϴ�">
<font color=#FF0000 >�����ϴ�<?php   echo ATTACHMAXCOUNT-getAttachmentCount(); ?>�����ܴ�С<?php   echo intval((ATTACHMAXTOTALSIZE-$totalsize)/1024) ;?>K</font>��
  ���ƣ�һƪ����<?php   echo ATTACHMAXCOUNT; ?>����<!--һ��<?php   echo $GroupSetting[50]; ?>��,-->ÿ��<?php   echo intval(ATTACHMAXSIZE/1024); ?>K�������ܴ�С<?php   echo intval(ATTACHMAXTOTALSIZE/1024); ?>K
</td></tr>
</table>
</form>
</body>
</html>
<?php 

function getAttachmentCount(){
	global $currentuser;
	global $utmpnum;
	global $totalsize;
	$totalsize=0;
	$filecount=0;
	$attachdir=bbs_getattachtmppath($currentuser["userid"] ,$utmpnum);
	@mkdir($attachdir);

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
			$totalsize+=filesize($file);
			$filecount++;
		}
		fclose($fp);
	}
	return $filecount;
}

function errorQuit($str){
?>
<body topmargin=0 leftmargin=0>
<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><td class=TableBody2 valign=top height=30>
&nbsp; <?php echo $str; ?>
</td></tr>
</body>
</html>
<?php
	exit(0);
}
?>
