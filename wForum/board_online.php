<?php

require("inc/funcs.php");
require("inc/user.inc.php");

if (!ONBOARD_USERS) exit;

if (!isset($_GET["board"])) exit;
$boardName = $_GET["board"];
$isnormalboard = bbs_normalboard($boardName);
if ($isnormalboard) {
	if (update_cache_header(3, 180)) {
		exit(0);
	}
}
if (isset($_GET["js"])) {
	board_online_js($boardName);
} else {
	board_online($boardName);
}


function board_online_js($board) {
    $users = array();
    $nUsers = bbs_useronboard($board, $users);
    if ($nUsers <= 0) return;
    $id = 0;
    foreach($users as $user) {
    	if ($user["USERID"] == "_wwwguest") break;
    	if ($id == 0) {
    		$str = "&nbsp;&nbsp;[ ���������û���";
		}
		$id++;
		if ($id > 10) {
?>
	function oo() {
		openScript('board_online.php?board=<?php echo $board; ?>',500,400);
	}
<?php
			$str .= "<a href='javascript:oo();'>��������...</a> ";
			break;
		}
		$str .= $user["USERID"]." ";
    }
    if ($id > 0) {
    	$str .= "]";
    }
?>
	var o = getRawObject("onboard_users");
	o.innerHTML = "<?php echo $str; ?>";
<?php
}

function board_online($board) {
	html_init();
    $users = array();
    $nUsers = bbs_useronboard($board, $users);
?>
<body>
<table cellpadding="3" cellspacing="1" align="center" class="TableBorder1">
<tr><th height="25"><?php echo $board; ?> �������û�<?php if ($nUsers > 0) echo " [�� $nUsers λ�û�����]"; ?></th></tr>
<tr><td 
class="TableBody1">
<?php
    if ($nUsers > 0) {
    	$id = 0;
    	$str = "";
    	foreach($users as $user) {
    		if ($user["USERID"] == "_wwwguest") break;
    		$str .= $user["USERID"] . " ";
    		$id++;
    	}
    	if ($nUsers - $id > 0) $str .= "[".($nUsers - $id)." λ web �����û�]";
    } else {
    	$str = "�������߻����޷���ȡ�����û��б�";
    }
    echo $str;
?>
</td></tr>
</table>
<?php
	show_footer(false);
}
?>
