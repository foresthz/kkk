<?php
/*
**  Ϊˮľ�廪blog���붨�Ƶ����봦�����
**  �����������blog�û���$pcconfig["APPBOARD"]����ͨ�棬Email֪ͨ
**  ��ͨ��������Email֪ͨ�û�
**  @windinsn Mar 28 , 2004
*/
require("pcadmin_inc.php");
pc_admin_check_permission();
$link = pc_db_connect();

/*
** ��������������
** type = 0 : ��ͨ��������
**        1 : ������
**        2 : ���ص�����
**        3 : �Ժ󲻵�����
**
*/
function pc_apply_users($link,$type,$start,$pagesize,$userid="",$appname="")
{
	$type = intval($type);
	$order = ($type == 1)?"ASC":"DESC";
	if( $userid || $appname )
		$query = "SELECT * FROM newapply WHERE ( username = '".addslashes($userid)."' OR appname = '".addslashes($appname)."' ) AND management = ".$type." ORDER BY naid ".$order." LIMIT ".$start." , ".$pagesize." ;";
	else
		$query = "SELECT * FROM newapply WHERE management = ".$type." ORDER BY naid ".$order." LIMIT ".$start." , ".$pagesize." ;";
	$result = mysql_query($query,$link);
	$newApp = array();
	while($rows = mysql_fetch_array($result))
		$newApp[] = $rows;
	return $newApp;
}

function pc_add_users($link,$userid,$corpusname,$manual)
{
	global $pcconfig , $currentuser;
	if(!$userid || !$corpusname)
		return FALSE;
	$lookupuser=array ();
	if(bbs_getuser($userid, $lookupuser) == 0 )
		return FALSE;
	
	if(pc_load_infor($link,$userid))
		return FALSE;
	
	if($manual)
	{
		$query = "SELECT username FROM newapply WHERE management != 1 AND management != 3  AND management != 0 AND username = '".addslashes($lookupuser["userid"])."' LIMIT 0 , 1;";	
		$result = mysql_query($query,$link);
		if($rows = mysql_fetch_array($result))
			return FALSE;
	}
	
	//������˿ռ�
	if ($pcconfig["USERFILES"])
	{
	    $userfile_limit = $pcconfig["USERFILESLIMIT"];
	    $userfile_num_limit = $pcconfig["USERFILESNUMLIMIT"];
	}
	else
	    $userfile_limit = $userfile_num_limit = 0;
	
	//����û�
	$query = "INSERT INTO `users` ( `uid` , `username` , `corpusname` , `description` , `theme` , `nodelimit` , `dirlimit` , `createtime` , `style` , `backimage` , `visitcount` , `nodescount` , `logoimage` , `modifytime` , `links` , `htmleditor` , `indexnodechars` , `indexnodes` , `useremail` , `favmode` , `updatetime` , `userinfor` , `pctype` ,`defaulttopic`,`userfile`,`filelimit`) ".
		 "VALUES ('', '".addslashes($lookupuser["userid"])."', '".addslashes($corpusname)."', '".addslashes($corpusname)."' , 'others', '300', '300', NOW( ) , '0', '' , '0', '0', '' , NOW( ) , '', '1', '600', '5', '', '0', NOW( ) , '' , '0' , '�������' , '".$userfile_limit."','".$userfile_num_limit."');";
	if(!mysql_query($query,$link))
	{
		pc_db_close($link);
		exit("MySQL Error: ".mysql_error($link));
	}
	
	//logһ��
	$action = $currentuser["userid"]. " ͨ�� " . $lookupuser["userid"] . " ��BLOG����(www)";
	pc_logs($link , $action , "" , $lookupuser["userid"] );
	
	//���������
	if($manual)
		$query = "INSERT INTO `newapply` ( `naid` , `username` , `appname` , `appself` , `appdirect` , `hostname` , `apptime` , `manager` , `management` ) ".
	 		 "VALUES ('', '".addslashes($lookupuser["userid"])."', '".addslashes($corpusname)."', '', '', '".addslashes($_SERVER["REMOTE_ADDR"])."', NOW( ) , '".addslashes($currentuser["userid"])."' , '0');";
	else
		$query = "UPDATE newapply SET apptime = apptime ,manager = '".addslashes($currentuser["userid"])."',management = '0' WHERE username = '".addslashes($lookupuser["userid"])."' ORDER BY naid DESC LIMIT 1 ;";
	if(!mysql_query($query,$link))
	{
		pc_db_close($link);
		exit("MySQL Error: ".mysql_error($link));
	}
	
	//��������
	$annTitle = "[����] ��׼ ".$lookupuser["userid"]." �� Blog ����";
	$annBody =  "\n\n        �����û� ".$lookupuser["userid"]." ���룬����ˡ����ۺ������ͨ���û�\n".
		    "    Blog ��Blog ���ơ�".$corpusname."����\n\n".
		    "        Blog �󲿷ֹ����ṩ��web ģʽ�£�Blog ���ơ�������\n".
		    "    ������������û���web ��¼�������޸ġ�\n\n";
	$ret = bbs_postarticle($pcconfig["APPBOARD"], preg_replace("/\\\(['|\"|\\\])/","$1",$annTitle), preg_replace("/\\\(['|\"|\\\])/","$1",$annBody), 0 , 0 , 0 , 0);
	if($ret != 0)
		return FALSE;
	//���ż����û�
	$ret = bbs_postmail($lookupuser["userid"],preg_replace("/\\\(['|\"|\\\])/","$1",$annTitle), preg_replace("/\\\(['|\"|\\\])/","$1",$annBody),0,0);
	if($ret < 0)
		return FALSE;
	
	return TRUE;
}

