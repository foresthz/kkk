<?php
session_start();
require("pcadmin_inc.php");
pc_admin_check_permission();
$link = pc_db_connect();

$blogadmin = $_SESSION["blogadmin"];
if( $_GET["act"] == "login" && !session_is_registered($blogadmin) )
{
	$blogadmin = array(
			"user" => $currentuser,
			"logintime" => time(),
			"hostname" => $_SERVER["REMOTE_ADDR"]
			);
	session_register($blogadmin);
	$action = $currentuser[userid]." ��¼Blog����Ա";
	$comment = $currentuser[userid]." �� ".date("Y-m-d H:i:s",$blogadmin["logintime"])." �� ".$_SERVER["REMOTE_ADDR"]." ��¼��վBlog����Ա��";
	pc_logs($link , $action , $comment);
}
if( $_GET["act"] == "logout" && session_is_registered($blogadmin))
{
	$action = $currentuser[userid]." �˳�Blog����Ա��¼";
	$comment = $currentuser[userid]." �� ".date("Y-m-d H:i:s")." �� ".$_SERVER["REMOTE_ADDR"]." �˳���վBlog����Ա��¼����ʱ ".intval((time() - $blogadmin["logintime"]) / 60)." ���ӡ�";
	pc_logs($link , $action , $comment);
	session_unregister($blogadmin);
}

pc_html_init("gb2312",$pcconfig["BBSNAME"]."Blog����Ա��¼");
?>
<br/><br/><br/><br/>
<?php
if( session_is_registered($blogadmin) )
{
?>
<p align="center">
�û�����<font color=red><?php echo $blogadmin["user"]["userid"]; ?></font><br/>
��¼ʱ�䣺<font color=red><?php echo date("Y-m-d H:i:s",$blogadmin["logintime"]); ?></font><br/>
��¼IP��<font color=red><?php echo $blogadmin["hostname"]; ?></font><br/>
<br/><br/><a href="pcadmin_log.php?act=logout">�˳�����Ա��¼</a>
</p>
<?php	
}
else
{
?>
<p align="center"><a href="pcadmin_log.php?act=login">����Ա��¼</a></p>
<?php	
}
pc_db_close($link);
pc_admin_navigation_bar();
html_normal_quit();
?>