<?
require("funcs.jsp");
$boardname=$_GET["board"];
$filename=$_GET["file"];
$num=$_GET["num"];
$currboard=array ();
$ret=bbs_getboard($boardname,$currboard);
if ($ret==0)
	return error_alert("�����������");
if (($currboard["level"]==0)||($currboard["level"] & BBS_PERM_POSTMASK)||
		($currboard["level"] & BBS_PERM_NOZAP))
	$normalboard=1;
else 
if (!($currentuser["userlevel"]&$currboard["level"]))
	return error_alert("�����������");

if (!valid_filename($filename))
	return error_alert("�����������");
$fullpath=getboardfilename($boardname,$filename);
if (($modifytime=filemtime($fullpath))==FALSE)
	return error_alert("���²����ڻ����ѱ�ɾ��");
if ($nomalboard) { //need to do cache check
	session_cache_limiter("public");
	$oldmidofied=$_SERVER["HTTP_IF_MODIFIED_SINCE"];
	if ($oldmidofied!="") {
		list($dayobweek,$day,$month,$year,$hour,$minute,$second)=
			sscanf($oldmidofied,"%s, %d %d %d %d:%d:%d");
		$oldtime=gmmktime($hour,$minute,$second,$month,$day,$year);
	} else $oldtime=0;
	if ($oldtime==$modifytime) {
		header("HTTP/1.1 304 Not Modified");
		return;
	}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", $modifytime) . " GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s", $modifytime+300) . " GMT");
	header("Cache-Control: max-age=300, must-revalidate");
} else {
	header("Expires: Fri, 12 12 1930 00:00:00 GMT");
	header("Cache-Control: no-cache");
}
?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<link rel="stylesheet" type="text/css" href="/bbs.css">
<center>
ˮľ�廪 -- �����Ķ� [������: <? echo $boardname; ?>]<hr color="green"><table width="610" border="1">
<tr><td>
<pre>
<? 
	bbs_printansifile($fullpath);
?>
</pre></td></tr>
</table><hr>
[<a href="bbsfwd?board=&file=M.1020239019.i0">ת��/�Ƽ�</a>][<a href="bbsccc?board=vote&file=M.1020239019.i0">ת��</a>][<a onclick="return confirm('�����Ҫɾ��������?')" href="bbsdel?board=vote&file=M.1020239019.i0">ɾ������</a>][<a href="bbsedit?board=vote&file=M.1020239019.i0">�޸�����</a>][<a href="bbscon?board=vote&file=M.1020228664.80&num=4100">��һƪ</a>][<a href="bbsdoc?board=vote">��������</a>][<a href="bbscon?board=vote&file=M.1020250273.10&num=4102">��һƪ</a>][<a href="bbspst?board=vote&file=M.1020239019.i0&userid=deliver&title=Re: [֪ͨ] Mj �ٰ�ͶƱ�����ڴ������꼼���������а���᰸ ">������</a>][<a href="bbstfind?board=vote&title=[֪ͨ] Mj �ٰ�ͶƱ�����ڴ������꼼���������а���᰸ ">ͬ�����Ķ�</a>]
</center>
</html>