function pc_reject_apply($link,$userid,$applyAgain)
{
	global $currentuser;
	if(!$userid) return FALSE;
	if($applyAgain != 3) $applyAgain = 2;
	$query = "UPDATE newapply SET apptime = apptime ,manager = '".addslashes($currentuser["userid"])."',management = '".$applyAgain."' WHERE username = '".addslashes($userid)."';";
	if(!mysql_query($query,$link))
	{
		pc_db_close($link);
		exit("MySQL Error: ".mysql_error($link));
	}
	
	//logһ��
	$action = $currentuser["userid"]. " ���� " . $userid . " ��BLOG����(www)";
	if($applyAgain == 3)
		$content = $action . "��\n��������������BLOG��";
	else
		$content = "";
	pc_logs($link , $action , $content , $userid );
}

if($_GET["userid"])
{
	if($_GET["act"] == "y")
		pc_add_users($link,$_GET["userid"],$_GET["pcname"],$_GET["manual"]);
	elseif($_GET["act"] == "r")
		pc_reject_apply($link,$_GET["userid"],2);
	elseif($_GET["act"] == "d")
		pc_reject_apply($link,$_GET["userid"],3);
	else
	{
	}
}
$pno = $_GET["pno"];
$pno = intval($pno);
if($pno < 1) $pno = 1;
$type = $_GET["type"];
$type = intval($type);
if(!isset($_GET["type"]) || ($type != 0 && $type != 2 && $type != 3))
	$type = 1;

$pagesize = 20;
$start = ($pno - 1) * $pagesize;

if($_GET["act"] == "q")
	$newApps = pc_apply_users($link,$type,$start,$pagesize,$_GET["userid"],$_GET["pcname"]);
else
	$newApps = pc_apply_users($link,$type,$start,$pagesize);

pc_html_init("gb2312" , $pcconfig["BBSNAME"]."Blog�������û�����");
pc_admin_navigation_bar();
?>
<script language="javascript">
<!--
function bbsconfirm(url,infor){
	if(confirm(infor)){
		window.location.href=url;
		return true;
		}
	return false;
}
-->
</script>
<br />
<p align="center">BLOG�������û�����</p>
<center>
<table cellspacing=0 cellpadding=3 class=t1 width="95%">
<tr>
	<td width="30" class="t2">���</td>
	<td class="t2" colspan="2">BLOG����</td>
