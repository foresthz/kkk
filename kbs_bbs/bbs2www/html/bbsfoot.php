<?php
    $setboard=0;
    require("www2-funcs.php");
	login_init();
	page_header("״̬", FALSE);
	if (isset($_GET["total"]))
		$oldtotal = $_GET["total"];
	else
		$oldtotal = 0;
	settype($oldtotal,"integer");

	if (isset($_GET["unread"]))
		$oldunread = $_GET["unread"];
	else
		$oldunread = 0;
	settype($oldunread,"integer");
	
	if (strcmp($currentuser["userid"], "guest")) {
		if (!bbs_getmailnum($currentuser["userid"],$total,$unread, $oldtotal, $oldunread)) {
			$unread = $total = 0;
		}
	} else {
		$unread = false;
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
	if ($unread !== false) {
echo "����[<a href=\"bbsmailbox.php?path=.DIR&title=�ռ���\" target=\"f3\">";
		if ($unread) {
			echo $total . "��(����" . $unread . ")</a>] <bgsound src='sound/newmail.mp3'>";
		} else {
			echo $total . "��</a>] ";
		}
	}
?>
ͣ��[<span id="divStay"></span>]
</div></body>
</html>
