<?php
	require("www2-funcs.php");
	login_init();
	
	function show_file( $board , $board , $bid , $article , $articlenum)
	{
?>
<script>
var o = new tconWriter('<?php echo addslashes($board); ?>',<?php echo $article["ID"]; ?>,'<?php echo $article["OWNER"]; ?>',<?php echo $articlenum; ?>);
o.o();
</script>
<div class="article">
<script type="text/javascript" src="jscon.php?bid=<?php echo $bid; ?>&id=<?php echo $article["ID"]; ?>"></script>
</div>
<?php
	}
	
	$gid = $_GET["gid"];
	settype($gid, "integer");
	$start = isset($_GET["start"])?$_GET["start"]:0;
	settype($start, "integer");
	$pno = isset($_GET["pno"])?$_GET["pno"]:0;
	settype($pno, "integer");
	
	if( $gid < 0 ) $gid = 0 ; 
	if($start <= 0 ) $start = $gid;
	$board = $_GET["board"];
	$brdarr = array();
	
	$bid = bbs_getboard($board , $brdarr);
	if($bid == 0)
	{
		html_error_quit("�����������");
	}
	$board = $brdarr["NAME"];
	$board_desc = $brdarr["DESC"];
	$brd_encode = urlencode( $board );
	
	$isnormalboard = bbs_normalboard($board);
	bbs_set_onboard($bid,1);

	$usernum = $currentuser["index"];
	if (!$isnormalboard && bbs_checkreadperm($usernum, $bid) == 0) 
	{
		html_error_quit("�����������");
	}
	
	$haveprev = 0;
	$num = bbs_get_threads_from_gid($bid, $gid, $start , $articles , $haveprev );
	if( $num == 0 )
	{
		html_error_quit("����Ĳ���");
	}
	
	$pagesize = 20;
	$totalpage = ( $num - 1 ) / $pagesize + 1;
	$totalpage = intval( $totalpage );
	if( $pno < 1 || $pno > $totalpage )
		$pno = 1;
	
	$startnum = ( $pno - 1 ) * $pagesize;
	$endnum = $startnum + $pagesize;
	if( $endnum > $num )
		$endnum = $num;
	/*
	 ** Cacheֻ�ж���ͬ��������һƪ���£�û�й˼�ǰ�����µ��޸�  windinsn jan 26 , 2004
	 */ 
	if ($isnormalboard)
	{
		$lastfilename = bbs_get_board_filename($board , $articles[$num - 1]["FILENAME"]);
		if (cache_header("public",@filemtime($lastfilename),300))
			return;
	}
	page_header("ͬ�����Ķ�", "<a href=\"bbsdoc.php?board=".$brd_encode."\">".htmlspecialchars($brdarr["DESC"])."</a>");
?>
<a name="top"></a>
<h1 class="ttit">ͬ�����Ķ���<?php echo htmlspecialchars($articles[0]["TITLE"]); ?> </h1>
<script>
var header = new tconHeader('<?php echo addslashes($board); ?>',<?php echo $gid; ?>,<?php echo $start; ?>,<?php echo $totalpage; ?>,<?php echo $pno; ?>);
header.h();
</script>
<?php
	for( $i = $startnum ; $i < $endnum ; $i ++ )
	{
		show_file( $board , $board , $bid , $articles[$i] , $i + 1);	
		if ($loginok==1&&($currentuser["userid"] != "guest"))
			bbs_brcaddread($board , $articles[$i]["ID"]);
	}
?>
<script>header.h();</script>
<div class="oper">
<?php bbs_add_super_fav ('[ͬ����] '.$articles[0]['TITLE'], 'bbstcon.php?board='.$board.'&gid='.$gid); ?>
| <a href="javascript:history.go(-1)">����</a>
| <a href="bbsdoc.php?board=<?php echo $board; ?>">���� <b><?php echo $board_desc; ?></b> ������</a>
<a href="#top">���ض���</a>
</div>
<?php
	page_footer();
?>