</tr>
<?php
	foreach($newApps as $newApp)
	{
		$start ++;
?>
<tr>
	<td class="t3" valign="middle" rowspan="5"><?php echo $start; ?></td>
	<td class="t3" width="25">ID</td>
	<td class="t5"><a href="/bbsqry.php?userid=<?php echo $newApp[username]; ?>"><?php echo $newApp[username]; ?></a></td>
</tr>
<tr>
	<td class="t3">����</td>
	<td class="t5"><?php echo html_format($newApp[appname]); ?></td>
</tr><tr>
	<td class="t3">����</td>
	<td class="t5"><?php echo html_format($newApp[appself],TRUE); ?></td>
</tr>
<tr>
	<td class="t3">�滮</td>
	<td class="t5"><?php echo html_format($newApp[appdirect],TRUE); ?></td>
</tr>
<tr>
	<td class="t3" colspan="2">
	FROM: <?php echo html_format($newApp[hostname]); ?> 
	@ <?php echo time_format($newApp[apptime]); ?>
	&nbsp;
<?php
	if($type == 1)
	{
?>	
	[<a href="#" onclick="bbsconfirm('<?php echo $_SERVER["PHP_SELF"]."?pno=".$pno."&type=".$type."&act=y&userid=".rawurlencode($newApp[username])."&pcname=".rawurlencode($newApp[appname]); ?>' , 'ͨ��<?php echo $newApp[username]; ?>������?')"><font color=red>ͨ��</font></a>]
	[<a href="#" onclick="bbsconfirm('<?php echo $_SERVER["PHP_SELF"]."?pno=".$pno."&type=".$type."&act=r&userid=".rawurlencode($newApp[username]); ?>' , '����<?php echo $newApp[username]; ?>������?')"><font color=red>����</font></a>]
	[<a href="#" onclick="bbsconfirm('<?php echo $_SERVER["PHP_SELF"]."?pno=".$pno."&type=".$type."&act=d&userid=".rawurlencode($newApp[username]); ?>' , '����<?php echo $newApp[username]; ?>������(����������������)?')"><font color=red>���ز�������������</font></a>]
<?php
	}
?>
	</td>
</tr>
<?php		
	}
?>
</table>
<?php
	if($type == 1)
	{
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<b>[�ֶ����]</b>
<input type="hidden" name="act" value="y">
<input type="hidden" name="type" value="1">
<input type="hidden" name="manual" value="1">
�û�����
<input type="text" class="f1" size="20" name="userid">
BLOG����
<input type="text" class="f1" size="20" name="pcname">
<input type="submit" class="f1" value="���">
</form>
<?php
	}
?>	
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<b>[�û���ѯ]</b>
<input type="hidden" name="act" value="q">
<input type="hidden" name="type" value="<?php echo $type; ?>">
�û�����
<input type="text" class="f1" size="20" name="userid">
BLOG����
<input type="text" class="f1" size="20" name="pcname">
<input type="submit" class="f1" value="��ѯ">
</form>	
</center>
<br />
<p align="center">
<?php
	if($pno >1 ) echo "<a href=\"".$_SERVER["PHP_SELF"]."?pno=".($pno-1)."&type=".$type."\">��һҳ</a>\n";
	if(count($newApps) == $pagesize ) echo "<a href=\"".$_SERVER["PHP_SELF"]."?pno=".($pno+1)."&type=".$type."\">��һҳ</a>\n";
?>
</p>
<p align="center">
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>">������</a>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?type=0">��ͨ������</a>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?type=2">���ص�����</a>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?type=3">�������������û�</a>
</p>
<?php
pc_db_close($link);
pc_admin_navigation_bar();
html_normal_quit();
?>