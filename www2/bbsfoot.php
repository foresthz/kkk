<?php
    $setboard=0;
    require("www2-funcs.php");
	login_init();
	page_header("״̬", FALSE);
	
	if (strcmp($currentuser["userid"], "guest")) {
		$tn = bbs_mail_get_num($currentuser["userid"]);
		if ($tn) {
			$unread = $tn["newmail"];
			$total = $tn["total"];
		}
	}
?>
<script type="text/javascript">
<!--
	addBootFn(footerStart);
	var stayTime = <?php echo (time()-$currentuinfo["logintime"]); ?>;
	var serverTime = <?php echo (time() + intval(date("Z"))); ?>;
	var hasMail = <?php echo $unread ? "1" : "0"; ?>;
//-->
</script>
<body><div class="footer">ʱ��[<span id="divTime"></span>] ����[<?php echo bbs_getonlinenumber(); ?>]
�ʺ�[<a href="bbsqry.php?userid=<?php echo $currentuser["userid"]; ?>" target="f3"><?php echo $currentuser["userid"]; ?></a>]
<?php
	if (isset($total)) {
echo "����[<a href=\"bbsmailbox.php?path=.DIR&title=�ռ���\" target=\"f3\">";
		if ($unread) {
			echo $total . "��(������)</a>] <bgsound src='sound/newmail.mp3'>";
		} else {
			echo $total . "��</a>] ";
		}
	}
?>
ͣ��[<span id="divStay"></span>]
</div></body>
</html>
