<?php
	/*
	** some comments actions in personal corp.
	** @id:windinsn  Nov 19,2003
	*/
	require("pcfuncs.php");
	
	if ($loginok != 1)
		html_nologin();
	elseif(!strcmp($currentuser["userid"],"guest"))
	{
		html_init("gb2312");
		html_error_quit("guest ���ܷ�������!");
		exit();
	}
	else
	{
		pc_html_init("gb2312","�����ļ�");		
		$nid = (int)($_GET["nid"]);
		$act = $_GET["act"];
		$cid = (int)($_GET["cid"]);
		
		$link =	pc_db_connect();
		$query = "SELECT `access`,`uid` FROM nodes WHERE `nid` = '".$nid."' AND `type` != '1' ;";
		$result = mysql_query($query,$link);
		$rows = mysql_fetch_array($result);
		mysql_free_result($result);
		
		if(!$rows)
		{
			html_error_quit("�����۵����²�����!");
			exit();
		}
		
		$uid = $rows[uid];
				
		if($rows[access] != 0)
		{
			$query = "SELECT `username` FROM users WHERE uid = '".$rows[uid]."' LIMIT 0 , 1 ;";
			$result = mysql_query($query,$link);
			$rows1 = mysql_fetch_array($result);
			if(!$rows1)
			{
				mysql_free_result($result);
				html_error_quit("�Բ�����Ҫ�鿴�ĸ����ļ�������");
				exit();
			}
			
			$pc = array(
					"USER" => $rows1[username],
					"UID" => $rows[uid]
					);
			mysql_free_result($result);
			
			if(strtolower($currentuser["userid"]) == strtolower($pc["USER"]))
				$pur = 2;
			elseif(pc_is_friend($currentuser["userid"],$pc["USER"]))
				$pur = 1;
			else
				$pur = 0;
			
			if( $rows[access] == 1 && $pur < 1)
			{
				html_error_quit("�Բ���ֻ�к����б��е��û����ܲ鿴����������!");
				exit();
			}
			if( $rows[access] > 1 && $pur < 2 )
			{
				html_error_quit("�Բ����ļ������߲��ܲ鿴������!");
				exit();
			}
		}
		
		if($act == "pst")
		{
?>
<script language="Javascript1.2"><!-- // load htmlarea
_editor_url = "htmlarea/";                     // URL to htmlarea files
var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
if (win_ie_ver >= 5.5) {
 document.write('<scr' + 'ipt src="' +_editor_url+ 'editor.js"');
 document.write(' language="Javascript1.2"></scr' + 'ipt>');  
} else { document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>'); }
// --></script> 
<script language="JavaScript1.2" defer>
editor_generate('combody');
</script>
<br><center>		
<form name="postform" action="pccom.php?act=add&nid=<?php echo $nid; ?>" method="post" onsubmit="if(this.subject.value==''){alert('��������������!');return false;}">
<table cellspacing="0" cellpadding="5" width="90%" border="0" class="t1">
<tr>
	<td class="t2">��������</td>
</tr>
<tr>
	<td class="t8">
	����
	<input type="text" name="subject" size="100" class="f1">
	</td>
</tr>
<tr>
	<td class="t13">�������</td>
</tr>
<tr>
	<td class="t5"><?php @require("emote.html"); ?></td>
</tr>
<tr>
	<td class="t11">����</td>
</tr>
<tr>
	<td class="t8"><textarea name="combody" class="f1" cols="100" rows="20" id="combody"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.postform.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.postform.submit()' wrap="physical">
	<!--NoWrap-->
	</textarea></td>
</tr>
<tr>
	<td class="t2">
	<input type="submit" value="��������" class="b1">
	<input type="button" value="������ҳ" class="b1" onclick="history.go(-1)">
</tr>
</table>
</form></center>	
<?php			
		}
		elseif($act == "add")
		{
			if(!$_POST["subject"])
			{
				html_error_quit("���������۱���!");
				exit();
			}
			$emote = (int)($_POST["emote"]);
			$query = "INSERT INTO `comments` ( `cid` , `nid` , `uid` , `emote` , `hostname` , `username` , `subject` , `created` , `changed` , `body` )". 
				"VALUES ('', '".$nid."', '".$uid."', '".$emote."' , '".$_SERVER["REMOTE_ADDR"]."', '".$currentuser["userid"]."', '".addslashes($_POST["subject"])."', '".date("YmdHis")."' , '".date("YmdHis")."', '".addslashes($_POST["combody"])."');";
			mysql_query($query,$link);
			$query = "UPDATE nodes SET commentcount = commentcount + 1 WHERE `nid` = '".$nid."' ; ";
			mysql_query($query,$link);
?>
<script language="javascript">
window.location.href="pccon.php?id=<?php echo $uid; ?>&nid=<?php echo $nid; ?>";
</script>
<?php
		}
		
		pc_db_close($link);
		html_normal_quit();
	} 
?>