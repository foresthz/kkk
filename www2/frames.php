<?php
	require("www2-funcs.php");
	login_init();
	$isguest = ($currentuser["userid"] == "guest"); //������Զ��� TRUE
	cache_header("nocache");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>��ӭݰ��<?php echo BBS_FULL_NAME; ?></title>
</head>
<frameset name=mainframe id=mainframe frameborder=0 border=0 cols="167,11,*">
	<frame name=menu id=menu noresize="true" marginwidth=0 marginheight=0 src="bbsleft.php">
	<frame scrolling=no noresize="true" name=toogle marginwidth=0 marginheight=0 src="wtoogle.html">
	<frameset name="viewfrm" rows="0,*,20" id="viewfrm">
		<frame scrolling=no noresize="true" marginwidth=4 marginheight="0" name="fmsg" src="<?php if (!$isguest) echo "bbsgetmsg.php"; ?>">
		<frame marginwidth=0 marginheight=0 name="f3" src="<?php
			echo (isset($_GET["mainurl"])) ? htmlspecialchars($_GET["mainurl"],ENT_QUOTES) : MAINPAGE_FILE; ?>">
		<frame scrolling=no noresize="true" marginwidth=4 marginheight="1" name="f4" src="bbsfoot.php">
	</frameset>
</frameset>
</html>
