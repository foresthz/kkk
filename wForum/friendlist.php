<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�����б�");

show_nav();

if ($loginok==1) {
?>
<table border="0" width="97%">
<?php
	showUserMailbox();
?>
</table>
<?php
}

head_var($userid."�Ŀ������","usermanagemenu.php",0);

if ($loginok==1) {
	preProcess();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}

if (isErrFounded()) {
	html_error_quit();
}

show_footer();

function preProcess() {
	global $_GET, $currentuser;
	$error = 0; //0: nothing happened; 1: OK; 2: error
	$msg = "";
	
	if (isset($_GET["addfriend"])) {
		$friend = $_GET["addfriend"];
		$ret = bbs_add_friend( $friend ,"" );
		$error = 2;
		if($ret == -1) {
			$msg = "��û��Ȩ���趨���ѻ��ߺ��Ѹ�����������";
		} else if($ret == -2) {
			$msg = "$friend ����������ĺ���������";
		} else if($ret == -3) {
			$msg = "ϵͳ����";
		} else if($ret == -4) {
			$msg = "$friend �û�������";
		} else{
			$msg = "$friend �����ӵ����ĺ���������";
			$error = 1;
		}
	} else if (isset($_GET["delfriend"])) {
		$friend = $_GET["delfriend"];
		$ret = bbs_delete_friend( $friend );
		$error = 2;
		if ($ret == 1) {
			$msg = "��û���趨�κκ���";
		} else if($ret == 2) {
			$msg = "$friend �����Ͳ�����ĺ���������";
		} else if($ret == 3) {
			$msg = "ɾ��ʧ��";
		} else {
			$msg = "$friend �Ѵ����ĺ���������ɾ��";
			$error = 1;			
		}
	}
	if ($error > 0) {
?>
<table cellpadding=3 cellspacing=1 align=center class=tableborder1 style="width:75%">
<tr align=center>
<th width="100%">��̳<?php echo $error==1?"�ɹ�":"����"; ?>��Ϣ
</td>
</tr>
<tr>
<td width="100%" class=tablebody1>
<b><?php echo $error==1?"�����ɹ�":"�������ִ���"; ?>:</b><br><br>
<li><?php echo $msg; ?>
</td></tr>
<tr align=center><td width="100%" class=tablebody2>
<a href="javascript:window.close()">�رմ���</a> 
</td></tr>
</table>
<?php
	}
}

function main() {
	global $currentuser;
?>
<br>
<form action="" method=post id="oForm">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th valign=middle width=30 height=25>���</th>
<th valign=middle width=100>�û��˺�</th>
<th valign=middle width=280>����˵��</th>
<th valign=middle width=150>����</th>
</tr>
<?php
	define("USERSPERPAGE", 20); //ToDo: USERSPERPAGE should always be 20 here because of phplib - atppp
	$total_friends = bbs_countfriends($currentuser["userid"]);
	if( isset( $_GET["start"] ) ){
		$startNum = $_GET["start"];
		if ($startNum >= $total_friends) $startNum = $total_friends - USERSPERPAGE;
		if ($startNum < 0) $startNum = 0;
	} else {
		$startNum = 0;
	}
	$friends_list = bbs_getfriends($currentuser["userid"], $startNum);
    
	$count = count ( $friends_list );

	$i = 0;
	foreach($friends_list as $friend) {
		$i++;
?>
<tr>
<td class=TableBody1 align=center valign=middle>
<?php echo $startNum+$i; ?>
</td>
<td class=TableBody1 align=center valign=middle style="font-weight:normal">
<a href="dispuser.php?id=<?php echo $friend['ID'] ; ?>" target=_blank>
<?php echo $friend['ID'] ?></a>
</td>
<td class=TableBody1 align=left style="font-weight:normal"><a href="dispuser.php?id=<?php echo $friend['ID'] ; ?>" > <?php       echo htmlspecialchars($friend['EXP'],ENT_QUOTES); ?></a>	</td>
<td align=center valign=middle width=150 class=TableBody1>
<a href="friendlist.php?delfriend=<?php echo $friend['ID']; ?>">ɾ������</a> <a href="sendmail.php?receiver=<?php echo $friend['ID']; ?>">�����ʼ�</a> [ToDo]
</td>
</tr>
<?php
	}
?>
<tr>
<td align=right valign=middle colspan=4 class=TableBody2>
<?php
			
		if ($startNum > 0)
		{
			$i = $startNum - USERSPERPAGE;
			if ($i < 0) $i = 0;
			echo ' [<a href=friendlist.php>��һҳ</a>] ';
			echo ' [<a href=friendlist.php?start='.$i.'>��һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[��һҳ]</font>
<?php 
		}
		if ($startNum < $total_friends - USERSPERPAGE)
		{
			$i = $startNum + USERSPERPAGE;
			if ($i >= $total_friends) $i = $total_friends - USERSPERPAGE;
			echo ' [<a href=friendlist.php?start='.$i.'>��һҳ</a>] ';
			echo ' [<a href=friendlist.php?start='.($total_friends - USERSPERPAGE).'>���һҳ</a>] ';
		} else {
?>
<font color=gray>[��һҳ]</font>
<font color=gray>[���һҳ]</font>
<?php
		}
?>
<br>
������ <b><?php echo $total_friends ; ?></b> λ���ѡ�
</td>
</tr>
</table>
</form>
<?php
}


?>
